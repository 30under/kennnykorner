<?php
$label = (string) ($args['label'] ?? '');
$edit = (string) ($args['edit'] ?? '');
$owner = (string) ($args['owner'] ?? '');
$video = function_exists('kk_video_source') ? kk_video_source() : ['url' => '', 'poster' => '', 'mime' => 'video/mp4'];
if ($video['url'] !== '') :
?>
  <video class="js-kitchen-video" muted loop playsinline controls preload="metadata"<?php echo $video['poster'] !== '' ? ' poster="' . esc_url($video['poster']) . '"' : ''; ?> aria-label="<?php echo esc_attr($label); ?>"<?php echo function_exists('kk_edit') ? kk_edit($edit, $owner, false) : ''; ?>>
    <source src="<?php echo esc_url($video['url']); ?>" type="<?php echo esc_attr($video['mime']); ?>">
  </video>
<?php elseif ($video['poster'] !== '') : ?>
  <img src="<?php echo esc_url($video['poster']); ?>" alt="<?php echo esc_attr($label); ?>" loading="lazy" decoding="async">
<?php endif; ?>
