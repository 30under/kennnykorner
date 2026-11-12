# Cách sửa nội dung

Đăng nhập WordPress, mở menu **Kenny's Korner**. Mỗi mục tương ứng một phần của website. Nút **View page** mở trang đang sửa.

Bấm **Save and publish** để khách thấy nội dung. Bấm **Save preview** để xem trước khi đăng nhập; bản xem trước không thay trang đang public và không chặn lần import đầu.

## Việc nên làm

- Sửa chữ ngay trên ô có nhãn. Dấu nháy, tiếng Việt và xuống dòng được giữ.
- Với tiêu đề lớn, mỗi dòng là một hàng. Chữ cần màu xanh bỏ vào ô **Color**. Để trống Color nếu cả dòng một màu. Không dán HTML.
- Ảnh: bấm **Replace**, chọn file, xem thumbnail, rồi Publish. **Remove** bỏ ảnh khỏi section, không xóa file khỏi Media Library.
- Danh sách (nút, ảnh, mốc thời gian, giá trị, cách ghé thăm, trang menu): **Add item**, **Remove**, kéo **Drag to reorder**.
- Link: chọn đích có sẵn, hoặc Custom và dán URL. Tick mở tab mới khi cần. Để trống URL của một nút thì nút đó biến mất.
- Giờ mở cửa, địa chỉ, điện thoại, email, link Toast và link chỉ đường chỉ sửa ở **General settings**. Header, footer, trang chủ và Visit Us dùng chung các giá trị này.
- Email nhà hàng cũng nằm ở General settings. Khi có email hợp lệ, Visit Us và footer hiện liên kết `mailto:`. Form Catering gửi thông báo về đúng email này và gửi email xác nhận cho khách. Để trống thì form báo chưa sẵn sàng và không hiện đã gửi thành công.
- Thư đi qua `wp_mail()`. Cài plugin SMTP (WP Mail SMTP, FluentSMTP hoặc Post SMTP) rồi nhập máy chủ, cổng, tên đăng nhập và mật khẩu do nhà cung cấp email đưa. Không nhập mật khẩu SMTP vào Kenny's Korner. Không tự tạo mật khẩu ứng dụng Gmail từ trang này. Vào **Kenny's Korner → Setup**, bấm **Send test email**. Thông báo chỉ nói WordPress có nhận lệnh gửi hay không.
- Facebook và Instagram cũng sửa ở General settings. Footer chỉ hiện icon khi URL bắt đầu bằng `http://` hoặc `https://`. URL trống, hoặc Instagram đang là `#gallery`, không hiện icon.
- Trang Catering sửa ở **Kenny's Korner → Catering**: hero, các khối dịch vụ, ảnh, và lời hướng dẫn cạnh form. Nút Catering trên trang chủ dùng ô **Homepage button label**. Form gồm tên, email, điện thoại, ngày, giờ, số khách, loại sự kiện, địa điểm và ghi chú.
- Video mục Today sửa trong **Our Story → Timeline**, ở moment Today: file video hoặc URL, ảnh poster và caption. Poster phải là ảnh. Nếu một file video đang nằm ở poster trong khi ô video trống, trang sẽ chuyển file đó sang Video file. Để trống video thì khung biến mất và sóng cũ vẫn còn. File lớn hơn giới hạn upload thì dán URL.
- Carousel video của Gallery sửa ở **Gallery → Video carousel**. Thêm, xóa và kéo để sắp xếp. Mỗi video có file hoặc URL, poster, tiêu đề và caption. Danh sách trống thì carousel không hiện. Tường ảnh Gallery không dùng các trường này.
- Menu PDF: vào **Menu**, thay file PDF, sắp lại các trang preview, Publish. Nút Open PDF và Download PDF dùng file vừa lưu.
- Video bếp: vào **General settings**, thay file hoặc dán URL nếu hosting không nhận file lớn. Poster là ảnh hiện trước khi phát.

## Việc không có trong admin

Khoảng cách, cỡ chữ, màu nền, sóng, mặt trời và cây dừa thuộc thiết kế. Sửa chúng cần chỉnh theme.

Tên các mục trên thanh menu sửa tại **Appearance → Menus**, menu “Kenny's Korner”.

## Cần xác nhận trước khi coi là số liệu thật

Cùng danh sách này nằm trên **Kenny's Korner → Setup**, bảng “Confirm with the restaurant”. Giá trị dưới đây là demo, không phải thông tin khách đã xác nhận.

- Lịch sử trên Our Story là lời của bản demo: The Hut, đổi tên năm 1990, và hiện tại.
- Giờ của toàn website chỉ sửa ở General settings. Demo đang ghi Monday—Sunday, 7:00 AM—2:00 PM. File PDF in sẵn ghi 2:30 PM. Sửa giờ trên website không sửa file PDF.
- Chưa có số nhà và điện thoại. Ô để trống sẽ không hiện. Không điền địa chỉ giả.
- Nút chỉ đường đang mở kết quả tìm “Kenny's Korner” trên Google vì source không có số nhà.
- Email, Facebook và Instagram sửa ở General settings. Footer chỉ hiện mạng xã hội khi URL là `http://` hoặc `https://`.

## Nháp

Đặt Visibility thành Draft thì trang biến khỏi website công khai. Preview vẫn xem được khi đang đăng nhập.
