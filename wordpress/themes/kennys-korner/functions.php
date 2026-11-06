<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/markup.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('custom-logo', [
        'height' => 512,
        'width' => 512,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    register_nav_menus([
        'primary' => __('Primary menu', 'kennys-korner'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'kk-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Nunito:wght@700;800;900&display=swap',
        [],
        null
    );
    wp_enqueue_style('kk-site', get_theme_file_uri('assets/css/site.css'), ['kk-fonts'], filemtime(get_theme_file_path('assets/css/site.css')));
    wp_enqueue_style('kk-wordpress', get_theme_file_uri('assets/css/wordpress.css'), ['kk-site'], filemtime(get_theme_file_path('assets/css/wordpress.css')));
    wp_enqueue_script('kk-site', get_theme_file_uri('assets/js/site.js'), [], filemtime(get_theme_file_path('assets/js/site.js')), true);
});

add_filter('wp_resource_hints', function ($urls, $relation) {
    if ($relation === 'preconnect') {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
    }
    return $urls;
}, 10, 2);

add_action('wp_head', function () {
    if (has_site_icon()) {
        return;
    }
    echo '<link rel="icon" href="' . esc_url(get_theme_file_uri('assets/favicon.svg')) . '">' . "\n";
}, 5);

add_action('admin_notices', function () {
    if (!is_admin() || function_exists('kk_page')) {
        return;
    }
    echo '<div class="notice notice-warning"><p>Kenny\'s Korner theme needs the Kenny\'s Korner Core plugin to be active.</p></div>';
});

add_filter('template_include', function ($template) {
    if (!is_page()) {
        return $template;
    }
    $key = (string) get_post_meta(get_queried_object_id(), '_kk_key', true);
    $map = [
        'home' => 'page-templates/home.php',
        'menu' => 'page-templates/menu.php',
        'gallery' => 'page-templates/gallery.php',
        'story' => 'page-templates/story.php',
        'visit' => 'page-templates/visit.php',
    ];
    if ($key === 'home' && is_front_page()) {
        return $template;
    }
    if (isset($map[$key])) {
        $found = locate_template($map[$key]);
        if ($found) {
            return $found;
        }
    }
    return $template;
});
