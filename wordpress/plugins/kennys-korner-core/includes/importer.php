<?php

function kk_seed_dir(): string {
    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir']) . 'kennys-korner-seed';
    $base = (string) apply_filters('kk_seed_directory', $base);
    if (kk_is_seed_dir($base)) {
        return $base;
    }
    if (is_dir($base)) {
        $entries = scandir($base);
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $base . '/' . $entry;
                if (is_dir($path) && kk_is_seed_dir($path)) {
                    return $path;
                }
            }
        }
    }
    return '';
}

function kk_is_seed_dir(string $dir): bool {
    return is_file($dir . '/images/kennys-korner-logo.jpg') || is_file($dir . '/menu/kennys-korner-official-menu.pdf');
}

function kk_media_manifest(): array {
    $path = KK_CORE_DIR . 'seed/media-manifest.json';
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data['assets'] ?? null) ? $data['assets'] : [];
}

final class KK_Importer {
    public static function init(): void {
        add_action('wp_ajax_kk_import', [self::class, 'ajax']);
        add_action('admin_post_kk_upload_seed', [self::class, 'upload_seed']);
        add_action('admin_post_kk_delete_content', [self::class, 'delete_content']);
    }

    public static function ajax(): void {
        check_ajax_referer('kk_import', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'You do not have permission to import content.'], 403);
        }
        $step = isset($_POST['step']) ? sanitize_key(wp_unslash($_POST['step'])) : 'batch';
        if ($step === 'start') {
            $overwrite = !empty($_POST['overwrite']);
            $confirm = isset($_POST['confirm']) ? sanitize_text_field(wp_unslash($_POST['confirm'])) : '';
            if ($overwrite && $confirm !== 'REPLACE DEMO CONTENT') {
                wp_send_json_error(['message' => 'Replacement was not confirmed. Type REPLACE DEMO CONTENT or leave replacement off.']);
            }
            $manifest = kk_media_manifest();
            if ($manifest === []) {
                wp_send_json_error(['message' => 'The media manifest is missing from the plugin.']);
            }
            update_option('kk_import_job', [
                'overwrite' => $overwrite,
                'queue' => array_values(array_map(static fn ($asset) => (string) $asset['key'], $manifest)),
                'index' => 0,
                'log' => [],
                'errors' => [],
                'phase' => 'media',
            ], false);
        }
        $job = get_option('kk_import_job', []);
        if (!is_array($job) || empty($job['queue'])) {
            wp_send_json_error(['message' => 'Start the import again.']);
        }
        if (($job['phase'] ?? '') === 'media') {
            self::import_next_asset($job);
            if ((int) $job['index'] >= count($job['queue'])) {
                self::import_structure(!empty($job['overwrite']), $job['log'], $job['errors']);
                $job['phase'] = 'done';
            }
        }
        update_option('kk_import_job', $job, false);
        wp_send_json_success([
            'done' => ($job['phase'] ?? '') === 'done',
            'completed' => (int) $job['index'],
            'total' => count($job['queue']),
            'log' => array_slice($job['log'], -40),
            'errors' => $job['errors'],
        ]);
    }

    private static function import_next_asset(array &$job): void {
        $manifest = [];
        foreach (kk_media_manifest() as $asset) {
            $manifest[$asset['key']] = $asset;
        }
        $key = $job['queue'][$job['index']] ?? '';
        $job['index']++;
        if ($key === '' || !isset($manifest[$key])) {
            $job['errors'][] = 'Missing manifest entry.';
            return;
        }
        $asset = $manifest[$key];
        $existing = self::attachment_by_key($key);
        $seed = kk_seed_dir();
        if ($seed === '' && !$existing) {
            $job['errors'][] = 'Seed folder not found. Upload the seed zip or copy it to wp-content/uploads/kennys-korner-seed/.';
            $job['index'] = count($job['queue']);
            return;
        }
        if ($existing) {
            $imported = $existing;
            self::remember_asset($key, $existing);
            $job['log'][] = 'Kept media ' . $key . '.';
        } else {
            $file = $seed . '/' . ltrim((string) $asset['file'], '/');
            $imported = self::sideload($file, (string) ($asset['alt'] ?? $key), $key);
            if (is_wp_error($imported)) {
                $job['errors'][] = $key . ': ' . $imported->get_error_message();
                return;
            }
            if (!empty($asset['alt'])) {
                update_post_meta($imported, '_wp_attachment_image_alt', sanitize_text_field((string) $asset['alt']));
            }
            self::remember_asset($key, (int) $imported);
            $job['log'][] = 'Imported ' . $key . '.';
        }
        if ($seed === '') {
            return;
        }
        $webp_meta = [];
        foreach ($asset['webp'] ?? [] as $webp) {
            if (!is_array($webp)) {
                continue;
            }
            $width = (int) ($webp['width'] ?? 0);
            $child_key = $key . '-w' . $width;
            $child = self::attachment_by_key($child_key);
            if (!$child) {
                $child_file = $seed . '/' . ltrim((string) ($webp['file'] ?? ''), '/');
                $child = self::sideload($child_file, $key . ' ' . $width, $child_key);
            }
            if (is_wp_error($child)) {
                $job['errors'][] = $child_key . ': ' . $child->get_error_message();
                continue;
            }
            self::remember_asset($child_key, (int) $child);
            $webp_meta[] = ['id' => (int) $child, 'width' => $width];
        }
        if ($webp_meta !== []) {
            update_post_meta($imported, '_kk_webp', $webp_meta);
        }
    }

    public static function import_structure(bool $overwrite, array &$log, array &$errors): void {
        $content = kk_default_content();
        foreach ($content['pages'] as $key => $page) {
            $existing = kk_find_page_id($key);
            $live = $existing ? get_post_meta($existing, '_kk_content', true) : [];
            $has_live = is_array($live) && $live !== [];
            if ($existing && $has_live && !$overwrite) {
                $log[] = 'Kept the ' . $page['title'] . ' page.';
                continue;
            }
            if (!$existing) {
                $by_slug = get_page_by_path($page['slug']);
                if ($by_slug instanceof WP_Post && (string) get_post_meta($by_slug->ID, '_kk_key', true) === '') {
                    $errors[] = 'A page at /' . $page['slug'] . '/ already exists and was not changed.';
                    continue;
                }
            }
            $post = [
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => '',
            ];
            if ($existing) {
                $post['ID'] = $existing;
                if (!$overwrite && get_post_status($existing) === 'draft') {
                    $post['post_status'] = 'draft';
                }
                $page_id = wp_update_post($post, true);
            } else {
                $page_id = wp_insert_post($post, true);
            }
            if (is_wp_error($page_id)) {
                $errors[] = $page['title'] . ': ' . $page_id->get_error_message();
                continue;
            }
            update_post_meta((int) $page_id, '_kk_key', $key);
            update_post_meta((int) $page_id, '_wp_page_template', $page['template']);
            update_post_meta((int) $page_id, '_kk_content', $page['sections']);
            update_post_meta((int) $page_id, '_kk_customized', '0');
            delete_post_meta((int) $page_id, '_kk_preview');
            $log[] = ($existing ? 'Updated ' : 'Created ') . $page['title'] . '.';
        }
        kk_flush_content_cache();

        $settings = get_option('kk_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
        $flags = get_option('kk_settings_customized', []);
        if (!is_array($flags)) {
            $flags = [];
        }
        foreach (['header', 'footer', 'general'] as $group) {
            $exists = isset($settings[$group]) && is_array($settings[$group]) && $settings[$group] !== [];
            if ($exists && !$overwrite) {
                $log[] = 'Kept ' . $group . ' settings.';
                continue;
            }
            $settings[$group] = $content[$group];
            $flags[$group] = false;
            $log[] = 'Imported ' . $group . ' settings.';
        }
        update_option('kk_settings', $settings, false);
        update_option('kk_settings_customized', $flags, false);
        $logo_id = kk_image_id($settings['header']['logo'] ?? []);
        if ($logo_id) {
            set_theme_mod('custom_logo', $logo_id);
        }

        self::assign_reading($overwrite, $log);
        self::assign_menu($overwrite, $log);
        if ((string) get_option('permalink_structure') === '') {
            update_option('permalink_structure', '/%postname%/');
            $log[] = 'Turned on pretty permalinks.';
        }
        update_option('kk_imported', 1, false);
        kk_purge_page_caches();
        flush_rewrite_rules(false);
    }

    private static function assign_reading(bool $overwrite, array &$log): void {
        $home = kk_find_page_id('home');
        if (!$home) {
            $log[] = 'Homepage was not created, so Reading settings were left alone.';
            return;
        }
        $current = (int) get_option('page_on_front');
        $show = (string) get_option('show_on_front');
        if ($overwrite || $show !== 'page' || $current === 0 || $current === $home) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home);
            $log[] = 'Set Home as the site homepage.';
            return;
        }
        $log[] = 'Left the existing homepage in place. Choose Home under Settings → Reading when this site should use it.';
    }

    private static function assign_menu(bool $overwrite, array &$log): void {
        $locations = get_nav_menu_locations();
        if (!empty($locations['primary']) && !$overwrite) {
            $log[] = 'Left the existing navigation menu in place.';
            return;
        }
        $menu = wp_get_nav_menu_object("Kenny's Korner");
        $menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu("Kenny's Korner");
        if ($menu_id <= 0) {
            $log[] = 'Could not create the navigation menu.';
            return;
        }
        if ($overwrite) {
            $items = wp_get_nav_menu_items($menu_id);
            if (is_array($items)) {
                foreach ($items as $item) {
                    wp_delete_post((int) $item->ID, true);
                }
            }
        }
        $existing = wp_get_nav_menu_items($menu_id);
        $have = [];
        $position = 0;
        if (is_array($existing)) {
            foreach ($existing as $item) {
                $have[(int) $item->object_id] = true;
                $position++;
            }
        }
        $labels = [
            'home' => 'Home',
            'menu' => 'Menu',
            'gallery' => 'Gallery',
            'story' => 'Our story',
            'visit' => 'Visit us',
        ];
        foreach ($labels as $key => $label) {
            $page_id = kk_find_page_id($key);
            if (!$page_id || isset($have[$page_id])) {
                continue;
            }
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $label,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $page_id,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-position' => ++$position,
            ]);
        }
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
        $log[] = 'Assigned the primary menu.';
    }

    public static function attachment_by_key(string $key): int {
        $mapped = kk_asset_id($key);
        if ($mapped) {
            return $mapped;
        }
        $ids = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_key' => '_kk_asset_key',
            'meta_value' => $key,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'suppress_filters' => true,
        ]);
        return $ids ? (int) $ids[0] : 0;
    }

    private static function remember_asset(string $key, int $id): void {
        $map = kk_asset_map();
        $map[$key] = $id;
        update_option('kk_asset_map', $map, false);
    }

    private static function sideload(string $path, string $title, string $key) {
        if (!is_file($path)) {
            return new WP_Error('kk_missing_file', 'Missing file ' . basename($path));
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam($path);
        if (!$tmp || !copy($path, $tmp)) {
            return new WP_Error('kk_copy', 'Could not stage ' . basename($path));
        }
        $file = ['name' => basename($path), 'tmp_name' => $tmp];
        add_filter('intermediate_image_sizes_advanced', '__return_empty_array', 99);
        $id = media_handle_sideload($file, 0, $title !== '' ? $title : $key);
        remove_filter('intermediate_image_sizes_advanced', '__return_empty_array', 99);
        if (is_wp_error($id)) {
            return $id;
        }
        update_post_meta((int) $id, '_kk_asset_key', $key);
        return (int) $id;
    }

    public static function upload_seed(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to upload the seed.', 'kennys-korner-core'));
        }
        check_admin_referer('kk_upload_seed');
        if (empty($_FILES['seed']['name'])) {
            self::redirect_notice(['Choose a zip file.'], 'error');
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload = wp_handle_upload($_FILES['seed'], [
            'test_form' => false,
            'mimes' => ['zip' => 'application/zip'],
        ]);
        if (!empty($upload['error'])) {
            self::redirect_notice([(string) $upload['error']], 'error');
        }
        $uploads = wp_upload_dir();
        $dest = trailingslashit($uploads['basedir']) . 'kennys-korner-seed';
        wp_mkdir_p($dest);
        $unzipped = unzip_file($upload['file'], $dest);
        wp_delete_file($upload['file']);
        if (is_wp_error($unzipped)) {
            self::redirect_notice([$unzipped->get_error_message()], 'error');
        }
        self::strip_executable_files($dest);
        if (kk_seed_dir() === '') {
            self::redirect_notice(['The zip unpacked, but the images or menu folder was not inside it.'], 'error');
        }
        self::redirect_notice(['Seed files are ready to import.']);
    }

    private static function strip_executable_files(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }
            if (preg_match('/\.(php|phtml|phar|htaccess)$/i', $file->getFilename())) {
                wp_delete_file($file->getPathname());
            }
        }
    }

    public static function delete_content(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to remove this content.', 'kennys-korner-core'));
        }
        check_admin_referer('kk_delete_content');
        $confirm = isset($_POST['confirm']) ? sanitize_text_field(wp_unslash($_POST['confirm'])) : '';
        if ($confirm !== 'DELETE KK CONTENT') {
            self::redirect_notice(['Removal was not confirmed.'], 'error');
        }
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'any',
            'meta_key' => '_kk_key',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ]);
        foreach ($pages as $page_id) {
            if ((int) get_option('page_on_front') === (int) $page_id) {
                update_option('show_on_front', 'posts');
                update_option('page_on_front', 0);
            }
            wp_delete_post((int) $page_id, true);
        }
        $media = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_key' => '_kk_asset_key',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ]);
        foreach ($media as $attachment_id) {
            wp_delete_attachment((int) $attachment_id, true);
        }
        $menu = wp_get_nav_menu_object("Kenny's Korner");
        if ($menu) {
            wp_delete_nav_menu((int) $menu->term_id);
        }
        delete_option('kk_settings');
        delete_option('kk_settings_customized');
        delete_option('kk_asset_map');
        delete_option('kk_import_job');
        delete_option('kk_imported');
        kk_flush_content_cache();
        self::redirect_notice(['Imported Kenny\'s Korner content was removed. Other site content was left in place.']);
    }

    private static function redirect_notice(array $messages, string $type = 'success'): void {
        set_transient('kk_notice_' . get_current_user_id(), [
            'type' => $type,
            'messages' => $messages,
        ], 60);
        wp_safe_redirect(admin_url('admin.php?page=kk-setup'));
        exit;
    }

    public static function run_all(bool $overwrite = false): array {
        $log = [];
        $errors = [];
        $manifest = kk_media_manifest();
        $job = ['queue' => array_map(static fn ($asset) => (string) $asset['key'], $manifest), 'index' => 0, 'log' => &$log, 'errors' => &$errors];
        while ($job['index'] < count($job['queue'])) {
            self::import_next_asset($job);
        }
        self::import_structure($overwrite, $log, $errors);
        return ['log' => $log, 'errors' => $errors];
    }
}
