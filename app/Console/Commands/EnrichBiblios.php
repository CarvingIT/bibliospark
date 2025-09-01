<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnrichBiblios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BS:enrich-biblios {filepath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Biblio record enrichment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filepath = $this->argument('filepath');
        $lines = file($filepath);
        $header_row = strtolower(array_shift($lines));
        $fields = explode("\t", ltrim(rtrim($header_row)));
        $data = [];
        $identifiers = ['isbn', 'issn'];
        foreach($lines as $l){
            $vals = explode("\t", ltrim(rtrim($l)));
            $data[] = array_combine($fields, $vals);
        }
        //print_r($data);
        foreach($data as $d){
            if(!empty($data['isbn'])){

            }
            else if(!empty($data['issn'])){

            }
            else{ // use title/author to identify a biblio; this is not perfect
            }
        }
    }
}
