<?php
$key = (string) get_post_meta(get_queried_object_id(), '_kk_key', true);
get_header();
if ($key === 'home') {
    get_template_part('template-parts/home');
} elseif (have_posts()) {
    the_post();
    get_template_part('template-parts/generic');
}
get_footer();
