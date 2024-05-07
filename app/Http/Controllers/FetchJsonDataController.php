<?php

namespace App\Http\Controllers;

use App\Jobs\FetchApiJob;
use App\Jobs\FetchCategoriesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class FetchJsonDataController extends Controller
{
    public function index(){
        $id = rand(299, 2458);
        $data = DB::table('posts')->where('id', $id)->first();
//        return response()->json($data);
        return view('welcome', compact('data'));
    }
    public function showPosts()
    {
        // $data = json_decode(file_get_contents(storage_path('jsonData.json')), true);
        $data = json_decode(file_get_contents(storage_path('PostData.json')), true);
        $data = array_reverse($data);


        return response()->json([
            "total" => count($data),
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
        /*
        $pages = [196,198,204];
        foreach ($pages as $page) {
            FetchApiJob::dispatch($page);
        }
        */
       for ($page = 216; $page <= 218; $page++) {
            FetchApiJob::dispatch($page);
       }
    }

    public function storeCategory()
    {
        for ($page = 1; $page <= 3; $page++) {
            FetchCategoriesJob::dispatch($page);
        }
    }

    public function findDuplicates()
    {
        
        $data = json_decode(File::get(storage_path('PostData.json')), true);

        // Convert the array to a Laravel collection
        $collection = collect($data);

        // Group the collection by title and content
        $grouped = $collection->groupBy(function ($item) {
            return $item['title'] . '-' . $item['content'];
        });

        // Filter the groups to find duplicates
        $duplicates = $grouped->filter(function ($group) {
            return $group->count() > 1;
        });

        // Output the duplicate groups
        return response()->json($duplicates);

    }
}
