<?php

use App\Http\Controllers\DownloadFileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [WebhookController::class, 'inbox'])->middleware(
    'WebHook'
);
Route::get('/notRegistered', [ReplyController::class, 'notRegistered']);
Route::get('/processregister', [WebhookController::class, 'processregister']);

Route::get('postuser', [PostController::class, 'index']);
Route::get('/invalids', function () {
    return view('invalid');
});


Route::get('ahs', function () {
    $licenses = DB::table('licenses')->limit(100)->get();
    //  dd($licenses);
    $data = [];
    $ids = [];
    $licenses->map(function ($license) use (&$ids, &$data) {
        if ($license->updated_at === null) {
            $links = explode(',', $license->host);
            $isactive = false;

            // foreach ($links as $link) {
            //     echo $link;
            //     echo '<br>';
            //     if ($isactive) continue;
            //     // Trim the link and add "https://" if it doesn't start with "http://" or "https://"
            //     $trimmedLink = trim($link);
            //     if (!preg_match("~^(?:f|ht)tps?://~i", $trimmedLink)) {
            //         $trimmedLink = "https://" . $trimmedLink;
            //     }
            //     // Create a context with timeout
            //     $context = stream_context_create(['http' => ['timeout' => 2]]);
            //     // Set the context option for file_get_contents
            //     $headers = @get_headers($trimmedLink, 0, $context);

            //     // Check if the request timed out or if the status code is 200
            //     if ($headers === false || (is_array($headers) && strpos($headers[0], '200'))) {
            //         $isactive = true;
            //         echo $trimmedLink . " - active";
            //         echo "<br>";
            //     }
            // }
            $data[] = [
                //  'id' => $license->id,
                'product' => 'Whatsapp gateway',
                'host' => $license->host,
                'max_links' => $license->max_links,
                'licensekey' => $license->licensekey,
                'customer_name' => $license->customer_name,
                'customer_email' => $license->customer_email,
                'customer_mobile' => $license->customer_mobile,
                'purchase_code' => $license->licensekey,
                'expire_date' => $license->expire_date,
                'is_active' => $isactive,
                'support_end' => Carbon::parse($license->created_at)->addMonths(2),
                'created_at' => "2022-02-23 13:22:12",
            ];
            $ids[] = $license->id;
        }
    });
    try {
        DB::beginTransaction();
        DB::connection('mysql2')->table('licenses')->insert($data);
        DB::table('licenses')->whereIn('id', $ids)->delete();
        DB::commit();
    } catch (\Throwable $th) {
        DB::rollBack();
        throw $th;
    }
});
