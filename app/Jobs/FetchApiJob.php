<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class FetchApiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;

    public function __construct($page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        $response = Http::get('https://dhakawatch24.com/wp-json/wp/v2/posts?page=' . $this->page);

        if ($response->successful()) {
            $jsonData = $response->json();

            foreach ($jsonData as $key => $value) {
                if (isset($value["_links"]["wp:featuredmedia"][0]["href"])) {
                    $imageDetailsLink = $value["_links"]["wp:featuredmedia"][0]["href"];
                } elseif (isset($value["_links"]["wp:attachment"][0]["href"])) {
                    $imageDetailsLink = $value["_links"]["wp:attachment"][0]["href"];
                } else {
                    $imageDetailsLink = "";
                }

                $imageDetailsJson = json_decode(file_get_contents($imageDetailsLink), true);

                if (isset($value["_links"]["wp:featuredmedia"][0]["href"])) {
                    $imageName = $imageDetailsJson["media_details"]["file"];
                } elseif (isset($value["_links"]["wp:attachment"][0]["href"])) {
                    $imageName = $imageDetailsJson[0]["media_details"]["file"];
                } else {
                    $imageName = "";
                }

                $data[] = [
                    'date' => $value['date'],
                    'title' => $value['title']['rendered'],
                    'content' => $value['content']['rendered'],
                    'author' => $value['author'],
                    'featured_media' => $value['featured_media'],
                    'categories' => $value['categories'],
                    'image' => $imageName,
                ];
            }

            $filePath = storage_path('PostData.json');

            if (File::exists($filePath)) {
                $existingData = json_decode(File::get($filePath), true);
                $data = array_merge($existingData, $data);
            }

            File::put($filePath, json_encode($data));

        } else {
            // Retry or log the error
            $this->release(2); // Retry after 10 seconds
        }
    }
}
