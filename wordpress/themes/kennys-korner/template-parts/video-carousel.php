<?php
$page = function_exists('kk_content_page') ? kk_content_page('gallery') : [];
$videos = is_array($page['videos'] ?? null) ? $page['videos'] : [];
$slides = [];
foreach (array_values($videos['items'] ?? []) as $index => $item) {
    if (!is_array($item) || !function_exists('kk_media_video')) {
        continue;
    }
    $clip = kk_media_video($item);
    if ($clip['url'] === '') {
        continue;
    }
    $slides[] = [
        'index' => $index,
        'item' => $item,
        'clip' => $clip,
        'path' => function_exists('kk_item_path') ? kk_item_path('videos.items', $item, $index) : '',
    ];
}
if ($slides === []) {
    return;
}
$total = count($slides);
$label = 'Gallery videos';
$heading = $videos['title'][0]['before'] ?? '';
if (is_string($heading) && trim($heading) !== '') {
    $label = trim($heading);
}
?>
<section class="video-carousel" tabindex="0" aria-roledescription="carousel" aria-label="<?php echo esc_attr($label); ?>">
  <div class="video-carousel-intro">
    <?php kk_section_label((string) ($videos['eyebrow'] ?? ''), false, 'videos.eyebrow'); ?>
    <?php kk_headline($videos['title'] ?? [], 'h2', ['id' => 'gallery-video-title', 'edit' => 'videos.title']); ?>
  </div>
  <div class="video-carousel-stage<?php echo $total > 1 ? ' has-nav' : ''; ?>">
    <?php if ($total > 1) : ?>
      <button type="button" class="video-carousel-nav video-carousel-nav--prev" data-carousel-prev aria-label="Previous video"><span aria-hidden="true">‹</span></button>
    <?php endif; ?>
    <div class="video-carousel-viewport">
    <div class="video-carousel-track">
      <?php foreach ($slides as $position => $slide) : ?>
        <?php
        $item = $slide['item'];
        $clip = $slide['clip'];
        $path = $slide['path'];
        $title = trim((string) ($item['title'] ?? ''));
        $caption = trim((string) ($item['caption'] ?? ''));
        $label = $title !== '' ? $title : 'Video ' . ($position + 1);
        ?>
        <article class="video-slide<?php echo $position === 0 ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $position); ?>"<?php echo $position === 0 ? '' : ' hidden'; ?><?php echo kk_edit($path, '', false); ?>>
          <div class="video-slide-frame">
            <video class="js-carousel-video" muted playsinline preload="none" controls<?php echo $clip['poster'] !== '' ? ' poster="' . esc_url($clip['poster']) . '"' : ''; ?> aria-label="<?php echo esc_attr($label); ?>"<?php echo kk_edit($path . '.video', '', false); ?>>
              <source src="<?php echo esc_url($clip['url']); ?>" type="<?php echo esc_attr($clip['mime']); ?>">
            </video>
            <button type="button" class="video-slide-play">Play <?php echo esc_html($label); ?></button>
            <p class="video-fallback" hidden>This video could not be loaded.</p>
          </div>
          <?php if ($title !== '' || $caption !== '') : ?>
            <div class="video-slide-copy">
              <?php if ($title !== '') : ?><h3<?php echo kk_edit($path . '.title'); ?>><?php echo esc_html($title); ?></h3><?php endif; ?>
              <?php if ($caption !== '') : ?><p<?php echo kk_edit($path . '.caption'); ?>><?php echo esc_html($caption); ?></p><?php endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
    </div>
    <?php if ($total > 1) : ?>
      <button type="button" class="video-carousel-nav video-carousel-nav--next" data-carousel-next aria-label="Next video"><span aria-hidden="true">›</span></button>
    <?php endif; ?>
  </div>
  <?php if ($total > 1) : ?>
    <div class="video-carousel-bar">
      <div class="video-carousel-dots" role="group" aria-label="Choose a video">
        <?php foreach ($slides as $position => $slide) : ?>
          <button type="button" data-carousel-dot="<?php echo esc_attr((string) $position); ?>" aria-label="<?php echo esc_attr('Show video ' . ($position + 1) . ' of ' . $total); ?>"<?php echo $position === 0 ? ' aria-current="true"' : ''; ?>><span class="visually-hidden">Video <?php echo esc_html((string) ($position + 1)); ?></span></button>
        <?php endforeach; ?>
      </div>
      <p class="video-carousel-status" aria-live="polite">1 / <?php echo esc_html((string) $total); ?></p>
    </div>
  <?php endif; ?>
</section>
