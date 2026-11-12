<?php

function kk_unconfirmed_items(): array {
    return [
        [
            'item' => 'Hours',
            'demo' => 'Monday—Sunday, 7:00 AM—2:00 PM',
            'note' => 'One source: General settings. The homepage, footer, and Visit Us all read it. The printed menu PDF says 2:30 PM instead.',
        ],
        [
            'item' => 'Street address',
            'demo' => 'Empty',
            'note' => 'The source has no street address. The field stays hidden until the restaurant provides one.',
        ],
        [
            'item' => 'Phone',
            'demo' => 'Empty',
            'note' => 'The source has no phone number. It is hidden until filled in.',
        ],
        [
            'item' => 'Directions',
            'demo' => 'Google search for Kenny\'s Korner',
            'note' => 'Used because no street address was in the source. Replace the URL after the address is confirmed.',
        ],
        [
            'item' => 'History',
            'demo' => 'Timeline copy from the demo: The Hut, the 1990 rename, and today',
            'note' => 'Kept so the page matches the demo. Confirm it before treating the wording as the official history.',
        ],
    ];
}

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
    $store_email = function_exists('kk_store_email') ? kk_store_email() : '';
    $checks[] = [
        'label' => 'Restaurant email',
        'status' => $store_email !== '' ? 'OK' : 'Fix',
        'detail' => $store_email !== '' ? 'Saved in General settings. Catering requests can be sent.' : 'Missing. Add it in General settings. The catering form stays unavailable.',
    ];
    $upload = wp_max_upload_size();
    $checks[] = [
        'label' => 'Upload limit',
        'status' => $upload >= 32 * MB_IN_BYTES ? 'OK' : 'Check',
        'detail' => 'Web request: upload_max_filesize ' . ini_get('upload_max_filesize') . ', post_max_size ' . ini_get('post_max_size') . ', effective ' . size_format($upload) . '. The seed zip is about 48 MB. ZipArchive does not raise this limit. Memory limit: ' . ini_get('memory_limit') . '.',
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
        'detail' => class_exists('ZipArchive') ? 'ZipArchive can unpack a zip this web request has already accepted. It does not raise the upload limit.' : 'ZipArchive is missing. Unzip the seed zip by SFTP and leave the inner seed folder in place.',
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
