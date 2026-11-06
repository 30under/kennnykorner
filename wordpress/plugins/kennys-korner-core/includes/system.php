<?php

function kk_system_checks(): array {
    $checks = [];
    $php = PHP_VERSION;
    $checks[] = [
        'label' => 'PHP',
        'status' => version_compare($php, '8.0', '>=') ? 'OK' : 'Fix',
        'detail' => $php . (version_compare($php, '8.0', '>=') ? '' : ' — Kenny\'s Korner needs PHP 8.0 or newer.'),
    ];
    $wp = isset($GLOBALS['wp_version']) ? (string) $GLOBALS['wp_version'] : '';
    $checks[] = [
        'label' => 'WordPress',
        'status' => version_compare($wp, '6.4', '>=') ? 'OK' : 'Check',
        'detail' => $wp !== '' ? $wp : 'Unknown',
    ];
    $permalinks = (string) get_option('permalink_structure');
    $checks[] = [
        'label' => 'Permalinks',
        'status' => $permalinks !== '' ? 'OK' : 'Fix',
        'detail' => $permalinks !== '' ? $permalinks : 'Pretty permalinks are off. The importer turns them on when they are still the default.',
    ];
    $home = function_exists('kk_find_page_id') ? kk_find_page_id('home') : 0;
    $front = (int) get_option('page_on_front');
    $checks[] = [
        'label' => 'Homepage',
        'status' => $home && $front === $home && get_option('show_on_front') === 'page' ? 'OK' : 'Check',
        'detail' => $home ? 'Home page ID ' . $home . '.' : 'Home has not been created.',
    ];
    $missing = [];
    foreach (array_keys(kk_default_content()['pages']) as $key) {
        if (!kk_find_page_id($key)) {
            $missing[] = $key;
        }
    }
    $checks[] = [
        'label' => 'Pages',
        'status' => $missing === [] ? 'OK' : 'Check',
        'detail' => $missing === [] ? 'Home, Menu, Gallery, Our Story, and Visit Us exist.' : 'Missing: ' . implode(', ', $missing),
    ];
    $upload = wp_max_upload_size();
    $checks[] = [
        'label' => 'Upload limit',
        'status' => $upload >= 32 * MB_IN_BYTES ? 'OK' : 'Check',
        'detail' => size_format($upload) . '. The kitchen video is about 20 MB and the full seed zip is about 48 MB. Use SFTP if the zip is too large. Memory limit: ' . ini_get('memory_limit') . '.',
    ];
    $image_editor = extension_loaded('imagick') || extension_loaded('gd');
    $checks[] = [
        'label' => 'Image processing',
        'status' => $image_editor ? 'OK' : 'Check',
        'detail' => $image_editor ? 'GD or Imagick is available for future uploads. Demo images already include WebP previews.' : 'Neither GD nor Imagick is available. New uploads may not get resized copies. The imported WebP previews still work.',
    ];
    $checks[] = [
        'label' => 'Zip support',
        'status' => class_exists('ZipArchive') ? 'OK' : 'Check',
        'detail' => class_exists('ZipArchive') ? 'Zip uploads can be unpacked.' : 'ZipArchive is missing. Copy the seed folder by SFTP instead.',
    ];
    $seed = kk_seed_dir();
    $checks[] = [
        'label' => 'Seed files',
        'status' => $seed !== '' ? 'OK' : 'Needed for import',
        'detail' => $seed !== '' ? $seed : 'Upload the seed zip or place it in wp-content/uploads/kennys-korner-seed/.',
    ];
    $theme = wp_get_theme();
    $checks[] = [
        'label' => 'Theme',
        'status' => $theme->get_stylesheet() === 'kennys-korner' ? 'OK' : 'Check',
        'detail' => 'Active theme: ' . $theme->get('Name'),
    ];
    $checks[] = [
        'label' => 'Imported',
        'status' => get_option('kk_imported') ? 'OK' : 'Not yet',
        'detail' => get_option('kk_imported') ? 'Demo content has been imported at least once.' : 'Import has not been run. Activating the plugin does not import content.',
    ];
    $subdir = trim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');
    $checks[] = [
        'label' => 'Site address',
        'status' => 'OK',
        'detail' => home_url('/') . ($subdir !== '' ? ' Installed in a subdirectory.' : ''),
    ];
    return $checks;
}
