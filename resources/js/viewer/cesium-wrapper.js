import * as CesiumLib from '../../../node_modules/cesium/Build/Cesium/index.js';

// Expose Cesium to window for legacy libraries like cesium-navigation-es6
if (typeof window !== 'undefined') {
    window.Cesium = CesiumLib;
}

export * from '../../../node_modules/cesium/Build/Cesium/index.js';
export default CesiumLib;

export const defined = function(value) {
    return value !== undefined && value !== null;
};