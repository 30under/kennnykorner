<?php
$general = kk_content_setting('general');
$ordering = (string) ($general['ordering_url'] ?? '');
$label = (string) ($general['order_button_label'] ?? '');
if ($ordering === '' && trim((string) ($general['order_kicker'] ?? '')) === '' && empty($general['order_title'])) {
    return;
}
?>
<section class="page-order-banner">
  <div>
    <?php if (!empty($general['order_kicker'])) : ?>
      <span><?php echo esc_html($general['order_kicker']); ?></span>
    <?php endif; ?>
    <?php kk_headline($general['order_title'] ?? [], 'h2'); ?>
  </div>
  <?php if ($ordering !== '' && $label !== '') : ?>
    <a href="<?php echo esc_url($ordering); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($label); ?> <span aria-hidden="true">→</span></a>
  <?php endif; ?>
</section>
