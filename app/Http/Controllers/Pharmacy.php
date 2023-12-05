<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Pharmacy extends Controller
{
     
     public function register(Request $request)
     {
         $filepath = 'C:\xampp\htdocs\laravel\jsons\Users.json';
         $filecontent = file_get_contents($filepath);
         $jsoncontent = json_decode($filecontent, true);
     
         $username = $request->input('username');
         $phone = $request->input('phone');
         $password = $request->input('password');
         $hashedPassword = Hash::make($password);
     
         if (!$username || !$phone || !$password) {
             return response()->json([
                 'message' => 'All fields are required'
             ], 400);
         }
     
         // Check if the user already exists
         foreach ($jsoncontent as $item) {
             if ($phone == $item['phone']) {
                 return response()->json([
                     'message' => 'User already exists'
                 ]);
             }
         }
          $token=Str::random(60);
         // Create user information
         $info = [
             'username' => $username,
             'phone' => $phone,
             'password' => $hashedPassword,
             'token' => $token,
         ];
     
         $jsoncontent[] = $info;
         file_put_contents($filepath, json_encode($jsoncontent));
     
         return response()->json([
             'message' => 'Registration successful',
             'token' => $token,
         ]);
     }
     
     public function login(Request $request)
 {
     $filepath = 'C:\xampp\htdocs\laravel\jsons\Users.json';
     $filecontent = file_get_contents($filepath);
     $jsoncontent = json_decode($filecontent, true);
     $phone = $request->input('phone');
     $password = $request->input('password');
 
     foreach ($jsoncontent as $item) {
         if ($phone == $item['phone'] ) {
            if (Hash::check($password, $item['password'])) 
             return response()->json([
                 'message' => 'Login successful',
                 'token' => $item['token'],
             ]);
            else 
            return response()->json([
             'message' => 'password is incorrect'
         ], 401);
         }
         else 
         return response()->json([
             'message' => 'phone number is incorrect'
         ], 401);    
         
     }
 }
 public function getCategories()
     {
         $filepath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json'; 
         $filecontent = file_get_contents($filepath);
         $jsoncontent = json_decode($filecontent, true);
         $categories = array_keys($jsoncontent['categories'] ?? []);
         return response()->json($categories);
     }
     public function getMedicinesByCategory($category)
     {
         $filepath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json'; 
         $filecontent = file_get_contents($filepath);
         $jsoncontent = json_decode($filecontent, true);
         $medicines = $jsoncontent['categories'][$category] ?? [];
         return response()->json( $medicines);
     }
     public function searchByCategory($category)
     {
         $filepath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';
         $filecontent = file_get_contents($filepath);
         $jsoncontent = json_decode($filecontent, true);
         
         if (isset($jsoncontent['categories'][$category])) {
             return response()->json( [$category]);
         } else {
             return response()->json(['message' => 'Not found'], 404);
         }
     }
     public function searchMedicine($query)
     {
         $filepath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json'; 
         $filecontent = file_get_contents($filepath);
         $jsoncontent = json_decode($filecontent, true);
 
 
        
         $results = $this->searchInMedicines($jsoncontent, $query);
 
         return response()->json($results);
     }
 
     private function searchInMedicines($medicines, $query)
     {
         $results = [];
 
         foreach ($medicines['categories'] as $category) {
             foreach ($category as $medicine) {
               
                 if (stripos($medicine['scientific_name'], $query) !== false || stripos($medicine['trade_name'], $query) !== false) {
                     $results[] = $medicine;
                 }
             }
         }
 
         return $results;
     }
}
