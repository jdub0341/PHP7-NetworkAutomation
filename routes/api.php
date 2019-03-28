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

/**
 * @SWG\Info(title="test oauth API", version="0.3")
 **/

/**
 * @SWG\Get(
 *     path="/api/hello",
 *     summary="Hello world test for API troubleshooting",
 *     @SWG\Response(response="200", description="Hello world example")
 * )
 **/
Route::middleware('api')->get('/hello', function (Request $request) {
    return 'hello world';
});

// This was the default file contents of this file, it has been disabled by PHP7-Laravel5-EnterpriseAuth
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

/*******************************************************************************/
// Device

/**
 * @SWG\Get(
 *     path="/api/device",
 *     tags={"Device"},
 *     summary="Get List of Devices",
 *     description="",
 *     operationId="",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="include",
 *         in="query",
 *         description="relationships to include (Comma seperated)",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[id]",
 *         in="query",
 *         description="id of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[ip]",
 *         in="query",
 *         description="ip of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[type]",
 *         in="query",
 *         description="type of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[name]",
 *         in="query",
 *         description="name of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[model]",
 *         in="query",
 *         description="model of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="filter[serial]",
 *         in="query",
 *         description="serial of device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="successful operation",
 *     ),
 *     security={
 *         {"AzureAD": {}},
 *     }
 * )
 **/

/**
 * @SWG\Get(
 *     path="/api/device/{id}",
 *     tags={"Device"},
 *     summary="Get Device by ID",
 *     description="",
 *     operationId="",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of Device",
 *         required=true,
 *         type="integer"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="successful operation",
 *     ),
 *     @SWG\Response(
 *         response="401",
 *         description="Unauthorized user",
 *     ),
 *     security={
 *         {"AzureAD": {}},
 *     }
 * )
 **/

/**
 * @SWG\Put(
 *     path="/api/device/{id}",
 *     tags={"Device"},
 *     summary="Update device by ID",
 *     description="",
 *     operationId="",
 *     consumes={"application/x-www-form-urlencoded"},
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of Device",
 *         required=true,
 *         type="integer"
 *     ),
 *     @SWG\Parameter(
 *         name="name",
 *         in="formData",
 *         description="Name of Device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="ip",
 *         in="formData",
 *         description="IP of Device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="type",
 *         in="formData",
 *         description="type of Device",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="successful operation",
 *     ),
 *     @SWG\Response(
 *         response="401",
 *         description="Unauthorized user",
 *     ),
 *     security={
 *         {"AzureAD": {}},
 *     }
 * )
 */

/**
     * @SWG\Delete(
     *     path="/api/device/{id}",
     *     tags={"Device"},
     *     summary="Delete by ID",
     *     description="",
     *     operationId="",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     *     security={
     *         {"AzureAD": {}},
     *     }
     * )
     */
Route::apiResource('/device', 'DeviceController');

/**
 * @SWG\Get(
 *     path="/api/device/search/{search}",
 *     tags={"Device"},
 *     summary="Search for text inside device data fields using Scout",
 *     description="",
 *     operationId="",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="search",
 *         in="path",
 *         description="search",
 *         required=true,
 *         type="string"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="successful operation",
 *     ),
 *     @SWG\Response(
 *         response="401",
 *         description="Unauthorized user",
 *     ),
 *     security={
 *         {"AzureAD": {}},
 *     }
 * )
 **/
Route::get('device/search/{search}', 'DeviceController@search')->name('device.search');
