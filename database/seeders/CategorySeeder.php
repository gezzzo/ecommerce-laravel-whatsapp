<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    /**
     * Seed the categories from the old system's JSON export.
     */
    public function run(): void
    {
        $json = json_decode(
            file_get_contents(database_path('seeders/category.json')),
            true,
        );

        $categories = $json['categories']['data'];

        foreach ($categories as $categoryData) {
            $icon = null;

            if (! empty($categoryData['attachments'])) {
                $icon = $this->downloadImage(
                    $categoryData['attachments'][0]['file_path'],
                    'categories',
                );
            }

            Category::create([
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'icon' => $icon,
            ]);
        }

        $this->command->info('Seeded ' . count($categories) . ' categories.');
    }

    /**
     * Download an image from the old server and store it locally.
     *
     * @return string|null The local storage path, or null on failure.
     */
    private function downloadImage(string $oldPath, string $directory): ?string
    {
        $url = 'https://backend.tijaracod.com/storage/' . $oldPath;
        $filename = basename($oldPath);
        $localPath = $directory . '/' . $filename;

        try {
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($localPath, $response->body());

                return $localPath;
            }
        } catch (\Exception $e) {
            $this->command->warn("Failed to download: {$url} — {$e->getMessage()}");
        }

        return null;
    }
}
