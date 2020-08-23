<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatternSelectorController extends Controller
{
    //
    protected $filePattenAllUrl = 'all_url';

    protected $filePattenInfoProduct = 'info_product';

    public function setPatterngGetAllUrl(Request $request ){
        $patt = $request->post('patt');
        Storage::disk('local')->put('public/'.$this->filePattenAllUrl,$patt);
    }

    public function setPatternGetInfoProduct(Request $request){
        $patt = $request->post('patt');
        Storage::disk('local')->put('public/'.$this->filePattenInfoProduct,$patt);
    }

    public function getPatterngGetAllUrl(Request $request ){
        return Storage::disk('local')->get('public/'.$this->filePattenAllUrl);
    }

    public function getPatternGetInfoProduct(Request $request){
        return Storage::disk('local')->get('public/'.$this->filePattenInfoProduct);
    }

    public function deletePatten(Request $request){
        Storage::disk('local')->delete('public/'.$this->filePattenAllUrl);
        Storage::disk('local')->delete('public/'.$this->filePattenInfoProduct);
    }
}
