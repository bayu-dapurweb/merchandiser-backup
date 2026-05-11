<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Article;
use App\ArticleDownload;
use App\ArticleGenres;
use App\ArticleMeta;

class Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data to JSON';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (env('DB_CONNECTION') == "pgsql") {
            $tables = \DB::table('pg_catalog.pg_tables')
                ->where('schemaname', 'public')
                ->get();
        } else {
            $tables = \DB::select('SHOW TABLES');
        }
        $data_name = env('DB_DATABASE');

        foreach ($tables as $tab) {
            if (env('DB_CONNECTION') == "pgsql") {
                $table_name = $tab->tablename;
            } else {
                $table_name = $tab->{"Tables_in_" . $data_name};
            }

            $datas = \DB::table($table_name)->get()->toArray();
            foreach ($datas as $k => $v) {
                Storage::put('export/'.$table_name.'/'.$v->id.'.json', json_encode($v));
            }
            echo "# $table_name export " . count($datas) . " items \n";
        }
    }
}
