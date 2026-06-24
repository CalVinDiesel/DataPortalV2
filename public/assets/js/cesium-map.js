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
        sceneMode: typeof window.cesiumDefaultSceneMode !== 'undefined' ? window.cesiumDefaultSceneMode : Cesium.SceneMode.SCENE2D,
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

    // Lock camera if initializing in 3D to simulate 2D view
    if (viewerOptions.sceneMode === Cesium.SceneMode.SCENE3D) {
        var controller = cesiumViewer.scene.screenSpaceCameraController;
        controller.enableTilt = false;
        controller.enableLook = false;
        controller.rotateEventTypes = [Cesium.CameraEventType.LEFT_DRAG];
        controller.zoomEventTypes = [Cesium.CameraEventType.WHEEL, Cesium.CameraEventType.PINCH];
        controller.tiltEventTypes = [];
        controller.lookEventTypes = [];
    }

    // High-visibility lighting
    cesiumViewer.scene.globe.enableLighting = false; // Off for 2D dashboard clarity
    cesiumViewer.scene.highDynamicRange = true;

    // Zoom to Sabah
    var setViewOptions = {
        destination: Cesium.Cartesian3.fromDegrees(116.46905, 5.63444, 710000)
    };
    if (viewerOptions.sceneMode === Cesium.SceneMode.SCENE3D) {
        setViewOptions.orientation = {
            heading: 0.0,
            pitch: Cesium.Math.toRadians(-90),
            roll: 0.0
        };
    }
    cesiumViewer.camera.setView(setViewOptions);

    window.cesiumViewer = cesiumViewer;
    return cesiumViewer;
}

window.addEventListener('load', function () {
    if (document.getElementById('cesiumContainer')) {
        initializeCesium();
    }
});