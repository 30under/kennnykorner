# Trạng thái triển khai

Cập nhật: 6 tháng 11 năm 2026. Nhánh `feature/wordpress-conversion`. Bản dưới đây chỉ ghi những gì đã chạy trên site cài từ ZIP.

## Đã xong trong source

- Theme PHP cho trang chủ, Menu, Gallery, Our Story, Visit Us, header, footer và 404.
- Plugin quản lý nội dung, admin theo section, preview, và importer chỉ chạy khi administrator bấm.
- Giờ, link đặt món, chỉ đường, Instagram và video bếp có một nguồn ở General settings.
- Setup có bảng “Confirm with the restaurant” cho giờ, địa chỉ, điện thoại, email, chỉ đường, Instagram và lịch sử demo.
- Gói `dist/kennys-korner-theme.zip`, `dist/kennys-korner-core.zip`, `dist/kennys-korner-seed.zip`.

## Môi trường đã kiểm tra

Lệnh trên máy này:

- `php -v`: PHP 8.5.12
- `wp core version`: WordPress 7.1.4
- `wp --info`: WP-CLI 2.12.0
- `php -m`: có `gd`, `zip`, `pdo_sqlite`, `sqlite3`. Không có Imagick.
- `docker`, `mysql` và `mariadbd` không có trong PATH. `.wp-env.json` (PHP 8.2, MySQL) chưa được chạy.

Site nghiệm thu: `http://127.0.0.1:8091`, thư mục `.local/acceptance/wordpress`, database SQLite `.local/acceptance/kk.sqlite` qua drop-in `db.php`. Plugin SQLite Database Integration 3.1.3 nằm trong site nhưng WP-CLI liệt kê nó là inactive; kết nối database đi qua drop-in. Theme và plugin là thư mục thật, cài từ zip, không phải symlink.

Khách chưa có thông tin hosting. Giới hạn tương thích đã ghi trong header: WordPress 6.4 trở lên, đã thử 7.1.4; PHP 8.0 trở lên, đã thử 8.5.12. Chưa thử MySQL, MariaDB, thư mục con, cache HTML của host, hoặc PHP 8.0–8.4.

CLI `php -r` đọc `/opt/homebrew/etc/php/8.5/php.ini`: `upload_max_filesize=2M`, `post_max_size=8M`, `memory_limit=128M`. Request web tới `http://127.0.0.1:8091/kk-limits.php` trên process `php -S` báo `sapi=cli-server`, `upload=64M`, `post=64M`, `memory=512M`, vì process đó được khởi động với `-d upload_max_filesize=64M -d post_max_size=64M -d memory_limit=512M`. Seed 48MB đi qua được vì nhỏ hơn hai giới hạn của process web. ZipArchive chỉ chạy sau khi PHP đã nhận file. Lần thử khi post còn 8M, PHP hủy upload và Setup hướng dẫn File Manager. WordPress Studio đo trên request web: PHP 8.4, upload 2G, post 2G.

## Đã kiểm tra đạt trên bản ZIP

1. `npm run package:wordpress` tạo ba zip. Theme được cài và kích hoạt trước plugin. Trang chủ trả HTTP 200, không fatal. Admin và trang công khai (khi đã đăng nhập) báo Kenny's Korner Core chưa active và chỉ cách cài `kennys-korner-core.zip`.
2. Plugin cài từ `dist/kennys-korner-core.zip`, version 1.0.0, thư mục thật.
3. Upload `kennys-korner-seed.zip` trong Setup. Sau khi sửa giải nén, thông báo là “Seed files are ready to import.” và thư mục được nhận là `uploads/kennys-korner-seed/seed`.
4. Zip bàn giao giải nén thành `uploads/kennys-korner-seed/seed/` và Setup nhận thư mục đó, không cần chuyển `images`, `menu`, `videos` lên một cấp. Không có File Manager của host để bấm.
5. Import admin lần đầu: 31/31 media, 0 lỗi, 5 trang (Home, Menu, Gallery, Our Story, Visit Us), Sample Page còn. 82 attachment có asset key, 0 key trùng. PDF `kennys-korner-official-menu.pdf`, video `behind-the-griddle.mp4`.
6. Import lại không tick thay thế: log “Kept media” và “Kept the … page”, không thêm trang, không thêm key media.
7. Sửa bằng form admin thật rồi Publish: eyebrow và CTA trang chủ, ảnh hero, giờ ở General, tiêu đề timeline, caption gallery, thứ tự ảnh gallery, file PDF menu, thứ tự preview. Trang công khai đổi theo. Import lại không tick thay thế giữ các sửa đó.
8. Import có `REPLACE DEMO CONTENT` đưa chữ, giờ, PDF và thứ tự về demo. Media không bị nhân bản. Sau bước này site local lại đúng nội dung demo.
9. User editor: Setup trả 403, không có nút import. Ajax với nonce của chính editor trả “You do not have permission to import content.”
10. Zip thử chứa `../wp-config-stolen.php`, `evil.php` và `.htaccess` bị từ chối: “The archive contains a path that is not allowed.” Không có file đó trên đĩa.
11. Lightbox menu trên bản ZIP: desktop 1920, tablet 768, mobile 390. Mở preview, nút next đổi ảnh, Escape trả focus về nút vừa mở, zoom mobile lên 125%. Overflow ngang bằng 0 trên menu ở cả ba bề rộng, và trên trang chủ, Gallery, Our Story, Visit Us ở 390. Menu mobile mở được.
12. Giờ `7:00 AM—1:00 PM` sau khi sửa General xuất hiện ở hero, dải visit, footer và `/visit/`. PDF đang chọn không đổi. Visit không hiện địa chỉ, điện thoại hay email. Chỉ đường là tìm Google cho tên quán.

## Lỗi đã sửa

- Giải nén seed bằng `unzip_file()` trả “Could not access filesystem” vì WordPress đòi tài khoản FTP. Setup giờ giải nén bằng `ZipArchive` sau khi quét đường dẫn. Đã cài lại zip plugin và upload seed thành công.
- Theme không còn chết khi plugin tắt. Nó hiện cảnh báo cài plugin trong admin và, với administrator, trên trang công khai.

## Chưa kiểm tra được

- MySQL hoặc MariaDB, và `wp-env` vì máy không có Docker.
- Hosting khách, thư mục con, cache Varnish/Nginx/CDN, và upload video khi giới hạn nhỏ hơn 20MB trên host thật. Giới hạn 8M chỉ được thử với cả file seed, chưa thử riêng file video.
- File Manager của nhà cung cấp. Chỉ dựng lại đúng cấu trúc thư mục mà hướng dẫn mô tả.
- Kéo tay cầm bằng chuột. Thứ tự preview và gallery được thử bằng cách gửi form admin theo thứ tự dòng mới, đúng dữ liệu trình duyệt gửi sau khi sortable đổi vị trí.
- Thêm và xóa một mục Plan your visit, trạng thái Draft, trang 404, năm copyright, và header dính dưới admin bar. Các mục này từng chạy trên site symlink cổng 8090, không chạy lại trên site cài từ ZIP.
- So pixel với https://kennnykorner.figma.site/.
- PHP 8.5 in cảnh báo deprecated từ chính WP-CLI 2.12.0. Đó không phải lỗi của theme hay plugin.
