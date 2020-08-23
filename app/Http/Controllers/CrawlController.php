<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Services\Utils;
use App\Jobs\CrawlerJob;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class CrawlController extends Controller
{

    protected $cacheUrl = 'cache_url';

    protected $cacheInfo = 'cache_info';

    protected $filePattenAllUrl = 'all_url';

    protected $filePattenInfoProduct = 'info_product';

    protected $numberOfProcess = 5;

    public function __construct()
    {
        $this->utils = new Utils();
    }

    public function index(){
        return view('index');
    }

    public function test(Request $request){
        $data = $request->post('patt');
        $data = explode("\n",$data);
        $patts = $this->utils->getAllPattern($data[0]);
        $data = $this->utils->getDataColumnFromUrlWithDom($patts,[$data[1]]);
        return $data;
    }

    public function getData($patt,$url,$key){
        $patt = $this->utils->getAllPattern($patt);
        $data = $this->utils->getDataColumnFromUrlWithDom($patt,$url);
        $this->cache_set($key,$data);
        return $data;
    }


    public function getAllUrl(Request $request){
        $key = intval($request->post('key'));
        $data = Storage::disk('local')->get('public/'.$this->filePattenAllUrl);

        if($key == 0){
            Storage::disk('local')->delete("public/{$this->cacheUrl}");
        }
        $arrayDatas = explode("\n",$data);

        $page = isset($arrayDatas[2])?$arrayDatas[2]:0;
        for($i=1;$i<=$page;$i++){
            $urls[]= str_replace('{{page}}',$i,$arrayDatas[1]);
        }

        $urls = array_chunk($urls,40);
        $this->getData($arrayDatas[0],$urls[$key],$this->cacheUrl);
        $progressCount = $key+1;
        $progressPercent = ($progressCount/count($urls))*100;
        return ['value'=>intval($progressPercent),'currentKey'=>$progressCount,'maxKey'=>count($urls)];
    }

    public function getInfo(Request $request){
        $key = intval($request->post('key'));
        // remove old temp data.
        if($key == 0){
            Storage::disk('local')->delete("public/{$this->cacheInfo}");
        }
        //get data
        $data = $this->cache_get($this->cacheUrl);
        $data = array_chunk($data['src'],100);
        $patts = Storage::disk('local')->get("public/{$this->filePattenInfoProduct}");
        $arrayPatt = $this->utils->getAllPattern($patts);
        $this->createThreadGetInfo($data[$key],$arrayPatt);

        $progressCount = $key+1;
        $progressPercent = ($progressCount/count($data))*100;
        return ['value'=>intval($progressPercent),'currentKey'=>$progressCount,'maxKey'=>count($data)-1];
    }

    public function createThreadGetInfo($urls,$arrayPatt){

        $data = array_chunk($urls,20);

        for ($i = 0; $i < $this->numberOfProcess; $i++) {
            if(isset($data[$i])){
                $process = new Process(['php',base_path('artisan'),'craw:get',serialize($data[$i]),serialize($arrayPatt),'&']);
                $process->setTimeout(0);
                $process->disableOutput();
                $process->start();
                $processes[] = $process;
            }
        }
        // wait for above processes to complete
        while (count($processes)) {
            foreach ($processes as $i => $runningProcess) {
                if (! $runningProcess->isRunning()) {
                    unset($processes[$i]);
                }
                sleep(1);
            }
        }
    }

    public function getDataWithQueue($urls,$arrayPatt){
        foreach($urls['src'] as &$url){
           CrawlerJob::dispatch($url,$arrayPatt,$this->cacheInfo);
        }

    }
    public function exportData(){
        $data = file_get_contents(storage_path("app/public/{$this->cacheInfo}"));
        $data = $this->formatDataExport($data);
        $data = new UsersExport($data);
        return Excel::download($data,'invoices.xlsx');
    }

    public function formatDataExport($datas){
        $dataExport = [];
        $datas = explode("\n",$datas);
        foreach($datas as $key => &$data){
            $data = explode("\n",$data);
            $datas[$key] = unserialize($data[0]);
        };
        unset($data);
        $i = 0;
        $j = 0;
        foreach($datas as  $data){
            foreach($data as $index => $row){
                $i=$j;
                foreach($row as $key => $value){
                    $dataExport["header"][] = $index;
                    $dataExport['src'][$i][] = $value;
                    $i++;
                }
            }
            $j=$i;
        }
        $dataExport["header"] = array_unique($dataExport["header"]);
        return $dataExport;
    }

    function cache_set($key,$val) {
        Storage::disk('local')->append("public/{$key}",serialize($val));
    }

    function cache_get($key) {
        $cache_data = Storage::disk('local')->get("public/{$key}");
        return unserialize($cache_data);
    }


}
