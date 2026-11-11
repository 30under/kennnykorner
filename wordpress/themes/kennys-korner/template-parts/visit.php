<?php
$page = kk_content_page('visit');
$general = kk_content_setting('general');
$directions = (string) ($general['directions_url'] ?? '');
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'visit']); ?>
  <section class="visit-page-grid">
    <div class="visit-card visit-card--hours"<?php echo kk_edit('hours', '', false); ?>>
      <?php kk_section_label((string) ($general['hours_caption'] ?? 'Hours'), true, 'hours.hours_caption', 'general'); ?>
      <?php kk_headline($page['hours']['title'] ?? [], 'h2', ['edit' => 'hours.title']); ?>
      <?php $store_email = function_exists('kk_store_email') ? kk_store_email() : ''; ?>
      <?php if (!empty($general['hours_days_label']) || !empty($general['hours_time']) || $store_email !== '') : ?>
        <div class="hours-panel">
          <?php if (!empty($general['hours_days_label']) || !empty($general['hours_time'])) : ?>
            <div class="hours-row">
              <span<?php echo kk_edit('hours.hours_days_label', 'general'); ?>><?php echo esc_html((string) ($general['hours_days_label'] ?? '')); ?></span>
              <strong<?php echo kk_edit('hours.hours_time', 'general'); ?>><?php echo esc_html((string) ($general['hours_time'] ?? '')); ?></strong>
            </div>
          <?php endif; ?>
          <?php if ($store_email !== '') : ?>
            <p class="visit-email"><a href="<?php echo esc_url('mailto:' . $store_email); ?>"><?php echo esc_html($store_email); ?></a></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php kk_paragraphs((string) ($general['hours_note'] ?? ''), '', 'hours.hours_note', 'general'); ?>
    </div>
    <div class="visit-card visit-card--location"<?php echo kk_edit('location', '', false); ?>>
      <?php kk_section_label((string) ($page['location']['eyebrow'] ?? ''), false, 'location.eyebrow'); ?>
      <?php kk_headline($page['location']['title'] ?? [], 'h2', ['edit' => 'location.title']); ?>
      <?php kk_paragraphs((string) ($page['location']['description'] ?? ''), '', 'location.description'); ?>
      <?php if (!empty($general['address'])) : ?><p class="kk-address"<?php echo kk_edit('location.address', 'general'); ?>><?php echo nl2br(esc_html($general['address']), false); ?></p><?php endif; ?>
      <?php if ($directions !== '' && !empty($page['location']['cta_label'])) : ?>
        <a href="<?php echo esc_url($directions); ?>" target="_blank" rel="noopener noreferrer"<?php echo kk_edit('location.cta_label', '', false); ?>><span<?php echo kk_edit('location.cta_label'); ?>><?php echo esc_html($page['location']['cta_label']); ?></span> <span aria-hidden="true">→</span></a>
      <?php endif; ?>
      <div class="map-visual" aria-hidden="true">
        <span class="map-road map-road--one"></span>
        <span class="map-road map-road--two"></span>
        <span class="map-pin">K</span>
      </div>
    </div>
    <div class="visit-card visit-card--photo">
      <?php kk_picture($page['photo']['image'] ?? [], ['sizes' => '(max-width: 900px) calc(100vw - 32px), 55vw', 'edit' => 'photo.image']); ?>
    </div>
  </section>
  <section class="visit-help"<?php echo kk_edit('plan', '', false); ?>>
    <div>
      <?php kk_section_label((string) ($page['plan']['eyebrow'] ?? ''), false, 'plan.eyebrow'); ?>
      <?php kk_headline($page['plan']['title'] ?? [], 'h2', ['edit' => 'plan.title']); ?>
    </div>
    <div class="visit-help-list">
      <?php foreach (array_values($page['plan']['items'] ?? []) as $index => $item) : ?>
        <?php $plan_path = kk_item_path('plan.items', is_array($item) ? $item : [], $index); ?>
        <article<?php echo kk_edit($plan_path, '', false); ?>>
          <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
          <?php if (!empty($item['title'])) : ?><h3<?php echo kk_edit($plan_path . '.title'); ?>><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
          <?php kk_paragraphs((string) ($item['text'] ?? ''), '', $plan_path . '.text'); ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
