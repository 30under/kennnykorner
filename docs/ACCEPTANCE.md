# Bảng nghiệm thu

Đạt khi sửa field trong admin, Publish, rồi thấy đúng trên trang công khai mà không cần sửa code.

| Hạng mục | Cách thử | Kết quả local 6/11/2026 |
| --- | --- | --- |
| Hero trang chủ: eyebrow, headline có chữ màu, mô tả, ảnh, CTA, fresh note, giờ | Home → Publish → `/` | Eyebrow đã sửa và hiện ngay. Giờ đọc từ General |
| Neighborhood, quick actions, menu preview, kitchen, collage, gallery preview, visit, order strip | Sửa từng section trên Home | Render đủ trên `/`. Menu preview dùng dữ liệu trang Menu |
| PDF và 4 preview | Menu → đổi thứ tự hoặc ảnh → Publish | Cùng viewer trên `/` và `/menu/`. Lightbox zoom, next, Escape, trả focus |
| Open PDF và Download PDF | Bấm cả hai | Cùng attachment PDF |
| Gallery ảnh và video | Gallery + video ở General | Trang render. Video `preload=metadata` |
| Our Story intro, timeline, values, quote | Our Story | Template đủ field. Timeline demo được giữ và đánh dấu cần xác nhận |
| Visit giờ, vị trí, plan | Visit + General | Thêm và xóa một mục plan, Publish, `/visit/` đổi theo |
| Header logo, tên, nút Order | Header + General URL | Nút ẩn nếu URL trống |
| Footer mô tả, tagline, năm | Footer | Năm theo lịch máy chủ |
| Menu điều hướng | Appearance → Menus | Menu “Kenny's Korner” sau import |
| 404 | Mở URL lạ | “Wrong turn” và CTA từ General |
| Draft | Đặt Draft | Không public. Preview khi đăng nhập |
| Import lần hai | Setup, không tick thay thế | Không trùng trang, không đè chữ đã Publish |
| Import có dữ liệu sẵn | Site đã có Sample Page | Sample Page còn sau import |
| Admin bar | Đăng nhập, cuộn trang | Header dính dưới thanh admin, không bị che |
| Gói cài | `npm run package:wordpress` | Ba zip trong `dist/`, đúng thư mục gốc |

Chi tiết field nằm ở `docs/CMS-CONTENT-MAP.md`.
