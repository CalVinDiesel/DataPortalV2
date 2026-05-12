<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Hub Viewer | {{ $id }}</title>
    

    @viteReactRefresh
    @vite(['resources/js/viewer/main.tsx', 'resources/css/app.css'])
    <style>
        body { margin: 0; padding: 0; overflow: hidden; background-color: #000; }
        #root { width: 100vw; height: 100vh; }
    </style>
</head>
<body>
    <div id="root"></div>

    @guest
    <div style="position: absolute; top: 20px; right: 20px; z-index: 9999;">
        <a href="{{ route('request_access') }}" style="
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #696cff 0%, #3f51b5 100%);
            color: #fff;
            text-decoration: none;
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(105, 108, 255, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-size: 14px;
        " onmouseover="this.style.transform='scale(1.05)'; this.style.box_shadow='0 6px 20px rgba(105, 108, 255, 0.6)';" onmouseout="this.style.transform='scale(1)'; this.style.box_shadow='0 4px 15px rgba(105, 108, 255, 0.4)';">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            Request Access
        </a>
    </div>
    @endguest
    
    <script>
        // Provide the Laravel route context to the React application
        window.TemaDataPortal_API_BASE = window.location.origin;
        
        // If the React app needs the ID from a global variable as fallback
        window.__viewerId = "{{ $id }}";

        // The React app expects model ID in query param ?model=ID
        // We can force a redirect if missing, or handle it in React.
        // Let's ensure the URL is clean.
        if (!window.location.search.includes('model=')) {
            const url = new URL(window.location.href);
            url.searchParams.set('model', "{{ $id }}");
            window.history.replaceState({}, '', url);
        }
    </script>
</body>
</html>