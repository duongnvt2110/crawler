<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\Environment\Runtime as EnvironmentRuntime;
use App\Services\Utils;
use Illuminate\Support\Facades\Storage;
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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/','CrawlController@index');
Route::post('/test','CrawlController@test')->name('test');
Route::post('/get_url_data','CrawlController@getAllUrl')->name('getAllUrl');
Route::post('/get_info_data','CrawlController@getInfo')->name('getInfo');
Route::get('/export_data','CrawlController@exportData')->name('exportData');

Route::post('/set_patt_all_url','PatternSelectorController@setPatterngGetAllUrl')->name('setPattUrl');
Route::post('/set_patt_info_product','PatternSelectorController@setPatternGetInfoProduct')->name('setPattInfoProduct');
Route::post('/show_pattern_url','PatternSelectorController@getPatterngGetAllUrl')->name('showPattUrl');
Route::post('/show_pattern_info','PatternSelectorController@getPatternGetInfoProduct')->name('showPattInfo');
Route::delete('/delete_pattern','PatternSelectorController@deletePatten')->name('deletePattern');


Route::get('demo',function(Request $request){
    // $s = microtime(true);
    $data = 'https://www.etsy.com/listing/271160194/loose-linen-jumpsuit-charcoal-washed?ga_order=most_relevant&ga_search_type=all&ga_view_type=gallery&ga_search_query=&ref=sr_gallery-1-3&frs=1&bes=1&col=1';
    // $patts = Storage::disk('local')->get('public/info_product');
    // $utils = new Utils();
    // $arrayPatt = $utils->getAllPattern($patts);
    // $data = $utils->getDataColumnFromUrlWithDom($arrayPatt,[$data]);
    // //Storage::disk('local')->append('public/data',serialize($data));
    // echo microtime(true)-$s;

    $client = new \GuzzleHttp\Client();

    $promise = $client->getAsync($data);

    dd($promise);
});

Route::get('/show',function(){
    $s = Storage::disk('local')->get('public/data');
    dump($s);
});
