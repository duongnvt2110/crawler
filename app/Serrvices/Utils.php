<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Redis;

class Utils {

    protected $doc;

    public function __construct()
    {
        $this->doc = new \DOMDocument();
        libxml_use_internal_errors(true);
    }

    public function getDataColumnFromUrlWithDom($patts,$urls){
        $data = [];
        foreach($urls as $url){
            // Speed depend on a responose of this request.
            $content = $this->getFileContent($url);
            if($content instanceof \GuzzleHttp\Psr7\Stream){
                $this->doc->loadHTML($content);
                $xpath = new \DOMXpath($this->doc);
                foreach($patts as $key => $patt ){
                    if(!empty($patt)){
                        $elements = $xpath->query($patt[0]);
                        $keyValue = !empty($data[$url])?count($data[$url]):0;
                        if(!is_null($elements) && $elements == true && $elements->length > 0){
                            for($i=0;$i<$elements->length;$i++){
                                if(!empty($elements->item($i)->value)){
                                    $data[$url][$keyValue] = $elements->item($i)->value;
                                    $keyValue++;
                                }else if(!empty($elements->item($i)->wholeText)){
                                    $data[$url][$keyValue] = trim($elements->item($i)->wholeText);
                                    $keyValue++;
                                }
                            }
                            //Redis::hSet($patt[1],$url,json_encode($data[$url]));
                        }else{
                            $data[$url][$keyValue] = "";
                            //Redis::hSet($patt[1],$url,'');
                        }
                    }
                }
            }else{
                return $content;
            }
        }
        return $data;
    }

    public function getAllPattern($arrayDatas){
        $arrayPatt = [];
        $arrayDatas = explode("\n",$arrayDatas);
        foreach($arrayDatas as $arrayData){
            $columnWithLine = explode("|",$arrayData);
            if(!empty($columnWithLine[0])){
                $pattOrReg = str_replace('select:','',$columnWithLine[0]);
                if(!empty($columnWithLine[1])){
                    $attr = str_replace('attr:','',$columnWithLine[1]);
                    $pattOrReg .="/@{$attr}";
                }
            }
            if(!empty($columnWithLine[2])){
                $columnName = str_replace('column:','',$columnWithLine[2]);
            }
            if(!empty($columnName)){
                $arrayPatt[$columnName][0] = $pattOrReg;
                $arrayPatt[$columnName][1] = $columnName;
            }
        }
        return $arrayPatt;
    }

    public function getFileContent(&$url){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url,[
                'timeout '=> 30
            ]);
            unset($url);
            return $response->getBody(true);
        }catch (RequestException $e){
            return ['status'=>204,'error'=>$e->getMessage()];
        }catch (ConnectException $e){
            return ['status'=>204,'error'=>$e->getMessage()];
        }
    }

    public function reformatData($key,&$data){
        $dataCache = $this->cache_get($key);
        if(empty($dataCache)){
            $this->cache_set($key,$data);
        }else{
            $data = array_merge($dataCache,$data);
            $this->cache_set($key,$data);
        }
        unset($data);
    }

    function cache_set($key,$val) {
        Storage::disk('local')->put("public/{$key}",serialize($val));
    }

    function cache_append($key,$val) {
        Storage::disk('local')->append("public/{$key}",serialize($val));
    }

    function cache_get($key) {
        if(file_exists(storage_path("app/public/{$key}"))){
            $cache_data = Storage::disk('local')->get("public/{$key}");
            return unserialize($cache_data);
        }
        return [];
    }


    // public function newFile(){
    //     $client = new \GuzzleHttp\Client();

    //     $promise = $client->getAsync('http://httpbin.org/get');

    //     dd($promise);
    // }
}
