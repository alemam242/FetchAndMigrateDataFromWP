<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
protected $page;

    public function __construct($page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        $response = Http::get('https://dhakawatch24.com/wp-json/wp/v2/categories?page=' . $this->page);

        if ($response->successful()) {
            $jsonData = $response->json();

            foreach ($jsonData as $key => $value) {
                $data[] = [
                    'name' => $value['name'],
                    'slug' => str_replace(' ', '-', Str::lower($value['name'])),
                ];
            }

            $filePath = storage_path('jsonCategories.json');

            if (File::exists($filePath)) {
                $existingData = json_decode(File::get($filePath), true);
                $data = array_merge($existingData, $data);
            }

            File::put($filePath, json_encode($data));

        } else {
            // Retry or log the error
            $this->release(3); // Retry after 3 seconds
        }
    }
}
