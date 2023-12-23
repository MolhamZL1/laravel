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
// Route pharmacies
Route::post('/register', [Pharmacy::class, 'register']);//username,phonenumber,password
Route::post('/login', [Pharmacy::class, 'login']);//phonenumber,password
Route::get('/categories' , [Pharmacy::class ,'getCategories']);
Route::get('/medicines/{category}' , [Pharmacy::class ,'getMedicinesByCategory']);
Route::get('/searchByCategory/{category}' , [Pharmacy::class , 'searchByCategory']); 
Route::get('/searchMedicine/{searchTerm}' , [Pharmacy::class , 'searchMedicine']); 
Route::post('/addToCart' , [Pharmacy::class , 'addToCart']);//id,quantity,token
Route::get('/cart/{token}' , [Pharmacy::class , 'getCart']);//quary token
Route::post('/addorder' , [Pharmacy::class , 'order']);//token,username
Route::get('/orders/{token}' , [Pharmacy::class , 'getOrders']);//quary token

// Route repos Owner
Route::post('/loginAdmin' , [repo::class , 'loginAdmin']);
Route::post('/registerAdmin' , [repo::class , 'registerAdmin']); 
Route::post('/addMedicine' , [repo::class , 'addMedicine']); //token and medecine
Route::get('/allOrders' , [repo::class , 'getAllOrders']); 
Route::post('/paidState' , [repo::class , 'Paidstate']); //token,ordernumber,state : paid or not pait
Route::post('/state' , [repo::class , 'state']); //token,ordernumber,state : sent or delivered