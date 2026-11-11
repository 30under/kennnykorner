<?php
$brand = kk_brand();
$links = kk_nav_links();
$ordering = kk_ordering_url();
$home = function_exists('kk_page_url') ? kk_page_url('home') : home_url('/');
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content">Skip to content</a>
<div class="site-shell">
  <header class="site-header">
    <a class="brand" href="<?php echo esc_url($home); ?>" aria-label="<?php echo esc_attr($brand['aria']); ?>"<?php echo kk_edit('brand', 'header', false); ?>>
      <span class="brand-mark">
        <?php kk_picture($brand['logo'], ['loading' => 'eager', 'sizes' => '52px', 'edit' => 'brand.logo', 'owner' => 'header']); ?>
      </span>
      <span class="brand-name">
        <span<?php echo kk_edit('brand.brand_text', 'header'); ?>><?php echo esc_html($brand['text']); ?></span>
        <?php if ($brand['accent'] !== '') : ?>
          <strong<?php echo kk_edit('brand.brand_accent', 'header'); ?>><?php echo esc_html($brand['accent']); ?></strong>
        <?php endif; ?>
      </span>
    </a>

    <nav class="desktop-nav" aria-label="Main navigation"<?php echo kk_edit('navigation', 'menus', false); ?>>
      <?php foreach ($links as $link) : ?>
        <a class="<?php echo $link['active'] ? 'is-active' : ''; ?>" href="<?php echo esc_url($link['url']); ?>"<?php echo $link['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($link['label']); ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($ordering !== '' && $brand['order_label'] !== '') : ?>
      <a class="header-order" href="<?php echo esc_url($ordering); ?>" target="_blank" rel="noopener noreferrer"<?php echo kk_edit('order.order_label', 'header'); ?>><?php echo esc_html($brand['order_label']); ?></a>
    <?php endif; ?>

    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-navigation" aria-label="Open menu">
      <span></span>
      <span></span>
    </button>

    <nav id="mobile-navigation" class="mobile-nav" aria-label="Mobile navigation">
      <?php foreach ($links as $link) : ?>
        <a href="<?php echo esc_url($link['url']); ?>"<?php echo $link['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($link['label']); ?></a>
      <?php endforeach; ?>
      <?php if ($ordering !== '' && $brand['order_mobile_label'] !== '') : ?>
        <a href="<?php echo esc_url($ordering); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($brand['order_mobile_label']); ?></a>
      <?php endif; ?>
    </nav>
  </header>
