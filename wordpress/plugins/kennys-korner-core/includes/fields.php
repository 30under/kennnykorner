<?php

function kk_is_valid_link(string $url): bool {
    $url = trim($url);
    if ($url === '' || str_starts_with($url, '#') || (str_starts_with($url, '/') && !str_starts_with($url, '//'))) {
        return true;
    }
    if (str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
        return strlen($url) > 7 && strlen($url) <= 400;
    }
    if (preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $url)) {
        return true;
    }
    return function_exists('wp_http_validate_url') ? (bool) wp_http_validate_url($url) : (bool) filter_var($url, FILTER_VALIDATE_URL);
}

function kk_limit_text(string $value, int $max): string {
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function kk_clean_token(string $value): string {
    $value = strtolower(trim($value));
    return preg_match('/^[a-z0-9-]+$/', $value) ? $value : '';
}

function kk_sanitize_screen(array $screen, array $input): array {
    $errors = [];
    $sections = [];
    foreach ($screen['sections'] as $section) {
        $raw = isset($input[$section['id']]) && is_array($input[$section['id']]) ? $input[$section['id']] : [];
        $sections[$section['id']] = kk_sanitize_fields($section['fields'], $raw, $section['title'], $errors);
    }
    $page = [];
    if (!empty($screen['page_fields'])) {
        $raw_page = isset($input['_page']) && is_array($input['_page']) ? $input['_page'] : [];
        $page = kk_sanitize_fields($screen['page_fields'], $raw_page, 'Page', $errors);
    }
    return ['sections' => $sections, 'page' => $page, 'errors' => $errors];
}

function kk_sanitize_fields(array $fields, array $input, string $context, array &$errors): array {
    $output = [];
    foreach ($fields as $field) {
        $name = $field['name'];
        $label = $context . ' → ' . $field['label'];
        $raw = $input[$name] ?? null;
        $output[$name] = kk_sanitize_field($field, $raw, $label, $errors);
    }
    return $output;
}

function kk_sanitize_field(array $field, $raw, string $label, array &$errors) {
    $type = $field['type'];
    if ($type === 'text') {
        return kk_limit_text(sanitize_text_field(is_scalar($raw) ? (string) $raw : ''), 240);
    }
    if ($type === 'textarea') {
        return kk_limit_text(sanitize_textarea_field(is_scalar($raw) ? (string) $raw : ''), 5000);
    }
    if ($type === 'url') {
        $url = trim(is_scalar($raw) ? (string) wp_unslash((string) $raw) : '');
        $url = kk_limit_text($url, 400);
        if ($url !== '' && !kk_is_valid_link($url)) {
            $errors[] = $label . ' needs a valid web address, email, anchor, or site path.';
        }
        return sanitize_text_field($url);
    }
    if ($type === 'number') {
        return absint($raw);
    }
    if ($type === 'checkbox') {
        return !empty($raw);
    }
    if ($type === 'select') {
        $value = sanitize_key(is_scalar($raw) ? (string) $raw : '');
        $options = $field['options'];
        if (!isset($options[$value])) {
            $value = (string) array_key_first($options);
        }
        return $value;
    }
    if ($type === 'image' || $type === 'file') {
        return kk_sanitize_media($raw, !empty($field['with_alt']), $label, $errors);
    }
    if ($type === 'headline') {
        return kk_sanitize_headline($raw, (int) ($field['max'] ?? 4));
    }
    if ($type === 'repeater') {
        return kk_sanitize_repeater($field, $raw, $label, $errors);
    }
    if ($type === 'link') {
        $raw = is_array($raw) ? $raw : [];
        return kk_sanitize_fields($field['fields'], $raw, $label, $errors);
    }
    return '';
}

function kk_sanitize_media($raw, bool $with_alt, string $label, array &$errors): array {
    $raw = is_array($raw) ? $raw : [];
    $id = absint($raw['id'] ?? 0);
    if ($id && get_post_type($id) !== 'attachment') {
        $errors[] = $label . ' must be a file from the media library.';
        $id = 0;
    }
    $asset = kk_clean_token((string) ($raw['asset'] ?? ''));
    if ($id) {
        $asset = '';
    }
    $output = ['id' => $id, 'asset' => $asset];
    if ($with_alt) {
        $output['alt'] = kk_limit_text(sanitize_text_field((string) ($raw['alt'] ?? '')), 240);
    }
    return $output;
}

function kk_sanitize_headline($raw, int $max): array {
    $lines = [];
    if (!is_array($raw)) {
        return $lines;
    }
    foreach ($raw as $line) {
        if (!is_array($line)) {
            continue;
        }
        $before = kk_limit_text(sanitize_text_field((string) ($line['before'] ?? '')), 180);
        $accent = kk_limit_text(sanitize_text_field((string) ($line['accent'] ?? '')), 180);
        $after = kk_limit_text(sanitize_text_field((string) ($line['after'] ?? '')), 180);
        if ($before === '' && $accent === '' && $after === '') {
            continue;
        }
        $lines[] = ['before' => $before, 'accent' => $accent, 'after' => $after];
        if (count($lines) >= $max) {
            break;
        }
    }
    return $lines;
}

function kk_sanitize_repeater(array $field, $raw, string $label, array &$errors): array {
    $items = [];
    if (!is_array($raw)) {
        return $items;
    }
    $max = (int) ($field['max'] ?? 20);
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $item = kk_sanitize_fields($field['fields'], $row, $label, $errors);
        $item = kk_normalize_video_item($item);
        $id = kk_clean_token(str_replace('_', '-', (string) ($row['_id'] ?? '')));
        if ($id === '') {
            $id = 'row-' . wp_generate_password(8, false, false);
            $id = strtolower($id);
        }
        $item = ['_id' => $id] + $item;
        $items[] = $item;
        if (count($items) >= $max) {
            break;
        }
    }
    return $items;
}

function kk_repeater_token(array $item, string $index): string {
    $id = kk_clean_token(str_replace('_', '-', (string) ($item['_id'] ?? '')));
    return $id !== '' ? $id : $index;
}

function kk_render_screen(array $screen, array $sections, array $page_fields): void {
    if (!empty($screen['page_fields'])) {
        echo '<div class="kk-card" data-kk-path="_page"><h2>Page</h2>';
        kk_render_fields($screen['page_fields'], $page_fields, 'kk[_page]', '_page');
        echo '</div>';
    }
    foreach ($screen['sections'] as $section) {
        $values = $sections[$section['id']] ?? [];
        echo '<div class="kk-card" data-kk-path="' . esc_attr($section['id']) . '">';
        echo '<h2>' . esc_html($section['title']) . '</h2>';
        if (!empty($section['description'])) {
            echo '<p class="description">' . esc_html($section['description']) . '</p>';
        }
        kk_render_fields($section['fields'], is_array($values) ? $values : [], 'kk[' . $section['id'] . ']', $section['id']);
        echo '</div>';
    }
}

function kk_render_fields(array $fields, array $values, string $prefix, string $path = ''): void {
    foreach ($fields as $field) {
        $name = $field['name'];
        $child = $path === '' ? $name : $path . '.' . $name;
        $value = $values[$name] ?? null;
        kk_render_field($field, $value, $prefix . '[' . $name . ']', $child);
    }
}

function kk_render_field(array $field, $value, string $name, string $path = ''): void {
    $type = $field['type'];
    if ($type === 'headline') {
        kk_render_headline($field, is_array($value) ? $value : [], $name, $path);
        return;
    }
    if ($type === 'repeater') {
        kk_render_repeater($field, is_array($value) ? $value : [], $name, $path);
        return;
    }
    if ($type === 'link') {
        echo '<fieldset class="kk-link" data-kk-path="' . esc_attr($path) . '" data-kk-kind="link"><legend>' . esc_html($field['label']) . '</legend>';
        kk_render_fields($field['fields'], is_array($value) ? $value : [], $name, $path);
        echo '</fieldset>';
        return;
    }
    echo '<div class="kk-field" data-kk-path="' . esc_attr($path) . '" data-kk-kind="' . esc_attr($type) . '">';
    if ($type === 'image' || $type === 'file') {
        kk_render_media($field, is_array($value) ? $value : [], $name);
        kk_render_help($field);
        echo '</div>';
        return;
    }
    if ($type !== 'checkbox') {
        echo '<label><span>' . esc_html($field['label']) . '</span>';
    }
    if ($type === 'textarea') {
        echo '<textarea name="' . esc_attr($name) . '" rows="4">' . esc_textarea((string) $value) . '</textarea>';
    } elseif ($type === 'checkbox') {
        echo '<label class="kk-check"><input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked(!empty($value), true, false) . '> <span>' . esc_html($field['label']) . '</span></label>';
    } elseif ($type === 'select') {
        echo '<select name="' . esc_attr($name) . '">';
        foreach ($field['options'] as $option => $label) {
            echo '<option value="' . esc_attr((string) $option) . '" ' . selected((string) $value, (string) $option, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'number') {
        echo '<input type="number" min="0" step="1" name="' . esc_attr($name) . '" value="' . esc_attr((string) (int) $value) . '">';
    } else {
        echo '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '">';
    }
    if ($type !== 'checkbox') {
        echo '</label>';
    }
    kk_render_help($field);
    echo '</div>';
}

function kk_render_help(array $field): void {
    if (empty($field['help'])) {
        return;
    }
    $help = (string) $field['help'];
    if (str_contains($help, '{upload_limit}') && function_exists('wp_max_upload_size')) {
        $help = str_replace('{upload_limit}', size_format((int) wp_max_upload_size()), $help);
    }
    echo '<p class="description">' . esc_html($help) . '</p>';
}

function kk_render_media(array $field, array $value, string $name): void {
    $id = absint($value['id'] ?? 0);
    if (!$id && !empty($value['asset'])) {
        $id = kk_image_id($value);
    }
    $asset = $id ? '' : (string) ($value['asset'] ?? '');
    $library = (string) ($field['library'] ?? 'image');
    $stored_id = $id && get_post_type($id) === 'attachment' ? $id : 0;
    $kind = function_exists('kk_attachment_mime_kind') ? kk_attachment_mime_kind($stored_id) : '';
    echo '<div class="kk-media" data-library="' . esc_attr($library) . '">';
    echo '<strong class="kk-media-label">' . esc_html($field['label']) . '</strong>';
    echo '<input class="kk-media-id" type="hidden" name="' . esc_attr($name) . '[id]" value="' . esc_attr((string) $stored_id) . '">';
    echo '<input class="kk-media-asset" type="hidden" name="' . esc_attr($name) . '[asset]" value="' . esc_attr($asset) . '">';
    echo '<div class="kk-media-preview">';
    if ($stored_id) {
        if ($kind === 'image') {
            echo wp_get_attachment_image($stored_id, 'thumbnail');
        } else {
            echo '<span>' . esc_html(basename((string) get_attached_file($stored_id))) . '</span>';
        }
    } elseif ($asset !== '') {
        echo '<span>Demo file: ' . esc_html($asset) . '</span>';
    } else {
        echo '<span class="kk-media-empty">No file selected</span>';
    }
    echo '</div>';
    echo '<p class="kk-media-actions">';
    echo '<button type="button" class="button kk-media-select">' . esc_html($stored_id ? 'Replace' : 'Select') . '</button> ';
    echo '<button type="button" class="button-link-delete kk-media-remove">Remove</button>';
    echo '</p>';
    if ($library === 'image' && $kind === 'video') {
        echo '<p class="kk-media-note">This file is a video, so it is not used as the poster. Choose an image.</p>';
    } elseif ($library === 'video' && $stored_id && $kind !== 'video') {
        echo '<p class="kk-media-note">This file is not a video. Choose an MP4 or WebM file.</p>';
    }
    if (!empty($field['with_alt'])) {
        echo '<label class="kk-alt"><span>Alt text</span><input type="text" name="' . esc_attr($name) . '[alt]" value="' . esc_attr((string) ($value['alt'] ?? '')) . '"></label>';
    }
    echo '</div>';
}

function kk_render_headline(array $field, array $lines, string $name, string $path = ''): void {
    echo '<div class="kk-field" data-kk-path="' . esc_attr($path) . '" data-kk-kind="headline">';
    echo '<span class="kk-label">' . esc_html($field['label']) . '</span>';
    kk_render_help($field);
    $blank = ['before' => '', 'accent' => '', 'after' => ''];
    echo '<div class="kk-repeater" data-next="' . esc_attr((string) count($lines)) . '">';
    echo '<div class="kk-repeater-list">';
    foreach ($lines as $index => $line) {
        kk_render_headline_row($name, (string) $index, is_array($line) ? $line : $blank);
    }
    echo '</div>';
    echo '<template>';
    kk_render_headline_row($name, '__i__', $blank);
    echo '</template>';
    echo '<button type="button" class="button kk-repeater-add">Add line</button>';
    echo '</div></div>';
}

function kk_render_headline_row(string $name, string $index, array $line): void {
    $base = $name . '[' . $index . ']';
    echo '<div class="kk-repeater-item kk-headline-row">';
    echo '<button type="button" class="kk-drag" aria-label="Drag to reorder">↕</button>';
    echo '<label>Line <input type="text" name="' . esc_attr($base) . '[before]" value="' . esc_attr((string) ($line['before'] ?? '')) . '"></label>';
    echo '<label>Color <input type="text" name="' . esc_attr($base) . '[accent]" value="' . esc_attr((string) ($line['accent'] ?? '')) . '"></label>';
    echo '<label>After <input type="text" name="' . esc_attr($base) . '[after]" value="' . esc_attr((string) ($line['after'] ?? '')) . '"></label>';
    echo '<button type="button" class="button-link-delete kk-repeater-remove">Remove</button>';
    echo '</div>';
}

function kk_render_repeater(array $field, array $items, string $name, string $path = ''): void {
    echo '<div class="kk-field kk-repeater-field" data-kk-path="' . esc_attr($path) . '" data-kk-kind="repeater">';
    echo '<span class="kk-label">' . esc_html($field['label']) . '</span>';
    kk_render_help($field);
    echo '<div class="kk-repeater">';
    echo '<div class="kk-repeater-list">';
    foreach (array_values($items) as $index => $item) {
        kk_render_repeater_item($field, $name, (string) $index, is_array($item) ? $item : [], $path);
    }
    echo '</div>';
    echo '<template>';
    kk_render_repeater_item($field, $name, '__i__', ['_id' => '__id__'], $path);
    echo '</template>';
    $empty = $items === [];
    echo '<p class="kk-empty"' . ($empty ? '' : ' hidden') . '>Nothing here yet. Add the first ' . esc_html(strtolower($field['item_label'])) . '.</p>';
    echo '<button type="button" class="button kk-repeater-add">Add ' . esc_html(strtolower($field['item_label'])) . '</button>';
    echo '</div></div>';
}

function kk_render_repeater_item(array $field, string $name, string $index, array $item, string $path = ''): void {
    $token = kk_repeater_token($item, $index);
    $item_path = $path === '' ? $token : $path . '#' . $token;
    echo '<div class="kk-repeater-item" data-kk-path="' . esc_attr($item_path) . '" data-kk-id="' . esc_attr($token) . '">';
    echo '<div class="kk-repeater-bar">';
    echo '<button type="button" class="kk-drag" aria-label="Drag to reorder">↕</button>';
    echo '<strong>' . esc_html($field['item_label']) . '</strong>';
    echo '<button type="button" class="button-link-delete kk-repeater-remove">Remove</button>';
    echo '</div>';
    echo '<input type="hidden" name="' . esc_attr($name . '[' . $index . '][_id]') . '" value="' . esc_attr((string) ($item['_id'] ?? '')) . '">';
    kk_render_fields($field['fields'], $item, $name . '[' . $index . ']', $item_path);
    echo '</div>';
}
