const fs = require('fs');
const file = 'resources/views/portal/upload-data.blade.php';
let content = fs.readFileSync(file, 'utf8');

const startMarker = '    const SCANNER_WORKER_CODE = ';
const endMarker = '    function finishScanPhase() {';

const startIndex = content.indexOf(startMarker);
const endIndex = content.indexOf(endMarker);

if (startIndex === -1 || endIndex === -1) {
    console.error('Markers not found');
    process.exit(1);
}

const replacement = \    async function loadScannerDeps() {
        if (!window.JSZip) {
            await new Promise((r) => { const s = document.createElement('script'); s.src = 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js'; s.onload = r; document.head.appendChild(s); });
        }
        if (!window.EXIF) {
            await new Promise((r) => { const s = document.createElement('script'); s.src = 'https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.min.js'; s.onload = r; document.head.appendChild(s); });
        }
    }

    function extractExifFast(blob) {
        return new Promise(resolve => {
            EXIF.getData(blob, function() {
                const lat = EXIF.getTag(this, "GPSLatitude");
                const lng = EXIF.getTag(this, "GPSLongitude");
                const latRef = EXIF.getTag(this, "GPSLatitudeRef") || "N";
                const lngRef = EXIF.getTag(this, "GPSLongitudeRef") || "E";
                if (lat && lng) {
                    const dLat = (lat[0] + (lat[1]/60) + (lat[2]/3600)) * (latRef == "N" ? 1 : -1);
                    const dLng = (lng[0] + (lng[1]/60) + (lng[2]/3600)) * (lngRef == "E" ? 1 : -1);
                    resolve([dLat, dLng]);
                } else resolve(null);
            });
        });
    }

    async function startScan() {
        if (pendingUploadFiles.length === 0) return;
        
        document.getElementById('loadStep1').classList.add('completed');
        document.getElementById('loadStep2').classList.add('active');
        const scanDisplay = document.getElementById('scanCount');
        
        await loadScannerDeps();

        // 🏎️ METEOR SCANNER: Reads only the first 128KB headers for extreme speed
        let processed = 0;
        let lastYield = Date.now();

        for (let i = 0; i < pendingUploadFiles.length; i++) {
            const file = pendingUploadFiles[i];
            
            if (file.name.match(/\\\\.zip$/i)) {
                try {
                    const zip = await JSZip.loadAsync(file);
                    const entries = Object.values(zip.files).filter(entry => !entry.dir && entry.name.match(/\\\\.(jpg|jpeg|png)$/i)).slice(0, 50);
                    for (const entry of entries) {
                        const blob = await entry.async("blob");
                        const coords = await extractExifFast(blob);
                        if (coords) flightPathPoints.push(coords);
                    }
                } catch(e) {}
            } else if (file.name.match(/\\\\.(jpg|jpeg|png)$/i)) {
                // Slice file to grab only metadata headers (insanely fast, avoids memory bloat)
                const metadataSlice = file.slice(0, 131072);
                const coords = await extractExifFast(metadataSlice);
                if (coords) flightPathPoints.push(coords);
            }
            
            processed++;
            
            // Yield UI to maintain Green INP score
            if (Date.now() - lastYield > 30) {
                await new Promise(r => requestAnimationFrame(r));
                scanDisplay.textContent = processed;
                lastYield = Date.now();
            }
        }
        
        scanDisplay.textContent = processed;
        finishScanPhase();
    }

\;

content = content.substring(0, startIndex) + replacement + content.substring(endIndex);
fs.writeFileSync(file, content);
console.log('Successfully injected Meteor Scanner logic.');
