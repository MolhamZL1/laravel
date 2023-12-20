<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pharmacy;
use App\Http\Controllers\repo;
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
//Route::post('/addOrder' , [Pharmacy::class , 'addOrder']);
//Route::get('/orders' , [Pharmacy::class , 'orders']); 
//Route::post('/addCart' , [Pharmacy::class , 'addCart']);
//Route::get('/cart' , [Pharmacy::class , 'cart']);

// Route Warehouse Owner
Route::post('/loginAdmin' , [repo::class , 'loginAdmin']);
Route::post('/registerAdmin' , [repo::class , 'registerAdmin']); 
//Route::post('/order_modify' , [repo::class , 'ordermodify']);
Route::post('/addMedicine' , [repo::class , 'addMedicine']); 
