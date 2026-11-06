<?php

require __DIR__ . '/../wordpress/plugins/kennys-korner-core/includes/schema.php';
$content = require __DIR__ . '/../wordpress/plugins/kennys-korner-core/includes/default-content.php';
$errors = [];

foreach (kk_screens() as $screen) {
    if ($screen['storage'] === 'page') {
        $page = $content['pages'][$screen['key']] ?? null;
        if (!$page) {
            $errors[] = $screen['id'] . ' has no default page';
            continue;
        }
        $section_ids = array_column($screen['sections'], 'id');
        $default_ids = array_keys($page['sections']);
        if ($section_ids !== $default_ids) {
            $errors[] = $screen['id'] . ' sections differ: ' . implode(',', $section_ids) . ' vs ' . implode(',', $default_ids);
        }
        foreach ($screen['sections'] as $section) {
            check_fields($screen['id'] . '.' . $section['id'], $section['fields'], $page['sections'][$section['id']] ?? [], $errors);
        }
        continue;
    }
    $group = $content[$screen['group']] ?? null;
    if (!is_array($group)) {
        $errors[] = $screen['id'] . ' has no default settings';
        continue;
    }
    foreach ($screen['sections'] as $section) {
        foreach ($section['fields'] as $field) {
            if (!array_key_exists($field['name'], $group)) {
                $errors[] = $screen['id'] . '.' . $field['name'] . ' missing from defaults';
            } else {
                check_shape($screen['id'] . '.' . $field['name'], $field, $group[$field['name']], $errors);
            }
        }
    }
}

if ($errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "Schema matches demo content\n";

function check_fields(string $path, array $fields, array $values, array &$errors): void {
    foreach ($fields as $field) {
        if (!array_key_exists($field['name'], $values)) {
            $errors[] = $path . '.' . $field['name'] . ' missing from defaults';
            continue;
        }
        check_shape($path . '.' . $field['name'], $field, $values[$field['name']], $errors);
    }
}

function check_shape(string $path, array $field, $value, array &$errors): void {
    $type = $field['type'];
    if ($type === 'headline' && !is_array($value)) {
        $errors[] = $path . ' should be a headline list';
    }
    if (($type === 'image' || $type === 'file') && (!is_array($value) || !array_key_exists('asset', $value))) {
        $errors[] = $path . ' should be a media value';
    }
    if ($type === 'link') {
        if (!is_array($value)) {
            $errors[] = $path . ' should be a link';
            return;
        }
        check_fields($path, $field['fields'], $value, $errors);
    }
    if ($type === 'repeater') {
        if (!is_array($value) || $value === []) {
            return;
        }
        check_fields($path, $field['fields'], $value[0], $errors);
    }
}
