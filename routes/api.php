<?php

use Illuminate\Support\Facades\Route;

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

/*
 * Register process
 */
Route::post('register', 'RegisterController@index');
/*
 * Google (Android) and Apple (iOS) API Mock
 */
Route::post('google', 'GoogleController@index');
Route::post('ios', 'GoogleController@index');
/*
 * No access without client token parameter
 */
Route::group(['middleware' => ['clientTokenAuth']], function () {
    /*
     * Purchase process with double method
     */
    Route::match(['get', 'post'], 'purchase', 'PurchaseController@index');
    /*
     * Get active subscription/purchase list
     */
    Route::get('get_subscriptions', 'PurchaseController@get_subscriptions');
});
