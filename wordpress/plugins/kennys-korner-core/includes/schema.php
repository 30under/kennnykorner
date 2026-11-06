<?php
/**
 * Admin screen definitions. Field names match includes/default-content.php.
 */

function kk_preset_options(): array {
    return [
        'custom' => 'Custom URL or anchor',
        'ordering' => 'Online ordering link',
        'directions' => 'Directions link',
        'home' => 'Home page',
        'menu' => 'Menu page',
        'gallery' => 'Gallery page',
        'story' => 'Our Story page',
        'visit' => 'Visit Us page',
    ];
}

function kk_icon_options(): array {
    return [
        'none' => 'None',
        'arrow' => 'Arrow',
        'pin' => 'Map pin',
        'bag' => 'Bag',
        'clock' => 'Clock',
        'instagram' => 'Instagram',
    ];
}

function kk_style_options(): array {
    return [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
        'light' => 'Light',
    ];
}

function kk_text(string $name, string $label, string $help = ''): array {
    return ['type' => 'text', 'name' => $name, 'label' => $label, 'help' => $help];
}

function kk_textarea(string $name, string $label, string $help = ''): array {
    return ['type' => 'textarea', 'name' => $name, 'label' => $label, 'help' => $help];
}

function kk_url(string $name, string $label, string $help = ''): array {
    return ['type' => 'url', 'name' => $name, 'label' => $label, 'help' => $help];
}

function kk_number(string $name, string $label, string $help = ''): array {
    return ['type' => 'number', 'name' => $name, 'label' => $label, 'help' => $help];
}

function kk_checkbox(string $name, string $label, string $help = ''): array {
    return ['type' => 'checkbox', 'name' => $name, 'label' => $label, 'help' => $help];
}

function kk_select(string $name, string $label, array $options, string $help = ''): array {
    return ['type' => 'select', 'name' => $name, 'label' => $label, 'options' => $options, 'help' => $help];
}

function kk_image(string $name, string $label, string $help = '', bool $with_alt = true): array {
    return ['type' => 'image', 'name' => $name, 'label' => $label, 'help' => $help, 'with_alt' => $with_alt, 'library' => 'image'];
}

function kk_file(string $name, string $label, string $library, string $help = ''): array {
    return ['type' => 'file', 'name' => $name, 'label' => $label, 'help' => $help, 'library' => $library];
}

function kk_field_headline(string $name, string $label, string $help = ''): array {
    $help = $help !== '' ? $help : 'Each row is one line on the website. Put the teal words in Color. Leave Color empty for a normal line. Do not enter HTML.';
    return ['type' => 'headline', 'name' => $name, 'label' => $label, 'help' => $help, 'max' => 4];
}

function kk_repeater(string $name, string $label, array $fields, string $help = '', int $max = 20, string $item_label = 'Item'): array {
    return [
        'type' => 'repeater',
        'name' => $name,
        'label' => $label,
        'help' => $help,
        'fields' => $fields,
        'max' => $max,
        'item_label' => $item_label,
    ];
}

function kk_button_fields(bool $with_style = true, bool $with_icon = true): array {
    $fields = [
        kk_text('label', 'Label', 'Visible button text.'),
        kk_select('preset', 'Destination', kk_preset_options(), 'Ordering, directions, and page links stay in sync when the shared URL changes.'),
        kk_url('url', 'Custom URL', 'Used only when Destination is Custom. #menu stays on this page. A path such as /menu uses this site address.'),
        kk_checkbox('new_tab', 'Open in a new tab', 'Turn this on for ordering and directions.'),
    ];
    if ($with_style) {
        $fields[] = kk_select('style', 'Style', kk_style_options());
    }
    if ($with_icon) {
        $fields[] = kk_select('icon', 'Icon', kk_icon_options());
    }
    return $fields;
}

function kk_page_fields(): array {
    return [
        kk_text('post_title', 'Page title', 'Browser tab title. The large on-page headline is edited in the hero. Navigation labels are edited under Appearance → Menus.'),
        kk_select('post_status', 'Visibility', [
            'publish' => 'Published',
            'draft' => 'Draft — hidden from visitors',
        ], 'Draft pages are not public. Use Preview to review them while signed in.'),
        kk_textarea('meta_description', 'Meta description', 'Optional. Leave blank to omit the description tag. Do not paste the demo placeholder.'),
    ];
}

function kk_hero_fields(string $image_help): array {
    return [
        kk_text('eyebrow', 'Eyebrow'),
        kk_field_headline('headline', 'Headline'),
        kk_textarea('description', 'Description'),
        kk_image('image', 'Photo', $image_help),
        kk_text('badge', 'Badge', 'Short label on the photo. Leave blank to hide it.'),
    ];
}

function kk_screens(): array {
    static $screens = null;
    if ($screens !== null) {
        return $screens;
    }

    $screens = [
        'home' => kk_screen_home(),
        'menu' => kk_screen_menu(),
        'gallery' => kk_screen_gallery(),
        'story' => kk_screen_story(),
        'visit' => kk_screen_visit(),
        'header' => kk_screen_header(),
        'footer' => kk_screen_footer(),
        'general' => kk_screen_general(),
    ];
    return $screens;
}

function kk_screen(string $id): ?array {
    $screens = kk_screens();
    return $screens[$id] ?? null;
}

function kk_screen_home(): array {
    return [
        'id' => 'home',
        'menu_title' => 'Home',
        'page_title' => 'Home',
        'storage' => 'page',
        'key' => 'home',
        'intro' => 'Sections follow the homepage from top to bottom. Hours, ordering, directions, Instagram, and the kitchen video are edited once in General settings.',
        'page_fields' => kk_page_fields(),
        'sections' => [
            [
                'id' => 'hero',
                'title' => 'Hero',
                'description' => 'The first screen. The open-hours line uses General settings.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('headline', 'Headline'),
                    kk_textarea('description', 'Description'),
                    kk_image('image', 'Breakfast photo', 'Large photo on the right.'),
                    kk_image('logo', 'Mascot card', 'Small logo card overlapping the photo.'),
                    kk_text('fresh_note', 'Fresh note', 'Line under the buttons.'),
                    kk_text('sunburst_line_1', 'Sunburst line 1'),
                    kk_text('sunburst_line_2', 'Sunburst line 2'),
                    kk_repeater('actions', 'Buttons', kk_button_fields(), 'Designed for up to three buttons.', 3, 'Button'),
                ],
            ],
            [
                'id' => 'location',
                'title' => 'Neighborhood spot',
                'description' => 'The home-location band with the exterior photo.',
                'fields' => [
                    kk_image('image', 'Exterior photo'),
                    kk_text('stamp_line_1', 'Stamp line 1'),
                    kk_text('stamp_line_2', 'Stamp line 2'),
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    [
                        'type' => 'link',
                        'name' => 'cta',
                        'label' => 'Button',
                        'help' => '',
                        'fields' => kk_button_fields(),
                    ],
                ],
            ],
            [
                'id' => 'quick_actions',
                'title' => 'Quick actions',
                'description' => 'Three cards under the location band. Numbers update from the order.',
                'fields' => [
                    kk_repeater('items', 'Cards', [
                        kk_text('title', 'Title'),
                        kk_text('text', 'Supporting line'),
                        kk_select('preset', 'Destination', kk_preset_options()),
                        kk_url('url', 'Custom URL', 'Used only for a custom destination.'),
                        kk_checkbox('new_tab', 'Open in a new tab'),
                        kk_select('icon', 'Icon', kk_icon_options()),
                    ], 'Keep three cards so the row matches the layout.', 3, 'Card'),
                ],
            ],
            [
                'id' => 'menu',
                'title' => 'Menu preview heading',
                'description' => 'The PDF and page images are edited on the Menu screen and appear here as well.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                ],
            ],
            [
                'id' => 'kitchen',
                'title' => 'Kitchen film',
                'description' => 'Change the video file and poster in General settings. This section is the words around it.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    kk_text('link_label', 'Link label'),
                    kk_select('link_preset', 'Link destination', kk_preset_options()),
                    kk_text('video_label', 'Video label', 'Read by screen readers. It is not shown visually.'),
                    kk_text('badge', 'Badge', 'Leave blank to hide the badge.'),
                ],
            ],
            [
                'id' => 'story',
                'title' => 'Story collage',
                'description' => '',
                'fields' => [
                    kk_image('image_main', 'Large photo'),
                    kk_image('image_small', 'Small photo'),
                    kk_text('stamp_line_1', 'Stamp line 1'),
                    kk_text('stamp_line_2', 'Stamp line 2'),
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('body', 'Story', 'A blank line starts a new paragraph.'),
                    kk_text('link_label', 'Link label'),
                    kk_select('link_preset', 'Link destination', kk_preset_options()),
                ],
            ],
            [
                'id' => 'gallery',
                'title' => 'Gallery preview',
                'description' => 'The first photo is the tall tile. The next two fill the right column. The Instagram link is edited in General settings.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_repeater('images', 'Photos', [
                        kk_image('image', 'Photo'),
                    ], 'Use three photos for this mosaic.', 3, 'Photo'),
                ],
            ],
            [
                'id' => 'visit',
                'title' => 'Visit band',
                'description' => 'Hours, the location line, and directions come from General settings.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    kk_text('cta_label', 'Directions button label'),
                    kk_image('image', 'Photo'),
                ],
            ],
            [
                'id' => 'order',
                'title' => 'Order strip',
                'description' => 'This strip is only on the homepage. The button uses the ordering link from General settings. Inner pages use the shared order banner, also in General settings.',
                'fields' => [
                    kk_text('kicker', 'Kicker'),
                    kk_field_headline('title', 'Heading'),
                    kk_text('button_label', 'Button label'),
                ],
            ],
        ],
    ];
}

function kk_screen_menu(): array {
    return [
        'id' => 'menu',
        'menu_title' => 'Menu',
        'page_title' => 'Menu',
        'storage' => 'page',
        'key' => 'menu',
        'intro' => 'The homepage and the Menu page share this PDF and these preview images. Upload a replacement, then publish. Preview images are the pages visitors see; the PDF is what Open and Download use.',
        'page_fields' => kk_page_fields(),
        'sections' => [
            [
                'id' => 'hero',
                'title' => 'Page hero',
                'description' => '',
                'fields' => kk_hero_fields('Photo beside the headline.'),
            ],
            [
                'id' => 'menu',
                'title' => 'Official menu',
                'description' => 'Add the preview pages in reading order. Each page needs a large image for the zoom view. Small and medium images keep the page fast; if you only have one image, put it in Large and leave the others empty.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    kk_file('pdf', 'Menu PDF', 'application', 'The original menu. Open PDF and Download PDF both use this file.'),
                    kk_repeater('pages', 'Preview pages', [
                        kk_text('label', 'Label', 'Shown on the page, for example Page 1.'),
                        kk_textarea('alt', 'Alt text', 'Describe the page for someone who cannot see it.'),
                        kk_number('width', 'Full image width', 'Pixel width of the large image. The demo pages are 2200 wide.'),
                        kk_number('height', 'Full image height', 'Pixel height of the large image. This keeps the page from jumping while the image loads.'),
                        kk_image('small', 'Small image', 'About 600 pixels wide.', false),
                        kk_image('medium', 'Medium image', 'About 1200 pixels wide. This is the usual preview.', false),
                        kk_image('large', 'Large image', 'About 2200 pixels wide. Loaded when a visitor opens the zoom view.', false),
                    ], 'Drag the handle to change the page order.', 12, 'Menu page'),
                ],
            ],
        ],
    ];
}

function kk_screen_gallery(): array {
    return [
        'id' => 'gallery',
        'menu_title' => 'Gallery',
        'page_title' => 'Gallery',
        'storage' => 'page',
        'key' => 'gallery',
        'intro' => 'The kitchen video file is shared with the homepage and edited in General settings.',
        'page_fields' => kk_page_fields(),
        'sections' => [
            [
                'id' => 'hero',
                'title' => 'Page hero',
                'description' => '',
                'fields' => kk_hero_fields(''),
            ],
            [
                'id' => 'intro',
                'title' => 'Introduction',
                'description' => '',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                ],
            ],
            [
                'id' => 'photos',
                'title' => 'Photo wall',
                'description' => 'Order is the order on the page. The layout pattern repeats every eight photos.',
                'fields' => [
                    kk_text('caption_default', 'Caption when a photo caption is empty', 'Optional fallback. A photo with its own caption uses that instead.'),
                    kk_repeater('images', 'Photos', [
                        kk_image('image', 'Photo'),
                        kk_text('caption', 'Caption', 'Leave blank to use the fallback caption, or to hide the caption if the fallback is also blank.'),
                    ], '', 40, 'Photo'),
                ],
            ],
            [
                'id' => 'film',
                'title' => 'Video section',
                'description' => 'The video and poster come from General settings.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    kk_text('link_label', 'Link label'),
                    kk_select('link_preset', 'Link destination', kk_preset_options()),
                    kk_text('video_label', 'Video label', 'Read by screen readers.'),
                ],
            ],
        ],
    ];
}

function kk_screen_story(): array {
    return [
        'id' => 'story',
        'menu_title' => 'Our Story',
        'page_title' => 'Our Story',
        'storage' => 'page',
        'key' => 'story',
        'intro' => 'The timeline wording is the demo story. Confirm the dates and names with the restaurant before launch.',
        'page_fields' => kk_page_fields(),
        'sections' => [
            [
                'id' => 'hero',
                'title' => 'Page hero',
                'description' => '',
                'fields' => kk_hero_fields(''),
            ],
            [
                'id' => 'editorial',
                'title' => 'Editorial introduction',
                'description' => '',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('body', 'Story', 'A blank line starts a new paragraph.'),
                    kk_image('image_main', 'Main photo'),
                    kk_image('image_secondary', 'Secondary photo'),
                ],
            ],
            [
                'id' => 'timeline',
                'title' => 'Timeline',
                'description' => 'Confirm this history with the client. Icons and the wave panel are part of the design.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_repeater('items', 'Moments', [
                        kk_select('icon', 'Icon', [
                            'cup' => 'Cup',
                            'store' => 'Storefront',
                            'sun' => 'Sun',
                        ]),
                        kk_select('layout', 'Layout', [
                            'left' => 'Photo on the right, tilted left',
                            'right' => 'Photo on the right, tilted right',
                            'waves' => 'Waves, no photo',
                        ]),
                        kk_text('date', 'Date'),
                        kk_text('title', 'Title'),
                        kk_textarea('body', 'Text'),
                        kk_image('image', 'Photo', 'Ignored when the layout is Waves.'),
                        kk_text('caption', 'Caption'),
                    ], '', 12, 'Moment'),
                ],
            ],
            [
                'id' => 'values',
                'title' => 'Values',
                'description' => 'Numbers follow the order of the list.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_repeater('items', 'Values', [
                        kk_text('title', 'Title'),
                        kk_textarea('text', 'Text'),
                    ], '', 6, 'Value'),
                ],
            ],
            [
                'id' => 'quote',
                'title' => 'Quote',
                'description' => 'The large quotation mark is part of the design.',
                'fields' => [
                    kk_textarea('text', 'Quote'),
                    kk_text('link_label', 'Link label'),
                    kk_select('link_preset', 'Link destination', kk_preset_options()),
                ],
            ],
        ],
    ];
}

function kk_screen_visit(): array {
    return [
        'id' => 'visit',
        'menu_title' => 'Visit Us',
        'page_title' => 'Visit Us',
        'storage' => 'page',
        'key' => 'visit',
        'intro' => 'Opening hours, the address line, and the directions link are edited in General settings and reused everywhere.',
        'page_fields' => kk_page_fields(),
        'sections' => [
            [
                'id' => 'hero',
                'title' => 'Page hero',
                'description' => '',
                'fields' => kk_hero_fields(''),
            ],
            [
                'id' => 'hours',
                'title' => 'Hours card',
                'description' => 'The days, time, and holiday note come from General settings.',
                'fields' => [
                    kk_field_headline('title', 'Heading'),
                ],
            ],
            [
                'id' => 'location',
                'title' => 'Location card',
                'description' => 'The map drawing is part of the design. The button uses the directions link.',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_textarea('description', 'Description'),
                    kk_text('cta_label', 'Button label'),
                ],
            ],
            [
                'id' => 'photo',
                'title' => 'Photo card',
                'description' => '',
                'fields' => [
                    kk_image('image', 'Photo'),
                ],
            ],
            [
                'id' => 'plan',
                'title' => 'Plan your visit',
                'description' => '',
                'fields' => [
                    kk_text('eyebrow', 'Eyebrow'),
                    kk_field_headline('title', 'Heading'),
                    kk_repeater('items', 'Ways to visit', [
                        kk_text('title', 'Title'),
                        kk_textarea('text', 'Text'),
                    ], '', 6, 'Item'),
                ],
            ],
        ],
    ];
}

function kk_screen_header(): array {
    return [
        'id' => 'header',
        'menu_title' => 'Header',
        'page_title' => 'Header',
        'storage' => 'settings',
        'group' => 'header',
        'intro' => 'The ordering URL is edited in General settings, so the header button and every Order button stay together. Navigation labels and order are edited under Appearance → Menus.',
        'page_fields' => [],
        'sections' => [
            [
                'id' => 'brand',
                'title' => 'Brand',
                'description' => 'The accent word is shown in blue. The footer name is edited on the Footer screen.',
                'fields' => [
                    kk_image('logo', 'Logo', 'Square mark in the header. Alt text can stay empty because the restaurant name is next to it.'),
                    kk_text('brand_text', 'Name'),
                    kk_text('brand_accent', 'Accent word'),
                    kk_text('brand_aria', 'Home link label', 'Announced to screen readers on the logo link.'),
                ],
            ],
            [
                'id' => 'order',
                'title' => 'Order button',
                'description' => 'Both labels use the ordering URL from General settings.',
                'fields' => [
                    kk_text('order_label', 'Desktop label'),
                    kk_text('order_mobile_label', 'Mobile menu label'),
                ],
            ],
        ],
    ];
}

function kk_screen_footer(): array {
    return [
        'id' => 'footer',
        'menu_title' => 'Footer',
        'page_title' => 'Footer',
        'storage' => 'settings',
        'group' => 'footer',
        'intro' => 'Footer links use the same menu as the header. The copyright year is the current year and updates automatically.',
        'page_fields' => [],
        'sections' => [
            [
                'id' => 'footer',
                'title' => 'Footer',
                'description' => '',
                'fields' => [
                    kk_text('lead', 'Description', 'Example: Fresh-made breakfast & lunch.'),
                    kk_checkbox('append_hours', 'Add the current hours after the description', 'Uses the hours from General settings, so they are not typed twice.'),
                    kk_text('tagline', 'Tagline'),
                    kk_text('copyright_name', 'Copyright name'),
                ],
            ],
        ],
    ];
}

function kk_screen_general(): array {
    return [
        'id' => 'general',
        'menu_title' => 'General settings',
        'page_title' => 'General settings',
        'storage' => 'settings',
        'group' => 'general',
        'intro' => 'Change a shared detail here once. The header, footer, homepage, and Visit page all read it from this screen. Hours, the map link, and the history on Our Story still need the restaurant to confirm them.',
        'page_fields' => [],
        'sections' => [
            [
                'id' => 'ordering',
                'title' => 'Online ordering',
                'description' => 'Every Order button uses this URL.',
                'fields' => [
                    kk_url('ordering_url', 'Ordering URL', 'The demo uses the Toast page from the current site.'),
                ],
            ],
            [
                'id' => 'hours',
                'title' => 'Hours',
                'description' => 'Confirm these hours with the restaurant. The demo uses the same hours everywhere.',
                'fields' => [
                    kk_text('hours_open_label', 'Short label', 'Shown with the time in the hero and footer. Demo: Open daily.'),
                    kk_text('hours_daily_label', 'Daily label', 'Shown on the homepage visit band. Demo: Daily.'),
                    kk_text('hours_days_label', 'Days', 'Shown on Visit Us. Demo: Monday—Sunday.'),
                    kk_text('hours_time', 'Time', 'Demo: 7:00 AM—2:00 PM.'),
                    kk_text('hours_caption', 'Hours caption', 'Small label above the hours.'),
                    kk_textarea('hours_note', 'Note', 'Shown on the Visit Us hours card.'),
                ],
            ],
            [
                'id' => 'location',
                'title' => 'Location and contact',
                'description' => 'The demo has no street address, phone, or email. Those fields stay hidden until you fill them in.',
                'fields' => [
                    kk_text('location_caption', 'Location caption'),
                    kk_text('location_summary', 'Location line', 'Demo text: Find Kenny\'s Korner near you.'),
                    kk_textarea('address', 'Street address', 'Leave blank until the client confirms it. Not shown while empty.'),
                    kk_text('phone', 'Phone', 'Leave blank to hide it.'),
                    kk_text('email', 'Email', 'Leave blank to hide it. Enter an email address or a mailto link.'),
                    kk_url('directions_url', 'Directions URL', 'The demo links to a Google search for the restaurant name because no street address was in the source.'),
                ],
            ],
            [
                'id' => 'instagram',
                'title' => 'Instagram link',
                'description' => 'Shown on the homepage gallery. The demo used #gallery because no Instagram profile URL was in the source. Paste the real profile URL when you have it. Web addresses open in a new tab.',
                'fields' => [
                    kk_url('instagram_url', 'URL', 'Use #gallery to keep the current demo behavior, or paste a full Instagram URL.'),
                    kk_text('instagram_label', 'Label'),
                    kk_text('instagram_aria', 'Screen reader label', 'Announced instead of the short visible label.'),
                ],
            ],
            [
                'id' => 'video',
                'title' => 'Kitchen video',
                'description' => 'Used on the homepage and the Gallery page. The player loads metadata first and starts only when the film is on screen. If the file is larger than the upload limit ({upload_limit}), upload it by SFTP into the media library or paste a video URL.',
                'fields' => [
                    kk_file('video', 'Video file', 'video', 'MP4 is the format used by the demo.'),
                    kk_url('video_url', 'Video URL', 'Used only when no video file is selected. Useful when the host blocks large uploads.'),
                    kk_image('video_poster', 'Poster image', 'Shown before the video plays and if the visitor prefers reduced motion.'),
                ],
            ],
            [
                'id' => 'banner',
                'title' => 'Shared order banner',
                'description' => 'Shown at the bottom of Menu, Gallery, Our Story, and Visit Us. The homepage has its own order strip.',
                'fields' => [
                    kk_text('order_kicker', 'Kicker'),
                    kk_field_headline('order_title', 'Heading'),
                    kk_text('order_button_label', 'Button label'),
                ],
            ],
            [
                'id' => 'not_found',
                'title' => 'Page not found',
                'description' => 'Shown for unknown addresses.',
                'fields' => [
                    kk_image('not_found_image', 'Image'),
                    kk_text('not_found_eyebrow', 'Eyebrow'),
                    kk_text('not_found_title', 'Heading'),
                    kk_text('not_found_cta', 'Button label', 'The button returns to the homepage.'),
                ],
            ],
        ],
    ];
}
