<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Services\Utils;
use App\Jobs\CrawlerJob;
use App\Crawl;
use Illuminate\Support\Facades\Log;

class CrawlController extends Controller
{

    protected $dataUrl = 'data_url';

    protected $dataInfo = 'data_info';

    protected $pattenAllUrl = 'patt_url';

    protected $pattenInfo = 'patt_info';

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
        if(isset($data['status'])){
            return $data;
        }
        $this->utils->reformatData($key,$data);
    }


    public function getAllUrl(Request $request){
        $key = intval($request->post('key'));
        $pattUrl = $this->utils->cache_get($this->pattenAllUrl);
        if(empty($pattUrl)){
            return ['status'=>'204','error'=>'Not Find Xpath !'];
        }
        $pattUrl = explode("\n",$pattUrl);

        if($key == 0){
            Storage::disk('local')->delete("public/{$this->dataUrl}");
        }

        $page = isset($pattUrl[2])?$pattUrl[2]:0;
        for($i=1;$i<=$page;$i++){
            $urls[]= str_replace('{{page}}',$i,$pattUrl[1]);
        }

        $urls = array_chunk($urls,40);
        $data = $this->getData($pattUrl[0],$urls[$key],$this->dataUrl);
        if(isset($data['status'])){
            return $data;
        }
        $progressCount = $key+1;
        $progressPercent = ($progressCount/count($urls))*100;
        if($progressPercent == 100){
            $urlSql = $this->utils->cache_get($this->dataUrl);
            Crawl::create(['name'=>'esty','url'=>json_encode($urlSql)]);
        }
        return ['value'=>intval($progressPercent),'currentKey'=>$progressCount,'maxKey'=>count($urls)];
    }

    public function getInfo(Request $request){
        $key = intval($request->post('key'));
        if($key == 0){
            Storage::disk('local')->delete("public/{$this->dataInfo}");
        }

        $data = $this->utils->cache_get($this->dataUrl);
        if(empty($data)){
            return ['status'=>'204','error'=>'Not Url File Existed !'];
        }

        $data = array_values($data);
        $patts = $this->utils->cache_get("{$this->pattenInfo}");
        $arrayPatt = $this->utils->getAllPattern($patts);
        $this->createThreadGetInfo($data[$key],$arrayPatt);

        $progressCount = $key+1;
        $progressPercent = ($progressCount/count($data))*100;
        $dataSql = $this->utils->cache_get($this->dataInfo);
        if($progressPercent == 100){
            $dataSql = $this->utils->cache_get($this->dataUrl);
            Crawl::where('name','=','esty')->update(['data'=>json_encode($dataSql)]);
        }
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
           CrawlerJob::dispatch($url,$arrayPatt,$this->dataInfo);
        }

    }

    public function exportData(){
        $data = $this->utils->cache_get($this->dataInfo);
        $patts = $this->utils->cache_get($this->pattenInfo);
        $keyPatts = array_keys($this->utils->getAllPattern($patts));
        array_unshift($data,[$keyPatts]);
        $data = new UsersExport($data);
        return Excel::download($data,'invoices.xlsx');
    }

    public function formatDataExport($datas){
        $dataExport = [];
        if(!empty($datas)){
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
        }
        return $dataExport;
    }

}
