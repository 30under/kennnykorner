<?php
$home = kk_content_page('home');
$general = kk_content_setting('general');
$hero = $home['hero'] ?? [];
$location = $home['location'] ?? [];
$quick = $home['quick_actions']['items'] ?? [];
$menu = $home['menu'] ?? [];
$kitchen = $home['kitchen'] ?? [];
$story = $home['story'] ?? [];
$gallery = $home['gallery'] ?? [];
$visit = $home['visit'] ?? [];
$order = $home['order'] ?? [];
$ordering = (string) ($general['ordering_url'] ?? '');
$directions = (string) ($general['directions_url'] ?? '');
?>
<main id="content">
  <section class="hero" id="top"<?php echo kk_edit('hero', '', false); ?>>
    <div class="hero-sun" aria-hidden="true"></div>
    <div class="hero-copy">
      <?php kk_section_label((string) ($hero['eyebrow'] ?? ''), false, 'hero.eyebrow'); ?>
      <?php kk_headline($hero['headline'] ?? [], 'h1', ['edit' => 'hero.headline']); ?>
      <?php kk_paragraphs((string) ($hero['description'] ?? ''), 'hero-description', 'hero.description'); ?>
      <div class="hero-actions">
        <?php foreach (array_values($hero['actions'] ?? []) as $index => $action) : ?>
          <?php kk_action_link(is_array($action) ? $action : [], kk_item_path('hero.actions', is_array($action) ? $action : [], $index), '', '', ['hero' => true]); ?>
        <?php endforeach; ?>
        <?php
        $catering_id = function_exists('kk_find_page_id') ? kk_find_page_id('catering') : 0;
        $catering_page = $catering_id ? kk_content_page('catering') : [];
        $catering_label = trim((string) ($catering_page['request']['home_label'] ?? ''));
        if ($catering_id && get_post_status($catering_id) === 'publish' && $catering_label !== '') {
            kk_action_link([
                'preset' => 'custom',
                'url' => get_permalink($catering_id),
                'label' => $catering_label,
            ], '', '', '', ['hero' => true]);
        }
        ?>
      </div>
      <?php if (!empty($hero['fresh_note'])) : ?>
        <p class="hero-fresh-note"<?php echo kk_edit('hero.fresh_note'); ?>><?php echo esc_html($hero['fresh_note']); ?></p>
      <?php endif; ?>
      <?php if (!empty($general['hours_open_label']) || !empty($general['hours_time'])) : ?>
        <div class="open-note"<?php echo kk_edit('hours', 'general', false); ?>>
          <?php kk_icon('clock'); ?>
          <span>
            <?php if (!empty($general['hours_open_label'])) : ?><strong<?php echo kk_edit('hours.hours_open_label', 'general'); ?>><?php echo esc_html($general['hours_open_label']); ?></strong><?php endif; ?>
            <span<?php echo kk_edit('hours.hours_time', 'general'); ?>><?php echo esc_html((string) ($general['hours_time'] ?? '')); ?></span>
          </span>
        </div>
      <?php endif; ?>
    </div>
    <div class="coastal-horizon" aria-hidden="true">
      <svg class="coastal-palm" viewBox="0 0 150 180" fill="none">
        <path d="M67 178c8-48 7-94-1-137" stroke="currentColor" stroke-width="7" stroke-linecap="round"></path>
        <path d="M66 48C47 24 29 17 8 18c17 6 29 17 37 34M68 45C66 22 74 8 92 2c-7 14-9 28-5 43M70 48c20-21 40-27 61-20-19 2-33 10-43 26M64 45C47 35 32 35 18 43c16 1 29 7 39 19" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"></path>
      </svg>
      <span class="coastal-sun"></span>
      <span class="coastal-wave coastal-wave--one"></span>
      <span class="coastal-wave coastal-wave--two"></span>
      <span class="coastal-wave coastal-wave--three"></span>
    </div>
    <div class="hero-visual">
      <div class="hero-rays" aria-hidden="true"></div>
      <span class="sparkle sparkle--one" aria-hidden="true"></span>
      <span class="sparkle sparkle--two" aria-hidden="true"></span>
      <div class="hero-image-waves" aria-hidden="true"><span></span><span></span><span></span></div>
      <?php if (function_exists('kk_image_id') && kk_image_id($hero['image'] ?? [])) : ?>
        <div class="hero-image-frame">
          <?php kk_picture($hero['image'], [
              'priority' => true,
              'sizes' => '(max-width: 900px) calc(100vw - 32px), (orientation: portrait) and (max-width: 1100px) calc(100vw - 64px), 44vw',
              'edit' => 'hero.image',
          ]); ?>
        </div>
      <?php endif; ?>
      <?php if (function_exists('kk_image_id') && kk_image_id($hero['logo'] ?? [])) : ?>
        <div class="hero-logo-card">
          <?php kk_picture($hero['logo'], ['loading' => 'eager', 'sizes' => '205px', 'edit' => 'hero.logo']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($hero['sunburst_line_1']) || !empty($hero['sunburst_line_2'])) : ?>
        <div class="sunburst" aria-hidden="true">
          <?php if (!empty($hero['sunburst_line_1'])) : ?><span<?php echo kk_edit('hero.sunburst_line_1'); ?>><?php echo esc_html($hero['sunburst_line_1']); ?></span><?php endif; ?>
          <?php if (!empty($hero['sunburst_line_2'])) : ?><span<?php echo kk_edit('hero.sunburst_line_2'); ?>><?php echo esc_html($hero['sunburst_line_2']); ?></span><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="wave wave--one" aria-hidden="true"></div>
    <div class="wave wave--two" aria-hidden="true"></div>
  </section>

  <section class="home-location"<?php echo kk_edit('location', '', false); ?>>
    <div class="home-location-photo">
      <?php kk_picture($location['image'] ?? [], ['sizes' => '(max-width: 900px) calc(100vw - 48px), 58vw', 'edit' => 'location.image']); ?>
      <?php if (!empty($location['stamp_line_1']) || !empty($location['stamp_line_2'])) : ?>
        <span class="home-location-stamp" aria-hidden="true">
          <span<?php echo kk_edit('location.stamp_line_1'); ?>><?php echo esc_html((string) ($location['stamp_line_1'] ?? '')); ?></span>
          <?php if (!empty($location['stamp_line_2'])) : ?><strong<?php echo kk_edit('location.stamp_line_2'); ?>><?php echo esc_html($location['stamp_line_2']); ?></strong><?php endif; ?>
        </span>
      <?php endif; ?>
    </div>
    <div class="home-location-copy">
      <?php kk_section_label((string) ($location['eyebrow'] ?? ''), false, 'location.eyebrow'); ?>
      <?php kk_headline($location['title'] ?? [], 'h2', ['edit' => 'location.title']); ?>
      <?php kk_paragraphs((string) ($location['description'] ?? ''), '', 'location.description'); ?>
      <?php kk_action_link(is_array($location['cta'] ?? null) ? $location['cta'] : [], 'location.cta'); ?>
    </div>
  </section>

  <?php if ($quick !== []) : ?>
    <section class="quick-actions" aria-label="Quick links"<?php echo kk_edit('quick_actions', '', false); ?>>
      <?php foreach (array_values($quick) as $index => $item) : ?>
        <?php $quick_path = kk_item_path('quick_actions.items', is_array($item) ? $item : [], $index); ?>
        <?php
        $resolved = function_exists('kk_resolve_link') ? kk_resolve_link([
            'preset' => $item['preset'] ?? 'custom',
            'url' => $item['url'] ?? '',
            'label' => $item['title'] ?? '',
            'new_tab' => !empty($item['new_tab']),
        ]) : ['url' => '', 'new_tab' => false];
        $href = kk_esc_href($resolved['url']);
        if ($href === '') {
            continue;
        }
        ?>
        <a href="<?php echo $href; ?>"<?php echo $resolved['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?><?php echo kk_edit($quick_path, '', false); ?>>
          <span class="quick-number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
          <span>
            <strong<?php echo kk_edit($quick_path . '.title'); ?>><?php echo esc_html((string) ($item['title'] ?? '')); ?></strong>
            <?php if (!empty($item['text'])) : ?><span<?php echo kk_edit($quick_path . '.text'); ?>><?php echo esc_html($item['text']); ?></span><?php endif; ?>
          </span>
          <?php kk_icon((string) ($item['icon'] ?? 'arrow')); ?>
        </a>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <section class="menu-section" id="menu"<?php echo kk_edit('menu', '', false); ?>>
    <div class="section-heading">
      <div>
        <?php kk_section_label((string) ($menu['eyebrow'] ?? ''), false, 'menu.eyebrow'); ?>
        <?php kk_headline($menu['title'] ?? [], 'h2', ['edit' => 'menu.title']); ?>
      </div>
      <?php kk_paragraphs((string) ($menu['description'] ?? ''), '', 'menu.description'); ?>
    </div>
    <?php get_template_part('template-parts/menu-viewer'); ?>
  </section>

  <div class="section-wave-divider" aria-hidden="true"><span></span><span></span><span></span></div>

  <section class="kitchen-film"<?php echo kk_edit('kitchen', '', false); ?>>
    <div class="kitchen-film-copy">
      <?php kk_section_label((string) ($kitchen['eyebrow'] ?? ''), false, 'kitchen.eyebrow'); ?>
      <?php kk_headline($kitchen['title'] ?? [], 'h2', ['edit' => 'kitchen.title']); ?>
      <?php kk_paragraphs((string) ($kitchen['description'] ?? ''), '', 'kitchen.description'); ?>
      <?php kk_text_link((string) ($kitchen['link_preset'] ?? 'gallery'), (string) ($kitchen['link_label'] ?? ''), '', 'kitchen.link_label'); ?>
    </div>
    <div class="kitchen-film-frame">
      <div class="kitchen-film-rings" aria-hidden="true"></div>
      <?php get_template_part('template-parts/kitchen-video', null, ['label' => (string) ($kitchen['video_label'] ?? ''), 'edit' => 'video.video', 'owner' => 'general']); ?>
      <?php if (!empty($kitchen['badge'])) : ?>
        <span class="kitchen-film-badge"<?php echo kk_edit('kitchen.badge'); ?>><?php echo esc_html($kitchen['badge']); ?></span>
      <?php endif; ?>
    </div>
  </section>

  <section class="story-section" id="story"<?php echo kk_edit('story', '', false); ?>>
    <div class="story-collage">
      <?php kk_picture($story['image_main'] ?? [], ['class' => 'story-image-main', 'sizes' => '(max-width: 900px) 88vw, 42vw', 'edit' => 'story.image_main']); ?>
      <?php kk_picture($story['image_small'] ?? [], ['class' => 'story-image-small', 'sizes' => '(max-width: 900px) 42vw, 22vw', 'edit' => 'story.image_small']); ?>
      <?php if (!empty($story['stamp_line_1']) || !empty($story['stamp_line_2'])) : ?>
        <div class="story-stamp" aria-hidden="true">
          <?php if (!empty($story['stamp_line_1'])) : ?><span<?php echo kk_edit('story.stamp_line_1'); ?>><?php echo esc_html($story['stamp_line_1']); ?></span><?php endif; ?>
          <?php if (!empty($story['stamp_line_2'])) : ?><strong<?php echo kk_edit('story.stamp_line_2'); ?>><?php echo esc_html($story['stamp_line_2']); ?></strong><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="story-copy">
      <?php kk_section_label((string) ($story['eyebrow'] ?? ''), false, 'story.eyebrow'); ?>
      <?php kk_headline($story['title'] ?? [], 'h2', ['edit' => 'story.title']); ?>
      <?php kk_paragraphs((string) ($story['body'] ?? ''), '', 'story.body'); ?>
      <?php kk_text_link((string) ($story['link_preset'] ?? 'story'), (string) ($story['link_label'] ?? ''), '', 'story.link_label'); ?>
    </div>
  </section>

  <section class="gallery-section" id="gallery"<?php echo kk_edit('gallery', '', false); ?>>
    <div class="gallery-heading">
      <div>
        <?php kk_section_label((string) ($gallery['eyebrow'] ?? ''), false, 'gallery.eyebrow'); ?>
        <?php kk_headline($gallery['title'] ?? [], 'h2', ['edit' => 'gallery.title']); ?>
      </div>
      <?php
      $instagram = (string) ($general['instagram_url'] ?? '');
      $instagram_label = (string) ($general['instagram_label'] ?? '');
      if ($instagram !== '' && $instagram_label !== '') :
          $external = str_starts_with($instagram, 'http');
      ?>
        <a class="instagram-link" href="<?php echo kk_esc_href($instagram); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> aria-label="<?php echo esc_attr((string) ($general['instagram_aria'] ?? $instagram_label)); ?>"<?php echo kk_edit('instagram.instagram_label', 'general', false); ?>>
          <?php kk_icon('instagram'); ?>
          <span<?php echo kk_edit('instagram.instagram_label', 'general'); ?>><?php echo esc_html($instagram_label); ?></span>
        </a>
      <?php endif; ?>
    </div>
    <div class="gallery-grid">
      <?php foreach (array_values($gallery['images'] ?? []) as $index => $item) : ?>
        <figure<?php echo $index === 0 ? ' class="gallery-tall"' : ''; ?>>
          <?php kk_picture($item['image'] ?? [], [
              'sizes' => $index === 0 ? '(max-width: 900px) calc(100vw - 32px), 58vw' : '(max-width: 900px) 50vw, 29vw',
              'edit' => kk_item_path('gallery.images', is_array($item) ? $item : [], $index) . '.image',
          ]); ?>
        </figure>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="visit-section" id="visit"<?php echo kk_edit('visit', '', false); ?>>
    <div class="visit-content">
      <?php kk_section_label((string) ($visit['eyebrow'] ?? ''), true, 'visit.eyebrow'); ?>
      <?php kk_headline($visit['title'] ?? [], 'h2', ['edit' => 'visit.title']); ?>
      <?php kk_paragraphs((string) ($visit['description'] ?? ''), '', 'visit.description'); ?>
      <div class="visit-details">
        <?php if (!empty($general['hours_time'])) : ?>
          <div>
            <?php kk_icon('clock'); ?>
            <span>
              <small<?php echo kk_edit('hours.hours_caption', 'general'); ?>><?php echo esc_html((string) ($general['hours_caption'] ?? 'Hours')); ?></small>
              <span<?php echo kk_edit('hours.hours_time', 'general'); ?>><?php echo esc_html(trim((string) ($general['hours_daily_label'] ?? '') . (($general['hours_daily_label'] ?? '') !== '' ? ', ' : '') . (string) $general['hours_time'])); ?></span>
            </span>
          </div>
        <?php endif; ?>
        <?php if (!empty($general['location_summary']) || !empty($general['address'])) : ?>
          <div>
            <?php kk_icon('pin'); ?>
            <span>
              <small<?php echo kk_edit('location.location_caption', 'general'); ?>><?php echo esc_html((string) ($general['location_caption'] ?? 'Location')); ?></small>
              <?php echo esc_html((string) ($general['location_summary'] ?? '')); ?>
              <?php if (!empty($general['address'])) : ?><span class="kk-address"><?php echo nl2br(esc_html($general['address']), false); ?></span><?php endif; ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($directions !== '' && !empty($visit['cta_label'])) : ?>
        <?php kk_action_link([
            'label' => $visit['cta_label'],
            'preset' => 'directions',
            'url' => '',
            'new_tab' => true,
            'style' => 'light',
            'icon' => 'none',
        ], 'visit.cta_label', '', 'visit.cta_label'); ?>
      <?php endif; ?>
    </div>
    <div class="visit-image">
      <?php kk_picture($visit['image'] ?? [], ['sizes' => '(max-width: 900px) 100vw, 50vw', 'edit' => 'visit.image']); ?>
    </div>
  </section>

  <section class="order-strip" id="order"<?php echo kk_edit('order', '', false); ?>>
    <div>
      <?php if (!empty($order['kicker'])) : ?><span class="order-kicker"<?php echo kk_edit('order.kicker'); ?>><?php echo esc_html($order['kicker']); ?></span><?php endif; ?>
      <?php kk_headline($order['title'] ?? [], 'h2', ['edit' => 'order.title']); ?>
    </div>
    <?php if ($ordering !== '' && !empty($order['button_label'])) : ?>
      <?php kk_action_link([
          'label' => $order['button_label'],
          'preset' => 'ordering',
          'url' => '',
          'new_tab' => true,
          'style' => 'primary',
          'icon' => 'none',
      ], 'order.button_label', '', 'order.button_label'); ?>
    <?php endif; ?>
  </section>
</main>
