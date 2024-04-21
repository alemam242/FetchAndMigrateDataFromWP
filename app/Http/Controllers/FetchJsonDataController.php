<?php

namespace App\Http\Controllers;

use App\Jobs\FetchApiJob;
use App\Jobs\FetchCategoriesJob;

class FetchJsonDataController extends Controller
{
    public function showPosts()
    {
        $data = json_decode(file_get_contents(storage_path('jsonData.json')), true);
$data = array_reverse($data);

$uniqueData = [];
$duplicateObjects = [];

foreach ($data as $item) {
    $serializedItem = serialize($item);
    if (!in_array($serializedItem, $uniqueData)) {
        $uniqueData[] = $serializedItem;
    } else {
        $duplicateObjects[] = $item;
    }
}

return response()->json([
    "total" => count($data),
    'duplicate' => count($duplicateObjects),
    'duplicate_objects' => $duplicateObjects,
    "data" => $data,
]);

    }
    public function showCategories()
    {
        $data = json_decode(file_get_contents(storage_path('jsonCategories.json')), true);

        return response()->json([
            "total" => count($data),
            "data" => $data,
        ]);
    }

    public function storePost()
    {
        for ($page = 65; $page <= 215; $page++) {
            FetchApiJob::dispatch($page);
        }
    }

    public function storeCategory()
    {
        for ($page = 1; $page <= 3; $page++) {
            FetchCategoriesJob::dispatch($page);
        }
    }
}
