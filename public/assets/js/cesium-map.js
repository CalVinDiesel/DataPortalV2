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

    // Set an empty token to avoid any Ion service conflicts
    // If you have a real token, you can paste it here later
    if (typeof Cesium.Ion !== 'undefined') {
        Cesium.Ion.defaultAccessToken = '';
    }

    // Use the official high-quality satellite imagery
    var provider = Cesium.createWorldImagery ? Cesium.createWorldImagery() : new Cesium.OpenStreetMapImageryProvider({
        url: 'https://tile.openstreetmap.org/'
    });

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
        sceneMode: Cesium.SceneMode.SCENE2D,
        requestRenderMode: true,
        useDefaultRenderLoop: true,
        baseLayer: new Cesium.ImageryLayer(new Cesium.OpenStreetMapImageryProvider({
            url: 'https://tile.openstreetmap.org/'
        }))
    };

    try {
        cesiumViewer = new Cesium.Viewer(containerId, viewerOptions);
    } catch (err) {
        console.error('[CesiumMap] Viewer creation failed:', err);
        return null;
    }

    // High-visibility lighting
    cesiumViewer.scene.globe.enableLighting = false; // Off for 2D dashboard clarity
    cesiumViewer.scene.highDynamicRange = true;

    // Zoom to Sabah
    cesiumViewer.camera.setView({
        destination: Cesium.Cartesian3.fromDegrees(116.46905, 5.63444, 710000)
    });

    window.cesiumViewer = cesiumViewer;
    return cesiumViewer;
}

window.addEventListener('load', function () {
    if (document.getElementById('cesiumContainer')) {
        initializeCesium();
    }
});