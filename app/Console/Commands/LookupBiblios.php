<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Biblio;
use App\Models\LookupQuery;

class LookupBiblios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BS:lookup-biblios {filepath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Biblio record look up';

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
        $query_attributes = ['title', 'authors','edition','publication','publication_year'];
        foreach($data as $d){
            $query = [];
            if(!empty($data['isbn'])){
                $isbn_digits = preg_replace("/[^0-9]/", "", $data['isbn']);
                $length = strlen($isbn_digits);
                $query = ['isbn_'.$length => $isbn_digits];
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
            // see if you get a match
            $biblio = $this->getMatchingBiblio($query);
            if($biblio){ // found a match
                echo $biblio->id."\n";
            }
            else{ // no match; query the API
                echo "Adding new query\n";
                $q = new LookupQuery;
                $q->query = json_encode($query);
                $q->save();
            }
        }
    }

    protected function translate($header_row){
        $header_row = strtolower($header_row);
        $header_row = str_replace('020$a','isbn', $header_row);
        $header_row = str_replace('022$a','issn', $header_row);
        $header_row = str_replace('100$a','authors', $header_row);
        $header_row = str_replace('245$a','title', $header_row);
        $header_row = str_replace('260$b','publication', $header_row);
        $header_row = str_replace('260$c','publication_year', $header_row);
        $header_row = str_replace('250$a','edition', $header_row);
        $header_row = str_replace('490$v','volume', $header_row);
        return $header_row;
    }

    protected function getMatchingBiblio($query){
        $biblio = Biblio::whereRaw('1=1');
        foreach($query as $k=>$v){
            $biblio = $biblio->where($k, $v);
        }
        return $biblio->first();
    } 
}
