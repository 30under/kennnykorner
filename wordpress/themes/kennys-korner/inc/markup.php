<?php

function kk_content_page(string $key): array {
    return function_exists('kk_page') ? kk_page($key) : [];
}

function kk_content_setting(string $group): array {
    return function_exists('kk_setting') ? kk_setting($group) : [];
}

function kk_esc_href(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (preg_match('/^(#|\/(?!\/))/', $url)) {
        return esc_attr($url);
    }
    return esc_url($url);
}

function kk_edit(string $path, string $owner = '', bool $bind = true): string {
    if ($path === '' || !function_exists('kk_is_live_editor') || !kk_is_live_editor()) {
        return '';
    }
    $html = ' data-kk-edit="' . esc_attr($path) . '"';
    if ($bind) {
        $html .= ' data-kk-bind="' . esc_attr($path) . '"';
    }
    if ($owner !== '' && function_exists('kk_live_screen') && $owner !== kk_live_screen()) {
        $html .= ' data-kk-screen="' . esc_attr($owner) . '"';
    }
    return $html;
}

function kk_item_path(string $base, array $item, int $index): string {
    $raw = str_replace('_', '-', strtolower(trim((string) ($item['_id'] ?? ''))));
    $token = preg_match('/^[a-z0-9-]+$/', $raw) ? $raw : (string) $index;
    return $base . '#' . $token;
}

function kk_section_label(string $text, bool $light = false, string $edit = '', string $owner = ''): void {
    if (trim($text) === '') {
        return;
    }
    echo '<p class="section-label' . ($light ? ' section-label--light' : '') . '"' . kk_edit($edit, $owner) . '>' . esc_html($text) . '</p>';
}

function kk_headline($lines, string $tag, array $attrs = []): void {
    if (!is_array($lines) || !in_array($tag, ['h1', 'h2', 'h3'], true)) {
        return;
    }
    $rendered = [];
    foreach ($lines as $line) {
        if (!is_array($line)) {
            continue;
        }
        $piece = esc_html((string) ($line['before'] ?? ''));
        $accent = (string) ($line['accent'] ?? '');
        if ($accent !== '') {
            $piece .= '<em>' . esc_html($accent) . '</em>';
        }
        $piece .= esc_html((string) ($line['after'] ?? ''));
        if ($piece !== '') {
            $rendered[] = $piece;
        }
    }
    if ($rendered === []) {
        return;
    }
    $edit = (string) ($attrs['edit'] ?? '');
    $owner = (string) ($attrs['owner'] ?? '');
    $attributes = kk_edit($edit, $owner);
    foreach ($attrs as $key => $value) {
        if (!in_array($key, ['id', 'class'], true)) {
            continue;
        }
        $attributes .= ' ' . $key . '="' . esc_attr((string) $value) . '"';
    }
    echo '<' . $tag . $attributes . '>' . implode('<br>', $rendered) . '</' . $tag . '>';
}

function kk_paragraphs(string $text, string $class = '', string $edit = '', string $owner = ''): void {
    $chunks = preg_split("/\n\s*\n/", trim($text)) ?: [];
    foreach ($chunks as $chunk) {
        $chunk = trim((string) $chunk);
        if ($chunk === '') {
            continue;
        }
        echo '<p' . ($class !== '' ? ' class="' . esc_attr($class) . '"' : '') . kk_edit($edit, $owner) . '>' . nl2br(esc_html($chunk), false) . '</p>';
    }
}

function kk_picture($image, array $args = []): void {
    if (!function_exists('kk_prepare_image')) {
        return;
    }
    $prepared = kk_prepare_image($image);
    if ($prepared['src'] === '') {
        return;
    }
    $priority = !empty($args['priority']);
    $sizes = (string) ($args['sizes'] ?? '(max-width: 900px) 100vw, 50vw');
    $class = (string) ($args['class'] ?? '');
    $loading = $priority ? 'eager' : (string) ($args['loading'] ?? 'lazy');
    $edit = (string) ($args['edit'] ?? '');
    $owner = (string) ($args['owner'] ?? '');
    echo '<picture class="optimized-picture"' . kk_edit($edit, $owner) . '>';
    if ($prepared['webp'] !== '') {
        echo '<source type="image/webp" sizes="' . esc_attr($sizes) . '" srcset="' . esc_attr($prepared['webp']) . '">';
    }
    echo '<img';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    echo ' src="' . esc_url($prepared['src']) . '" alt="' . esc_attr($prepared['alt']) . '"';
    if ($prepared['srcset'] !== '') {
        echo ' srcset="' . esc_attr($prepared['srcset']) . '" sizes="' . esc_attr($sizes) . '"';
    }
    if ($prepared['width'] > 0) {
        echo ' width="' . esc_attr((string) $prepared['width']) . '"';
    }
    if ($prepared['height'] > 0) {
        echo ' height="' . esc_attr((string) $prepared['height']) . '"';
    }
    echo ' loading="' . esc_attr($loading) . '" decoding="async"';
    if ($priority) {
        echo ' fetchpriority="high"';
    }
    echo '>';
    echo '</picture>';
}

function kk_icon(string $name): void {
    $icons = [
        'arrow' => '<path d="M5 12h14M14 7l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'pin' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="2"/>',
        'clock' => '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'bag' => '<path d="M5 8h14l1 13H4L5 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.5 6.5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'previous' => '<path d="m14.5 6-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'next' => '<path d="m9.5 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'minus' => '<path d="M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'plus' => '<path d="M12 6v12M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'download' => '<path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'open' => '<path d="M13 5h6v6M19 5l-8 8M10 7H5v12h12v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
    if (!isset($icons[$name])) {
        return;
    }
    echo '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none">' . $icons[$name] . '</svg>';
}

function kk_timeline_icon(string $name): void {
    $icons = [
        'cup' => '<path d="M12 18h24v9a12 12 0 0 1-12 12 12 12 0 0 1-12-12v-9Z"/><path d="M36 21h2a5 5 0 0 1 0 10h-3"/><path d="M18 13c-2-2 2-4 0-7M25 13c-2-2 2-4 0-7M32 13c-2-2 2-4 0-7"/>',
        'store' => '<path d="M9 20h30l-3-9H12l-3 9Z"/><path d="M12 23v16h24V23M18 39V29h12v10"/><path d="M9 20c0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0"/>',
        'sun' => '<circle cx="24" cy="24" r="8"/><path d="M24 5v7M24 36v7M5 24h7M36 24h7M10.5 10.5l5 5M32.5 32.5l5 5M37.5 10.5l-5 5M15.5 32.5l-5 5"/>',
    ];
    $markup = $icons[$name] ?? $icons['sun'];
    echo '<svg viewBox="0 0 48 48" aria-hidden="true">' . $markup . '</svg>';
}

function kk_action_link(array $link, string $edit = '', string $owner = '', string $label_edit = '', array $options = []): void {
    if (!function_exists('kk_resolve_link')) {
        return;
    }
    $resolved = kk_resolve_link($link);
    $href = kk_esc_href($resolved['url']);
    if ($href === '' || $resolved['label'] === '') {
        return;
    }
    $hero = !empty($options['hero']);
    $style = in_array($resolved['style'], ['primary', 'secondary', 'light'], true) ? $resolved['style'] : 'primary';
    $class = $hero ? 'action-link hero-action' : 'action-link action-link--' . $style;
    echo '<a class="' . esc_attr($class) . '" href="' . $href . '"' . kk_edit($edit, $owner, false);
    if ($resolved['new_tab']) {
        echo ' target="_blank" rel="noopener noreferrer"';
    }
    echo '>';
    if (!$hero && $resolved['icon'] !== '' && $resolved['icon'] !== 'none') {
        echo '<span class="action-link-icon">';
        kk_icon($resolved['icon']);
        echo '</span>';
    }
    $label_path = $label_edit !== '' ? $label_edit : ($edit !== '' ? $edit . '.label' : '');
    echo '<span' . kk_edit($label_path, $owner) . '>' . esc_html($resolved['label']) . '</span>';
    if ($hero) {
        echo '<span class="action-link-icon" aria-hidden="true">';
        kk_icon('arrow');
        echo '</span>';
    } else {
        kk_icon('arrow');
    }
    echo '</a>';
}

function kk_text_link(string $preset, string $label, string $url = '', string $edit = '', string $owner = ''): void {
    if (!function_exists('kk_resolve_link') || $label === '') {
        return;
    }
    $resolved = kk_resolve_link([
        'preset' => $preset,
        'url' => $url,
        'label' => $label,
        'new_tab' => in_array($preset, ['ordering', 'directions'], true),
    ]);
    $href = kk_esc_href($resolved['url']);
    if ($href === '') {
        return;
    }
    echo '<a class="text-link" href="' . $href . '"' . kk_edit($edit, $owner, false);
    if ($resolved['new_tab']) {
        echo ' target="_blank" rel="noopener noreferrer"';
    }
    echo '><span' . kk_edit($edit, $owner) . '>' . esc_html($label) . '</span> ';
    kk_icon('arrow');
    echo '</a>';
}

function kk_nav_links(): array {
    $locations = get_nav_menu_locations();
    if (!empty($locations['primary'])) {
        $items = wp_get_nav_menu_items($locations['primary']);
        if (is_array($items) && $items !== []) {
            $links = [];
            foreach ($items as $item) {
                if ((int) $item->menu_item_parent !== 0) {
                    continue;
                }
                if ($item->object === 'page' && get_post_status((int) $item->object_id) !== 'publish' && !current_user_can('edit_pages')) {
                    continue;
                }
                $classes = (array) $item->classes;
                $links[] = [
                    'label' => $item->title,
                    'url' => $item->url,
                    'active' => in_array('current-menu-item', $classes, true) || in_array('current_page_item', $classes, true),
                    'new_tab' => $item->target === '_blank',
                ];
            }
            if ($links !== []) {
                return $links;
            }
        }
    }
    $fallback = [
        'home' => 'Home',
        'menu' => 'Menu',
        'gallery' => 'Gallery',
        'catering' => 'Catering',
        'story' => 'Our story',
        'visit' => 'Visit us',
    ];
    $links = [];
    foreach ($fallback as $key => $label) {
        $page_id = function_exists('kk_find_page_id') ? kk_find_page_id($key) : 0;
        $active = $key === 'home' ? is_front_page() : ($page_id && is_page($page_id));
        $links[] = [
            'label' => $label,
            'url' => function_exists('kk_page_url') ? kk_page_url($key) : home_url('/'),
            'active' => $active,
            'new_tab' => false,
        ];
    }
    return $links;
}

function kk_brand(): array {
    $header = kk_content_setting('header');
    $text = (string) ($header['brand_text'] ?? '');
    $accent = (string) ($header['brand_accent'] ?? '');
    if ($text === '' && $accent === '') {
        $text = "Kenny's";
        $accent = 'Korner';
    }
    return [
        'text' => $text,
        'accent' => $accent,
        'aria' => (string) ($header['brand_aria'] ?? "Kenny's Korner home"),
        'logo' => $header['logo'] ?? [],
        'order_label' => (string) ($header['order_label'] ?? 'Order online'),
        'order_mobile_label' => (string) ($header['order_mobile_label'] ?? 'Online ordering'),
    ];
}

function kk_ordering_url(): string {
    $general = kk_content_setting('general');
    return (string) ($general['ordering_url'] ?? '');
}
