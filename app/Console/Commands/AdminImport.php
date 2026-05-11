<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AdminImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:admin';

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
        $table_list = Storage::directories('exportadmin');
        
        if (!empty($table_list)) {
            foreach ($table_list as $dir_list) {
                $dirpath = explode("/",$dir_list);
                $table_name = $dirpath[count($dirpath) - 1];

                \DB::statement("TRUNCATE $table_name");
                
                if ($table_name == 'migrations' || $table_name == 'gopg_migrations') {
                    continue;
                }

                $files = Storage::files($dir_list);
                $total = count($files);
                echo "# insert $table_name of $total  data \n";
                foreach ($files as $k => $v) {
                    $content = Storage::get($v);
                    $this->showStatus($k, $total);
                    $insert = json_decode($content, true);
                    // unset($insert['id']);
                    \DB::table($table_name)->insert($insert);
                }

                $DB_CONNECTION = env("DB_CONNECTION");
                if ($DB_CONNECTION == 'pgsql') {
                    \DB::statement("SELECT setval('".$table_name."_id_seq', (
                        SELECT id FROM $table_name ORDER BY id DESC LIMIT 1
                    ), true);");
                }

                echo "\n";
            }
        }
    }

    function showStatus($done, $total, $size=30) {

        static $start_time;
    
        // if we go over our bound, just ignore it
        if($done > $total) return;
    
        if(empty($start_time)) $start_time=time();
        $now = time();
    
        $perc=(double)(($done+1)/$total);
    
        $bar=floor($perc*$size);
    
        $status_bar="\r[";
        $status_bar.=str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="=";
        }
    
        $disp=number_format($perc*100, 0);
    
        $status_bar.="] $disp%  $done/$total";
    
        if ($done == 0) {
            $done = 1;
        }
        $rate = ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);
    
        $elapsed = $now - $start_time;
    
        $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";
    
        echo "$status_bar  ";
    
        flush();
    
        // when done, send a newline
        if($done == $total) {
            echo "\n";
        }
    
    }
}
