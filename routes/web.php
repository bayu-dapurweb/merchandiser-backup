<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/', function(){
//     return file_get_contents(__DIR__ . "/../public/index.html");
// });

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Rbac;
use App\Http\Controllers\Elvista\ElvistaController;
use App\Http\Controllers\Elvista\PaymentController;
use App\Http\Controllers\Elvista\MudikController;

Route::get('/delete-filemanager', 'DeleteFilemanagerController@filemanagerDelete')->name('delete-filemanager');

// OAuth redirect route for social login
Route::get('/auth/redirect/{provider}', function($provider) {
    // Redirect placeholder - returns to login page
    // If you want to use OAuth, implement the actual Socialite logic here
    return redirect()->route('getLogin')->with('message', 'OAuth provider not configured');
})->name('redirect');

Route::get('/', function(){
    return redirect( uri('admin') );
})->name('get.home');

Route::get('/about-us', 'Fe\PageController@aboutus')->name('get.about-us');
Route::get('/cars', 'Fe\PageController@cars')->name('get.cars');
Route::get('/tours', 'Fe\PageController@tours')->name('get.tours');
Route::get('/tours/{slug}', 'Fe\PageController@tourdetail')->name('get.tour.detail');

Route::get('/sitemap.xml', 'Fe\PageController@sitemap')->name('get.sitemap');


Route::match(['get', 'post'], '/import-users', function (Request $request) {
    if ($request->isMethod('get')) {
        // Show upload form
        return <<<HTML
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{$request->session()->token()}">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Upload & Import</button>
        </form>
HTML;
    }

    // Handle POST: import CSV
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('csv_file');
    $handle = fopen($file->getRealPath(), 'r');
    $header = fgetcsv($handle);

    $userCount = 0;
    $missingBranches = [];
    $missingStores = [];
    $invalidRoles = [];
    $now = date('Y-m-d H:i:s');

    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($header, $row);

        $userPayload = [
            'name' => $data['Nama'],
            'status' => 'Active',
        ];

        $privilegeId = Rbac::resolveImportPrivilegeId($data['Role ID'] ?? null);
        if ($privilegeId !== null) {
            $userPayload['id_cms_privileges'] = $privilegeId;
        } elseif (!Rbac::isEnabled()) {
            $userPayload['id_cms_privileges'] = $data['Role ID'];
        } else {
            $invalidRoles[$data['Role ID'] ?? ''] = true;
        }

        // Insert or update user
        DB::table('cms_users')->updateOrInsert(
            ['email' => $data['Email']],
            $userPayload
        );
        $user = DB::table('cms_users')->where('email', $data['Email'])->first();

        // Handle branches (lookup only)
        $branches = array_map('trim', explode(',', $data['Branch']));
        foreach ($branches as $branchCode) {
            if (!$branchCode) continue;
            $branch = DB::table('ref_branches')->where('code', $branchCode)->first();
            if ($branch) {
                DB::table('map_cms_users_ref_branches')->updateOrInsert(
                    [
                        'ref_branches_id' => $branch->id,
                        'cms_users_id' => $user->id,
                    ],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            } else {
                $missingBranches[$branchCode] = true;
            }
        }

        // Handle stores (lookup only)
        $stores = array_map('trim', explode(',', $data['Store']));
        foreach ($stores as $storeCode) {
            if (!$storeCode) continue;
            $store = DB::table('ref_warehouses')->where('code', $storeCode)->first();
            if ($store) {
                DB::table('map_cms_users_ref_warehouses')->updateOrInsert(
                    [
                        'ref_warehouses_id' => $store->id,
                        'cms_users_id' => $user->id,
                    ],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            } else {
                $missingStores[$storeCode] = true;
            }
        }

        $userCount++;
    }
    fclose($handle);

    $missingBranchesList = implode(', ', array_keys($missingBranches));
    $missingStoresList = implode(', ', array_keys($missingStores));
    $invalidRolesList = implode(', ', array_filter(array_keys($invalidRoles), 'strlen'));

    $msg = "Imported $userCount users from uploaded CSV.<br>";
    if ($invalidRolesList) {
        $msg .= "Invalid role IDs (privilege not updated): $invalidRolesList<br>";
    }
    if ($missingBranchesList) {
        $msg .= "Missing branches (not mapped): $missingBranchesList<br>";
    }
    if ($missingStoresList) {
        $msg .= "Missing stores (not mapped): $missingStoresList<br>";
    }
    return $msg;
})->middleware('\crocodicstudio\crudbooster\middlewares\CBBackend');