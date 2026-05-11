<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


$router->group(['prefix' => 'public' ], function () use ($router) {
    $router->get('/splash','Api\SplashController@index');
    $router->get('/faq/tags','Api\FaqController@tags');
    $router->get('/faq/posts','Api\FaqController@index');
    
});

$router->group(['prefix' => 'home' ], function () use ($router) {
    $router->get('/promo/list','Api\PromoController@index');
    $router->get('/article','Api\ArticleController@indexhome');
    $router->get('/rentalinfo','Api\PageController@homeinfo');
    
});

$router->group(['prefix' => 'pages' ], function () use ($router) {
    $router->get('/aboutus','Api\PageController@aboutus');
    $router->get('/support','Api\PageController@support');
    $router->get('/privacypolicy','Api\PageController@privacypolicy');
    $router->get('/termsandcondition','Api\PageController@termsandcondition');
    
});

$router->group(['prefix' => 'auth' ], function () use ($router) {
    $router->post('/sign/guest','Api\AuthController@guestsign');
    $router->post('/sign/google','Api\AuthController@googlesign');
    $router->post('/sign/apple','Api\AuthController@applesign');
    
});

$router->group(['prefix' => 'sauth', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/myprofile','Api\MyProfileController@myprofile');
    $router->post('/myprofile','Api\MyProfileController@updateprofile');
    $router->post('/removeaccount/request','Api\MyProfileController@removeaccountrequest');
    $router->post('/removeaccount/submit','Api\MyProfileController@removeaccountsubmit');
});


$router->group(['prefix' => 'trip', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/info','Api\TripsController@info');
    $router->get('/pickup-point/list','Api\TripsController@pickuppointlist');
    $router->post('/pickup-point/set','Api\TripsController@setpickuppoint');
    $router->post('/roundtrip/set','Api\TripsController@roundtrip');
    $router->post('/swaptrip/set','Api\TripsController@swaptrip');
    $router->post('/submit','Api\TripsController@submit');
});

$router->group(['prefix' => 'car', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/list','Api\CarsController@list');
    $router->get('/rating/{id}','Api\CarsController@ratingbytypes');
    $router->post('/submit','Api\CarsController@submit');
});

$router->group(['prefix' => 'checkout', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/overview','Api\CheckoutController@overview');
    $router->post('/promo','Api\CheckoutController@promosubmit');
    $router->post('/promo/remove','Api\CheckoutController@promoremove');
    $router->post('/voucher','Api\CheckoutController@vouchersubmit');
    $router->post('/pay','Api\CheckoutController@pay');
});

//fastpay expres
$router->group(['prefix' => 'checkout/v2/', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/overview','Apiv2\CheckoutController@overview');
    $router->get('/promo/activated','Apiv2\CheckoutController@promoactivated');
    $router->post('/promo/add','Apiv2\CheckoutController@promosubmit');
    $router->post('/promo/remove','Apiv2\CheckoutController@promoremove');
    $router->post('/pay','Api\CheckoutController@pay');
});

//fastpay custom
$router->group(['prefix' => 'checkout/v3/', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/overview','Apiv2\CheckoutController@overview');
    $router->get('/promo/activated','Apiv2\CheckoutController@promoactivated');
    $router->post('/promo/add','Apiv2\CheckoutController@promosubmit');
    $router->post('/promo/remove','Apiv2\CheckoutController@promoremove');
    $router->post('/voucher','Api\CheckoutController@vouchersubmit');
    $router->get('/paymentoption','Apiv3\CheckoutController@paymentoptions');
    $router->post('/pay','Apiv3\CheckoutController@pay');
    $router->get('/payment/detail/{id}','Apiv3\CheckoutController@paymentdetail');
});


// $router->group(['prefix' => 'rental', 'middleware' => ['auth.api']], function () use ($router) {
//     $router->get('/rental','Api\RentalController@info');
// });

$router->group(['prefix' => 'location', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/lastvisit','Api\LocationController@lastvisit');
    $router->get('/searchplace','Api\LocationController@searchplace');
    $router->get('/searchgeo','Api\LocationController@searchaddress');
    $router->get('/lastselectedlocation','Api\LocationController@lastselectedlocation');
    $router->get('/addressbook','Api\AddressbookController@index');
    $router->post('/addressbook/create','Api\AddressbookController@create');
    $router->post('/addressbook/delete','Api\AddressbookController@delete');
});

$router->group(['prefix' => 'pickup', 'middleware' => ['auth.api']], function () use ($router) {
    $router->post('/selectpickup','Api\LocationController@selectpickup');
    $router->post('/remove','Api\LocationController@removepickup');
});

$router->group(['prefix' => 'origin', 'middleware' => ['auth.api']], function () use ($router) {
    $router->post('/selectlocation','Api\LocationController@selectlocation');
    $router->post('/remove','Api\LocationController@removedestination');
});


$router->group(['prefix' => 'destination', 'middleware' => ['auth.api']], function () use ($router) {
    $router->post('/selectlocation','Api\LocationController@selectlocation');
    $router->post('/remove','Api\LocationController@removedestination');
});

$router->group(['prefix' => 'promo', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/list','Api\PromoController@index');
    $router->get('/detail/{id}','Api\PromoController@detail');
});

$router->group(['prefix' => 'article', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/tags','Api\ArticleController@tag');
    $router->get('/list','Api\ArticleController@index');
    $router->get('/detail/{id}','Api\ArticleController@detail');
});

$router->group(['prefix' => 'myorder', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/list','Api\MyOrderController@index');
    $router->get('/status/options','Api\MyOrderController@statusoptions');
    $router->get('/detail/{id}','Api\MyOrderController@detail');
    $router->get('/ratings/{id}','Api\RatingController@detail');
    $router->get('/ratings-tag','Api\RatingController@options');
    $router->post('/rating/submit','Api\RatingController@submit');
    $router->get('/searchdriver/{id}','Api\MyOrderController@searchdriver');
    $router->get('/qrcode/{id}','Api\MyOrderController@qrcode');
    $router->get('/pdf/{id}','Api\MyOrderController@pdf');
    $router->post('/additionalpayment/submit','Api\AdditionalPaymentController@submit');
    $router->get('/additionalpayment/detail/{id}','Api\AdditionalPaymentController@detail');
});

$router->group(['prefix' => 'mynotification', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/list','Api\NotificationController@index');
    $router->post('/setread','Api\NotificationController@setread');
});

$router->group(['prefix' => 'shuttle', 'middleware' => ['auth.api']], function () use ($router) {
    $router->get('/overview','Apiv3\ShuttleController@overview');
    $router->get('/options/points','Apiv3\ShuttleController@points');
    $router->get('/options/available-date','Apiv3\ShuttleController@availabledate');
    $router->get('/search','Apiv3\ShuttleController@search');
    $router->get('/seats','Apiv3\ShuttleController@seats');
    $router->post('/seats/select','Apiv3\ShuttleOrderController@selectseat');
    $router->post('/seats/unselect','Apiv3\ShuttleOrderController@unselectseat');
});

$router->group(['prefix' => 'rating', 'middleware' => ['auth.api']], function () use ($router) {
    $router->post('/submit','Api\RatingController@submit');
});

$router->group(['prefix' => 'callback/flip', 'middleware' => []], function () use ($router) {
    $router->post('/{tipe}','Api\CallbackController@flip');
});

$router->group(['prefix' => 'callback/fastpay', 'middleware' => ['ip.whitelist']], function () use ($router) {
    $router->post('/{tipe}','Api\CallbackController@fastpay');
});

$router->group(['prefix' => 'page', 'middleware' => []], function () use ($router) {
    $router->get('/aboutus','Api\PageController@aboutus');
    $router->get('/privacypolicy','Api\PageController@privacypolicy');
    $router->get('/termsandcondition','Api\PageController@termsandcondition');
    $router->get('/support','Api\PageController@support');
    $router->get('/contactus','Api\PageController@contactus');
});

/* api driver */





$router->group(['prefix' => 'driver/splash', 'middleware' => []], function () use ($router) {
    $router->get('/','Api\SplashController@driver');
});

$router->group(['prefix' => 'driver/faq/list', 'middleware' => []], function () use ($router) {
    $router->get('/','Api\Driver\FaqController@list');
});

$router->group(['prefix' => 'driver/sign', 'middleware' => []], function () use ($router) {
    $router->post('/google','Api\Driver\AuthController@signin');
});

$router->group(['prefix' => 'v2/driver/auth', 'middleware' => []], function () use ($router) {
    $router->post('/login','Apiv2\Driver\AuthController@signin');
    $router->post('/forget','Apiv2\Driver\AuthController@forget');
    $router->post('/otp','Apiv2\Driver\AuthController@otp');
    $router->post('/new-password','Apiv2\Driver\AuthController@newpass');
});

$router->group(['prefix' => 'driver/registration', 'middleware' => []], function () use ($router) {
    $router->get('/info','Api\Driver\AuthController@getinfo');
    $router->post('/biodata','Api\Driver\AuthController@postbiodata');
    $router->post('/doc','Api\Driver\AuthController@postdoc');
    $router->post('/submit','Api\Driver\AuthController@submit');
});

$router->group(['prefix' => 'driver/activation', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/info','Api\Driver\ActivationController@info');
    $router->post('/submit','Api\Driver\ActivationController@submit');
});

$router->group(['prefix' => 'driver/location', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->post('/submit','Api\Driver\TrackingController@submit');
});

$router->group(['prefix' => 'driver/cars', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/availablecar','Api\Driver\CarController@availablecar');
    $router->get('/info','Api\Driver\CarController@info');
    $router->post('/submit','Api\Driver\CarController@submit');
});

$router->group(['prefix' => 'driver/bank', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/list','Api\Driver\BankController@list');
    $router->post('/create','Api\Driver\BankController@create');
    $router->post('/delete','Api\Driver\BankController@remove');
});

$router->group(['prefix' => 'driver/service', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/info','Api\Driver\ProfileController@serviceinfo');
    $router->post('/update','Api\Driver\ProfileController@serviceinfoupdate');
});

$router->group(['prefix' => 'driver/profile', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/info','Api\Driver\ProfileController@getinfo');
    $router->post('/update','Api\Driver\ProfileController@profileupdate');
    $router->post('/photo','Api\Driver\ProfileController@photoupdate');
});


$router->group(['prefix' => 'driver/bank', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/info','Api\Driver\ProfileController@serviceinfo');
    $router->post('/update','Api\Driver\ProfileController@serviceinfoupdate');
});

$router->group(['prefix' => 'driver/sos', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->post('/','Api\HelperController@dummysos');
});

$router->group(['prefix' => 'driver/page', 'middleware' => []], function () use ($router) {
    $router->get('/contactus','Api\Driver\PageController@contactus');
});

$router->group(['prefix' => 'driver/notification', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/list','Api\NotificationController@indexdriver');
    $router->post('/setread','Api\NotificationController@setread');
});

$router->group(['prefix' => 'driver/setting', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/autoaccept','Apiv3\DriverSettingController@autoaccept');
    $router->post('/autoaccept','Apiv3\DriverSettingController@autoacceptsubmit');
});

$router->group(['prefix' => 'driver/order', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/active','Api\Driver\OrderController@active');
    $router->get('/new','Api\Driver\OrderController@new');
    $router->post('/new','Api\Driver\OrderController@newaccepts');
    $router->get('/list','Api\Driver\OrderController@list');
    $router->get('/detail/{id}','Api\Driver\OrderController@detail');
    $router->post('/qrscan','Api\Driver\OrderController@qrscan');
    $router->post('/arrived','Api\Driver\OrderController@arrived');
    $router->post('/starttrip','Api\Driver\OrderController@starttrip');
    $router->post('/complete','Api\Driver\OrderController@complete');
    $router->post('/carchange','Api\Driver\OrderController@carchange');
    $router->post('/baggages','Api\Driver\OrderController@baggages');
});

$router->group(['prefix' => 'driver/wallet', 'middleware' => ['auth.api.driver']], function () use ($router) {
    $router->get('/index','Api\Driver\WalletController@index');
    $router->get('/list','Api\Driver\WalletController@list');
    $router->get('/detail/{code}','Api\Driver\WalletController@detail');
    $router->post('/withdraw','Api\Driver\WalletController@withdraw');
    $router->post('/topup','Api\Driver\WalletController@topup');
    $router->get('/topup/nominaloption','Api\Driver\WalletController@nominaloption');
    $router->post('/topup/payment','Api\Driver\WalletController@payment');
    $router->get('/topup/payment/detail/{id}','Api\Driver\WalletController@paymentdetail');
});



/* cron */
$router->group(['prefix' => 'cron', 'middleware' => []], function () use ($router) {
    $router->get('/expiredorder','Api\CronController@expiredorder');
    $router->get('/simexpired','Api\CronController@simexpired');
    $router->get('/cutoff','Api\CronController@cutoff');
});


/* Location Helper */
$router->group(['prefix' => 'location-helper', 'middleware' => []], function () use ($router) {
    $router->get('/province','Apiv2\LocationHelperController@province');
    $router->get('/city','Apiv2\LocationHelperController@city');
    $router->get('/kecamatan','Apiv2\LocationHelperController@kecamatan');
    $router->get('/kelurahan','Apiv2\LocationHelperController@kelurahan');
    $router->get('/driverincome','Apiv2\LocationHelperController@driverincome');
});


$router->group(['middleware' => []], function () use ($router) { 
    Route::post('/helper/fcm-notification', 'Api\HelperController@fcmnotification')->name('option-helper');
    Route::get('/option/helper', 'Api\HelperController@optionHelper')->name('option-helper');
    Route::post('/upload/helper', 'Api\HelperController@uploadHelper')->name('upload-helper');
    Route::post('/multi-upload/helper', 'Api\HelperController@multiUploadHelper')->name('upload-helper-multiple');
    Route::post('/midtrans/callback', 'Api\MidtransController@callback')->name('post.midtrans.callback');
    Route::post('/survei/helper', 'Api\HelperController@survey')->name('post.survey.helper');
    Route::post('/pagesetting/helper', 'Api\HelperController@pagesetting')->name('post.pagesetting.helper');
    Route::post('/pagesetting/delete/helper', 'Api\HelperController@pagesettingdelete')->name('post.pagesetting.delete.helper');
    Route::get('/tagmap', 'Api\HelperController@tagmap')->name('tagmap');
    Route::get('/enscriptalluser', 'Api\HelperController@enscriptalluser')->name('enscriptalluser');
    Route::get('/region', 'Api\HelperController@region')->name('region.helper');
    Route::get('/selectedregion', 'Api\HelperController@selectedregion')->name('selectedregion.helper');
    Route::get('/qrcode', 'Api\HelperController@qrcode')->name('api.rcode.helper');
    Route::get('/en', 'Api\HelperController@en')->name('api.en.helper');


    Route::get('/option/brand', 'Api\HelperController@brandoption')->name('option-brand');
    Route::get('/option/vehicletype', 'Api\HelperController@vehicletypeoption')->name('option-vehicletype');
    Route::get('/option/banks', 'Api\HelperController@banksoption')->name('option-bank');
    
// Route::post('/lang/{lang}', 'Api\HelperController@lang')->name('post.helper.lang');
});


$router->group(['middleware' => []], function () use ($router) { 
    Route::get('/generate/pdf/{id}', 'Api\MyOrderController@pdf')->name('generate-pdf');
});

$router->group(['prefix' => 'cron', 'middleware' => []], function () use ($router) {
    $router->get('/mailer','Apiv2\MailerController@mailer');
    $router->get('/prodsync','Apiv2\CronController@itemmaster');
    $router->get('/refreshtoken','Apiv2\CronController@refreshtoken');
});

$router->group(['prefix' => 'admin', 'middleware' => []], function () use ($router) {
    $router->get('/product-branches/force-delete-soft-deleted', 'Api\ProductBranchesController@forceDeleteSoftDeleted');
});