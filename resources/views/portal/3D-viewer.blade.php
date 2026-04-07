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