# Trạng thái triển khai

Cập nhật: 6 tháng 11 năm 2026. Nhánh `feature/wordpress-conversion`.

## Đã xong

- Theme PHP cho trang chủ, Menu, Gallery, Our Story, Visit Us, header, footer và 404.
- Plugin quản lý nội dung, admin theo section, preview, và importer chạy khi admin chủ động bấm.
- Menu PDF dùng ảnh WebP có sẵn, lightbox zoom/pan/phím, Open PDF và Download PDF cùng một file.
- Ảnh qua attachment ID, srcset WebP khi có, JPEG khi không.
- Video đổi được trong admin, `preload="metadata"`, không tự tải hết khi mở trang.
- Import lần hai không tạo trùng trang hoặc media và không đè nội dung đã sửa.
- Gói `dist/kennys-korner-theme.zip`, `dist/kennys-korner-core.zip`, `dist/kennys-korner-seed.zip`.
- `npm run build` của demo React vẫn thành công.

## Đã kiểm tra trên máy local

WordPress 7.1.4, PHP 8.5.12, SQLite Database Integration, URL `http://127.0.0.1:8090`. Docker và MySQL không có trên máy này. `.wp-env.json` sẵn cho máy có Docker.

- Import lần đầu và lần hai: 5 trang, media theo asset key, Sample Page có sẵn không bị xóa.
- Sửa eyebrow trang chủ trong admin, Publish, trang chủ đổi ngay. Import lại không đè chữ đã sửa.
- Thêm rồi xóa một mục Plan your visit, kể cả dấu nháy trong câu. Trang `/visit/` hiện và hết mục đó đúng sau mỗi lần Publish.
- Lightbox menu: mở trang 1, mũi tên sang trang 2, Escape trả focus về nút trang 1.
- Sticky header khi đã đăng nhập nằm dưới admin bar, không bị thanh admin che, kể cả sau khi cuộn.
- Không có PHP warning trên HTML của `/`, `/menu/`, `/gallery/`, `/our-story/`, `/visit/`. `php -l` sạch trên toàn bộ file PHP của theme và plugin. `php tools/check-schema.php` khớp schema với nội dung demo.
- Overflow ngang bằng 0 tại 375, 390, 430, 768, 820, 1024, 1280 và 1440 trên trang chủ.

## Chưa kiểm tra trên hosting thật

- Cài zip trên WordPress MySQL của khách.
- Site nằm trong thư mục con.
- Cache HTML của host hoặc CDN.
- Upload video khi `upload_max_filesize` nhỏ hơn 20MB. Hướng dẫn và ô URL đã có; chưa mô phỏng giới hạn đó.
- Kéo thả chuột để đổi thứ tự repeater. Nút Add, Remove và Publish của repeater đã chạy thật. Tay cầm kéo có trên từng dòng và dùng jQuery UI Sortable.
- So pixel với https://kennnykorner.figma.site/ tại đủ tám bề rộng. Đã xem trang chủ, menu, lightbox và gallery trên WordPress và đối chiếu cấu trúc với CSS của demo.

## Việc còn lại cho khách

Xác nhận giờ mở cửa, địa chỉ, lịch sử, và link Instagram. Xem `docs/CUSTOMER-GUIDE.md`.
