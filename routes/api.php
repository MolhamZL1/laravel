<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
    
});
Route::post('/register', [Pharmacy::class, 'register']);
Route::post('/login', [Pharmacy::class, 'login']);
Route::get('/categories' , [Pharmacy::class ,'getCategories']);
Route::get('/medicines/{category}' , [Pharmacy::class ,'getMedicinesByCategory']);
Route::get('/searchByCategory/{category}' , [Pharmacy::class , 'searchByCategory']); 
Route::get('/searchMedicine/{searchTerm}' , [Pharmacy::class , 'searchMedicine']); 
// Route::post('/Add_order' , [Pharmacy::class , 'order']); // Query string and Request Body are required
//Route::get('/show_orders' , [Pharmacy::class , 'show_orders']); // Query string is required Query string is required
// Route Warehouse Owner
// Route::get('/Login' , [WarehouseOwner::class , 'Login']);
// Route::post('/Add' , [WarehouseOwner::class , 'add_product']); // Request Body is required // Query string is required // query string is required
// Route::post('/orders' , [WarehouseOwner::class , 'orderes_show']);
// Route::post('/order_modify' , [WarehouseOwner::class , 'ordermodify']); // Query string and Request Body are required
