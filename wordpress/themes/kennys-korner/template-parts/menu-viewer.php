<?php
$menu = function_exists('kk_menu_document') ? kk_menu_document() : ['url' => '', 'filename' => '', 'pages' => []];
$pages = $menu['pages'];
?>
<div class="menu-preview js-menu-viewer">
  <?php if ($menu['url'] !== '') : ?>
    <div class="menu-preview-actions"<?php echo kk_edit('menu.pdf', '', false); ?>>
      <a href="<?php echo esc_url($menu['url']); ?>" target="_blank" rel="noopener noreferrer">
        <?php kk_icon('open'); ?>
        <span>Open PDF</span>
      </a>
      <a href="<?php echo esc_url($menu['url']); ?>" download="<?php echo esc_attr($menu['filename']); ?>">
        <?php kk_icon('download'); ?>
        <span>Download PDF</span>
      </a>
    </div>
  <?php endif; ?>
  <?php if ($pages === []) : ?>
    <p class="menu-preview-empty">The menu pages will appear here after preview images are added.</p>
  <?php else : ?>
    <div class="menu-preview-grid">
      <?php foreach ($pages as $index => $page) : ?>
        <?php $page_path = 'menu.pages#' . (($page['id'] ?? '') !== '' ? $page['id'] : (string) $index); ?>
        <button type="button" class="menu-preview-page" data-index="<?php echo esc_attr((string) $index); ?>"<?php echo kk_edit($page_path, '', false); ?> aria-label="<?php echo esc_attr('Open ' . ($page['label'] !== '' ? $page['label'] : 'menu page') . ' in full-screen viewer'); ?>">
          <span<?php echo kk_edit($page_path . '.label'); ?>><?php echo esc_html($page['label'] !== '' ? $page['label'] : 'Page ' . ($index + 1)); ?></span>
          <img
            src="<?php echo esc_url($page['src']); ?>"
            <?php if ($page['srcset'] !== '') : ?>srcset="<?php echo esc_attr($page['srcset']); ?>" sizes="(max-width: 900px) calc(100vw - 48px), 44vw"<?php endif; ?>
            alt="<?php echo esc_attr($page['alt']); ?>"
            <?php if ($page['width'] > 0) : ?>width="<?php echo esc_attr((string) $page['width']); ?>"<?php endif; ?>
            <?php if ($page['height'] > 0) : ?>height="<?php echo esc_attr((string) $page['height']); ?>"<?php endif; ?>
            loading="lazy"
            decoding="async"
          >
        </button>
      <?php endforeach; ?>
    </div>
    <script type="application/json" class="js-menu-data"><?php
      echo str_replace('</', '<\/', (string) wp_json_encode($pages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    ?></script>
  <?php endif; ?>
</div>
