# Bảng nghiệm thu

Đạt khi thao tác đã chạy trên WordPress cài từ ZIP và kết quả thấy được trên trang công khai. Site đó là `http://127.0.0.1:8091` ngày 6/11/2026. Chi tiết lệnh và những mục chưa chạy nằm ở `docs/IMPLEMENTATION-STATUS.md`.

| Hạng mục | Cách đã chạy | Kết quả trên bản ZIP |
| --- | --- | --- |
| Theme khi chưa có plugin | Cài theme từ zip, kích hoạt, mở trang chủ và Dashboard | Đạt. HTTP 200, không fatal. Admin báo cài `kennys-korner-core.zip` |
| Hero: eyebrow, ảnh, CTA, giờ | Form Home và General, Publish, mở `/` | Đạt. Eyebrow, ảnh hero, CTA và giờ đổi ngay. Headline màu và fresh note chỉ được xem ở nội dung demo, chưa sửa riêng |
| Các section trang chủ | Mở `/` sau import | Đạt phần hiển thị: neighborhood, quick actions, menu, bếp, story, gallery, visit, order. Chưa sửa từng field ngoài eyebrow, ảnh, CTA |
| PDF, thứ tự preview, lightbox | Form Menu đổi thứ tự và file PDF. Mở `/` và `/menu/` ở 1920, 768, 390 | Đạt. Open PDF và Download PDF cùng file vừa lưu. Lightbox next, Escape trả focus, zoom 125% trên mobile. Overflow ngang 0 |
| Gallery | Form Gallery đổi caption và thứ tự ảnh. Mở `/gallery/` | Đạt. Caption và ảnh đầu đổi, import lại không mất. Video trỏ `behind-the-griddle.mp4`. Chưa thay file video |
| Our Story | Form Story đổi tiêu đề mốc đầu. Mở `/our-story/` | Đạt với tiêu đề timeline. Intro, values và quote render đúng demo, chưa sửa riêng |
| Visit và giờ dùng chung | Sửa giờ ở General. Mở `/`, footer và `/visit/` | Đạt. Cùng một giá trị ở hero, dải visit, footer và Visit Us. Không có địa chỉ, điện thoại, email |
| Header, footer, menu điều hướng | Xem sau import | Menu và logo hiện. Chưa sửa logo, chưa thử ẩn nút Order, chưa kiểm tra số năm |
| Import lần đầu, lần hai, lần sau khi sửa | Admin ajax trên site sạch | Đạt. Không trùng trang hay media. Sửa được giữ khi không tick thay thế |
| Import ghi đè | Gõ `REPLACE DEMO CONTENT` | Đạt. Nội dung demo trở lại, media không nhân bản |
| Quyền importer và zip | Editor, và một zip có `../` cùng file PHP | Đạt. Editor bị 403. Zip xấu bị từ chối, không ghi file ra ngoài thư mục seed |
| Seed qua Setup và layout File Manager | Upload zip, rồi chuyển `images/menu/videos` lên một cấp | Đạt cả hai layout. Chưa bấm File Manager của host |
| Gói cài | `npm run package:wordpress`, rồi cài hai zip | Đạt. Ba file trong `dist/`, theme và plugin không phải symlink |
| Draft, 404, admin bar, Plan your visit | Chưa chạy trên site ZIP | Chưa kiểm tra lại. Không tính là đạt của lần bàn giao này |
| MySQL, Docker, hosting khách | Lệnh `docker` và `mysql` không có | Chưa kiểm tra được |
