<?php

namespace Database\Seeders;

use App\Models\Bicycle;
use App\Models\BicycleReview;
use App\Models\Rental;
use App\Models\Station;
use App\Models\StationReview;
use App\Models\User;
use App\Models\Ward;
use App\Services\TopBikerService;
use Illuminate\Database\Seeder;

/**
 * Demo data so every screen has something to show:
 * 12 stations, ~100 bikes, 14 demo users (password: Test@1234), rentals of last month, reviews.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Station::count() > 0) {
            return; // never duplicate the demo data
        }

        mt_srand(2026); // same demo data on every machine

        $stations = $this->createStations();
        $bikes = $this->createBikes($stations);
        $users = $this->createUsers();
        $this->createRentalsAndReviews($users, $bikes, $stations);

        // Fill the Top Bikers table for last month (normally done by the monthly job)
        app(TopBikerService::class)->summarize(TopBikerService::previousMonth());

        optional($this->command)->info('Demo data created. Demo user login: 0901000001 / Test@1234');
    }

    /** @return \Illuminate\Support\Collection<int, Station> */
    private function createStations()
    {
        $wards = Ward::orderBy('id')->take(8)->get();

        $data = [
            ['Trạm Công viên Tao Đàn', '55C Nguyễn Thị Minh Khai'],
            ['Trạm Ga Metro Bến Thành', 'Công trường Quách Thị Trang'],
            ['Trạm Chợ Bến Thành', '1 Lê Lai'],
            ['Trạm Đại học Công Nghệ', '475A Điện Biên Phủ'],
            ['Trạm Bến xe Miền Đông', '292 Đinh Bộ Lĩnh'],
            ['Trạm Công viên 23/9', '189 Phạm Ngũ Lão'],
            ['Trạm Nhà văn hóa Thanh niên', '4 Phạm Ngọc Thạch'],
            ['Trạm Thảo Cầm Viên', '2 Nguyễn Bỉnh Khiêm'],
            ['Trạm Landmark 81', '720A Điện Biên Phủ'],
            ['Trạm Bến Bạch Đằng', 'Tôn Đức Thắng'],
            ['Trạm Đầm Sen', '3 Hòa Bình'],
            ['Trạm Khu dân cư Phú Mỹ', '25 Nguyễn Lương Bằng'],
        ];

        $stations = collect();
        foreach ($data as $i => [$name, $address]) {
            $stations->push(Station::create([
                'ward_id' => $wards[$i % $wards->count()]->id,
                'name' => $name,
                'address' => $address,
                'total_docks' => mt_rand(14, 24),
                // the last station is under maintenance
                'status' => $i === count($data) - 1 ? Station::STATUS_MAINTENANCE : Station::STATUS_ACTIVE,
            ]));
        }

        return $stations;
    }

    /** @return \Illuminate\Support\Collection<int, Bicycle> */
    private function createBikes($stations)
    {
        // Different fill levels so that both "good" and "bad" colours appear on screen
        $fill = [0.8, 0.3, 0.6, 0.45, 0.9, 0.5, 0.2, 0.7, 0.55, 0.35, 0.65, 0.4];
        $bikes = collect();
        $number = 1;

        foreach ($stations as $i => $station) {
            $count = (int) round($station->total_docks * $fill[$i % count($fill)]);

            for ($k = 0; $k < $count; $k++) {
                $bikes->push(Bicycle::create([
                    'bike_code' => sprintf('BG-%04d', $number),
                    'station_id' => $station->id,
                    'price_per_minute' => config('bikego.default_price_per_minute'),
                    'location_status' => Bicycle::LOCATION_AT_STATION,
                    // every 9th bike is being repaired
                    'condition_status' => $number % 9 === 0 ? Bicycle::CONDITION_REPAIRING : Bicycle::CONDITION_NORMAL,
                ]));
                $number++;
            }
        }

        return $bikes;
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function createUsers()
    {
        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Hoàng Cường', 'Phạm Minh Dũng', 'Võ Thanh Hà',
            'Đặng Quốc Huy', 'Bùi Ngọc Lan', 'Hồ Gia Khang', 'Mai Thu Trang', 'Đỗ Anh Tuấn',
            'Ngô Bảo Châu', 'Dương Mỹ Linh', 'Lý Hoàng Nam', 'Phan Khánh Vy',
        ];

        $users = collect();
        foreach ($names as $i => $name) {
            $n = $i + 1;
            $user = User::create([
                'name' => $name,
                'phone' => sprintf('09010000%02d', $n),
                'email' => "demo.user{$n}@gmail.com",
                'password' => 'Test@1234',
                'role' => User::ROLE_USER,
            ]);
            // created_at decides ties in the ranking
            $user->forceFill(['created_at' => now()->subDays(120 - $n)])->save();
            $users->push($user);
        }

        return $users;
    }

    private function createRentalsAndReviews($users, $bikes, $stations): void
    {
        $comments = [
            'Xe êm, trạm sạch sẽ.', 'Trạm dễ tìm, rất tiện.', 'Xe hơi cũ nhưng chạy tốt.',
            'Trả xe nhanh gọn.', 'Rất hài lòng!', 'Phanh hơi kêu, cần bảo dưỡng.',
            'Yên xe thoải mái.', 'Nên có thêm xe vào giờ cao điểm.', 'Nhân viên hỗ trợ nhiệt tình.',
        ];

        $lastMonth = now()->startOfMonth()->subMonth();

        foreach ($users as $index => $user) {
            // The first users rent more, so the ranking has a clear order
            $rentalCount = max(2, 14 - $index);

            for ($r = 0; $r < $rentalCount; $r++) {
                $bike = $bikes->random();
                $from = $stations->random();
                $to = $stations->random();
                $minutes = mt_rand(10, 110);
                $start = $lastMonth->copy()->addDays(mt_rand(0, 26))->setTime(mt_rand(6, 20), mt_rand(0, 59));
                $end = $start->copy()->addMinutes($minutes);

                $rental = Rental::create([
                    'user_id' => $user->id,
                    'bicycle_id' => $bike->id,
                    'bike_code' => $bike->bike_code,
                    'start_station_id' => $from->id,
                    'start_station_name' => $from->name,
                    'end_station_id' => $to->id,
                    'end_station_name' => $to->name,
                    'started_at' => $start,
                    'ended_at' => $end,
                    'price_per_minute' => $bike->price_per_minute,
                    'total_minutes' => $minutes,
                    'total_amount' => $minutes * $bike->price_per_minute,
                ]);

                StationReview::create([
                    'rental_id' => $rental->id,
                    'user_id' => $user->id,
                    'station_id' => $to->id,
                    'rating' => mt_rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);

                BicycleReview::create([
                    'rental_id' => $rental->id,
                    'user_id' => $user->id,
                    'bicycle_id' => $bike->id,
                    'rating' => mt_rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);
            }
        }
    }
}
