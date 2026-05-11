<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AdminExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:admin';

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

            $is_cms = substr($table_name, 0, 4);
            if ($is_cms != "cms_") {
                continue;
            }

            $datas = \DB::table($table_name)->get()->toArray();
            foreach ($datas as $k => $v) {
                Storage::put('exportadmin/'.$table_name.'/'.$v->id.'.json', json_encode($v));
            }
            echo "# $table_name export " . count($datas) . " items \n";
        }
    }
}
