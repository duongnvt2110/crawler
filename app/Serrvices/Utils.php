<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

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
            $content = $this->getFileContent($url);
            $this->doc->loadHTML($content);
            $xpath = new \DOMXpath($this->doc);
            foreach($patts as $key => $patt ){
                if(!empty($patt)){
                    $elements = $xpath->query($patt[0]);
                    if (!is_null($elements) && $elements == true && $elements->length > 0){
                        foreach ($elements as $element) {
                            $node = $element->childNodes[0];
                            if(!empty($node) && $node->length > 0){
                                $data[$patt[1]][] = trim($node->wholeText);
                            }
                        }
                    }else{
                        $data[$patt[1]][] = "";
                    }
                }
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
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        unset($url);
        return $response->getBody(true);
    }

    public function newFile(){
        $client = new \GuzzleHttp\Client();

        $promise = $client->getAsync('http://httpbin.org/get');

        dd($promise);
    }
}
