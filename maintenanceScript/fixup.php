<?php
$dir = 'resources/views/admin';
foreach(glob($dir . '/*.blade.php') as $file) {
    if(!is_file($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('href="index.html"', 'href="{{ route(\'admin_dashboard\') }}"', $content);
    $content = str_replace('href="add-3d-model.html"', 'href="{{ route(\'admin.add_3d_model\') }}"', $content);
    $content = str_replace('href="manage-map-pins.html"', 'href="{{ route(\'admin.manage_map_pins\') }}"', $content);
    $content = str_replace('href="manage-showcase.html"', 'href="{{ route(\'admin.manage_showcase\') }}"', $content);
    $content = str_replace('href="client-uploads.html"', 'href="{{ route(\'admin.client_uploads\') }}"', $content);
    $content = str_replace('href="manage-users.html"', 'href="{{ route(\'admin.manage_users\') }}"', $content);
    
    // Fix Landing Page URL in the sidebar
    $content = preg_replace('/href="[\.\/]*front-pages\/\{\{\s*route\(\'landing\'\)\s*\}\}"/', 'href="{{ route(\'landing\') }}"', $content);
    
    // Fix Auth Script Redirection URLs
    $content = str_replace('window.location.href = \'/html/front-pages/{{ route(\'landing\') }}?error=admin_only\';', 'window.location.href = \'{{ route(\'landing\') }}?error=admin_only\';', $content);
    $content = str_replace('window.location.href = \'/html/front-pages/{{ route(\'landing\') }}\';', 'window.location.href = \'{{ route(\'landing\') }}\';', $content);

    file_put_contents($file, $content);
}
echo "Replaced all HTML static links in admin blade files!\n";
