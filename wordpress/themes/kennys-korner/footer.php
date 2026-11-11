<?php
$footer = kk_content_setting('footer');
$general = kk_content_setting('general');
$brand = kk_brand();
$links = kk_nav_links();
$home = function_exists('kk_page_url') ? kk_page_url('home') : home_url('/');
$footer_name = trim($brand['text'] . ' ' . $brand['accent']);
$lead = trim((string) ($footer['lead'] ?? ''));
if (!empty($footer['append_hours'])) {
    $hours = trim((string) ($general['hours_open_label'] ?? '') . ' ' . (string) ($general['hours_time'] ?? ''));
    if ($lead !== '' && $hours !== '') {
        $lead .= ' · ' . $hours;
    } elseif ($hours !== '') {
        $lead = $hours;
    }
}
$copyright = (string) ($footer['copyright_name'] ?? $footer_name);
$phone = trim((string) ($general['phone'] ?? ''));
$email = function_exists('kk_store_email') ? kk_store_email() : '';
$facebook = function_exists('kk_public_http_url') ? kk_public_http_url((string) ($general['facebook_url'] ?? '')) : '';
$instagram = function_exists('kk_public_http_url') ? kk_public_http_url((string) ($general['instagram_url'] ?? '')) : '';
?>
  <footer>
    <a class="footer-brand" href="<?php echo esc_url($home); ?>"<?php echo kk_edit('footer', 'footer', false); ?>><?php echo esc_html($footer_name !== '' ? $footer_name : "Kenny's Korner"); ?></a>
    <?php if ($lead !== '') : ?>
      <p<?php echo kk_edit('footer.lead', 'footer'); ?>><?php echo esc_html($lead); ?></p>
    <?php endif; ?>
    <?php if ($phone !== '' || $email !== '') : ?>
      <p class="kk-contact">
        <?php if ($phone !== '') : ?>
          <a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
        <?php endif; ?>
        <?php if ($email !== '') : ?>
          <a class="footer-email" href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a>
        <?php endif; ?>
      </p>
    <?php endif; ?>
    <?php if ($facebook !== '' || $instagram !== '') : ?>
      <div class="footer-social">
        <?php if ($facebook !== '') : ?>
          <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
            <?php kk_icon('facebook'); ?>
            <span>Facebook</span>
          </a>
        <?php endif; ?>
        <?php if ($instagram !== '') : ?>
          <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <?php kk_icon('instagram'); ?>
            <span>Instagram</span>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="footer-links">
      <?php foreach ($links as $link) : ?>
        <a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-bottom">
      <span<?php echo kk_edit('footer.copyright_name', 'footer'); ?>>© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($copyright !== '' ? $copyright : "Kenny's Korner"); ?></span>
      <?php if (!empty($footer['tagline'])) : ?>
        <span<?php echo kk_edit('footer.tagline', 'footer'); ?>><?php echo esc_html($footer['tagline']); ?></span>
      <?php endif; ?>
    </div>
  </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
