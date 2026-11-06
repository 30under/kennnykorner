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
$email = trim((string) ($general['email'] ?? ''));
?>
  <footer>
    <a class="footer-brand" href="<?php echo esc_url($home); ?>"><?php echo esc_html($footer_name !== '' ? $footer_name : "Kenny's Korner"); ?></a>
    <?php if ($lead !== '') : ?>
      <p><?php echo esc_html($lead); ?></p>
    <?php endif; ?>
    <?php if ($phone !== '' || $email !== '') : ?>
      <p class="kk-contact">
        <?php if ($phone !== '') : ?>
          <a href="<?php echo esc_url('tel:' . preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
        <?php endif; ?>
        <?php if ($email !== '') : ?>
          <?php $mail = str_starts_with($email, 'mailto:') ? $email : 'mailto:' . $email; ?>
          <a href="<?php echo esc_url($mail); ?>"><?php echo esc_html(str_starts_with($email, 'mailto:') ? substr($email, 7) : $email); ?></a>
        <?php endif; ?>
      </p>
    <?php endif; ?>
    <div class="footer-links">
      <?php foreach ($links as $link) : ?>
        <a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-bottom">
      <span>© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($copyright !== '' ? $copyright : "Kenny's Korner"); ?></span>
      <?php if (!empty($footer['tagline'])) : ?>
        <span><?php echo esc_html($footer['tagline']); ?></span>
      <?php endif; ?>
    </div>
  </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
