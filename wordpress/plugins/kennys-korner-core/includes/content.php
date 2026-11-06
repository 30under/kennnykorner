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
    if ($page_id && kk_is_preview_request($page_id)) {
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
    $all = get_option('kk_settings', []);
    $defaults = kk_default_content()[$group] ?? [];
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

function kk_video_source(): array {
    $general = kk_setting('general');
    $file_id = kk_image_id($general['video'] ?? []);
    $url = $file_id ? (string) wp_get_attachment_url($file_id) : '';
    if ($url === '') {
        $url = (string) ($general['video_url'] ?? '');
    }
    $poster_id = kk_image_id($general['video_poster'] ?? []);
    $poster = $poster_id ? (string) wp_get_attachment_url($poster_id) : '';
    $mime = 'video/mp4';
    if ($file_id) {
        $mime = get_post_mime_type($file_id) ?: 'video/mp4';
    } elseif (preg_match('/\.webm($|\?)/i', $url)) {
        $mime = 'video/webm';
    }
    return ['url' => $url, 'poster' => $poster, 'mime' => $mime];
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
