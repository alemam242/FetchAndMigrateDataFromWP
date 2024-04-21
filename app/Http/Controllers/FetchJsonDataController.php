<?php

namespace App\Http\Controllers;

use App\Jobs\FetchApiJob;
use Illuminate\Http\Request;
use App\Jobs\FetchCategoriesJob;
use Illuminate\Support\Facades\Storage;

class FetchJsonDataController extends Controller
{
    public function showPosts(){
        $data = json_decode(file_get_contents(storage_path('jsonData.json')), true);

        return response()->json([
            "total"=>count($data),
            "data"=>$data
        ]);
    }
    public function showCategories(){
        $data = json_decode(file_get_contents(storage_path('jsonCategories.json')), true);

        return response()->json([
            "total"=>count($data),
            "data"=>$data
        ]);
    }

    function storePost(){
        for ($page = 1; $page <= 215; $page++) {
            FetchApiJob::dispatch($page);
        }
    }

    function storeCategory(){
        for ($page = 1; $page <= 3; $page++) {
            FetchCategoriesJob::dispatch($page);
        }
    }
}
