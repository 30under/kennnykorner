<?php
$page = kk_content_page('catering');
$request = is_array($page['request'] ?? null) ? $page['request'] : [];
$services = is_array($page['services'] ?? null) ? $page['services'] : [];
$state = function_exists('kk_catering_request_state') ? kk_catering_request_state() : ['status' => '', 'errors' => [], 'values' => []];
$values = is_array($state['values']) ? $state['values'] : [];
$errors = is_array($state['errors']) ? $state['errors'] : [];
$ready = function_exists('kk_store_email') && kk_store_email() !== '';
$button = trim((string) ($request['button_label'] ?? ''));
if ($button === '') {
    $button = 'Send request';
}
$value = static function (string $key) use ($values): string {
    return esc_attr((string) ($values[$key] ?? ''));
};
$invalid = static function (string $key) use ($errors): string {
    return isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="catering-error-' . esc_attr($key) . '"' : '';
};
?>
<main id="content" class="inner-page">
  <?php get_template_part('template-parts/page-hero', null, ['hero' => $page['hero'] ?? [], 'variant' => 'catering']); ?>
  <section class="catering-services">
    <div class="catering-services-intro">
      <?php kk_section_label((string) ($services['eyebrow'] ?? ''), false, 'services.eyebrow'); ?>
      <?php kk_headline($services['title'] ?? [], 'h2', ['edit' => 'services.title']); ?>
    </div>
    <div class="catering-services-grid">
      <?php foreach (array_values($services['items'] ?? []) as $index => $item) : ?>
        <?php
        if (!is_array($item)) {
            continue;
        }
        $path = kk_item_path('services.items', $item, $index);
        $has_image = function_exists('kk_image_id') && kk_image_id($item['image'] ?? []) > 0;
        if (trim((string) ($item['title'] ?? '')) === '' && trim((string) ($item['text'] ?? '')) === '' && !$has_image) {
            continue;
        }
        ?>
        <article class="catering-card"<?php echo kk_edit($path, '', false); ?>>
          <?php if ($has_image) : ?>
            <div class="catering-card-photo">
              <?php kk_picture($item['image'], ['sizes' => '(max-width: 900px) 92vw, 28vw', 'edit' => $path . '.image']); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($item['title'])) : ?><h3<?php echo kk_edit($path . '.title'); ?>><?php echo esc_html($item['title']); ?></h3><?php endif; ?>
          <?php kk_paragraphs((string) ($item['text'] ?? ''), '', $path . '.text'); ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="catering-request" id="catering-request">
    <div class="catering-request-copy">
      <?php kk_section_label((string) ($request['eyebrow'] ?? ''), false, 'request.eyebrow'); ?>
      <?php kk_headline($request['title'] ?? [], 'h2', ['id' => 'catering-request-title', 'edit' => 'request.title']); ?>
      <?php kk_paragraphs((string) ($request['description'] ?? ''), '', 'request.description'); ?>
    </div>
    <form class="catering-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" aria-labelledby="catering-request-title">
      <input type="hidden" name="action" value="kk_catering_request">
      <?php wp_nonce_field('kk_catering_request', 'kk_catering_nonce'); ?>
      <div class="kk-hp" aria-hidden="true" inert>
        <label for="kk-company">Company</label>
        <input id="kk-company" type="text" name="kk_company" tabindex="-1" autocomplete="off" value="">
      </div>
      <?php if ($state['status'] === 'sent') : ?>
        <p class="catering-notice catering-notice--ok" role="status">Thanks. We received your catering request and will reply by email.</p>
      <?php elseif ($state['status'] === 'unavailable' || !$ready) : ?>
        <p class="catering-notice catering-notice--bad" role="alert">This form is not ready yet. Please contact the restaurant directly.</p>
      <?php elseif ($state['status'] === 'error') : ?>
        <p class="catering-notice catering-notice--bad" role="alert">We could not send your request. Please try again.</p>
      <?php elseif ($errors !== []) : ?>
        <div class="catering-notice catering-notice--bad" role="alert">
          <p>Check the highlighted fields and send the request again.</p>
          <ul>
            <?php foreach ($errors as $message) : ?>
              <li><?php echo esc_html((string) $message); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <div class="catering-field">
        <label for="catering-name">Name</label>
        <input id="catering-name" name="guest_name" type="text" autocomplete="name" required maxlength="120" value="<?php echo $value('name'); ?>"<?php echo $invalid('name'); ?>>
        <?php if (isset($errors['name'])) : ?><p class="field-error" id="catering-error-name"><?php echo esc_html($errors['name']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-email">Email</label>
        <input id="catering-email" name="guest_email" type="email" autocomplete="email" required maxlength="160" value="<?php echo $value('email'); ?>"<?php echo $invalid('email'); ?>>
        <?php if (isset($errors['email'])) : ?><p class="field-error" id="catering-error-email"><?php echo esc_html($errors['email']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-phone">Phone</label>
        <input id="catering-phone" name="guest_phone" type="tel" autocomplete="tel" required maxlength="40" value="<?php echo $value('phone'); ?>"<?php echo $invalid('phone'); ?>>
        <?php if (isset($errors['phone'])) : ?><p class="field-error" id="catering-error-phone"><?php echo esc_html($errors['phone']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-guests">Guest count</label>
        <input id="catering-guests" name="guests" type="number" inputmode="numeric" min="1" max="5000" required value="<?php echo !empty($values['guests']) ? esc_attr((string) $values['guests']) : ''; ?>"<?php echo $invalid('guests'); ?>>
        <?php if (isset($errors['guests'])) : ?><p class="field-error" id="catering-error-guests"><?php echo esc_html($errors['guests']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-date">Event date</label>
        <input id="catering-date" name="event_date" type="date" required value="<?php echo $value('event_date'); ?>"<?php echo $invalid('event_date'); ?>>
        <?php if (isset($errors['event_date'])) : ?><p class="field-error" id="catering-error-event_date"><?php echo esc_html($errors['event_date']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-time">Event time</label>
        <input id="catering-time" name="event_time" type="time" value="<?php echo $value('event_time'); ?>"<?php echo $invalid('event_time'); ?>>
        <?php if (isset($errors['event_time'])) : ?><p class="field-error" id="catering-error-event_time"><?php echo esc_html($errors['event_time']); ?></p><?php endif; ?>
      </div>
      <div class="catering-field">
        <label for="catering-type">Event type</label>
        <input id="catering-type" name="event_type" type="text" maxlength="160" value="<?php echo $value('event_type'); ?>">
      </div>
      <div class="catering-field">
        <label for="catering-location">Location</label>
        <input id="catering-location" name="event_location" type="text" maxlength="200" value="<?php echo $value('location'); ?>">
      </div>
      <div class="catering-field is-wide">
        <label for="catering-notes">Notes</label>
        <textarea id="catering-notes" name="notes" rows="5" maxlength="4000"><?php echo esc_textarea((string) ($values['notes'] ?? '')); ?></textarea>
      </div>
      <div class="catering-field is-wide">
        <button class="action-link action-link--primary" type="submit"<?php echo $ready ? '' : ' disabled'; ?>>
          <span><?php echo esc_html($button); ?></span>
          <span class="action-link-icon" aria-hidden="true"><?php kk_icon('arrow'); ?></span>
        </button>
      </div>
    </form>
  </section>
</main>
