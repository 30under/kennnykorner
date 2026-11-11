<?php $page = kk_content_page('menu'); ?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'menu']); ?>
  <section class="menu-pdf-page"<?php echo kk_edit('menu', '', false); ?>>
    <div class="menu-pdf-page-heading">
      <?php kk_section_label((string) ($page['menu']['eyebrow'] ?? ''), false, 'menu.eyebrow'); ?>
      <?php kk_headline($page['menu']['title'] ?? [], 'h2', ['edit' => 'menu.title']); ?>
      <?php kk_paragraphs((string) ($page['menu']['description'] ?? ''), '', 'menu.description'); ?>
    </div>
    <?php get_template_part('template-parts/menu-viewer'); ?>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
