# Cài đặt, nâng cấp và rollback

Bộ bàn giao gồm ba file, tạo lại bằng `npm run package:wordpress`:

- `dist/kennys-korner-theme.zip` — cài vào Appearance → Themes. Thư mục gốc trong zip: `kennys-korner/`.
- `dist/kennys-korner-core.zip` — cài vào Plugins. Thư mục gốc: `kennys-korner-core/`.
- `dist/kennys-korner-seed.zip` — ảnh, PDF, preview và video. Không cài như plugin.

Zip không chứa `node_modules`, secret, hoặc WordPress core. Seed khoảng 48MB vì video khoảng 20MB. File `kennys-korner-seed.zip` có thư mục gốc `seed/`. Upload zip đó trong Setup. Plugin giải nén vào `wp-content/uploads/kennys-korner-seed/seed/` và nhận đúng cấu trúc này. Không cần chuyển `images`, `menu`, `videos` lên một cấp.

ZipArchive chỉ giải nén file mà PHP đã nhận. Nó không nâng `upload_max_filesize` hay `post_max_size`. Nếu request web vượt một trong hai giới hạn, PHP hủy body trước khi plugin thấy file. Khi đó giải nén cùng zip bằng File Manager hoặc SFTP vào `wp-content/uploads/kennys-korner-seed/` và giữ nguyên thư mục `seed` bên trong.

Setup từ chối đường dẫn tuyệt đối, `..`, file PHP và `.htaccess`. Chỉ administrator (`manage_options`) vào được Setup và chạy importer.

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

Schema hiện tại là phiên bản 2. Nâng cấp theme/plugin không ghi đè nội dung đã lưu. Lần chạy đầu sau bản 1.2.0 chỉ tạo trang Catering nếu chưa có, và thêm mục Catering vào menu chính nếu mục đó chưa tồn tại.

## Rollback

Giữ zip bản đang chạy. Khi cần lùi: upload lại đúng hai zip đó. Nội dung khách đã sửa vẫn nằm trong database. Rollback code không hoàn tác một lần import có `REPLACE DEMO CONTENT`. Muốn hoàn tác nội dung, dùng bản sao lưu database.

## Cache

Plugin xóa object cache của trang vừa lưu và gọi hàm purge của các plugin phổ biến nếu chúng đang hoạt động: WP Super Cache, W3 Total Cache, WP Rocket, LiteSpeed Cache, SG Optimizer, Cache Enabler. Hosting có cache HTML riêng (Varnish, Nginx FastCGI, CDN) cần được xóa theo bảng điều khiển của host sau khi đổi nội dung. Không có cache nội dung riêng trong theme.

## Permalink và thư mục con

Link nội bộ dùng `home_url()`. Site cài trong thư mục con vẫn ra URL đúng sau khi WordPress đã cấu hình **Settings → General**. Pretty permalink cần `mod_rewrite` hoặc quy tắc tương đương của Nginx.

## Local

Docker: `wp-env start` với `.wp-env.json` (PHP 8.2, MySQL). Theme, plugin, và `public/` được map vào `wp-content/uploads/kennys-korner-seed`. Sau đó vào Setup và nhập, hoặc `wp kk import`. File này chưa được chạy trên máy nghiệm thu vì không có Docker.

Không có Docker: PHP 8.0+, WP-CLI, và MySQL hoặc SQLite Database Integration. Máy nghiệm thu dùng WordPress 7.1.4, PHP 8.5.12, WP-CLI 2.12.0 và SQLite. Xem `docs/IMPLEMENTATION-STATUS.md`.

## Giới hạn upload

Giới hạn tính trên PHP đang phục vụ request web, không phải lệnh `php` trong terminal. Trên máy nghiệm thu, CLI đọc `/opt/homebrew/etc/php/8.5/php.ini` và báo `upload_max_filesize=2M`, `post_max_size=8M`. Process web cổng 8091 được khởi động với `-d upload_max_filesize=64M -d post_max_size=64M`, nên request tới site đó đo được 64M và 64M. Seed 48MB upload được vì nằm dưới hai giới hạn của process web đó. ZipArchive không tham gia vào việc vượt giới hạn.

Khi post lớn hơn `post_max_size`, PHP để `$_POST` và `$_FILES` rỗng. Setup báo đúng hai giá trị ini và hướng dẫn giải nén zip bằng File Manager hoặc SFTP. WordPress Studio trên máy này phục vụ site bằng PHP 8.4 với `upload_max_filesize=2G` và `post_max_size=2G`. Sửa giờ trong General không sửa chữ trong file PDF.
