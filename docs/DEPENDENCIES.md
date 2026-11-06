# Dependencies và giấy phép

## WordPress runtime

Theme `kennys-korner` và plugin `kennys-korner-core` phát hành theo GPL-2.0-or-later.

Không có dependency PHP ngoài WordPress. Không dùng plugin trả phí.

Admin dùng jQuery, jQuery UI Sortable và `wp.media` có sẵn trong WordPress.

Font DM Sans và Nunito tải từ Google Fonts, cùng URL với demo. Giấy phép font là SIL Open Font License. Site cần ra được `fonts.googleapis.com` và `fonts.gstatic.com`. Nếu hosting chặn font ngoài, chữ rơi về font hệ thống.

## Demo ở thư mục gốc

React 19, React DOM 19, React Router 7 (giấy phép MIT) và Vite 8, Tailwind CSS 4, TypeScript (giấy phép tương ứng trong `package.json`). Chúng chỉ phục vụ bản đối chiếu và lệnh build demo. Chúng không chạy trên hosting WordPress.

Node.js chỉ cần khi phát triển hoặc khi chạy `npm run package:wordpress` / `npm run check:wordpress`.

## Ảnh và video demo

Ảnh, PDF và video trong `public/` là tài sản của bản demo Kenny's Korner, được đóng gói lại trong seed để nhập vào Media Library. Không gắn giấy phép mã nguồn cho các file media này.
