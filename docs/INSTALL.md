# Cài đặt, nâng cấp và rollback

Bộ bàn giao gồm ba file, tạo lại bằng `npm run package:wordpress`:

- `dist/kennys-korner-theme.zip` — cài vào Appearance → Themes. Thư mục gốc trong zip: `kennys-korner/`.
- `dist/kennys-korner-core.zip` — cài vào Plugins. Thư mục gốc: `kennys-korner-core/`.
- `dist/kennys-korner-seed.zip` — ảnh, PDF, preview và video. Không cài như plugin.

Zip không chứa `node_modules`, secret, hoặc WordPress core. Seed khoảng 48MB vì video khoảng 20MB. Nếu hosting từ chối upload lớn, giải nén seed và copy vào `wp-content/uploads/kennys-korner-seed/` bằng SFTP. Cấu trúc bên trong là `images/`, `menu/`, `videos/`, và `media-manifest.json`.

## Cài trên WordPress sạch

1. Cài và kích hoạt theme **Kenny's Korner**.
2. Cài và kích hoạt plugin **Kenny's Korner Core**. Kích hoạt không tự nhập nội dung.
3. Vào **Kenny's Korner → Setup**.
4. Upload `kennys-korner-seed.zip`, hoặc xác nhận thư mục seed đã có trên máy chủ. Màn hình Setup báo seed tìm thấy hay chưa.
5. Bấm nhập demo. Không tick thay thế nội dung.
6. Đợi log hết lỗi. Kết quả tạo năm trang, menu “Kenny's Korner”, đặt Home làm trang chủ, và bật permalink `/%postname%/` nếu permalink đang để trống.
7. Mở trang chủ, Menu, Gallery, Our Story, Visit Us và một URL không tồn tại.

Importer chạy lại an toàn: trang và media đã có được giữ, không tạo bản sao. Muốn ghi đè nội dung demo lên chỉnh sửa của khách, tick thay thế và gõ đúng `REPLACE DEMO CONTENT`. File media đã import không bị thay file nhị phân.

Xóa dữ liệu do plugin tạo chỉ khi gõ đúng `DELETE KK CONTENT`. Gỡ plugin không xóa nội dung.

## WordPress đã có dữ liệu

Importer không đè permalink đang dùng, không đè menu chính đã gán, và không nhận một trang có cùng slug nếu trang đó không mang key của Kenny's Korner. Trang mẫu và bài viết có sẵn được giữ. Chỉ đặt Home làm trang chủ khi site đang hiện bài viết mới nhất, chưa có trang chủ tĩnh, hoặc trang chủ hiện tại đã là trang Home của theme.

## Nâng cấp

1. Sao lưu database và `wp-content/uploads`.
2. Upload zip theme và plugin mới đè bản cũ, hoặc xóa bản cũ rồi cài zip mới. Không xóa plugin nếu muốn giữ nội dung — nội dung nằm trong post meta và options, nhưng gỡ rồi cài lại vẫn giữ vì `uninstall.php` không xóa dữ liệu.
3. Vào Setup và đọc bảng kiểm tra. Không chạy lại import trừ khi cố ý.

Schema hiện tại là phiên bản 1. Bản sau sẽ migrate khi `KK_SCHEMA_VERSION` tăng.

## Rollback

Giữ zip bản đang chạy. Khi cần lùi: upload lại đúng hai zip đó. Nội dung khách đã sửa vẫn nằm trong database. Rollback code không hoàn tác một lần import có `REPLACE DEMO CONTENT`. Muốn hoàn tác nội dung, dùng bản sao lưu database.

## Cache

Plugin xóa object cache của trang vừa lưu và gọi hàm purge của các plugin phổ biến nếu chúng đang hoạt động: WP Super Cache, W3 Total Cache, WP Rocket, LiteSpeed Cache, SG Optimizer, Cache Enabler. Hosting có cache HTML riêng (Varnish, Nginx FastCGI, CDN) cần được xóa theo bảng điều khiển của host sau khi đổi nội dung. Không có cache nội dung riêng trong theme.

## Permalink và thư mục con

Link nội bộ dùng `home_url()`. Site cài trong thư mục con vẫn ra URL đúng sau khi WordPress đã cấu hình **Settings → General**. Pretty permalink cần `mod_rewrite` hoặc quy tắc tương đương của Nginx.

## Local

Docker: `wp-env start` với `.wp-env.json` (PHP 8.2). Theme, plugin, và `public/` được map vào `wp-content/uploads/kennys-korner-seed`. Sau đó vào Setup và nhập, hoặc `wp kk import`.

Không có Docker: PHP 8.0+, WP-CLI, và MySQL hoặc SQLite Database Integration. Xem `docs/IMPLEMENTATION-STATUS.md` cho môi trường đã dùng để kiểm tra.

## Giới hạn upload

Video khoảng 20MB, seed khoảng 48MB. Nếu `upload_max_filesize` hoặc `post_max_size` nhỏ hơn, Setup nói rõ giới hạn. Khi đó chọn video đã có trong Media Library hoặc dán URL video ở General settings. Preview menu luôn có thể upload tay từng ảnh.
