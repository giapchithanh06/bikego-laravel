# BikeGo – Hệ thống thuê xe đạp công cộng

Bài thi thiết kế Web (FIT-TDC 2026, Cao đẳng, Bảng B). Laravel 8 + Blade + MySQL, chạy trên PHP 7.4 trở lên.

## Cấu trúc

| Thư mục | Nội dung |
|---|---|
| `Phan_01/` | Phần 1 – giao diện tĩnh HTML/CSS/JS (index, stations, bike-rental, bike-return) |
| `app/`, `routes/`, `resources/views/`, `database/` | Phần 2 – chức năng Laravel |
| `public/assets/` | CSS/JS/ảnh dùng chung (đồng bộ từ Phan_01) |
| `tests/Feature/BikeGoTest.php` | Kiểm thử tự động |

## Chạy

```bash
php artisan migrate:fresh --seed
php artisan serve          # http://127.0.0.1:8000
php artisan test
```

Tài khoản: admin xem `config/bikego.php`; người dùng demo `demo.user1@gmail.com` / `Test@1234`.

## Yêu cầu → vị trí code

- Đăng ký/đăng nhập (regex họ tên, SĐT, gmail, mật khẩu): `app/Http/Requests/RegisterRequest.php`, `public/assets/js/register.js`
- Danh sách/chi tiết trạm, đánh giá (AJAX): `StationController`, `station-detail.js`
- Thuê/trả xe, tính tiền theo phút: `RentalController`, `rental.js`
- Đánh giá trạm/xe, lịch sử: `ReviewController`, `RentalController@history`
- Top Bikers hằng tháng: `TopBikerService`, lệnh `bikego:summarize-monthly`
- Quản trị xe & trạm (CRUD, thùng rác, khôi phục, xóa chọn/tất cả): `app/Http/Controllers/Admin/*`

## Top Bikers (lập lịch)

Chạy `php artisan schedule:work` (hoặc cron `schedule:run` mỗi phút); chạy tay: `php artisan bikego:summarize-monthly --month=YYYY-MM`.

## Nộp bài

`tools/make-submission.ps1` tạo thư mục `Phan_01` và `Phan_02`.
