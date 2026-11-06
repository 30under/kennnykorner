<?php
$page = kk_content_page('story');
$editorial = $page['editorial'] ?? [];
$timeline = $page['timeline'] ?? [];
$values = $page['values'] ?? [];
$quote = $page['quote'] ?? [];
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'story']); ?>
  <section class="story-editorial">
    <div class="story-editorial-copy">
      <?php kk_section_label((string) ($editorial['eyebrow'] ?? '')); ?>
      <?php kk_headline($editorial['title'] ?? [], 'h2'); ?>
      <?php kk_paragraphs((string) ($editorial['body'] ?? '')); ?>
    </div>
    <div class="story-editorial-images">
      <?php kk_picture($editorial['image_main'] ?? [], ['sizes' => '(max-width: 900px) 82vw, 44vw']); ?>
      <?php kk_picture($editorial['image_secondary'] ?? [], ['sizes' => '(max-width: 900px) 150px, 210px']); ?>
    </div>
  </section>

  <section class="story-timeline" aria-labelledby="story-timeline-title">
    <div class="story-timeline-heading">
      <?php kk_section_label((string) ($timeline['eyebrow'] ?? '')); ?>
      <?php kk_headline($timeline['title'] ?? [], 'h2', ['id' => 'story-timeline-title']); ?>
    </div>
    <div class="timeline-list">
      <?php foreach ($timeline['items'] ?? [] as $item) : ?>
        <?php $layout = (string) ($item['layout'] ?? 'left'); ?>
        <article class="timeline-item<?php echo $layout === 'waves' ? ' timeline-item--today' : ''; ?>">
          <div class="timeline-marker" aria-hidden="true"><?php kk_timeline_icon((string) ($item['icon'] ?? 'sun')); ?></div>
          <div class="timeline-copy">
            <?php if (!empty($item['date'])) : ?><p class="timeline-date"><?php echo esc_html($item['date']); ?></p><?php endif; ?>
            <?php if (!empty($item['title'])) : ?><h3><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
            <?php kk_paragraphs((string) ($item['body'] ?? '')); ?>
          </div>
          <?php if ($layout === 'waves') : ?>
            <div class="timeline-waves" aria-hidden="true"><span></span><span></span><span></span></div>
          <?php elseif (function_exists('kk_image_id') && kk_image_id($item['image'] ?? [])) : ?>
            <figure class="timeline-photo timeline-photo--<?php echo $layout === 'right' ? 'right' : 'left'; ?>">
              <?php kk_picture($item['image'], ['sizes' => '(max-width: 900px) calc(100vw - 112px), 32vw']); ?>
              <?php if (!empty($item['caption'])) : ?><figcaption><?php echo esc_html($item['caption']); ?></figcaption><?php endif; ?>
            </figure>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="values-section">
    <?php kk_section_label((string) ($values['eyebrow'] ?? ''), true); ?>
    <?php kk_headline($values['title'] ?? [], 'h2'); ?>
    <div class="values-grid">
      <?php foreach (array_values($values['items'] ?? []) as $index => $item) : ?>
        <article>
          <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
          <?php if (!empty($item['title'])) : ?><h3><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
          <?php kk_paragraphs((string) ($item['text'] ?? '')); ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="story-quote">
    <span aria-hidden="true">“</span>
    <?php if (!empty($quote['text'])) : ?><blockquote><?php echo esc_html($quote['text']); ?></blockquote><?php endif; ?>
    <?php
    if (!empty($quote['link_label']) && function_exists('kk_resolve_link')) {
        $resolved = kk_resolve_link([
            'preset' => $quote['link_preset'] ?? 'visit',
            'url' => '',
            'label' => $quote['link_label'],
            'new_tab' => false,
        ]);
        $href = kk_esc_href($resolved['url']);
        if ($href !== '') {
            echo '<a href="' . $href . '">' . esc_html($quote['link_label']) . '</a>';
        }
    }
    ?>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
