<?php
$hero = is_array($args['hero'] ?? null) ? $args['hero'] : [];
$variant = (string) ($args['variant'] ?? '');
$has_image = function_exists('kk_image_id') && kk_image_id($hero['image'] ?? []);
$class = 'page-hero' . ($variant !== '' ? ' page-hero--' . sanitize_html_class($variant) : '') . ($has_image ? '' : ' page-hero--copy');
?>
<section class="<?php echo esc_attr($class); ?>"<?php echo kk_edit('hero', '', false); ?>>
  <div class="page-hero-copy">
    <?php kk_section_label((string) ($hero['eyebrow'] ?? ''), false, 'hero.eyebrow'); ?>
    <?php kk_headline($hero['headline'] ?? [], 'h1', ['edit' => 'hero.headline']); ?>
    <?php kk_paragraphs((string) ($hero['description'] ?? ''), '', 'hero.description'); ?>
  </div>
  <?php if ($has_image) : ?>
    <div class="page-hero-image">
      <div class="page-hero-rays" aria-hidden="true"></div>
      <?php kk_picture($hero['image'], [
          'priority' => true,
          'sizes' => '(max-width: 900px) 94vw, 44vw',
          'edit' => 'hero.image',
      ]); ?>
      <?php if (!empty($hero['badge'])) : ?>
        <span aria-hidden="true"<?php echo kk_edit('hero.badge'); ?>><?php echo esc_html($hero['badge']); ?></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
