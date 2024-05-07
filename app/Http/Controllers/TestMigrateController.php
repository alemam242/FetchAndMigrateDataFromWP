<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestMigrateController extends Controller
{
    public function migratePost(Request $request)
    {
        // $data = json_decode(File::get(\storage_path("smallJsonData.json")), true);
        $data = json_decode(File::get(\storage_path("PostData.json")), true);

        // store category_id into post_term table
        foreach ($data as $value) {
            DB::beginTransaction();
            try {
                $postId = DB::table('posts')->insertGetId([
                    'post_title' => $value['title'],
                    'post_name' => str_replace(' ', '-', Str::lower($value['title'])),
                    'post_content' => $value['content'],
                    'post_author' => 1,
                    'post_language' => 1,
                    'post_type' => 'post',
                    'post_image' => $value['image'],
                    'created_at' => $value['date'],
                    'updated_at' => $value['date'],
                ]);

                DB::table('post_term')->insert([
                    'post_id' => $postId,
                    'term_id' => $value['categories'][0],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $transId = DB::table('translations')->insertGetId([
                    'value' => '{"en":'.$postId.'}'
                ]);

                DB::table('post_translations')->insert([
                    'post_id' => $postId,
                    'translation_id' => $transId,
                ]);

                // Finish the transaction
                DB::commit();
            } catch (Exception $e) {
                return response()->json([
                    "status" => "failed",
                    "message" => $e->getMessage(),
                    "data" => $data,
                ]);
            }

        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been migrated successfully.",
            "data" => $data,
        ]);
    }

    public function migrateCategory(){
        $data = json_decode(File::get(\storage_path("jsonCategories.json")), true);

        // store category_id into post_term table
        foreach ($data as $value) {
            try {
                DB::table('terms')->insert([
                    'name' => $value['name'],
                    'slug' => $value['slug'],
                    'taxonomy' => 'category',
                    'language_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Exception $e) {
                return response()->json([
                    "status" => "failed",
                    "message" => $e->getMessage(),
                    "data" => $data,
                ]);
            }

        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been migrated successfully.",
            "data" => $data,
        ]);
    }

    public function deleteDuplicatePosts(){
        //
        $result = DB::table('posts')
            ->select(DB::raw('MIN(id) as id'), 'post_title')
            ->groupBy('post_title')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        //$this->deletePost($result);

        $data = [];
        foreach ($result as $value) {
            $data[] = DB::table('posts')
                ->where('post_title', 'LIKE', $value->post_title)
                ->where('id', '!=', $value->id)
                ->get();
        }
        $this->deletePost($data);
        return response()->json([
            "status" => "success",
            "message" => "count = ".count($result),
            "data" => $data,
        ]);
    }

    function deletePost($result){
        DB::beginTransaction();
        foreach ($result as $value) {
            foreach ($value as $item) {
                DB::table('posts')
                    ->where('id', $item->id)
                    ->delete();

                DB::table('post_term')
                    ->where('post_id', $item->id)
                    ->delete();
            }
            /*
            DB::table('posts')
                ->where('post_title', 'LIKE', $value->post_title)
                ->where('id', '!=', $value->id)
                ->delete();
            */
        }
        DB::commit();
    }
}
