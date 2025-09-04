<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LookupQuery;
use App\Models\Biblio;
use Google\Client as Google_Client;
use Google\Service\Books as Google_Service_Books;

class SearchBiblios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BS:search-biblios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query API and fetch biblio details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Google_Client();
        $service = new Google_Service_Books($client);

        $lookup_queries = LookupQuery::all();
        foreach($lookup_queries as $l_q){
            //echo $l_q->query."\n";
            $query = json_decode($l_q->query);
            $q = $this->formQueryForGoogleAPI($query);
            echo "Query: ".$q."\n";
            $books = $service->volumes->listVolumes($q);    
            foreach($books->getItems() as $b){
                $biblio = new Biblio;
                $biblio->external_id = $b->etag;
                $biblio->title = $b->volumeInfo->title;
                $biblio->authors = json_encode($b->volumeInfo->authors);
                $biblio->publication = $b->volumeInfo->publisher;
                $biblio->published_date = $b->volumeInfo->publishedDate;
                $biblio->all_details = json_encode($b);
                if(!empty($b->volumeInfo->industryIdentifiers)){
                    foreach($b->volumeInfo->industryIdentifiers as $identifier){
                        if($identifier->type == 'ISBN_10'){
                            $biblio->isbn_10 = $identifier->identifier;
                        }
                        if($identifier->type == 'ISBN_13'){
                            $biblio->isbn_13 = $identifier->identifier;
                        }
                        if($identifier->type == 'ISSN'){
                            $biblio->issn = $identifier->identifier;
                        }
                    }
                }
                try{
                    $biblio->save();
                }
                catch(\Exception $e){
                    echo $e->getMessage()."\n";
                }
            }
            $l_q->delete();
        }
    }

    protected function formQueryForGoogleAPI($query){
            if(!empty($query->isbn))
                $q = 'isbn:'.$query->isbn;
            else if(!empty($query->issn))
                $q = 'issn:'.$query->issn;
            else
                $q = $query->title;
            return $q;
    }
}
