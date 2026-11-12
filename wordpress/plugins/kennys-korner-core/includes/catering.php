<?php

add_action('init', 'kk_ensure_catering', 20);
add_action('admin_notices', 'kk_catering_email_admin_notice');
add_action('admin_post_nopriv_kk_catering_request', 'kk_handle_catering_request');
add_action('admin_post_kk_catering_request', 'kk_handle_catering_request');
add_action('admin_post_kk_mail_test', 'kk_handle_mail_test');

function kk_ensure_catering(): void {
    if (!function_exists('kk_default_content')) {
        return;
    }
    $page_id = kk_ensure_catering_page();
    if ($page_id > 0) {
        kk_ensure_catering_menu_item($page_id);
    }
}

function kk_ensure_catering_page(): int {
    $page_id = kk_find_page_id('catering');
    if ($page_id > 0) {
        return $page_id;
    }
    $defaults = kk_default_content()['pages']['catering'] ?? null;
    if (!is_array($defaults)) {
        return 0;
    }
    $existing = get_page_by_path((string) $defaults['slug']);
    if ($existing instanceof WP_Post) {
        $key = (string) get_post_meta($existing->ID, '_kk_key', true);
        if ($key === '' || $key === 'catering') {
            if ($key === '') {
                update_post_meta($existing->ID, '_kk_key', 'catering');
            }
            $template = (string) get_post_meta($existing->ID, '_wp_page_template', true);
            if ($template === '' || $template === 'default') {
                update_post_meta($existing->ID, '_wp_page_template', (string) $defaults['template']);
            }
            $content = get_post_meta($existing->ID, '_kk_content', true);
            if (!is_array($content) || $content === []) {
                update_post_meta($existing->ID, '_kk_content', $defaults['sections']);
            }
            kk_flush_content_cache();
            return (int) $existing->ID;
        }
    }
    $created = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => (string) $defaults['title'],
        'post_name' => (string) $defaults['slug'],
        'post_content' => '',
    ], true);
    if (is_wp_error($created) || !$created) {
        return 0;
    }
    $page_id = (int) $created;
    update_post_meta($page_id, '_kk_key', 'catering');
    update_post_meta($page_id, '_wp_page_template', (string) $defaults['template']);
    update_post_meta($page_id, '_kk_content', $defaults['sections']);
    kk_flush_content_cache();
    if (!get_option('kk_catering_rewrite')) {
        flush_rewrite_rules(false);
        update_option('kk_catering_rewrite', 1, false);
    }
    return $page_id;
}

function kk_ensure_catering_menu_item(int $page_id): void {
    $saved = (int) get_option('kk_catering_menu_item', 0);
    if ($saved > 0) {
        $post = get_post($saved);
        if ($post instanceof WP_Post && $post->post_type === 'nav_menu_item' && (int) get_post_meta($saved, '_menu_item_object_id', true) === $page_id) {
            return;
        }
    }
    $locations = get_nav_menu_locations();
    if (empty($locations['primary'])) {
        return;
    }
    $menu_id = (int) $locations['primary'];
    $items = wp_get_nav_menu_items($menu_id);
    if (!is_array($items)) {
        $items = [];
    }
    $permalink = (string) get_permalink($page_id);
    foreach ($items as $item) {
        $same_page = $item->object === 'page' && (int) $item->object_id === $page_id;
        $same_url = $permalink !== '' && untrailingslashit((string) $item->url) === untrailingslashit($permalink);
        if ($same_page || $same_url) {
            update_option('kk_catering_menu_item', (int) $item->ID, false);
            return;
        }
    }
    $position = 0;
    foreach ($items as $item) {
        $position = max($position, (int) $item->menu_order);
    }
    $item_id = wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => 'Catering',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish',
        'menu-item-position' => $position + 1,
    ]);
    if (!is_wp_error($item_id) && $item_id) {
        update_option('kk_catering_menu_item', (int) $item_id, false);
    }
}

function kk_catering_request_state(): array {
    $status = isset($_GET['kk_form']) ? sanitize_key(wp_unslash($_GET['kk_form'])) : '';
    if (!in_array($status, ['sent', 'partial', 'invalid', 'error', 'unavailable'], true)) {
        $status = '';
    }
    $ref = isset($_GET['kk_ref']) ? sanitize_key(wp_unslash($_GET['kk_ref'])) : '';
    $stored = ($status === 'invalid' && $ref !== '') ? get_transient('kk_catering_' . $ref) : null;
    $stored = is_array($stored) ? $stored : [];
    return [
        'status' => $status,
        'errors' => is_array($stored['errors'] ?? null) ? $stored['errors'] : [],
        'values' => is_array($stored['values'] ?? null) ? $stored['values'] : [],
    ];
}

function kk_handle_catering_request(): void {
    $return = kk_catering_return_url();
    $result = kk_catering_process(wp_unslash($_POST));
    if ($result['status'] === 'invalid') {
        $ref = strtolower(wp_generate_password(12, false, false));
        set_transient('kk_catering_' . $ref, [
            'errors' => $result['errors'],
            'values' => $result['values'],
        ], 15 * MINUTE_IN_SECONDS);
        kk_catering_redirect($return, 'invalid', $ref);
    }
    kk_catering_redirect($return, $result['status']);
}

function kk_catering_process(array $post): array {
    $empty = ['status' => 'error', 'errors' => [], 'values' => []];
    $nonce = isset($post['kk_catering_nonce']) ? sanitize_text_field((string) $post['kk_catering_nonce']) : '';
    if (!wp_verify_nonce($nonce, 'kk_catering_request')) {
        return $empty;
    }
    $honeypot = isset($post['kk_company']) ? trim((string) $post['kk_company']) : '';
    if ($honeypot !== '') {
        return ['status' => 'sent', 'errors' => [], 'values' => []];
    }
    $rate_key = 'kk_cat_rate_' . md5(kk_catering_client_key());
    $hits = (int) get_transient($rate_key);
    if ($hits >= 5) {
        return $empty;
    }
    $values = kk_catering_values_from($post);
    $errors = kk_catering_validate($values);
    if ($errors !== []) {
        return ['status' => 'invalid', 'errors' => $errors, 'values' => $values];
    }
    $status = kk_catering_send($values, kk_store_email());
    if ($status === 'sent' || $status === 'partial') {
        set_transient($rate_key, $hits + 1, HOUR_IN_SECONDS);
    }
    return ['status' => $status, 'errors' => [], 'values' => []];
}

function kk_catering_send(array $values, string $restaurant): string {
    $restaurant = sanitize_email($restaurant);
    $guest = sanitize_email((string) ($values['email'] ?? ''));
    if (!is_email($restaurant)) {
        return 'unavailable';
    }
    if (!is_email($guest)) {
        return 'error';
    }
    $notified = wp_mail(
        $restaurant,
        kk_catering_mail_subject((string) $values['name']),
        kk_catering_mail_body($values),
        kk_catering_mail_headers((string) $values['name'], $guest)
    );
    if (!$notified) {
        return 'error';
    }
    $confirmed = wp_mail(
        $guest,
        kk_catering_guest_subject(),
        kk_catering_guest_body($values),
        kk_catering_guest_headers($restaurant)
    );
    return $confirmed ? 'sent' : 'partial';
}

function kk_catering_email_admin_notice(): void {
    if (!current_user_can('edit_pages') || kk_store_email() !== '') {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $on_plugin = ($screen && $screen->id === 'dashboard') || str_starts_with($page, 'kk-');
    if (!$on_plugin) {
        return;
    }
    echo '<div class="notice notice-warning"><p>The restaurant email is empty, so the catering form is unavailable. Add it in Kenny\'s Korner → General settings.</p></div>';
}

function kk_handle_mail_test(): void {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to send a test email.', 'kennys-korner-core'));
    }
    check_admin_referer('kk_mail_test');
    $result = kk_send_test_email();
    $type = $result === 'sent' ? 'success' : 'error';
    $messages = [
        'sent' => 'WordPress accepted the test message for the restaurant email in General settings.',
        'missing' => 'Add the restaurant email in General settings before sending a test.',
        'error' => 'WordPress could not send the test message. Check the SMTP plugin. No mailbox password is shown here.',
    ];
    set_transient('kk_notice_' . get_current_user_id(), [
        'type' => $type,
        'messages' => [$messages[$result] ?? $messages['error']],
    ], 60);
    wp_safe_redirect(admin_url('admin.php?page=kk-setup'));
    exit;
}

function kk_send_test_email(): string {
    if (!current_user_can('manage_options')) {
        return 'forbidden';
    }
    $to = kk_store_email();
    if ($to === '') {
        return 'missing';
    }
    $sent = wp_mail(
        $to,
        "Kenny's Korner email test",
        "This is a test message from Kenny's Korner.\nNo catering request was submitted.\n"
    );
    return $sent ? 'sent' : 'error';
}

function kk_catering_return_url(): string {
    $id = kk_find_page_id('catering');
    $url = $id ? (string) get_permalink($id) : home_url('/');
    return remove_query_arg(['kk_form', 'kk_ref'], $url);
}

function kk_catering_redirect(string $url, string $status, string $ref = ''): void {
    $args = ['kk_form' => $status];
    if ($ref !== '') {
        $args['kk_ref'] = $ref;
    }
    wp_safe_redirect(add_query_arg($args, $url));
    exit;
}

function kk_catering_client_key(): string {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    return preg_replace('/[^0-9a-fA-F:.]/', '', $ip) ?: 'unknown';
}

function kk_catering_submitted_values(): array {
    return kk_catering_values_from(wp_unslash($_POST));
}

function kk_catering_values_from(array $source): array {
    $date = isset($source['event_date']) ? sanitize_text_field((string) $source['event_date']) : '';
    $time = isset($source['event_time']) ? sanitize_text_field((string) $source['event_time']) : '';
    return [
        'name' => kk_limit_text(sanitize_text_field((string) ($source['guest_name'] ?? '')), 120),
        'email' => sanitize_email((string) ($source['guest_email'] ?? '')),
        'phone' => kk_limit_text(kk_catering_phone((string) ($source['guest_phone'] ?? '')), 40),
        'event_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '',
        'event_time' => preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '',
        'guests' => isset($source['guests']) && $source['guests'] !== '' ? min(5000, absint($source['guests'])) : 0,
        'event_type' => kk_limit_text(sanitize_text_field((string) ($source['event_type'] ?? '')), 160),
        'location' => kk_limit_text(sanitize_text_field((string) ($source['event_location'] ?? '')), 200),
        'notes' => kk_limit_text(sanitize_textarea_field((string) ($source['notes'] ?? '')), 4000),
    ];
}

function kk_catering_phone(string $value): string {
    $value = trim($value);
    return preg_replace('/[^0-9+().\-\s]/', '', $value) ?? '';
}

function kk_catering_validate(array $values): array {
    $errors = [];
    if ($values['name'] === '') {
        $errors['name'] = 'Enter your name.';
    }
    if ($values['email'] === '' || !is_email($values['email'])) {
        $errors['email'] = 'Enter a valid email address.';
    }
    $digits = preg_replace('/\D/', '', $values['phone']) ?? '';
    if (strlen($digits) < 7) {
        $errors['phone'] = 'Enter a phone number we can call.';
    }
    $date = DateTime::createFromFormat('Y-m-d', $values['event_date']);
    $date_ok = $date instanceof DateTime && $date->format('Y-m-d') === $values['event_date'];
    if (!$date_ok) {
        $errors['event_date'] = 'Choose the event date.';
    }
    if ($values['event_time'] !== '') {
        $time = DateTime::createFromFormat('H:i', $values['event_time']);
        if (!$time instanceof DateTime || $time->format('H:i') !== $values['event_time']) {
            $errors['event_time'] = 'Choose a valid time.';
        }
    }
    if ($values['guests'] < 1) {
        $errors['guests'] = 'Enter the number of guests.';
    }
    return $errors;
}

function kk_catering_mail_subject(string $name): string {
    $name = trim(str_replace(["\r", "\n"], ' ', $name));
    $subject = 'Catering request from ' . ($name !== '' ? $name : 'the website');
    return kk_limit_text($subject, 180);
}

function kk_catering_mail_headers(string $name, string $email): array {
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $reply = kk_catering_address_header($email, $name);
    if ($reply !== '') {
        $headers[] = 'Reply-To: ' . $reply;
    }
    return $headers;
}

function kk_catering_guest_subject(): string {
    return 'We received your catering request';
}

function kk_catering_guest_headers(string $restaurant): array {
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $reply = kk_catering_address_header($restaurant, "Kenny's Korner");
    if ($reply !== '') {
        $headers[] = 'Reply-To: ' . $reply;
    }
    return $headers;
}

function kk_catering_guest_body(array $values): string {
    $lines = [
        'Hello ' . $values['name'] . ',',
        '',
        "Kenny's Korner received your catering request and will contact you.",
        '',
        'This message is not a confirmation of the booking.',
        '',
        'Name: ' . $values['name'],
        'Email: ' . $values['email'],
        'Phone: ' . $values['phone'],
        'Event date: ' . $values['event_date'],
        'Event time: ' . ($values['event_time'] !== '' ? $values['event_time'] : 'Not given'),
        'Guests: ' . (string) $values['guests'],
        '',
        'Notes:',
        $values['notes'] !== '' ? $values['notes'] : 'None',
    ];
    return implode("\n", $lines);
}

function kk_catering_address_header(string $email, string $name = ''): string {
    $email = sanitize_email($email);
    $name = trim(str_replace(["\r", "\n", '<', '>', ',', ';', '"'], ' ', $name));
    if (!is_email($email)) {
        return '';
    }
    return $name !== '' ? $name . ' <' . $email . '>' : $email;
}

function kk_catering_mail_body(array $values): string {
    $lines = [
        'A catering request was sent from the website.',
        '',
        'Name: ' . $values['name'],
        'Email: ' . $values['email'],
        'Phone: ' . $values['phone'],
        'Event date: ' . $values['event_date'],
        'Event time: ' . ($values['event_time'] !== '' ? $values['event_time'] : 'Not given'),
        'Guests: ' . (string) $values['guests'],
        'Event type: ' . ($values['event_type'] !== '' ? $values['event_type'] : 'Not given'),
        'Location: ' . ($values['location'] !== '' ? $values['location'] : 'Not given'),
        '',
        'Notes:',
        $values['notes'] !== '' ? $values['notes'] : 'None',
    ];
    return implode("\n", $lines);
}
