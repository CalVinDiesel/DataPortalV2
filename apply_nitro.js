const fs = require('fs');
const file = 'resources/views/portal/upload-data.blade.php';
let content = fs.readFileSync(file, 'utf8');

const startMarker = 'const UPLOADER_WORKER_CODE';
const endMarker = '    async function finalizeOnServer';

const startIndex = content.indexOf(startMarker);
const endIndex = content.indexOf(endMarker);

if (startIndex === -1 || endIndex === -1) {
    console.error('Markers not found');
    process.exit(1);
}

const replacement = \    async function startFinalUpload() {
        const lat = document.getElementById('latitude').value, 
              lng = document.getElementById('longitude').value,
              title = document.getElementById('projectTitle').value;
              
        if (!lat || !title) return alert("Please set location and title.");
        if (pendingUploadFiles.length === 0) return alert("Nothing selected.");

        document.getElementById('uploadProgressContainer').style.display = 'block';
        const st = document.getElementById('uploadStatusText'), 
              pb = document.getElementById('uploadProgressBar'), 
              pt = document.getElementById('uploadPercentageText'), 
              btn = document.getElementById('submitBtn');
        btn.disabled = true;

        const totalSizeBytes = pendingUploadFiles.reduce((acc, f) => acc + f.size, 0);
        const projectID = document.getElementById('projectID').value;
        const uploadId = 'up_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
        const csrfToken = '{{ csrf_token() }}';

        st.textContent = "Preparing Nitro Stream...";

        // 🏎️ METEOR STREAMING: Main-thread async avoids Worker clone freezes
        const CHUNK_SIZE = 25 * 1024 * 1024;
        let overallSent = 0;
        let lastPaintTime = Date.now();

        const updateUI = (statusText) => {
            return new Promise(resolve => {
                const now = Date.now();
                if (now - lastPaintTime > 50 || statusText === "Finalizing") { 
                    requestAnimationFrame(() => {
                        let p = Math.round((overallSent / totalSizeBytes) * 100);
                        if (p > 100) p = 100;
                        pb.style.width = p + '%'; pt.textContent = p + '%';
                        st.textContent = statusText;
                        lastPaintTime = Date.now();
                        resolve();
                    });
                } else { resolve(); }
            });
        };

        try {
            for (let i = 0; i < pendingUploadFiles.length; i++) {
                const file = pendingUploadFiles[i];
                const relPath = file.webkitRelativePath || file.name;

                if (file.size < CHUNK_SIZE) {
                    const fd = new FormData();
                    fd.append('uploadId', uploadId); fd.append('projectID', projectID);
                    fd.append('filename', relPath); fd.append('file', file);
                    await fetch('/api/upload/direct', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd });
                    overallSent += file.size;
                } else {
                    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                    for (let c = 0; c < totalChunks; c++) {
                        const start = c * CHUNK_SIZE, end = Math.min(start + CHUNK_SIZE, file.size);
                        const fd = new FormData();
                        fd.append('uploadId', uploadId); fd.append('filename', relPath);
                        fd.append('chunkIndex', c); fd.append('totalChunks', totalChunks);
                        fd.append('projectID', projectID); fd.append('chunk', file.slice(start, end));
                        await fetch('/api/upload/chunk', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd });
                        overallSent += (end - start);
                        await updateUI('Slicing Heavy Data ' + (i+1) + '/' + pendingUploadFiles.length);
                    }
                    const afd = new FormData();
                    afd.append('uploadId', uploadId); afd.append('filename', relPath);
                    afd.append('totalChunks', totalChunks); afd.append('projectID', projectID);
                    await fetch('/api/upload/assemble-file', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: afd });
                }
                await updateUI('Ingesting ' + (i+1) + '/' + pendingUploadFiles.length);
                if (i % 10 === 0) await new Promise(r => setTimeout(r, 0)); 
            }
            overallSent = totalSizeBytes;
            await updateUI('Finalizing');
            finalizeOnServer(uploadId, projectID, title, totalSizeBytes, lat, lng);
        } catch (e) {
            console.error(e);
            alert("Nitro Stream Interrupted. Try again.");
            btn.disabled = false;
        }
    }

\;

content = content.substring(0, startIndex) + replacement + content.substring(endIndex);
fs.writeFileSync(file, content);
console.log('Successfully injected Batch-Nitro logic.');
