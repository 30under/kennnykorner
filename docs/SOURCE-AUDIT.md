# Kiểm tra source demo

Ngày kiểm tra: 6 tháng 11 năm 2026. Nhánh làm việc: `feature/wordpress-conversion`. Commit gốc trên `main` trước khi chuyển đổi: `b0b04be` (“Add files from Figma Make”).

## Entrypoint thật so với AGENTS.md

`AGENTS.md` và `CLAUDE.md` vẫn mô tả `src/main.tsx` mount `src/App.tsx`. Source đang checkout không có `src/App.tsx`.

Luồng thật:

- `src/main.tsx` import `./app/App` và `./index.css`.
- `src/app/App.tsx` dựng router.
- `src/app/routes.tsx` khai báo route.

## Route

| Đường dẫn | Màn hình |
| --- | --- |
| `/` | Trang chủ |
| `/menu` | Menu |
| `/gallery` | Gallery |
| `/our-story` | Our Story |
| `/visit` | Visit Us |
| Không khớp | Trang 404 |

## Công nghệ demo

`package.json`: React 19, React Router 7, Vite 8, Tailwind CSS 4. Tailwind được import trong `src/index.css` nhưng giao diện không dùng utility class. Toàn bộ hình thức nằm trong CSS viết tay và design token của file đó.

`vite.config.ts` và `.figma/make/` phục vụ Figma Make. Chúng không được đưa vào runtime WordPress.

Demo tham khảo: https://kennnykorner.figma.site/. `site.json` đặt tiêu đề “Demo Kennny Korner” và `noindex`. Đó là metadata của bản demo, không phải nội dung nhà hàng.

## Tài sản

- Ảnh tối ưu: `public/images/optimized/`.
- PDF chính thức: `public/menu/kennys-korner-official-menu.pdf`.
- Preview menu: `public/menu/preview/page-{1..4}-{600,1200,2200}.webp`. Trang 1 và 3 cao 3112px, trang 2 và 4 cao 3111px, rộng 2200px.
- Video: `public/videos/behind-the-griddle.mp4`, khoảng 20MB.
- Ảnh ngoại thất `kennys-exterior-avenue`, `corner`, `signage`, `street`, `wide` có trong thư viện demo nhưng không được đặt vào layout. Importer đưa chúng vào Media Library và không bịa vị trí.

## Dữ liệu dùng chung trong demo

Các giá trị sau bị lặp trong JSX và được gom về một nguồn trên WordPress:

- URL đặt hàng Toast: `https://order.toasttab.com/online/kennys-korner-restaurant-kennys-korner-restaurant`
- Giờ: Open daily / Daily / Monday—Sunday / 7:00 AM—2:00 PM
- Chỉ đường: tìm Google với cụm `Kenny's Korner`. Source không có số nhà.
- Không có số điện thoại hoặc email.
- Liên kết “Follow along” trỏ tới `#gallery`, không phải hồ sơ Instagram.

PDF in sẵn ghi “2:30PM”. Copy trên website demo ghi “2:00 PM”. Cả hai được giữ nguyên. Khách cần xác nhận giờ thật.

Timeline Our Story (The Hut / bốn chị em, đổi tên 1990, hôm nay) có trong source và được nhập lại. Đây là nội dung demo, cần khách xác nhận trước khi coi là lịch sử chính thức.
