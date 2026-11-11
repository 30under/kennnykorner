<?php

function kk_default_content(): array {
    static $content = null;
    if ($content === null) {
        $content = require KK_CORE_DIR . 'includes/default-content.php';
    }
    return $content;
}

function kk_flush_content_cache(): void {
    KK_Store::$page_ids = [];
}

final class KK_Store {
    public static array $page_ids = [];
}

function kk_find_page_id(string $key): int {
    if (isset(KK_Store::$page_ids[$key])) {
        return KK_Store::$page_ids[$key];
    }
    $ids = get_posts([
        'post_type' => 'page',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
        'meta_key' => '_kk_key',
        'meta_value' => $key,
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
    ]);
    $id = $ids ? (int) $ids[0] : 0;
    KK_Store::$page_ids[$key] = $id;
    return $id;
}

function kk_is_assoc(array $value): bool {
    if ($value === []) {
        return false;
    }
    return array_keys($value) !== range(0, count($value) - 1);
}

function kk_merge_missing(array $saved, array $defaults): array {
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $saved)) {
            $saved[$key] = $value;
            continue;
        }
        if (is_array($value) && is_array($saved[$key]) && kk_is_assoc($value)) {
            $saved[$key] = kk_merge_missing($saved[$key], $value);
        }
    }
    return $saved;
}

function kk_is_live_editor(): bool {
    if (is_admin() || empty($_GET['kk_live'])) {
        return false;
    }
    if (!is_user_logged_in() || !current_user_can('edit_pages')) {
        return false;
    }
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    return (bool) wp_verify_nonce($nonce, 'kk_live');
}

function kk_live_screen(): string {
    if (!kk_is_live_editor()) {
        return '';
    }
    $screen = isset($_GET['kk_screen']) ? sanitize_key(wp_unslash($_GET['kk_screen'])) : '';
    return function_exists('kk_screen') && kk_screen($screen) ? $screen : '';
}

function kk_live_transient_key(string $screen): string {
    return 'kk_live_' . get_current_user_id() . '_' . sanitize_key($screen);
}

function kk_live_staged(string $screen): ?array {
    $data = get_transient(kk_live_transient_key($screen));
    return is_array($data) ? $data : null;
}

function kk_live_page_sections(string $key): ?array {
    $screen_id = kk_live_screen();
    $screen = $screen_id && function_exists('kk_screen') ? kk_screen($screen_id) : null;
    if (!$screen || ($screen['storage'] ?? '') !== 'page' || ($screen['key'] ?? '') !== $key) {
        return null;
    }
    $staged = kk_live_staged($screen_id);
    if (!$staged || !isset($staged['sections']) || !is_array($staged['sections'])) {
        return null;
    }
    return $staged['sections'];
}

function kk_live_setting_group(string $group): ?array {
    $screen_id = kk_live_screen();
    $screen = $screen_id && function_exists('kk_screen') ? kk_screen($screen_id) : null;
    if (!$screen || ($screen['storage'] ?? '') !== 'settings' || ($screen['group'] ?? '') !== $group) {
        return null;
    }
    $staged = kk_live_staged($screen_id);
    if (!$staged || !isset($staged['sections']) || !is_array($staged['sections'])) {
        return null;
    }
    $flat = [];
    foreach ($staged['sections'] as $values) {
        if (is_array($values)) {
            $flat = array_merge($flat, $values);
        }
    }
    return $flat;
}

function kk_is_preview_request(int $page_id): bool {
    if ($page_id <= 0 || empty($_GET['kk_preview'])) {
        return false;
    }
    if (!is_user_logged_in() || !current_user_can('edit_pages')) {
        return false;
    }
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    return (bool) wp_verify_nonce($nonce, 'kk_preview_' . $page_id);
}

function kk_page(string $key): array {
    $defaults = kk_default_content()['pages'][$key]['sections'] ?? [];
    $page_id = kk_find_page_id($key);
    $stored = [];
    $live_sections = kk_live_page_sections($key);
    if (is_array($live_sections)) {
        $stored = $live_sections;
    } elseif ($page_id && kk_is_preview_request($page_id)) {
        $preview = get_post_meta($page_id, '_kk_preview', true);
        if (is_array($preview)) {
            $stored = $preview;
        }
    } elseif ($page_id) {
        $live = get_post_meta($page_id, '_kk_content', true);
        if (is_array($live)) {
            $stored = $live;
        }
    }
    if ($stored === []) {
        return [];
    }
    return kk_merge_missing($stored, $defaults);
}

function kk_setting(string $group): array {
    $defaults = kk_default_content()[$group] ?? [];
    $override = kk_live_setting_group($group);
    if (is_array($override)) {
        return kk_merge_missing($override, $defaults);
    }
    $all = get_option('kk_settings', []);
    if (!is_array($all) || !isset($all[$group]) || !is_array($all[$group])) {
        return [];
    }
    return kk_merge_missing($all[$group], $defaults);
}

function kk_editor_sections(string $screen_id): array {
    $screen = kk_screen($screen_id);
    if (!$screen) {
        return [];
    }
    if ($screen['storage'] === 'page') {
        $key = $screen['key'];
        $defaults = kk_default_content()['pages'][$key]['sections'] ?? [];
        $page_id = kk_find_page_id($key);
        $stored = $page_id ? get_post_meta($page_id, '_kk_content', true) : [];
        if (!is_array($stored) || $stored === []) {
            return $defaults;
        }
        return kk_merge_missing($stored, $defaults);
    }
    $group = $screen['group'];
    $defaults = kk_default_content()[$group] ?? [];
    $all = get_option('kk_settings', []);
    $stored = is_array($all) && isset($all[$group]) && is_array($all[$group]) ? $all[$group] : [];
    if ($stored === []) {
        return $defaults;
    }
    return kk_merge_missing($stored, $defaults);
}

function kk_editor_page_fields(string $screen_id): array {
    $screen = kk_screen($screen_id);
    if (!$screen || $screen['storage'] !== 'page') {
        return [];
    }
    $key = $screen['key'];
    $defaults = kk_default_content()['pages'][$key];
    $page_id = kk_find_page_id($key);
    if (!$page_id) {
        return [
            'post_title' => $defaults['title'],
            'post_status' => 'publish',
            'meta_description' => '',
        ];
    }
    return [
        'post_title' => get_the_title($page_id),
        'post_status' => get_post_status($page_id) === 'draft' ? 'draft' : 'publish',
        'meta_description' => (string) get_post_meta($page_id, '_kk_meta_description', true),
    ];
}

function kk_page_url(string $key): string {
    if ($key === 'home') {
        $id = kk_find_page_id('home');
        if ($id && (int) get_option('page_on_front') === $id && get_option('show_on_front') === 'page') {
            return home_url('/');
        }
        return $id ? (string) get_permalink($id) : home_url('/');
    }
    $id = kk_find_page_id($key);
    return $id ? (string) get_permalink($id) : home_url('/');
}

function kk_asset_map(): array {
    $map = get_option('kk_asset_map', []);
    return is_array($map) ? $map : [];
}

function kk_asset_id(string $key): int {
    $map = kk_asset_map();
    if ($key === '' || empty($map[$key])) {
        return 0;
    }
    $id = (int) $map[$key];
    return get_post_type($id) === 'attachment' ? $id : 0;
}

function kk_image_id($image): int {
    if (!is_array($image)) {
        return 0;
    }
    $id = absint($image['id'] ?? 0);
    if ($id && get_post_type($id) === 'attachment') {
        return $id;
    }
    $asset = isset($image['asset']) ? (string) $image['asset'] : '';
    if ($asset !== '' && preg_match('/^[a-z0-9-]+$/', $asset)) {
        return kk_asset_id($asset);
    }
    return 0;
}

function kk_prepare_image($image): array {
    $explicit_alt = is_array($image) && array_key_exists('alt', $image);
    $alt = $explicit_alt ? (string) $image['alt'] : '';
    $id = kk_image_id($image);
    if (!$id) {
        return [
            'id' => 0,
            'src' => '',
            'alt' => $alt,
            'width' => 0,
            'height' => 0,
            'srcset' => '',
            'webp' => '',
        ];
    }
    $src = (string) wp_get_attachment_url($id);
    $meta = wp_get_attachment_metadata($id);
    $width = is_array($meta) ? (int) ($meta['width'] ?? 0) : 0;
    $height = is_array($meta) ? (int) ($meta['height'] ?? 0) : 0;
    $srcset = (string) (wp_get_attachment_image_srcset($id, 'full') ?: '');
    $webp_items = get_post_meta($id, '_kk_webp', true);
    $parts = [];
    if (is_array($webp_items)) {
        foreach ($webp_items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $webp_id = (int) ($item['id'] ?? 0);
            $webp_width = (int) ($item['width'] ?? 0);
            $webp_url = $webp_id ? (string) wp_get_attachment_url($webp_id) : '';
            if ($webp_url !== '' && $webp_width > 0) {
                $parts[] = $webp_url . ' ' . $webp_width . 'w';
            }
        }
    }
    if (!$explicit_alt) {
        $alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
    }
    return [
        'id' => $id,
        'src' => $src,
        'alt' => $alt,
        'width' => $width,
        'height' => $height,
        'srcset' => $srcset,
        'webp' => implode(', ', $parts),
    ];
}

function kk_resolve_link(array $link): array {
    $preset = (string) ($link['preset'] ?? 'custom');
    $url = (string) ($link['url'] ?? '');
    $general = kk_setting('general');
    switch ($preset) {
        case 'ordering':
            $url = (string) ($general['ordering_url'] ?? '');
            break;
        case 'directions':
            $url = (string) ($general['directions_url'] ?? '');
            break;
        case 'home':
        case 'menu':
        case 'gallery':
        case 'story':
        case 'visit':
        case 'catering':
            $url = kk_page_url($preset);
            break;
        default:
            break;
    }
    if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
        $url = home_url($url);
    }
    return [
        'url' => $url,
        'label' => (string) ($link['label'] ?? ''),
        'new_tab' => !empty($link['new_tab']),
        'style' => (string) ($link['style'] ?? 'primary'),
        'icon' => (string) ($link['icon'] ?? 'none'),
    ];
}

function kk_public_http_url(string $url): string {
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url)) {
        return '';
    }
    $clean = esc_url_raw($url);
    if ($clean === '') {
        return '';
    }
    if (function_exists('wp_http_validate_url') && !wp_http_validate_url($clean)) {
        return '';
    }
    return $clean;
}

function kk_fill_blank_contact_settings(): void {
    $all = get_option('kk_settings', null);
    if (!is_array($all) || !isset($all['general']) || !is_array($all['general'])) {
        return;
    }
    $defaults = kk_default_content()['general'] ?? [];
    $general = $all['general'];
    $changed = false;
    $email = trim((string) ($general['email'] ?? ''));
    if (stripos($email, 'mailto:') === 0) {
        $email = trim(substr($email, 7));
    }
    $next_email = (string) ($defaults['email'] ?? '');
    if (($email === '' || !is_email($email)) && $next_email !== '' && is_email($next_email)) {
        $general['email'] = $next_email;
        $changed = true;
    }
    foreach (['instagram_url', 'facebook_url'] as $key) {
        $current = trim((string) ($general[$key] ?? ''));
        $next = (string) ($defaults[$key] ?? '');
        $placeholder = $current === '' || str_starts_with($current, '#') || !preg_match('#^https?://#i', $current);
        if ($placeholder && preg_match('#^https?://#i', $next)) {
            $general[$key] = $next;
            $changed = true;
        }
    }
    if (!$changed) {
        return;
    }
    $all['general'] = $general;
    update_option('kk_settings', $all, false);
}

function kk_store_email(): string {
    $general = kk_setting('general');
    $raw = trim((string) ($general['email'] ?? ''));
    if (stripos($raw, 'mailto:') === 0) {
        $raw = trim(substr($raw, 7));
    }
    $raw = sanitize_email($raw);
    return is_email($raw) ? $raw : '';
}

function kk_attachment_mime_kind(int $id): string {
    if ($id <= 0 || get_post_type($id) !== 'attachment') {
        return '';
    }
    $mime = (string) get_post_mime_type($id);
    if (str_starts_with($mime, 'video/')) {
        return 'video';
    }
    if (str_starts_with($mime, 'image/')) {
        return 'image';
    }
    return $mime;
}

function kk_video_mime(string $url, int $file_id = 0): string {
    if ($file_id > 0) {
        $mime = (string) get_post_mime_type($file_id);
        if (str_starts_with($mime, 'video/')) {
            return $mime;
        }
    }
    if (preg_match('/\.webm($|\?)/i', $url)) {
        return 'video/webm';
    }
    return 'video/mp4';
}

function kk_normalize_video_item(array $item): array {
    if (!isset($item['poster']) || !is_array($item['poster'])) {
        return $item;
    }
    $poster_id = kk_image_id($item['poster']);
    if (kk_attachment_mime_kind($poster_id) !== 'video') {
        return $item;
    }
    $video = is_array($item['video'] ?? null) ? $item['video'] : [];
    $video_id = kk_image_id($video);
    $url = trim((string) ($item['video_url'] ?? ''));
    $has_video = kk_attachment_mime_kind($video_id) === 'video' || preg_match('#^https?://#i', $url) === 1;
    if ($has_video) {
        return $item;
    }
    $item['video'] = ['id' => $poster_id, 'asset' => ''];
    $item['poster'] = [
        'id' => 0,
        'asset' => '',
        'alt' => (string) ($item['poster']['alt'] ?? ''),
    ];
    return $item;
}

function kk_repair_stored_video_fields(): void {
    if (get_option('kk_video_fields_repaired') === '1') {
        return;
    }
    $maps = [
        'story' => ['timeline', 'items'],
        'gallery' => ['videos', 'items'],
    ];
    $found = false;
    foreach ($maps as $key => $path) {
        $page_id = kk_find_page_id($key);
        if (!$page_id) {
            continue;
        }
        $found = true;
        $content = get_post_meta($page_id, '_kk_content', true);
        if (!is_array($content)) {
            continue;
        }
        [$section, $list] = $path;
        $items = $content[$section][$list] ?? null;
        if (!is_array($items)) {
            continue;
        }
        $next = [];
        $changed = false;
        foreach ($items as $item) {
            if (!is_array($item)) {
                $next[] = $item;
                continue;
            }
            $fixed = kk_normalize_video_item($item);
            $changed = $changed || $fixed !== $item;
            $next[] = $fixed;
        }
        if (!$changed) {
            continue;
        }
        $content[$section][$list] = $next;
        update_post_meta($page_id, '_kk_content', $content);
    }
    if ($found) {
        update_option('kk_video_fields_repaired', '1', false);
    }
}

function kk_media_video(array $source, string $file_key = 'video', string $url_key = 'video_url', string $poster_key = 'poster'): array {
    $file_id = kk_image_id($source[$file_key] ?? []);
    if (kk_attachment_mime_kind($file_id) !== 'video') {
        $file_id = 0;
    }
    $url = $file_id ? (string) wp_get_attachment_url($file_id) : '';
    if ($url === '') {
        $candidate = trim((string) ($source[$url_key] ?? ''));
        if (preg_match('#^https?://#i', $candidate)) {
            $url = $candidate;
        }
    }
    $poster_id = kk_image_id($source[$poster_key] ?? []);
    $poster = '';
    if (kk_attachment_mime_kind($poster_id) === 'image') {
        $poster = (string) wp_get_attachment_url($poster_id);
    } elseif ($url === '' && kk_attachment_mime_kind($poster_id) === 'video') {
        $file_id = $poster_id;
        $url = (string) wp_get_attachment_url($poster_id);
    }
    return ['url' => $url, 'poster' => $poster, 'mime' => kk_video_mime($url, $file_id)];
}

function kk_video_source(): array {
    return kk_media_video(kk_setting('general'), 'video', 'video_url', 'video_poster');
}

function kk_menu_document(): array {
    $page = kk_page('menu');
    $section = $page['menu'] ?? [];
    $pdf_id = kk_image_id($section['pdf'] ?? []);
    $pdf_url = $pdf_id ? (string) wp_get_attachment_url($pdf_id) : '';
    $filename = 'kennys-korner-official-menu.pdf';
    if ($pdf_url !== '') {
        $path = (string) parse_url($pdf_url, PHP_URL_PATH);
        if ($path !== '') {
            $filename = basename($path);
        }
    }
    $pages = [];
    foreach ($section['pages'] ?? [] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $sources = [];
        foreach (['small' => 600, 'medium' => 1200, 'large' => 2200] as $slot => $fallback_width) {
            $prepared = kk_prepare_image($item[$slot] ?? []);
            if ($prepared['src'] === '') {
                continue;
            }
            $width = $prepared['width'] > 0 ? $prepared['width'] : $fallback_width;
            $sources[] = ['url' => $prepared['src'], 'width' => $width, 'slot' => $slot];
        }
        if ($sources === []) {
            continue;
        }
        $large = $sources[count($sources) - 1];
        foreach ($sources as $source) {
            if ($source['slot'] === 'large') {
                $large = $source;
            }
        }
        $medium = $sources[0];
        foreach ($sources as $source) {
            if ($source['slot'] === 'medium') {
                $medium = $source;
            }
        }
        $srcset = [];
        foreach ($sources as $source) {
            $srcset[] = $source['url'] . ' ' . $source['width'] . 'w';
        }
        $pages[] = [
            'id' => (string) ($item['_id'] ?? ''),
            'label' => (string) ($item['label'] ?? ''),
            'alt' => (string) ($item['alt'] ?? ''),
            'width' => (int) ($item['width'] ?? 0) ?: $large['width'],
            'height' => (int) ($item['height'] ?? 0),
            'src' => $medium['url'],
            'srcset' => implode(', ', $srcset),
            'large' => $large['url'],
        ];
    }
    return [
        'url' => $pdf_url,
        'filename' => $filename,
        'pages' => $pages,
    ];
}

function kk_purge_page_caches(): void {
    foreach (array_keys(kk_default_content()['pages']) as $key) {
        $page_id = kk_find_page_id($key);
        if ($page_id) {
            clean_post_cache($page_id);
        }
    }
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }
    if (function_exists('w3tc_flush_posts')) {
        w3tc_flush_posts();
    }
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }
    if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
        LiteSpeed_Cache_API::purge_all();
    }
    if (function_exists('sg_cachepress_purge_cache')) {
        sg_cachepress_purge_cache();
    }
    if (function_exists('cache_enabler_clear_complete_cache')) {
        cache_enabler_clear_complete_cache();
    }
    do_action('kk_content_updated');
}
