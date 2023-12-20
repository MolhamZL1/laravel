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
             return response()->json();
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
    public function addToCart(Request $request)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Users.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);
        $token = $request->input('token');

        foreach ($jsoncontent as $item) {
            if (isset($item['token']) && $item['token'] === $token) {

    $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';
    $Content = file_get_contents($jsonFile);
    $medicinesData = json_decode($Content, true);

    $medicineId = $request->input('id');
    $quantity = $request->input('quantity');

    foreach ($medicinesData['categories'] as $category => $medicines) {
        foreach ($medicines as  $medicine) {
            if ($medicine['id'] === $medicineId) {
                // العثور على الدواء بناءً على الـ ID

                // التحقق من توفر كمية كافية
                if ($quantity > $medicine['quantity_available']) {
                    return response()->json(['error' => 'الكمية المطلوبة غير متاحة']);
                }

                // إضافة الدواء إلى سلة المستخدم
                $cartItem = [
                    'id' => $medicine['id'],
                    'category' => $category,
                    'scientific_name' => $medicine['scientific_name'],
                    'trade_name' => $medicine['trade_name'],
                    'quantity' => $quantity,
                    "manufacturer"=> $medicine['manufacturer'],
                    'price' => $medicine['price'],
                    "expiry_date"=> $medicine["expiry_date"],
                ];
                $cartpath = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
        $cartcontent = file_get_contents($cartpath);
        $cart = json_decode($cartcontent, true);
                // تحقق من وجود سلة للمستخدم، وإنشاءها إذا لم تكن موجودة
                if (!isset($cart['carts'][$token])) {
                    $cart['carts'][$token] = [];
                }

                // إضافة الدواء إلى سلة المستخدم
                $cart['carts'][$token][] = $cartItem;

                // حفظ التغييرات إلى ملف JSON
                file_put_contents($cartpath, json_encode($cart, JSON_PRETTY_PRINT));
                
                return response()->json(['message' => 'تمت إضافة الدواء إلى السلة بنجاح']);
            }
        }
    }

    // إذا لم يتم العثور على الدواء بناءً على الـ ID
    return response()->json(['error' => 'الدواء غير موجود']);
}}
return response()->json(['error' => 'التسجيل مطلوب']);
}
public function getCart(Request $token)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Cart.json'; 
    $filecontent = file_get_contents($filepath);
    $jsoncontent = json_decode($filecontent, true);
    $medicines = $jsoncontent['carts']["Ic9asVS7Y47iNVUn6ERh9RIQJZHXKWDx7zcH1jIUHbeLcL6lY2L320CI66tF"] ?? [];
    return response()->json($medicines);
}
public function order(Request $request)
{
    $jsonFilePath = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
    $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';

    $cartData = json_decode(file_get_contents($jsonFilePath), true);

    $token = $request->input('token');
    $username = $request->input('username');

    if (isset($cartData['carts'][$token])) {
        $medicines = $cartData['carts'][$token];
        $totalQuantity = 0;
        $totalPrice = 0;

        foreach ($medicines as $medicine) {
            $totalQuantity += $medicine['quantity'];
            $totalPrice += $medicine['price'];
        }

        $orderItem = [
            'ordernumber' => uniqid(),
            'username' => $username,
            'status' => 'pending',
            'total_price' => $totalPrice,
            'total_quantity' => $totalQuantity,
            'paid' => false,
            'medicines' => $medicines,
        ];

        $ordersData = json_decode(file_get_contents($orderFilePath), true);
        $ordersData['orders'][$token][] = $orderItem;

        file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));

        return response()->json(['message' => 'تمت إضافة الطلب بنجاح']);
    } else {
        return response()->json(['error' => 'التسجيل مطلوب']);
    }
}
public function getOrders(Request $token)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Orders.json'; 
    $filecontent = file_get_contents($filepath);
    $jsoncontent = json_decode($filecontent, true);
    $orders = $jsoncontent[$token]['orders'] ?? [];
    return response()->json( $orders);
}
}
