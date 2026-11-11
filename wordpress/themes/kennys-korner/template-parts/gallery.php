<?php
$page = kk_content_page('gallery');
$photos = $page['photos'] ?? [];
$film = $page['film'] ?? [];
$ordering = kk_ordering_url();
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? []]); ?>
  <section class="gallery-page-section"<?php echo kk_edit('photos', '', false); ?>>
    <div class="gallery-intro">
      <?php kk_section_label((string) ($page['intro']['eyebrow'] ?? ''), false, 'intro.eyebrow'); ?>
      <?php kk_headline($page['intro']['title'] ?? [], 'h2', ['edit' => 'intro.title']); ?>
      <?php kk_paragraphs((string) ($page['intro']['description'] ?? ''), '', 'intro.description'); ?>
    </div>
    <div class="photo-wall">
      <?php foreach (array_values($photos['images'] ?? []) as $index => $item) : ?>
        <?php
        $caption = trim((string) ($item['caption'] ?? ''));
        if ($caption === '') {
            $caption = trim((string) ($photos['caption_default'] ?? ''));
        }
        ?>
        <?php $photo_path = kk_item_path('photos.images', is_array($item) ? $item : [], $index); ?>
        <figure class="photo-wall-item photo-wall-item--<?php echo esc_attr((string) (($index % 8) + 1)); ?>"<?php echo kk_edit($photo_path, '', false); ?>>
          <?php kk_picture($item['image'] ?? [], ['sizes' => '(max-width: 900px) 100vw, 58vw', 'edit' => $photo_path . '.image']); ?>
          <?php if ($caption !== '') : ?><figcaption<?php echo kk_edit($photo_path . '.caption'); ?>><?php echo esc_html($caption); ?></figcaption><?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
    <?php get_template_part('template-parts/video-carousel'); ?>
    <div class="gallery-film">
      <div class="gallery-film-video">
        <?php get_template_part('template-parts/kitchen-video', null, ['label' => (string) ($film['video_label'] ?? ''), 'edit' => 'video.video', 'owner' => 'general']); ?>
      </div>
      <div class="gallery-film-copy">
        <?php kk_section_label((string) ($film['eyebrow'] ?? ''), true, 'film.eyebrow'); ?>
        <?php kk_headline($film['title'] ?? [], 'h2', ['edit' => 'film.title']); ?>
        <?php kk_paragraphs((string) ($film['description'] ?? ''), '', 'film.description'); ?>
        <?php if (($film['link_label'] ?? '') !== '' && $ordering !== '' && ($film['link_preset'] ?? '') === 'ordering') : ?>
          <a href="<?php echo esc_url($ordering); ?>" target="_blank" rel="noopener noreferrer"<?php echo kk_edit('film.link_label', '', false); ?>><span<?php echo kk_edit('film.link_label'); ?>><?php echo esc_html($film['link_label']); ?></span> <span aria-hidden="true">→</span></a>
        <?php else : ?>
          <?php kk_text_link((string) ($film['link_preset'] ?? ''), (string) ($film['link_label'] ?? ''), '', 'film.link_label'); ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php get_template_part('template-parts/order-banner'); ?>
</main>
