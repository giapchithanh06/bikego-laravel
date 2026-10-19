<?php

namespace App\Services;

use App\Models\Ward;
use Illuminate\Support\Facades\Http;

/**
 * Loads the ward list (JSON given by the exam) into the "wards" table.
 *
 * Expected JSON: { "communes": [ { "code": 25747, "name": "Phường Thủ Dầu Một", ... }, ... ] }
 */
class WardImporter
{
    /** Small built-in list used only when the JSON can not be downloaded (offline machine). */
    public const FALLBACK = [
        'Phường Sài Gòn', 'Phường Bến Thành', 'Phường Tân Định', 'Phường Cầu Ông Lãnh',
        'Phường Chợ Quán', 'Phường An Đông', 'Phường Chợ Lớn', 'Phường Bình Thạnh',
        'Phường Gia Định', 'Phường Phú Nhuận', 'Phường Tân Sơn Nhất', 'Phường Bảy Hiền',
        'Phường Thủ Đức', 'Phường Linh Xuân', 'Phường Hiệp Bình', 'Phường Tăng Nhơn Phú',
        'Phường Gò Vấp', 'Phường Hạnh Thông', 'Phường An Hội Tây', 'Phường Tân Bình',
    ];

    /** Import from a local JSON file. Returns the number of imported wards. */
    public function importFromFile(string $path): int
    {
        return $this->importFromArray(json_decode((string) file_get_contents($path), true) ?: []);
    }

    /** Download the JSON from $url, cache it in $cachePath and import it. */
    public function importFromUrl(string $url, ?string $cachePath = null): int
    {
        $response = Http::timeout(20)->acceptJson()->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' when downloading '.$url);
        }

        if ($cachePath) {
            @mkdir(dirname($cachePath), 0777, true);
            file_put_contents($cachePath, $response->body());
        }

        return $this->importFromArray($response->json() ?? []);
    }

    /** Insert the small fallback list. */
    public function importFallback(): int
    {
        $items = [];
        foreach (self::FALLBACK as $i => $name) {
            $items[] = ['code' => 'F'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT), 'name' => $name];
        }

        return $this->importFromArray(['communes' => $items]);
    }

    /** @param array<string, mixed> $json */
    public function importFromArray(array $json): int
    {
        $items = $json['communes'] ?? $json;
        $count = 0;

        foreach ($items as $item) {
            if (! is_array($item) || empty($item['name'])) {
                continue;
            }

            Ward::updateOrCreate(
                ['code' => (string) ($item['code'] ?? md5($item['name']))],
                ['name' => trim($item['name'])]
            );
            $count++;
        }

        return $count;
    }
}
