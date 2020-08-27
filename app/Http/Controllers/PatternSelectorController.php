<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\Utils;
class PatternSelectorController extends Controller
{
    //
    protected $pattenAllUrl = 'patt_url';

    protected $pattenInfo = 'patt_info';

    protected $utils;

    public function __construct()
    {
        $this->utils = new Utils();
    }

    public function setPatterngGetAllUrl(Request $request ){
        $patt = $request->post('patt');
        $this->utils->cache_set("{$this->pattenAllUrl}",$patt);
    }

    public function setPatternGetInfoProduct(Request $request){
        $patt = $request->post('patt');
        $this->utils->cache_set("{$this->pattenInfo}",$patt);
    }

    public function getPatterngGetAllUrl(Request $request ){
        return $this->utils->cache_get("{$this->pattenAllUrl}");
    }

    public function getPatternGetInfoProduct(Request $request){
        return $this->utils->cache_get("{$this->pattenInfo}");
    }

    public function deletePatten(Request $request){
        Storage::disk('local')->delete('public/'.$this->filePattenAllUrl);
        Storage::disk('local')->delete('public/'.$this->filePattenInfoProduct);
    }
}
