<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Helper;
use App\Models\Subdomains;
use Illuminate\Support\Facades\Artisan;
use DB;
use Illuminate\Support\Facades\Config;
use Exception;

class MigrateAllDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate_all:databases';

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
        $databases = Subdomains::all();
        foreach ($databases as $database){
            if(self::changeDataBaseConnection($database->company_url)){

            }
//            print_r(DB::getDatabaseName());
            try{
                Artisan::call('migrate', ['--path' => '/database/migrations/company_db']);
//                sleep(2);
                print_r($database->company_url);
                DB::disconnect($database->company_url);
            }
            catch(Exception $e)
            {
                DB::disconnect($database->company_url);
            }
        }
    }

    public static function changeDataBaseConnection($account)
    {
        if (self::checkIfDatabaseExists($account)) {
            Config::set('database.connections.' . $account, [
                'driver'    => env('DB_CONNECTION'),
                'host'      => env('DB_HOST'),
                'database'  => $account,
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                'prefix'    => '',
            ]);

            return DB::setDefaultConnection($account);
        }else{
            return false;
        }

    }

    public static function checkIfDatabaseExists($db_name)
    {
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'));
        return mysqli_select_db($mysqli, $db_name);
    }
}
