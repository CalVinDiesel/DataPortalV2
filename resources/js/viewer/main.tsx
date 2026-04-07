// main.tsx
import './cesium-wrapper'; // Ensure the wrapper sets the global first
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.tsx';

if (typeof window !== 'undefined') {
    // USE CDN for base assets (Textures, Skybox, etc) to ensure they never 404 in dev
    (window as any).CESIUM_BASE_URL = 'https://cesium.com/downloads/cesiumjs/releases/1.114/Build/Cesium/';
}

console.log('🚀 main.tsx: Cesium global initialized');

const rootElement = document.getElementById('root');
if (rootElement) {
    createRoot(rootElement).render(
        <StrictMode>
            <App />
        </StrictMode>,
    );
    console.log('✅ main.tsx: App mounted');
}
