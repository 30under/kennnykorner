<?php $general = kk_content_setting('general'); ?>
<main id="content" class="not-found">
  <?php kk_picture($general['not_found_image'] ?? [], ['loading' => 'eager', 'sizes' => '150px']); ?>
  <?php kk_section_label((string) ($general['not_found_eyebrow'] ?? '404 · Wrong turn')); ?>
  <h1><?php echo esc_html((string) ($general['not_found_title'] ?? 'This is not the Korner you were looking for.')); ?></h1>
  <a href="<?php echo esc_url(function_exists('kk_page_url') ? kk_page_url('home') : home_url('/')); ?>"><?php echo esc_html((string) ($general['not_found_cta'] ?? 'Back to homepage →')); ?></a>
</main>
