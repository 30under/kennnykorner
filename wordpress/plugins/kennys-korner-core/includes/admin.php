<?php

final class KK_Admin {
    public static function init(): void {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
        add_action('admin_post_kk_save', [self::class, 'save']);
        add_action('load-post.php', [self::class, 'redirect_page_editor']);
        add_filter('manage_pages_columns', [self::class, 'page_column']);
        add_action('manage_pages_custom_column', [self::class, 'page_column_value'], 10, 2);
        add_filter('document_title_parts', [self::class, 'preview_title']);
        add_action('wp_head', [self::class, 'meta_description']);
        add_action('wp_ajax_kk_stage_preview', [self::class, 'stage_preview']);
        add_action('wp_enqueue_scripts', [self::class, 'preview_assets']);
        add_filter('show_admin_bar', [self::class, 'hide_admin_bar_in_preview'], 1000);
        add_filter('body_class', [self::class, 'preview_body_class']);
        add_filter('admin_body_class', [self::class, 'editor_body_class']);
        add_filter('redirect_canonical', [self::class, 'keep_preview_query'], 10, 2);
    }

    public static function keep_preview_query($redirect_url, $requested_url) {
        if (!empty($_GET['kk_live']) || !empty($_GET['kk_preview'])) {
            return false;
        }
        return $redirect_url;
    }

    public static function menu(): void {
        $cap = 'edit_pages';
        add_menu_page('Kenny\'s Korner', 'Kenny\'s Korner', $cap, 'kk-home', [self::class, 'render'], 'dashicons-store', 25);
        foreach (kk_screens() as $screen) {
            add_submenu_page('kk-home', $screen['page_title'], $screen['menu_title'], $cap, 'kk-' . $screen['id'], [self::class, 'render']);
        }
        if (current_user_can('manage_options')) {
            add_submenu_page('kk-home', 'Setup', 'Setup', 'manage_options', 'kk-setup', [self::class, 'render_setup']);
        }
    }

    public static function assets(string $hook): void {
        if (!str_contains($hook, 'kk-')) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('kk-admin', KK_CORE_URL . 'assets/admin.css', [], KK_CORE_VERSION);
        wp_enqueue_script('kk-admin', KK_CORE_URL . 'assets/admin.js', ['jquery', 'jquery-ui-sortable'], KK_CORE_VERSION, true);
        $screen_id = 'home';
        if (preg_match('/kk-([a-z0-9-]+)$/', $hook, $matches)) {
            $screen_id = $matches[1];
        }
        $config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'screen' => $screen_id,
            'stageNonce' => wp_create_nonce('kk_live_stage'),
            'previewUrl' => $screen_id === 'setup' ? '' : self::preview_url($screen_id),
            'menusUrl' => admin_url('nav-menus.php'),
            'screens' => [],
        ];
        foreach (kk_screens() as $id => $screen) {
            $config['screens'][$id] = $screen['menu_title'];
        }
        if (current_user_can('manage_options') && str_ends_with($hook, 'kk-setup')) {
            $config['nonce'] = wp_create_nonce('kk_import');
        }
        wp_localize_script('kk-admin', 'kkAdmin', $config);
    }

    public static function preview_url(string $screen_id, string $view = ''): string {
        $screen = kk_screen($screen_id);
        $target = home_url('/');
        if ($view === '404') {
            $target = home_url('/kk-editor-missing-page/');
        } elseif ($screen && ($screen['storage'] ?? '') === 'page') {
            $page_id = kk_find_page_id($screen['key']);
            if ($page_id) {
                $link = get_post_status($page_id) === 'publish' ? get_permalink($page_id) : get_preview_post_link($page_id);
                if (is_string($link) && $link !== '') {
                    $target = $link;
                }
            }
        }
        return add_query_arg([
            'kk_live' => '1',
            'kk_screen' => $screen_id,
            '_wpnonce' => wp_create_nonce('kk_live'),
        ], $target);
    }

    public static function preview_assets(): void {
        if (!kk_is_live_editor()) {
            return;
        }
        wp_enqueue_script('kk-live-preview', KK_CORE_URL . 'assets/live-preview.js', [], KK_CORE_VERSION, true);
    }

    public static function hide_admin_bar_in_preview(bool $show): bool {
        return kk_is_live_editor() ? false : $show;
    }

    public static function preview_body_class(array $classes): array {
        if (kk_is_live_editor()) {
            $classes[] = 'kk-live-editor';
        }
        return $classes;
    }

    public static function editor_body_class(string $classes): string {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (str_starts_with($page, 'kk-') && $page !== 'kk-setup') {
            $classes .= ' kk-editor-screen';
        }
        return $classes;
    }

    public static function stage_preview(): void {
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'You do not have permission to edit this content.'], 403);
        }
        check_ajax_referer('kk_live_stage', 'nonce');
        $screen_id = isset($_POST['screen']) ? sanitize_key(wp_unslash($_POST['screen'])) : '';
        $screen = kk_screen($screen_id);
        if (!$screen) {
            wp_send_json_error(['message' => 'Unknown screen.'], 400);
        }
        $input = isset($_POST['kk']) && is_array($_POST['kk']) ? wp_unslash($_POST['kk']) : [];
        $result = kk_sanitize_screen($screen, $input);
        set_transient(kk_live_transient_key($screen_id), [
            'sections' => $result['sections'],
            'page' => $result['page'],
        ], HOUR_IN_SECONDS);
        wp_send_json_success(['errors' => $result['errors']]);
    }

    public static function render(): void {
        if (!current_user_can('edit_pages')) {
            wp_die(esc_html__('Sorry, you are not allowed to edit this content.', 'kennys-korner-core'));
        }
        $screen_id = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'kk-home';
        $screen_id = str_starts_with($screen_id, 'kk-') ? substr($screen_id, 3) : $screen_id;
        $screen = kk_screen($screen_id);
        if (!$screen) {
            wp_die(esc_html__('Unknown screen.', 'kennys-korner-core'));
        }
        $redo = get_transient('kk_redo_' . get_current_user_id());
        $errors = [];
        if (is_array($redo) && ($redo['screen'] ?? '') === $screen_id) {
            $sections = $redo['sections'];
            $page_fields = $redo['page'];
            $errors = $redo['errors'];
            delete_transient('kk_redo_' . get_current_user_id());
        } else {
            $sections = self::sections_for_form($screen);
            $page_fields = kk_editor_page_fields($screen_id);
        }
        $notice = get_transient('kk_notice_' . get_current_user_id());
        delete_transient('kk_notice_' . get_current_user_id());
        delete_transient(kk_live_transient_key($screen_id));
        echo '<div class="kk-studio is-preview" data-screen="' . esc_attr($screen_id) . '">';
        echo '<form class="kk-studio-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('kk_save_' . $screen_id);
        echo '<input type="hidden" name="action" value="kk_save">';
        echo '<input type="hidden" name="screen" value="' . esc_attr($screen_id) . '">';
        echo '<input type="hidden" name="kk_form" value="1">';
        echo '<div class="kk-studio-bar">';
        echo '<h1>' . esc_html($screen['page_title']) . '</h1>';
        echo '<p class="kk-studio-status" data-state="saved">Đã lưu</p>';
        echo '<div class="kk-studio-tabs" role="tablist">';
        echo '<button type="button" class="is-active" data-pane="preview" role="tab" aria-selected="true">Xem trước</button>';
        echo '<button type="button" data-pane="edit" role="tab" aria-selected="false">Chỉnh sửa</button>';
        echo '</div>';
        echo '<div class="kk-studio-devices" role="group" aria-label="Preview width">';
        echo '<button type="button" class="is-active" data-device="desktop">Desktop</button>';
        echo '<button type="button" data-device="tablet">Tablet</button>';
        echo '<button type="button" data-device="mobile">Mobile</button>';
        echo '</div>';
        if ($screen_id === 'general') {
            echo '<button type="button" class="button" data-kk-view="404">Trang 404</button>';
        }
        echo '<button class="button button-primary" name="kk_action" value="publish">Lưu thay đổi</button>';
        echo '</div>';
        echo '<div class="kk-studio-body">';
        echo '<div class="kk-studio-preview" data-pane="preview">';
        echo '<iframe title="Xem trước website" src="' . esc_url(self::preview_url($screen_id)) . '"></iframe>';
        echo '</div>';
        echo '<div class="kk-studio-panel" data-pane="edit">';
        if (is_array($notice) && !empty($notice['messages'])) {
            $class = ($notice['type'] ?? '') === 'error' ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html(implode(' ', $notice['messages'])) . '</p></div>';
        }
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        echo '<p class="kk-live-note" hidden></p>';
        if (!empty($screen['intro'])) {
            echo '<p class="kk-intro">' . esc_html($screen['intro']) . '</p>';
        }
        echo '<p class="description">Bấm một tiêu đề, đoạn văn, ảnh, nút hoặc section trong bản xem trước để mở đúng nhóm trường. Thay đổi hiện ngay ở bản xem trước và chỉ công khai khi bấm Lưu thay đổi.</p>';
        self::view_links($screen);
        kk_render_screen($screen, $sections, $page_fields);
        echo '</div></div></form></div>';
    }

    private static function sections_for_form(array $screen): array {
        if ($screen['storage'] === 'page') {
            return kk_editor_sections($screen['id']);
        }
        $flat = kk_editor_sections($screen['id']);
        $sections = [];
        foreach ($screen['sections'] as $section) {
            $bucket = [];
            foreach ($section['fields'] as $field) {
                if (array_key_exists($field['name'], $flat)) {
                    $bucket[$field['name']] = $flat[$field['name']];
                }
            }
            $sections[$section['id']] = $bucket;
        }
        return $sections;
    }

    private static function view_links(array $screen): void {
        if ($screen['storage'] !== 'page') {
            if ($screen['id'] === 'header') {
                echo '<p><a class="button" href="' . esc_url(admin_url('nav-menus.php')) . '">Edit navigation</a></p>';
            }
            return;
        }
        $page_id = kk_find_page_id($screen['key']);
        if (!$page_id) {
            echo '<p class="description">Save or run Setup → Import demo content to create this page.</p>';
            return;
        }
        $url = get_post_status($page_id) === 'publish' ? get_permalink($page_id) : get_preview_post_link($page_id);
        echo '<p><a class="button" target="_blank" rel="noopener noreferrer" href="' . esc_url((string) $url) . '">View page</a></p>';
    }

    public static function save(): void {
        if (!current_user_can('edit_pages')) {
            wp_die(esc_html__('Sorry, you are not allowed to edit this content.', 'kennys-korner-core'));
        }
        $screen_id = isset($_POST['screen']) ? sanitize_key(wp_unslash($_POST['screen'])) : '';
        $screen = kk_screen($screen_id);
        if (!$screen || empty($_POST['kk_form'])) {
            wp_die(esc_html__('The form could not be saved.', 'kennys-korner-core'));
        }
        check_admin_referer('kk_save_' . $screen_id);
        $input = isset($_POST['kk']) && is_array($_POST['kk']) ? wp_unslash($_POST['kk']) : [];
        $result = kk_sanitize_screen($screen, $input);
        $action = isset($_POST['kk_action']) ? sanitize_key(wp_unslash($_POST['kk_action'])) : 'publish';
        if ($result['errors']) {
            set_transient('kk_redo_' . get_current_user_id(), [
                'screen' => $screen_id,
                'sections' => $result['sections'],
                'page' => $result['page'],
                'errors' => $result['errors'],
            ], 120);
            wp_safe_redirect(admin_url('admin.php?page=kk-' . $screen_id));
            exit;
        }
        if ($screen['storage'] === 'page') {
            $preview = $action === 'preview';
            $page_id = self::ensure_page($screen, $result['page'], $preview);
            if ($preview) {
                $preview_data = $result['sections'];
                $preview_data['_post_title'] = $result['page']['post_title'] ?? '';
                $preview_data['_meta_description'] = $result['page']['meta_description'] ?? '';
                update_post_meta($page_id, '_kk_preview', $preview_data);
                $url = add_query_arg([
                    'kk_preview' => '1',
                    '_wpnonce' => wp_create_nonce('kk_preview_' . $page_id),
                ], get_post_status($page_id) === 'publish' ? get_permalink($page_id) : get_preview_post_link($page_id));
                wp_safe_redirect($url);
                exit;
            }
            update_post_meta($page_id, '_kk_content', $result['sections']);
            update_post_meta($page_id, '_kk_customized', '1');
            update_post_meta($page_id, '_kk_meta_description', $result['page']['meta_description'] ?? '');
            delete_post_meta($page_id, '_kk_preview');
            wp_update_post([
                'ID' => $page_id,
                'post_title' => $result['page']['post_title'] !== '' ? $result['page']['post_title'] : get_the_title($page_id),
                'post_status' => ($result['page']['post_status'] ?? 'publish') === 'draft' ? 'draft' : 'publish',
            ]);
            kk_flush_content_cache();
            kk_purge_page_caches();
            delete_transient(kk_live_transient_key($screen_id));
            self::notice(['Saved. The live page is updated.']);
        } else {
            $flat = [];
            foreach ($result['sections'] as $values) {
                $flat = array_merge($flat, $values);
            }
            $all = get_option('kk_settings', []);
            if (!is_array($all)) {
                $all = [];
            }
            $all[$screen['group']] = $flat;
            update_option('kk_settings', $all, false);
            $flags = get_option('kk_settings_customized', []);
            if (!is_array($flags)) {
                $flags = [];
            }
            $flags[$screen['group']] = true;
            update_option('kk_settings_customized', $flags, false);
            if ($screen['group'] === 'header') {
                $logo_id = kk_image_id($flat['logo'] ?? []);
                if ($logo_id) {
                    set_theme_mod('custom_logo', $logo_id);
                }
            }
            kk_purge_page_caches();
            delete_transient(kk_live_transient_key($screen_id));
            self::notice(['Saved. Shared content is updated everywhere it is used.']);
        }
        wp_safe_redirect(admin_url('admin.php?page=kk-' . $screen_id));
        exit;
    }

    private static function ensure_page(array $screen, array $page_fields, bool $preview): int {
        $key = $screen['key'];
        $defaults = kk_default_content()['pages'][$key];
        $page_id = kk_find_page_id($key);
        $status = $preview ? 'draft' : ((($page_fields['post_status'] ?? 'publish') === 'draft') ? 'draft' : 'publish');
        $title = $page_fields['post_title'] !== '' ? $page_fields['post_title'] : $defaults['title'];
        if ($page_id) {
            return $page_id;
        }
        $created = wp_insert_post([
            'post_type' => 'page',
            'post_status' => $status,
            'post_title' => $title,
            'post_name' => $defaults['slug'],
            'post_content' => '',
        ], true);
        if (is_wp_error($created)) {
            wp_die(esc_html($created->get_error_message()));
        }
        update_post_meta((int) $created, '_kk_key', $key);
        update_post_meta((int) $created, '_wp_page_template', $defaults['template']);
        kk_flush_content_cache();
        return (int) $created;
    }

    private static function notice(array $messages, string $type = 'success'): void {
        set_transient('kk_notice_' . get_current_user_id(), [
            'type' => $type,
            'messages' => $messages,
        ], 60);
    }

    public static function redirect_page_editor(): void {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
        if (!$post_id) {
            return;
        }
        $key = (string) get_post_meta($post_id, '_kk_key', true);
        if ($key !== '' && kk_screen($key)) {
            wp_safe_redirect(admin_url('admin.php?page=kk-' . $key));
            exit;
        }
    }

    public static function page_column(array $columns): array {
        $columns['kk_screen'] = 'Kenny\'s Korner';
        return $columns;
    }

    public static function page_column_value(string $column, int $post_id): void {
        if ($column !== 'kk_screen') {
            return;
        }
        $key = (string) get_post_meta($post_id, '_kk_key', true);
        if ($key === '' || !kk_screen($key)) {
            return;
        }
        echo '<a href="' . esc_url(admin_url('admin.php?page=kk-' . $key)) . '">Edit content</a>';
    }

    public static function preview_title(array $parts): array {
        if (!is_page()) {
            return $parts;
        }
        $page_id = (int) get_queried_object_id();
        $key = (string) get_post_meta($page_id, '_kk_key', true);
        $screen_id = kk_live_screen();
        $screen = $screen_id ? kk_screen($screen_id) : null;
        if ($screen && ($screen['key'] ?? '') === $key) {
            $staged = kk_live_staged($screen_id);
            if (is_array($staged) && !empty($staged['page']['post_title'])) {
                $parts['title'] = (string) $staged['page']['post_title'];
                return $parts;
            }
        }
        if (!kk_is_preview_request($page_id)) {
            return $parts;
        }
        $preview = get_post_meta($page_id, '_kk_preview', true);
        if (is_array($preview) && !empty($preview['_post_title'])) {
            $parts['title'] = (string) $preview['_post_title'];
        }
        return $parts;
    }

    public static function meta_description(): void {
        if (!is_page()) {
            return;
        }
        $page_id = (int) get_queried_object_id();
        $description = (string) get_post_meta($page_id, '_kk_meta_description', true);
        if (kk_is_preview_request($page_id)) {
            $preview = get_post_meta($page_id, '_kk_preview', true);
            if (is_array($preview) && isset($preview['_meta_description'])) {
                $description = (string) $preview['_meta_description'];
            }
        }
        $description = trim($description);
        if ($description !== '') {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
    }

    public static function render_setup(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to set up the site.', 'kennys-korner-core'));
        }
        $notice = get_transient('kk_notice_' . get_current_user_id());
        delete_transient('kk_notice_' . get_current_user_id());
        echo '<div class="wrap kk-wrap">';
        echo '<h1>Setup and system check</h1>';
        if (is_array($notice) && !empty($notice['messages'])) {
            $class = ($notice['type'] ?? '') === 'error' ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr($class) . '"><p>' . esc_html(implode(' ', $notice['messages'])) . '</p></div>';
        }
        echo '<div class="kk-card"><h2>System check</h2><table class="widefat striped"><tbody>';
        foreach (kk_system_checks() as $check) {
            echo '<tr><td>' . esc_html($check['label']) . '</td><td><strong>' . esc_html($check['status']) . '</strong></td><td>' . esc_html($check['detail']) . '</td></tr>';
        }
        echo '</tbody></table></div>';
        $seed = kk_seed_dir();
        echo '<div class="kk-card"><h2>Demo media</h2>';
        echo '<p>Text imports from the plugin. Photos, the menu PDF, preview images, and the kitchen video come from the seed folder.</p>';
        echo '<p>Current folder: <code>' . esc_html($seed !== '' ? $seed : 'Not found') . '</code></p>';
        echo '<p>Upload <code>kennys-korner-seed.zip</code> as the package script creates it. The zip contains a <code>seed</code> folder, and Setup detects that folder without moving <code>images</code>, <code>menu</code>, or <code>videos</code> up a level.</p>';
        echo '<p>This server currently allows <code>upload_max_filesize=' . esc_html((string) ini_get('upload_max_filesize')) . '</code> and <code>post_max_size=' . esc_html((string) ini_get('post_max_size')) . '</code>. Those are the limits for this web request, not a separate CLI configuration. The seed zip is about 48 MB. ZipArchive unpacks the file only after PHP accepts the upload. If either limit is smaller, use the host File Manager or SFTP to unzip the same zip into <code>wp-content/uploads/kennys-korner-seed/</code> and leave the inner <code>seed</code> folder where the zip puts it.</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        wp_nonce_field('kk_upload_seed');
        echo '<input type="hidden" name="action" value="kk_upload_seed">';
        echo '<input type="file" name="seed" accept=".zip,application/zip" required> ';
        echo '<button class="button">Upload seed zip</button></form></div>';
        echo '<div class="kk-card"><h2>Confirm with the restaurant</h2>';
        echo '<p>These are internal notes. The public site keeps the demo values below and does not invent a street address, phone, email, Instagram profile, or a different history. Hours on the website come only from General settings.</p>';
        echo '<table class="widefat striped"><thead><tr><th>Item</th><th>Value in the demo</th><th>Why it is not confirmed</th></tr></thead><tbody>';
        foreach (kk_unconfirmed_items() as $item) {
            echo '<tr><td>' . esc_html($item['item']) . '</td><td>' . esc_html($item['demo']) . '</td><td>' . esc_html($item['note']) . '</td></tr>';
        }
        echo '</tbody></table></div>';
        echo '<div class="kk-card"><h2>Email delivery</h2>';
        echo '<p>Catering uses <code>wp_mail()</code>. Install an SMTP plugin and enter the host, port, username, and password supplied by the mailbox provider. This plugin does not store those credentials and does not create a Gmail account or an app password.</p>';
        echo '<p>WP Mail SMTP, FluentSMTP, and Post SMTP can all deliver mail that Kenny\'s Korner sends. After the plugin is connected, send the test below. The result only says whether WordPress accepted the message.</p>';
        if (kk_store_email() === '') {
            echo '<div class="notice notice-warning inline"><p>Add the restaurant email in Kenny\'s Korner → General settings. The catering form stays unavailable until that address is saved.</p></div>';
        } else {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            wp_nonce_field('kk_mail_test');
            echo '<input type="hidden" name="action" value="kk_mail_test">';
            echo '<button class="button">Send test email</button></form>';
            echo '<p class="description">The test goes to the restaurant email in General settings.</p>';
        }
        echo '</div>';
        echo '<div class="kk-card"><h2>Import demo content</h2>';
        echo '<p>This creates the pages, menu, and media that are missing. It does not replace content you have already saved, and it does not run by itself when the plugin is activated.</p>';
        echo '<p><label><input type="checkbox" id="kk-overwrite"> Replace Kenny\'s Korner demo content</label></p>';
        echo '<p><label>Type REPLACE DEMO CONTENT to allow replacement<br><input type="text" id="kk-confirm" class="regular-text" autocomplete="off"></label></p>';
        echo '<p><button type="button" class="button button-primary" id="kk-import-start">Import demo content</button></p>';
        echo '<div id="kk-import-log" class="kk-log" hidden></div></div>';
        echo '<div class="kk-card"><h2>Remove imported content</h2>';
        echo '<p>Deletes pages, media, and settings created by this plugin. Other pages and media stay. Type DELETE KK CONTENT to confirm.</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('kk_delete_content');
        echo '<input type="hidden" name="action" value="kk_delete_content">';
        echo '<input type="text" name="confirm" class="regular-text" autocomplete="off"> ';
        echo '<button class="button button-link-delete">Remove imported content</button></form></div>';
        echo '</div>';
    }
}
