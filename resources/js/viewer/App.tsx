import { BrowserRouter, Routes, Route } from 'react-router-dom';
import DiscoveryPage from './pages/DiscoveryPage';
import './index.css';

function App() {
    console.log('🏗️ App: Component function called');
    return (
        <BrowserRouter>
            <Routes>
                {/* 
                  When loaded via Laravel route /viewer/{id}, 
                  the ID is usually passed via query param ?model=ID by loading-3d.blade.php
                */}
                <Route path="*" element={<DiscoveryPage />} />
            </Routes>
        </BrowserRouter>
    );
}

export default App;
