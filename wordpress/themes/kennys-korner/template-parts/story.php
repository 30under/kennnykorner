<?php
$page = kk_content_page('story');
$editorial = $page['editorial'] ?? [];
$timeline = $page['timeline'] ?? [];
$values = $page['values'] ?? [];
$quote = $page['quote'] ?? [];
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'story']); ?>
  <section class="story-editorial"<?php echo kk_edit('editorial', '', false); ?>>
    <div class="story-editorial-copy">
      <?php kk_section_label((string) ($editorial['eyebrow'] ?? ''), false, 'editorial.eyebrow'); ?>
      <?php kk_headline($editorial['title'] ?? [], 'h2', ['edit' => 'editorial.title']); ?>
      <?php kk_paragraphs((string) ($editorial['body'] ?? ''), '', 'editorial.body'); ?>
    </div>
    <div class="story-editorial-images">
      <?php kk_picture($editorial['image_main'] ?? [], ['sizes' => '(max-width: 900px) 82vw, 44vw', 'edit' => 'editorial.image_main']); ?>
      <?php kk_picture($editorial['image_secondary'] ?? [], ['sizes' => '(max-width: 900px) 150px, 210px', 'edit' => 'editorial.image_secondary']); ?>
    </div>
  </section>

  <section class="story-timeline" aria-labelledby="story-timeline-title"<?php echo kk_edit('timeline', '', false); ?>>
    <div class="story-timeline-heading">
      <?php kk_section_label((string) ($timeline['eyebrow'] ?? ''), false, 'timeline.eyebrow'); ?>
      <?php kk_headline($timeline['title'] ?? [], 'h2', ['id' => 'story-timeline-title', 'edit' => 'timeline.title']); ?>
    </div>
    <div class="timeline-list">
      <?php foreach (array_values($timeline['items'] ?? []) as $index => $item) : ?>
        <?php
        $layout = (string) ($item['layout'] ?? 'left');
        $moment = kk_item_path('timeline.items', is_array($item) ? $item : [], $index);
        ?>
        <article class="timeline-item<?php echo $layout === 'waves' ? ' timeline-item--today' : ''; ?>"<?php echo kk_edit($moment, '', false); ?>>
          <div class="timeline-marker" aria-hidden="true"><?php kk_timeline_icon((string) ($item['icon'] ?? 'sun')); ?></div>
          <div class="timeline-copy">
            <?php if (!empty($item['date'])) : ?><p class="timeline-date"<?php echo kk_edit($moment . '.date'); ?>><?php echo esc_html($item['date']); ?></p><?php endif; ?>
            <?php if (!empty($item['title'])) : ?><h3<?php echo kk_edit($moment . '.title'); ?>><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
            <?php kk_paragraphs((string) ($item['body'] ?? ''), '', $moment . '.body'); ?>
          </div>
          <?php
          $clip = function_exists('kk_media_video') ? kk_media_video(is_array($item) ? $item : []) : ['url' => '', 'poster' => '', 'mime' => 'video/mp4'];
          ?>
          <?php if ($clip['url'] !== '') : ?>
            <figure class="timeline-video">
              <div class="timeline-video-frame">
                <video class="js-story-video" muted autoplay loop playsinline controls preload="metadata"<?php echo $clip['poster'] !== '' ? ' poster="' . esc_url($clip['poster']) . '"' : ''; ?> aria-label="<?php echo esc_attr((string) ($item['title'] ?? 'Story video')); ?>"<?php echo kk_edit($moment . '.video', '', false); ?>>
                  <source src="<?php echo esc_url($clip['url']); ?>" type="<?php echo esc_attr($clip['mime']); ?>">
                </video>
                <button type="button" class="timeline-video-play" hidden>Play video</button>
                <p class="video-fallback" hidden>This video could not be loaded.</p>
              </div>
              <?php if (!empty($item['caption'])) : ?><figcaption<?php echo kk_edit($moment . '.caption'); ?>><?php echo esc_html($item['caption']); ?></figcaption><?php endif; ?>
            </figure>
          <?php elseif ($layout === 'waves') : ?>
            <div class="timeline-waves" aria-hidden="true"><span></span><span></span><span></span></div>
          <?php elseif (function_exists('kk_image_id') && kk_image_id($item['image'] ?? [])) : ?>
            <figure class="timeline-photo timeline-photo--<?php echo $layout === 'right' ? 'right' : 'left'; ?>">
              <?php kk_picture($item['image'], ['sizes' => '(max-width: 900px) calc(100vw - 112px), 32vw', 'edit' => $moment . '.image']); ?>
              <?php if (!empty($item['caption'])) : ?><figcaption<?php echo kk_edit($moment . '.caption'); ?>><?php echo esc_html($item['caption']); ?></figcaption><?php endif; ?>
            </figure>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="values-section"<?php echo kk_edit('values', '', false); ?>>
    <?php kk_section_label((string) ($values['eyebrow'] ?? ''), true, 'values.eyebrow'); ?>
    <?php kk_headline($values['title'] ?? [], 'h2', ['edit' => 'values.title']); ?>
    <div class="values-grid">
      <?php foreach (array_values($values['items'] ?? []) as $index => $item) : ?>
        <?php $value_path = kk_item_path('values.items', is_array($item) ? $item : [], $index); ?>
        <article<?php echo kk_edit($value_path, '', false); ?>>
          <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
          <?php if (!empty($item['title'])) : ?><h3<?php echo kk_edit($value_path . '.title'); ?>><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
          <?php kk_paragraphs((string) ($item['text'] ?? ''), '', $value_path . '.text'); ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="story-quote"<?php echo kk_edit('quote', '', false); ?>>
    <span aria-hidden="true">“</span>
    <?php if (!empty($quote['text'])) : ?><blockquote<?php echo kk_edit('quote.text'); ?>><?php echo esc_html($quote['text']); ?></blockquote><?php endif; ?>
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
            echo '<a href="' . $href . '"' . kk_edit('quote.link_label', '', false) . '><span' . kk_edit('quote.link_label') . '>' . esc_html($quote['link_label']) . '</span></a>';
        }
    }
    ?>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
