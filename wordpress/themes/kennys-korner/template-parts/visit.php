<?php
$page = kk_content_page('visit');
$general = kk_content_setting('general');
$directions = (string) ($general['directions_url'] ?? '');
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'visit']); ?>
  <section class="visit-page-grid">
    <div class="visit-card visit-card--hours">
      <?php kk_section_label((string) ($general['hours_caption'] ?? 'Hours'), true); ?>
      <?php kk_headline($page['hours']['title'] ?? [], 'h2'); ?>
      <?php if (!empty($general['hours_days_label']) || !empty($general['hours_time'])) : ?>
        <div class="hours-row">
          <span><?php echo esc_html((string) ($general['hours_days_label'] ?? '')); ?></span>
          <strong><?php echo esc_html((string) ($general['hours_time'] ?? '')); ?></strong>
        </div>
      <?php endif; ?>
      <?php kk_paragraphs((string) ($general['hours_note'] ?? '')); ?>
    </div>
    <div class="visit-card visit-card--location">
      <?php kk_section_label((string) ($page['location']['eyebrow'] ?? '')); ?>
      <?php kk_headline($page['location']['title'] ?? [], 'h2'); ?>
      <?php kk_paragraphs((string) ($page['location']['description'] ?? '')); ?>
      <?php if (!empty($general['address'])) : ?><p class="kk-address"><?php echo nl2br(esc_html($general['address']), false); ?></p><?php endif; ?>
      <?php if ($directions !== '' && !empty($page['location']['cta_label'])) : ?>
        <a href="<?php echo esc_url($directions); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($page['location']['cta_label']); ?> <span aria-hidden="true">→</span></a>
      <?php endif; ?>
      <div class="map-visual" aria-hidden="true">
        <span class="map-road map-road--one"></span>
        <span class="map-road map-road--two"></span>
        <span class="map-pin">K</span>
      </div>
    </div>
    <div class="visit-card visit-card--photo">
      <?php kk_picture($page['photo']['image'] ?? [], ['sizes' => '(max-width: 900px) calc(100vw - 32px), 55vw']); ?>
    </div>
  </section>
  <section class="visit-help">
    <div>
      <?php kk_section_label((string) ($page['plan']['eyebrow'] ?? '')); ?>
      <?php kk_headline($page['plan']['title'] ?? [], 'h2'); ?>
    </div>
    <div class="visit-help-list">
      <?php foreach (array_values($page['plan']['items'] ?? []) as $index => $item) : ?>
        <article>
          <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
          <?php if (!empty($item['title'])) : ?><h3><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
          <?php kk_paragraphs((string) ($item['text'] ?? '')); ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
