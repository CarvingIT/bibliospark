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
        // replacements
        $header_row = $this->translate($header_row);
        $fields = explode("\t", ltrim(rtrim($header_row)));
        $data = [];
        foreach($lines as $l){
            $vals = explode("\t", ltrim(rtrim($l)));
            $data[] = array_combine($fields, $vals);
        }
        //print_r($data);
        $query_attributes = ['title', 'author','edition','volume','publisher','publication_year'];
        foreach($data as $d){
            $query = [];
            if(!empty($data['isbn'])){
                $query = ['isbn' => $d['isbn']];
            }
            else if(!empty($data['issn'])){
                $query = ['issn' => $d['issn']];
            }
            else{ // use title/author to identify a biblio; this is not perfect
                foreach($query_attributes as $qa){
                    if(!empty($d[$qa]))
                    $query[$qa] = $d[$qa];
                }
            }
        }
    }

    public function translate($header_row){
        $header_row = strtolower($header_row);
        $header_row = str_replace('020$a','isbn', $header_row);
        $header_row = str_replace('022$a','issn', $header_row);
        $header_row = str_replace('100$a','author', $header_row);
        $header_row = str_replace('245$a','title', $header_row);
        $header_row = str_replace('260$b','publisher', $header_row);
        $header_row = str_replace('260$c','publication_year', $header_row);
        $header_row = str_replace('250$a','edition', $header_row);
        $header_row = str_replace('490$v','volume', $header_row);
        return $header_row;
    }
}
