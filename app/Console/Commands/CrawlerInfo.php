<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Utils;

class CrawlerInfo extends Command
{
    protected $patt;

    protected $data;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'craw:get {info*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->utils = new Utils();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $urls = unserialize($this->arguments('info')['info'][0]);
        $patts = unserialize($this->arguments('info')['info'][1]);
        $data = $this->utils->getDataColumnFromUrlWithDom($patts,$urls);
        Storage::disk('local')->append('public/cache_info',serialize($data));
    }

}
