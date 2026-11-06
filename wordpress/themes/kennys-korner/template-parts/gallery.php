<?php
$page = kk_content_page('gallery');
$photos = $page['photos'] ?? [];
$film = $page['film'] ?? [];
$ordering = kk_ordering_url();
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? []]); ?>
  <section class="gallery-page-section">
    <div class="gallery-intro">
      <?php kk_section_label((string) ($page['intro']['eyebrow'] ?? '')); ?>
      <?php kk_headline($page['intro']['title'] ?? [], 'h2'); ?>
      <?php kk_paragraphs((string) ($page['intro']['description'] ?? '')); ?>
    </div>
    <div class="photo-wall">
      <?php foreach (array_values($photos['images'] ?? []) as $index => $item) : ?>
        <?php
        $caption = trim((string) ($item['caption'] ?? ''));
        if ($caption === '') {
            $caption = trim((string) ($photos['caption_default'] ?? ''));
        }
        ?>
        <figure class="photo-wall-item photo-wall-item--<?php echo esc_attr((string) (($index % 8) + 1)); ?>">
          <?php kk_picture($item['image'] ?? [], ['sizes' => '(max-width: 900px) 100vw, 58vw']); ?>
          <?php if ($caption !== '') : ?><figcaption><?php echo esc_html($caption); ?></figcaption><?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
    <div class="gallery-film">
      <div class="gallery-film-video">
        <?php get_template_part('template-parts/kitchen-video', null, ['label' => (string) ($film['video_label'] ?? '')]); ?>
      </div>
      <div class="gallery-film-copy">
        <?php kk_section_label((string) ($film['eyebrow'] ?? ''), true); ?>
        <?php kk_headline($film['title'] ?? [], 'h2'); ?>
        <?php kk_paragraphs((string) ($film['description'] ?? '')); ?>
        <?php if (($film['link_label'] ?? '') !== '' && $ordering !== '' && ($film['link_preset'] ?? '') === 'ordering') : ?>
          <a href="<?php echo esc_url($ordering); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($film['link_label']); ?> <span aria-hidden="true">→</span></a>
        <?php else : ?>
          <?php kk_text_link((string) ($film['link_preset'] ?? ''), (string) ($film['link_label'] ?? '')); ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
