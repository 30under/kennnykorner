<?php
$hero = is_array($args['hero'] ?? null) ? $args['hero'] : [];
$variant = (string) ($args['variant'] ?? '');
$class = 'page-hero' . ($variant !== '' ? ' page-hero--' . sanitize_html_class($variant) : '');
?>
<section class="<?php echo esc_attr($class); ?>">
  <div class="page-hero-copy">
    <?php kk_section_label((string) ($hero['eyebrow'] ?? '')); ?>
    <?php kk_headline($hero['headline'] ?? [], 'h1'); ?>
    <?php kk_paragraphs((string) ($hero['description'] ?? '')); ?>
  </div>
  <?php if (function_exists('kk_image_id') && kk_image_id($hero['image'] ?? []) ) : ?>
    <div class="page-hero-image">
      <div class="page-hero-rays" aria-hidden="true"></div>
      <?php kk_picture($hero['image'], [
          'priority' => true,
          'sizes' => '(max-width: 900px) 94vw, 44vw',
      ]); ?>
      <?php if (!empty($hero['badge'])) : ?>
        <span aria-hidden="true"><?php echo esc_html($hero['badge']); ?></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
