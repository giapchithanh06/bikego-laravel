<?php

namespace Tests\Feature;

use App\Http\Requests\RegisterRequest;
use App\Models\Bicycle;
use App\Models\BicycleReview;
use App\Models\MonthlyTopBiker;
use App\Models\Rental;
use App\Models\Station;
use App\Models\StationReview;
use App\Models\User;
use App\Models\Ward;
use App\Services\TopBikerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * End-to-end tests of the BikeGo exam requirements.
 * Run with: php artisan test
 */
class BikeGoTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeStation(array $attrs = []): Station
    {
        $ward = Ward::firstOrCreate(['code' => 'T1'], ['name' => 'Phường Test']);

        return Station::create(array_merge([
            'ward_id' => $ward->id,
            'name' => 'Trạm Test',
            'address' => '1 Đường Test',
            'total_docks' => 10,
            'status' => Station::STATUS_ACTIVE,
        ], $attrs));
    }

    private function makeBike(?Station $station, array $attrs = []): Bicycle
    {
        return Bicycle::create(array_merge([
            'bike_code' => 'BG-0001',
            'station_id' => optional($station)->id,
            'price_per_minute' => 500,
            'location_status' => Bicycle::LOCATION_AT_STATION,
            'condition_status' => Bicycle::CONDITION_NORMAL,
        ], $attrs));
    }

    private function makeUser(array $attrs = []): User
    {
        return User::create(array_merge([
            'name' => 'Nguyễn Văn Test',
            'phone' => '0912345678',
            'email' => 'tester01@gmail.com',
            'password' => 'Test@1234',
            'role' => User::ROLE_USER,
        ], $attrs));
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'admin',
            'phone' => null,
            'email' => 'admin@bikego.local',
            'password' => '12345',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function validRegistration(array $override = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Văn An',
            'phone' => '0912345678',
            'email' => 'nguyen.van.an@gmail.com',
            'password' => 'Abcd@1234',
            'password_confirmation' => 'Abcd@1234',
        ], $override);
    }

    // ------------------------------------------------------------------
    // Public pages
    // ------------------------------------------------------------------

    public function test_public_pages_load(): void
    {
        $this->get('/')->assertOk()->assertSee('Top Bikers')->assertSee("Let's BikeGo");
        $this->get('/stations')->assertOk()->assertSee('Chọn Phường');
        $this->get('/register')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_pages_use_the_common_exam_title(): void
    {
        $this->get('/')->assertSee('<title>'.config('bikego.exam_title').'</title>', false);
    }

    public function test_guest_is_redirected_to_login_for_rental_pages(): void
    {
        $this->get('/bike-rental')->assertRedirect('/login');
        $this->get('/bike-return')->assertRedirect('/login');
        $this->get('/rental-history')->assertRedirect('/login');
    }

    // ------------------------------------------------------------------
    // Station AJAX endpoints (2.1, 2.2)
    // ------------------------------------------------------------------

    public function test_station_list_and_detail_api(): void
    {
        $station = $this->makeStation(['total_docks' => 10]);
        $this->makeBike($station, ['bike_code' => 'BG-0001']);
        $this->makeBike($station, ['bike_code' => 'BG-0002']);
        $user = $this->makeUser();

        // 7 reviews: the detail shows 5 first, then "load more" gives the rest
        foreach (range(1, 7) as $i) {
            StationReview::create(['user_id' => $user->id, 'station_id' => $station->id, 'rating' => 4, 'comment' => "Review {$i}"]);
        }

        $this->getJson('/api/stations?ward_id='.$station->ward_id)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Trạm Test')
            ->assertJsonPath('data.0.bikes_present', 2)
            ->assertJsonPath('data.0.empty_docks', 8);

        $detail = $this->getJson('/api/stations/'.$station->id)->assertOk();
        $detail->assertJsonPath('data.address', '1 Đường Test')
            ->assertJsonPath('data.has_more', true)
            ->assertJsonCount(5, 'data.reviews')
            ->assertJsonPath('data.reviews.0.comment', 'Review 7'); // newest first

        $this->getJson('/api/stations/'.$station->id.'/reviews?offset=5')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('has_more', false);
    }

    public function test_dropdown_options_hide_unsuitable_stations(): void
    {
        $withBike = $this->makeStation(['name' => 'Có xe', 'total_docks' => 5]);
        $this->makeBike($withBike, ['bike_code' => 'A1']);

        $empty = $this->makeStation(['name' => 'Hết xe', 'total_docks' => 5]);
        $full = $this->makeStation(['name' => 'Hết chỗ', 'total_docks' => 1]);
        $this->makeBike($full, ['bike_code' => 'A2']);
        $maintenance = $this->makeStation(['name' => 'Bảo trì', 'status' => Station::STATUS_MAINTENANCE]);
        $this->makeBike($maintenance, ['bike_code' => 'A3']);

        $wardId = $withBike->ward_id;

        // Rent: only active stations that still have a rentable bike
        $rent = collect($this->getJson("/api/wards/{$wardId}/stations?purpose=rent")->json('data'))->pluck('name')->all();
        $this->assertEqualsCanonicalizing(['Có xe', 'Hết chỗ'], $rent);

        // Return: only active stations with a free dock (not full, not under maintenance)
        $return = collect($this->getJson("/api/wards/{$wardId}/stations?purpose=return")->json('data'))->pluck('name')->all();
        $this->assertEqualsCanonicalizing(['Có xe', 'Hết xe'], $return);
    }

    // ------------------------------------------------------------------
    // Register / login (2.5)
    // ------------------------------------------------------------------

    public function test_register_success_redirects_home_with_message(): void
    {
        $this->post('/register', $this->validRegistration())
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Đăng ký tài khoản thành công');

        $this->assertDatabaseHas('users', [
            'email' => 'nguyen.van.an@gmail.com',
            'phone' => '0912345678',
            'role' => 'user',
        ]);
    }

    public function test_register_shows_server_validation_errors(): void
    {
        $this->post('/register', [
            'name' => 'An',
            'phone' => '123',
            'email' => 'abc@yahoo.com',
            'password' => 'abc',
            'password_confirmation' => 'xyz',
        ])->assertSessionHasErrors(['name', 'phone', 'email', 'password', 'password_confirmation']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_same_phone_in_three_formats_counts_as_one_account(): void
    {
        $this->post('/register', $this->validRegistration());

        foreach (['+84912345678', '84912345678', '0912345678'] as $i => $phone) {
            $this->post('/register', $this->validRegistration([
                'phone' => $phone,
                'email' => "other.person{$i}@gmail.com",
            ]))->assertSessionHasErrors('phone');
        }

        $this->assertDatabaseCount('users', 1);
    }

    public function test_email_must_be_unique(): void
    {
        $this->post('/register', $this->validRegistration());
        $this->post('/register', $this->validRegistration(['phone' => '0987654321']))
            ->assertSessionHasErrors('email');
    }

    public function test_password_rules(): void
    {
        $bad = ['abcdefg1!' /* no upper */, 'ABCDEFG1!' /* no lower */, 'Abcdefgh!' /* no digit */,
            'Abcdefg12' /* no special */, 'Ab1!' /* too short */, 'Mậtkhẩu1!' /* Vietnamese */];

        foreach ($bad as $password) {
            $this->post('/register', $this->validRegistration([
                'password' => $password,
                'password_confirmation' => $password,
            ]))->assertSessionHasErrors('password');
        }

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @dataProvider nameProvider
     */
    public function test_name_regex(string $value, bool $valid): void
    {
        $this->assertSame($valid, (bool) preg_match(RegisterRequest::NAME_REGEX, $value)
            && preg_match_all('/\p{Latin}/u', $value) >= 3);
    }

    public static function nameProvider(): array
    {
        return [
            ['Nguyễn Văn An', true],
            ['Ngô Bảo Châu', true],
            ['Anh', true],
            ['An', false],            // fewer than 3 letters
            ['Nguyễn  Văn', false],   // two spaces in a row
            [' Nguyễn Văn', false],   // leading space
            ['Nguyễn Văn ', false],   // trailing space
            ['Nguyen Van2', false],   // digit
        ];
    }

    /**
     * @dataProvider phoneProvider
     */
    public function test_phone_regex(string $value, bool $valid): void
    {
        $this->assertSame($valid, (bool) preg_match(RegisterRequest::PHONE_REGEX, $value));
    }

    public static function phoneProvider(): array
    {
        return [
            ['0912345678', true],
            ['84912345678', true],
            ['+84912345678', true],
            ['912345678', false],
            ['09123456789', false],
            ['8491234567', false],
            ['+8491234567', false],
            ['091234567a', false],
        ];
    }

    /**
     * @dataProvider emailProvider
     */
    public function test_email_regex(string $value, bool $valid): void
    {
        $this->assertSame($valid, (bool) preg_match(RegisterRequest::EMAIL_REGEX, $value));
    }

    public static function emailProvider(): array
    {
        return [
            ['abcdef@gmail.com', true],
            ['abc.def1@gmail.com', true],
            ['abcde@gmail.com', false],            // 5 chars
            [str_repeat('a', 31).'@gmail.com', false], // 31 chars
            ['.abcdef@gmail.com', false],          // starts with a dot
            ['abcdef.@gmail.com', false],          // ends with a dot
            ['abc..def@gmail.com', false],         // two dots in a row
            ['abcdef@yahoo.com', false],           // only gmail.com
            ['Abcdef@gmail.com', false],           // upper case
            ['abc_def@gmail.com', false],          // underscore
        ];
    }

    public function test_login_with_phone_or_email(): void
    {
        $user = $this->makeUser();

        $this->post('/login', ['identifier' => '0912345678', 'password' => 'Test@1234'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', ['identifier' => 'tester01@gmail.com', 'password' => 'Test@1234'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_failures(): void
    {
        $this->makeUser();

        $this->post('/login', ['identifier' => '0912345678', 'password' => 'wrong'])
            ->assertSessionHasErrors('identifier');
        $this->post('/login', ['identifier' => '', 'password' => ''])
            ->assertSessionHasErrors(['identifier', 'password']);
        $this->assertGuest();
    }

    public function test_navigation_changes_after_login(): void
    {
        $this->get('/')->assertSee('Đăng nhập')->assertSee('Đăng ký')->assertDontSee('Đăng xuất');

        $user = $this->makeUser();
        $this->actingAs($user)->get('/')
            ->assertSee('Nguyễn Văn Test')
            ->assertSee('Đăng xuất')
            ->assertDontSee('href="'.route('register').'"', false);
    }

    // ------------------------------------------------------------------
    // Rent / return / review / history (2.3, 2.4, 2.6)
    // ------------------------------------------------------------------

    public function test_full_rent_return_and_review_flow(): void
    {
        Carbon::setTestNow(Carbon::now());

        $from = $this->makeStation(['name' => 'Trạm đi']);
        $to = $this->makeStation(['name' => 'Trạm đến', 'total_docks' => 5]);
        $bike = $this->makeBike($from, ['bike_code' => 'BG-0001']);
        $user = $this->makeUser();

        // Rent (plate is case-insensitive)
        $this->actingAs($user)
            ->post('/bike-rental', ['ward_id' => $from->ward_id, 'station_id' => $from->id, 'bike_code' => 'bg-0001'])
            ->assertRedirect(route('rental.rent'));

        $bike->refresh();
        $this->assertSame(Bicycle::LOCATION_RENTED, $bike->location_status);
        $this->assertNull($bike->station_id);

        $rental = Rental::firstOrFail();
        $this->assertNull($rental->ended_at);
        $this->assertSame('Trạm đi', $rental->start_station_name);

        // While renting, the rental page shows the info instead of the form
        $this->actingAs($user)->get('/bike-rental')->assertSee('BG-0001')->assertSee('Trạm đi');
        $this->actingAs($user)->get('/bike-return')->assertSee('Trả xe và thanh toán');

        // Return after 30 minutes
        $this->travel(30)->minutes();

        $this->actingAs($user)
            ->post('/bike-return', ['ward_id' => $to->ward_id, 'station_id' => $to->id])
            ->assertRedirect(route('rental.review', $rental));

        $rental->refresh();
        $this->assertSame(30, $rental->total_minutes);
        $this->assertSame(30 * 500, (int) $rental->total_amount);
        $this->assertSame('Trạm đến', $rental->end_station_name);

        $bike->refresh();
        $this->assertSame(Bicycle::LOCATION_AT_STATION, $bike->location_status);
        $this->assertSame($to->id, $bike->station_id);

        // Review page + saving the reviews
        $this->actingAs($user)->get(route('rental.review', $rental))->assertOk()->assertSee('BG-0001');

        $this->actingAs($user)->post(route('rental.review.store', $rental), [
            'bike_rating' => 5,
            'bike_comment' => 'Xe tốt',
            'station_rating' => 4,
            'station_comment' => 'Trạm sạch',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('bicycle_reviews', ['bicycle_id' => $bike->id, 'rating' => 5, 'comment' => 'Xe tốt']);
        $this->assertDatabaseHas('station_reviews', ['station_id' => $to->id, 'rating' => 4, 'comment' => 'Trạm sạch']);
    }

    public function test_review_validation(): void
    {
        $station = $this->makeStation();
        $bike = $this->makeBike($station);
        $user = $this->makeUser();
        $rental = Rental::create([
            'user_id' => $user->id, 'bicycle_id' => $bike->id, 'bike_code' => $bike->bike_code,
            'start_station_id' => $station->id, 'start_station_name' => $station->name,
            'end_station_id' => $station->id, 'end_station_name' => $station->name,
            'started_at' => now()->subHour(), 'ended_at' => now(), 'price_per_minute' => 500,
            'total_minutes' => 60, 'total_amount' => 30000,
        ]);

        $this->actingAs($user)->post(route('rental.review.store', $rental), [
            'bike_rating' => 6,
            'station_rating' => 0,
            'bike_comment' => str_repeat('a', 201),
        ])->assertSessionHasErrors(['bike_rating', 'station_rating', 'bike_comment']);

        // Somebody else can not review this rental
        $other = $this->makeUser(['phone' => '0900000001', 'email' => 'other.user@gmail.com']);
        $this->actingAs($other)->get(route('rental.review', $rental))->assertForbidden();
    }

    public function test_a_user_can_rent_only_one_bike_at_a_time(): void
    {
        $station = $this->makeStation();
        $this->makeBike($station, ['bike_code' => 'BG-0001']);
        $this->makeBike($station, ['bike_code' => 'BG-0002']);
        $user = $this->makeUser();

        $payload = fn (string $code) => ['ward_id' => $station->ward_id, 'station_id' => $station->id, 'bike_code' => $code];

        $this->actingAs($user)->post('/bike-rental', $payload('BG-0001'));
        $this->actingAs($user)->post('/bike-rental', $payload('BG-0002'))
            ->assertSessionHas('error');

        $this->assertSame(1, Rental::count());
        $this->assertSame(Bicycle::LOCATION_AT_STATION, Bicycle::where('bike_code', 'BG-0002')->value('location_status'));
    }

    public function test_rent_rejects_wrong_plate_and_repairing_bike(): void
    {
        $station = $this->makeStation();
        $otherStation = $this->makeStation(['name' => 'Trạm khác']);
        $this->makeBike($station, ['bike_code' => 'BG-0001', 'condition_status' => Bicycle::CONDITION_REPAIRING]);
        $this->makeBike($otherStation, ['bike_code' => 'BG-0002']);
        $user = $this->makeUser();

        $payload = fn (string $code) => ['ward_id' => $station->ward_id, 'station_id' => $station->id, 'bike_code' => $code];

        // unknown plate
        $this->actingAs($user)->post('/bike-rental', $payload('NOPE'))->assertSessionHasErrors('bike_code');
        // bike exists but in another station
        $this->actingAs($user)->post('/bike-rental', $payload('BG-0002'))->assertSessionHasErrors('bike_code');
        // bike is being repaired
        $this->actingAs($user)->post('/bike-rental', $payload('BG-0001'))->assertSessionHasErrors('bike_code');
        // empty plate
        $this->actingAs($user)->post('/bike-rental', $payload(''))->assertSessionHasErrors('bike_code');

        $this->assertSame(0, Rental::count());
    }

    public function test_return_is_refused_when_station_is_full(): void
    {
        $from = $this->makeStation(['name' => 'Trạm đi']);
        $full = $this->makeStation(['name' => 'Trạm đầy', 'total_docks' => 1]);
        $this->makeBike($from, ['bike_code' => 'BG-0001']);
        $this->makeBike($full, ['bike_code' => 'BG-0002']);
        $user = $this->makeUser();

        $this->actingAs($user)->post('/bike-rental', ['ward_id' => $from->ward_id, 'station_id' => $from->id, 'bike_code' => 'BG-0001']);
        $this->actingAs($user)->post('/bike-return', ['ward_id' => $full->ward_id, 'station_id' => $full->id])
            ->assertSessionHasErrors('station_id');

        $this->assertNull(Rental::first()->ended_at);
    }

    public function test_rental_history_is_sorted_newest_first(): void
    {
        $station = $this->makeStation();
        $user = $this->makeUser();

        foreach ([['BG-OLD', 3], ['BG-NEW', 1]] as [$code, $daysAgo]) {
            Rental::create([
                'user_id' => $user->id, 'bike_code' => $code,
                'start_station_id' => $station->id, 'start_station_name' => $station->name,
                'end_station_id' => $station->id, 'end_station_name' => $station->name,
                'started_at' => now()->subDays($daysAgo), 'ended_at' => now()->subDays($daysAgo)->addMinutes(20),
                'price_per_minute' => 500, 'total_minutes' => 20, 'total_amount' => 10000,
            ]);
        }

        $this->actingAs($user)->get('/rental-history')
            ->assertOk()
            ->assertSeeInOrder(['BG-NEW', 'BG-OLD']);
    }

    // ------------------------------------------------------------------
    // Top bikers (2.7) and home page (2.8)
    // ------------------------------------------------------------------

    public function test_top_bikers_ranking_and_tie_breaks(): void
    {
        $station = $this->makeStation();
        $month = TopBikerService::previousMonth();
        $day = now()->startOfMonth()->subMonth()->addDays(5);

        // [minutes per rental, number of rentals, account created X days ago]
        $setup = [
            'u1' => [50, 2, 5],   // 100 min, 2 rentals
            'u2' => [20, 5, 10],  // 100 min, 5 rentals, newer account
            'u3' => [20, 5, 20],  // 100 min, 5 rentals, older account -> beats u2
            'u4' => [10, 5, 30],  // 50 min
        ];

        $users = [];
        foreach ($setup as $key => [$minutes, $count, $createdDaysAgo]) {
            $users[$key] = $this->makeUser([
                'name' => "User {$key}", 'phone' => '09000000'.substr($key, 1).'1', 'email' => "user.{$key}.test@gmail.com",
            ]);
            $users[$key]->forceFill(['created_at' => now()->subDays($createdDaysAgo)])->save();

            for ($i = 0; $i < $count; $i++) {
                Rental::create([
                    'user_id' => $users[$key]->id, 'bike_code' => 'X',
                    'start_station_id' => $station->id, 'start_station_name' => $station->name,
                    'end_station_id' => $station->id, 'end_station_name' => $station->name,
                    'started_at' => $day->copy(), 'ended_at' => $day->copy()->addMinutes($minutes),
                    'price_per_minute' => 500, 'total_minutes' => $minutes, 'total_amount' => $minutes * 500,
                ]);
            }
        }

        // A rental of the current month must not be counted
        Rental::create([
            'user_id' => $users['u4']->id, 'bike_code' => 'X',
            'start_station_id' => $station->id, 'start_station_name' => $station->name,
            'started_at' => now()->subMinutes(500), 'ended_at' => now(),
            'price_per_minute' => 500, 'total_minutes' => 500, 'total_amount' => 250000,
        ]);

        $stored = app(TopBikerService::class)->summarize($month);

        $this->assertSame(4, $stored);
        // more minutes -> more rentals -> older account
        $this->assertSame(
            [$users['u3']->id, $users['u2']->id, $users['u1']->id, $users['u4']->id],
            MonthlyTopBiker::where('month', $month)->orderBy('rank')->pluck('user_id')->all()
        );

        // Running the job again replaces the rows instead of duplicating them
        app(TopBikerService::class)->summarize($month);
        $this->assertSame(4, MonthlyTopBiker::where('month', $month)->count());

        // The home page only reads the stored ranking
        $this->get('/')->assertSee('User u3')->assertSee('***031');
    }

    public function test_home_page_statistics_and_top_stations(): void
    {
        $best = $this->makeStation(['name' => 'Trạm xuất sắc']);
        $worse = $this->makeStation(['name' => 'Trạm tạm được']);
        $this->makeBike($best, ['bike_code' => 'B1']);
        $this->makeBike($worse, ['bike_code' => 'B2']);
        $user = $this->makeUser();

        StationReview::create(['user_id' => $user->id, 'station_id' => $best->id, 'rating' => 5, 'comment' => 'Tuyệt vời']);
        StationReview::create(['user_id' => $user->id, 'station_id' => $worse->id, 'rating' => 3, 'comment' => 'Bình thường']);

        $response = $this->get('/')->assertOk();
        $response->assertSee('2 xe')->assertSee('2 trạm');
        $response->assertSeeInOrder(['Trạm xuất sắc', 'Tuyệt vời', 'Trạm tạm được']);
    }

    // ------------------------------------------------------------------
    // Admin (2.9 - 2.11)
    // ------------------------------------------------------------------

    public function test_admin_area_requires_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/bikes')->assertRedirect(route('admin.login'));
        $this->get('/admin/stations')->assertRedirect(route('admin.login'));

        // A normal user is not an admin
        $this->actingAs($this->makeUser())->get('/admin/bikes')->assertRedirect(route('admin.login'));
    }

    public function test_admin_login(): void
    {
        $admin = $this->makeAdmin();

        $this->post('/admin/login', ['username' => 'admin', 'password' => 'wrong'])
            ->assertSessionHasErrors('username');

        $this->post('/admin/login', ['username' => 'admin', 'password' => '12345'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->get('/admin')->assertOk()->assertSee('Quản lý xe')->assertSee('Quản lý trạm xe');
    }

    public function test_admin_bike_crud_and_trash(): void
    {
        $admin = $this->makeAdmin();
        $station = $this->makeStation();
        $this->actingAs($admin);

        // Create
        $this->post('/admin/bikes', ['bike_code' => 'BG-1000', 'station_id' => $station->id, 'condition_status' => 'normal'])
            ->assertRedirect(route('admin.bikes.index'));
        $bike = Bicycle::where('bike_code', 'BG-1000')->firstOrFail();

        // Duplicate code / missing fields are rejected
        $this->post('/admin/bikes', ['bike_code' => 'BG-1000', 'station_id' => $station->id, 'condition_status' => 'normal'])
            ->assertSessionHasErrors('bike_code');
        $this->post('/admin/bikes', [])->assertSessionHasErrors(['bike_code', 'station_id', 'condition_status']);

        // List + detail + edit pages
        $this->get('/admin/bikes')->assertOk()->assertSee('BG-1000');
        $this->get('/admin/bikes/'.$bike->id)->assertOk()->assertSee('BG-1000');
        $this->get('/admin/bikes/'.$bike->id.'/edit')->assertOk();

        // Update success / failure
        $this->put('/admin/bikes/'.$bike->id, ['bike_code' => 'BG-1000', 'station_id' => $station->id, 'condition_status' => 'repairing'])
            ->assertRedirect(route('admin.bikes.index'))
            ->assertSessionHas('success', 'Cập nhật thành công');
        $this->assertSame('repairing', $bike->fresh()->condition_status);

        $this->from('/admin/bikes/'.$bike->id.'/edit')
            ->put('/admin/bikes/'.$bike->id, ['bike_code' => '', 'station_id' => $station->id, 'condition_status' => 'normal'])
            ->assertRedirect('/admin/bikes/'.$bike->id.'/edit')
            ->assertSessionHas('error', 'Cập nhật thất bại');

        // Soft delete -> trash
        $this->delete('/admin/bikes/'.$bike->id)->assertRedirect(route('admin.bikes.index'));
        $this->assertSoftDeleted($bike);
        $this->get('/admin/bikes/bin')->assertOk()->assertSee('BG-1000');

        // Restore
        $this->post("/admin/bikes/{$bike->id}/restore")
            ->assertRedirect(route('admin.bikes.index'))
            ->assertSessionHas('success', 'Khôi phục thành công');
        $this->assertNotSoftDeleted($bike);

        // Delete again, then delete forever (stays in the trash page)
        $this->delete('/admin/bikes/'.$bike->id);
        $this->post("/admin/bikes/{$bike->id}/force-delete")
            ->assertRedirect(route('admin.bikes.bin'))
            ->assertSessionHas('success', 'Xóa thành công');
        $this->assertDatabaseMissing('bicycles', ['id' => $bike->id]);
    }

    public function test_admin_bike_bulk_trash_actions(): void
    {
        $this->actingAs($this->makeAdmin());
        $station = $this->makeStation(['total_docks' => 20]);

        $bikes = collect(range(1, 4))->map(fn ($i) => $this->makeBike($station, ['bike_code' => "BG-{$i}"]));
        $bikes->each->delete();

        // restore selected (2 of 4)
        $this->post('/admin/bikes/restore-selected', ['ids' => [$bikes[0]->id, $bikes[1]->id]])
            ->assertRedirect(route('admin.bikes.index'))
            ->assertSessionHas('success', 'Khôi phục thành công');
        $this->assertNotSoftDeleted($bikes[0]);
        $this->assertSoftDeleted($bikes[2]);

        // nothing selected -> back to the trash with an error
        $this->post('/admin/bikes/delete-selected', [])->assertRedirect(route('admin.bikes.bin'))->assertSessionHas('error');

        // delete selected (stays in the trash)
        $this->post('/admin/bikes/delete-selected', ['ids' => [$bikes[2]->id]])
            ->assertRedirect(route('admin.bikes.bin'))
            ->assertSessionHas('success', 'Xóa thành công');
        $this->assertDatabaseMissing('bicycles', ['id' => $bikes[2]->id]);

        // restore all / delete all -> management page
        $this->post('/admin/bikes/restore-all')->assertRedirect(route('admin.bikes.index'));
        $this->assertNotSoftDeleted($bikes[3]);
        $bikes[3]->delete();
        $this->post('/admin/bikes/delete-all')->assertRedirect(route('admin.bikes.index'));
        $this->assertDatabaseMissing('bicycles', ['id' => $bikes[3]->id]);
    }

    public function test_admin_cannot_delete_a_rented_bike(): void
    {
        $this->actingAs($this->makeAdmin());
        $bike = $this->makeBike(null, ['location_status' => Bicycle::LOCATION_RENTED]);

        $this->delete('/admin/bikes/'.$bike->id)->assertSessionHas('error');
        $this->assertNotSoftDeleted($bike);
    }

    public function test_admin_cannot_put_a_bike_into_a_full_station(): void
    {
        $this->actingAs($this->makeAdmin());
        $full = $this->makeStation(['total_docks' => 1]);
        $this->makeBike($full, ['bike_code' => 'BG-0001']);

        $this->post('/admin/bikes', ['bike_code' => 'BG-0002', 'station_id' => $full->id, 'condition_status' => 'normal'])
            ->assertSessionHasErrors('station_id');
    }

    public function test_admin_station_crud_and_trash(): void
    {
        $this->actingAs($this->makeAdmin());
        $ward = Ward::create(['code' => 'W1', 'name' => 'Phường Một']);

        $payload = ['name' => 'Trạm Mới', 'address' => '99 Lê Lợi', 'ward_id' => $ward->id, 'total_docks' => 12, 'status' => 'active'];

        $this->post('/admin/stations', $payload)->assertRedirect(route('admin.stations.index'));
        $station = Station::where('name', 'Trạm Mới')->firstOrFail();

        $this->post('/admin/stations', [])->assertSessionHasErrors(['name', 'address', 'ward_id', 'total_docks', 'status']);

        $this->get('/admin/stations')->assertOk()->assertSee('Trạm Mới');
        $this->get('/admin/stations/'.$station->id)->assertOk()->assertSee('99 Lê Lợi');

        $this->put('/admin/stations/'.$station->id, array_merge($payload, ['status' => 'maintenance']))
            ->assertRedirect(route('admin.stations.index'))
            ->assertSessionHas('success', 'Cập nhật thành công');

        // total docks can not be lower than the parked bikes
        $this->makeBike($station, ['bike_code' => 'BG-0001']);
        $this->makeBike($station, ['bike_code' => 'BG-0002']);
        $this->put('/admin/stations/'.$station->id, array_merge($payload, ['total_docks' => 1]))
            ->assertSessionHasErrors('total_docks')
            ->assertSessionHas('error', 'Cập nhật thất bại');

        // soft delete / restore
        $this->delete('/admin/stations/'.$station->id)->assertRedirect(route('admin.stations.index'));
        $this->assertSoftDeleted($station);
        $this->get('/admin/stations/bin')->assertOk()->assertSee('Trạm Mới');
        $this->post("/admin/stations/{$station->id}/restore")->assertRedirect(route('admin.stations.index'));
        $this->assertNotSoftDeleted($station);

        // permanent delete is refused while bikes still belong to the station
        $this->delete('/admin/stations/'.$station->id);
        $this->post("/admin/stations/{$station->id}/force-delete")
            ->assertRedirect(route('admin.stations.bin'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('stations', ['id' => $station->id]);

        // an empty station can be deleted forever
        $empty = Station::create(array_merge($payload, ['name' => 'Trạm trống']));
        $empty->delete();
        $this->post("/admin/stations/{$empty->id}/force-delete")
            ->assertRedirect(route('admin.stations.bin'))
            ->assertSessionHas('success', 'Xóa thành công');
        $this->assertDatabaseMissing('stations', ['id' => $empty->id]);
    }

    public function test_admin_lists_are_paginated_by_ten(): void
    {
        $this->actingAs($this->makeAdmin());
        $station = $this->makeStation(['total_docks' => 50]);

        foreach (range(1, 12) as $i) {
            $this->makeBike($station, ['bike_code' => sprintf('BG-%04d', $i)]);
        }

        $this->get('/admin/bikes')->assertSee('BG-0010')->assertDontSee('BG-0011');
        $this->get('/admin/bikes?page=2')->assertSee('BG-0011')->assertSee('BG-0012');
    }
}
