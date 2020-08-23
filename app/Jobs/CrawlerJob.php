<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Services\Utils;
use Illuminate\Support\Facades\Log;

class CrawlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    protected $patt;

    protected $cacheInfo;

    protected $utils;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url,$patt,$cacheInfo)
    {
        //
        $this->url = $url;
        $this->patt = $patt;
        $this->cacheInfo = $cacheInfo;
        $this->utils = new Utils();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->utils->getDataColumnFromUrlWithDom($this->patt,[$this->url]);
        Storage::disk('local')->append("public/{$this->cacheInfo}",serialize($data));
    }
}
