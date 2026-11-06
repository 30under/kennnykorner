# Bản đồ nội dung

Nội dung sống trong plugin `kennys-korner-core`. Đổi theme không xóa nội dung. Mỗi nội dung chỉ có một màn hình sửa.

Headline là các dòng `Line` / `Color` / `After`. Phần `Color` được theme bọc bằng `em` và tô xanh. Khách không nhập HTML. Dòng trống bị bỏ qua.

Link có nhãn, đích có sẵn (ordering, directions, home, menu, gallery, story, visit) hoặc URL tùy chỉnh, và tùy chọn mở tab mới. Ô URL để trống thì nút không hiện.

Ảnh và file lưu attachment ID. Trước khi import, form giữ asset key của demo để lần lưu đầu không xóa liên kết.

## Nguồn dùng chung

Sửa một lần tại **Kenny's Korner → General settings**. Mọi vị trí bên dưới đọc cùng giá trị.

| Nội dung demo | Field | Nơi hiện |
| --- | --- | --- |
| URL Toast | `ordering_url` | Nút Order trên header, quick action, banner, strip |
| Open daily / Daily / Monday—Sunday / 7:00 AM—2:00 PM | `hours_open_label`, `hours_daily_label`, `hours_days_label`, `hours_time` | Hero, footer, Visit, ghi chú giờ |
| Ghi chú ngày lễ | `hours_note` | Visit, khi có nội dung |
| “Find us at the corner…” | `location_summary` | Visit và các dòng vị trí |
| Địa chỉ, điện thoại, email | `address`, `phone`, `email` | Để trống trong demo. Chỉ hiện khi có giá trị |
| Google search Kenny's Korner | `directions_url` | Nút Get directions |
| Follow along → `#gallery` | `instagram_url`, `instagram_label`, `instagram_aria` | Gallery preview trên trang chủ |
| `behind-the-griddle.mp4` và poster | `video`, `video_url`, `video_poster` | Kitchen film trang chủ và Gallery |

Banner đặt hàng trên các trang con dùng `order_kicker`, `order_title`, `order_button_label`. Dải đặt hàng cuối trang chủ là nội dung riêng của Home.

Trang 404 dùng `not_found_image`, `not_found_eyebrow`, `not_found_title`, `not_found_cta` trong General settings. Template: `template-parts/not-found.php`.

## Header và Footer

**Header** (`header.php`): logo, `brand_text` “Kenny's”, `brand_accent` “Korner”, nhãn nút Order desktop và mobile. Menu điều hướng là menu WordPress “Kenny's Korner”, sửa tại Appearance → Menus.

**Footer** (`footer.php`): mô tả, tùy chọn gắn giờ hiện tại, tagline “Good food. Good mood.”, tên copyright. Năm lấy từ `wp_date('Y')`.

## Trang chủ

Admin: **Kenny's Korner → Home**. Template: `template-parts/home.php`. Menu preview dùng chung `template-parts/menu-viewer.php` với trang Menu.

| Section | Nội dung demo | Field | Nghiệm thu |
| --- | --- | --- | --- |
| Hero | Eyebrow, “Good mornings / start here.”, mô tả, ảnh, mascot, Fresh every day, ba CTA, dòng giờ | `hero.*` | Đổi eyebrow hiện ngay sau Publish. Ảnh không méo. CTA trống thì ẩn |
| Neighborhood | Ảnh ngoại thất, stamp, tiêu đề, mô tả, nút | `location.*` | Stamp hai dòng. Nút dùng directions chung |
| Quick actions | Ba card 01–03 | `quick_actions.items` | Tối đa 3. Thêm, xóa, kéo thứ tự |
| Menu preview | Eyebrow và tiêu đề. PDF lấy từ trang Menu | `menu.*` | Cùng bốn trang preview với `/menu` |
| Kitchen film | Tiêu đề, mô tả, badge, nhãn link. Video lấy từ General | `kitchen.*` | `preload=metadata`, playsInline, controls, dừng khi reduced motion |
| Story collage | Hai ảnh, stamp, bài ngắn, link Our story | `story.*` | Ảnh trống thì khối ảnh thu lại |
| Gallery preview | Tiêu đề và tối đa 3 ảnh đầu | `gallery.images` | Link Instagram lấy từ General |
| Visit band | Tiêu đề, mô tả, ảnh, nhãn directions | `visit.*` | Giờ lấy từ General |
| Order strip | “Skip the wait…” | `order.*` | Nút dùng URL Toast chung |

## Menu

Admin: **Menu**. Template: `template-parts/menu.php` và `menu-viewer.php`.

Hero: eyebrow “Breakfast & lunch”, headline, mô tả, ảnh, badge “Fresh daily”.

Official menu: tiêu đề, mô tả, PDF gốc, bốn preview theo thứ tự. Mỗi trang có nhãn, alt, chiều rộng, chiều cao, ảnh small 600, medium 1200, large 2200. Open PDF và Download PDF dùng cùng file PDF. Mobile xếp dọc. Desktop xếp lưới. Ảnh `object-fit` không crop. Lightbox có zoom, pan, trang trước/sau, Escape, và trả focus về nút đã mở.

Không có chuyển PDF tự động. Thay menu bằng cách upload PDF và các ảnh preview mới, rồi Publish.

## Gallery

Admin: **Gallery**. Template: `template-parts/gallery.php`.

Hero, đoạn giới thiệu, danh sách ảnh có caption và thứ tự, section video. Video và poster lấy từ General. Caption trống dùng `caption_default`.

## Our Story

Admin: **Our Story**. Template: `template-parts/story.php`.

Hero, editorial (hai ảnh và bài), timeline (icon cup/store/sun, layout left/right/waves, ngày, tiêu đề, nội dung, ảnh, caption), values, quote và CTA. Layout `waves` là mốc “Today” và không có ảnh.

## Visit Us

Admin: **Visit Us**. Template: `template-parts/visit.php`.

Hero, tiêu đề thẻ giờ, thẻ vị trí, ảnh, và repeater Plan your visit. Giờ, địa chỉ, chỉ đường lấy từ General settings. Bản đồ vẽ là trang trí, không phải field.

## Trang trí, không có field

Mặt trời, tia nắng, cây dừa, sóng, sparkle, đường vẽ bản đồ, dấu ngoặc kép của quote, và hình timeline. Không có control margin, cỡ chữ, hoặc màu từng section.

## Template

| Key | Slug | Template |
| --- | --- | --- |
| home | `home` | `page-templates/home.php` |
| menu | `menu` | `page-templates/menu.php` |
| gallery | `gallery` | `page-templates/gallery.php` |
| story | `our-story` | `page-templates/story.php` |
| visit | `visit` | `page-templates/visit.php` |
