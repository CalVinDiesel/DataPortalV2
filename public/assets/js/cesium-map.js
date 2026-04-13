let cesiumViewer = null;

// --- Initialize Cesium viewer (2D only) ---
// Exposes window.cesiumViewer for cesium-map-markers.js (pins, thumbnails, hover cards).
// Markers script uses the same document to resolve image URLs, so no extra link is needed.
function initializeCesium(containerId = 'cesiumContainer') {
    if (typeof Cesium === 'undefined') {
        console.error('Cesium is not loaded');
        return null;
    }
    if (cesiumViewer && cesiumViewer.scene) {
        return cesiumViewer;
    }

    // Use OpenStreetMap so no Cesium ion token is required for the overview map
    Cesium.Ion.defaultAccessToken = '';

    // Build imagery provider flexibly: support both new (UrlTemplateImageryProvider)
    // and old (OpenStreetMapImageryProvider) Cesium API versions automatically.
    function buildOsmImageryProvider() {
        var osmTileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmCredit = '© OpenStreetMap contributors';
        var subdomains = ['a', 'b', 'c'];

        // Cesium >= 1.104: use UrlTemplateImageryProvider wrapped in ImageryLayer
        if (typeof Cesium.UrlTemplateImageryProvider === 'function') {
            try {
                return {
                    useBaseLayer: true,
                    provider: new Cesium.UrlTemplateImageryProvider({
                        url: osmTileUrl,
                        subdomains: subdomains,
                        credit: osmCredit
                    })
                };
            } catch (e) {
                console.warn('[CesiumMap] UrlTemplateImageryProvider failed, trying legacy:', e);
            }
        }

        // Cesium < 1.104 fallback: OpenStreetMapImageryProvider
        if (typeof Cesium.OpenStreetMapImageryProvider === 'function') {
            try {
                return {
                    useBaseLayer: false,
                    provider: new Cesium.OpenStreetMapImageryProvider({
                        url: 'https://tile.openstreetmap.org/'
                    })
                };
            } catch (e) {
                console.warn('[CesiumMap] OpenStreetMapImageryProvider failed:', e);
            }
        }

        console.error('[CesiumMap] No imagery provider available.');
        return null;
    }

    var imageryResult = buildOsmImageryProvider();

    // Viewer options — no token-gated features used
    var viewerOptions = {
        animation: false,
        baseLayerPicker: false,
        fullscreenButton: false,
        vrButton: false,
        geocoder: false,
        homeButton: false,
        infoBox: false,
        sceneModePicker: false,
        selectionIndicator: false,
        timeline: false,
        navigationHelpButton: false,
        navigationInstructionsInitiallyVisible: false,
        sceneMode: Cesium.SceneMode.SCENE2D,
        
        // Performance optimizations to reduce [Violation] 'requestAnimationFrame' handler took X ms warnings
        targetFrameRate: 30,
        requestRenderMode: true,
        maximumRenderTimeChange: Infinity,
        useBrowserRecommendedResolution: true
    };

    // Attach imagery provider using the correct API for the detected Cesium version
    if (imageryResult) {
        if (imageryResult.useBaseLayer && typeof Cesium.ImageryLayer === 'function') {
            viewerOptions.baseLayer = new Cesium.ImageryLayer(imageryResult.provider);
        } else {
            viewerOptions.imageryProvider = imageryResult.provider;
        }
    }

    try {
        cesiumViewer = new Cesium.Viewer(containerId, viewerOptions);
    } catch (err) {
        console.error('[CesiumMap] Viewer creation failed:', err);
        return null;
    }

    cesiumViewer.camera.setView({
        destination: Cesium.Cartesian3.fromDegrees(116.46905, 5.63444, 710000)
    });

    cesiumViewer.scene.requestRenderMode = true;
    cesiumViewer.scene.maximumRenderTimeChange = Infinity;
    
    // Disable Fast Approximate Anti-Aliasing to shave off roughly 15-20ms of frame time, minimizing 'Violation' warnings
    if (cesiumViewer.scene.postProcessStages && cesiumViewer.scene.postProcessStages.fxaa) {
        cesiumViewer.scene.postProcessStages.fxaa.enabled = false;
    }

    window.cesiumViewer = cesiumViewer;
    return cesiumViewer;
}

// Use addEventListener so it never conflicts with other scripts using window.onload
window.addEventListener('load', function () {
    initializeCesium();
});