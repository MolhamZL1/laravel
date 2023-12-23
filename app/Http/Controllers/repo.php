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
class repo extends Controller
{
    public function registerAdmin(Request $request)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
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
    
    public function loginAdmin(Request $request)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
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
public function addMedicine(Request $request)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
    $filecontent = file_get_contents($filepath);
    $jsoncontent = json_decode($filecontent, true);
    $token = $request->input('token');

    foreach ($jsoncontent as $item) {
        if (isset($item['token']) && $item['token'] === $token) {
            // تحويل ملف JSON إلى مصفوفة PHP
            $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';
            $jsonContent = file_get_contents($jsonFile);
            $medicinesData = json_decode($jsonContent, true);

            // تحقق من وجود التصنيف
            $category = $request->input('category');
            if (isset($medicinesData['categories'][$category])) {
                // إضافة دواء جديد
                $newMedicine = [
                    'id' => uniqid(),
                    'category'=> $request->input('category'),
                    'scientific_name' => $request->input('scientific_name'),
                    'trade_name' => $request->input('trade_name'),
                    'manufacturer' => $request->input('manufacturer'),
                    'quantity_available' => $request->input('quantity_available'),
                    'expiry_date' => $request->input('expiry_date'),
                    'price' => $request->input('price'),
                ];

                // إضافة الدواء إلى التصنيف
                $medicinesData['categories'][$category][] = $newMedicine;

                // حفظ التغييرات إلى الملف JSON
                file_put_contents($jsonFile, json_encode($medicinesData, JSON_PRETTY_PRINT));

                return response()->json(['message' => 'تمت إضافة الدواء بنجاح']);
            } else {
                return response()->json(['error' => 'التصنيف غير موجود']);
            }
        }
    }

    // إذا لم يتم العثور على المستخدم
    return response()->json(['error' => 'التسجيل مطلوب']);
}
public function getAllOrders()
{
    $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';

    // Load existing orders data
    $ordersData = json_decode(file_get_contents($orderFilePath), true);

    if (!empty($ordersData)) {
        $allOrders = [];

        foreach ($ordersData as  $userData) {
            $userOrders = $userData['orders'];
            foreach ($userOrders as $order) {
                $allOrders[] = $order;
            }
        }

        return response()->json(['orders' => $allOrders]);
    } else {
        return response()->json(['message' => 'No orders found']);
    }
}
public function Paidstate(Request $request)
{
    $orderNumber = $request->input('ordernumber');
    $state = $request->input('state');
    $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';
    // Load existing orders data
    $ordersData = json_decode(file_get_contents($orderFilePath), true);

    // تحديث حالة الطلب إلى "Paid" إذا كان الرقم صحيحًا
    foreach ($ordersData as $token => &$userData) {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
    $filecontent = file_get_contents($filepath);
    $jsoncontent = json_decode($filecontent, true);
    $token = $request->input('token');

    foreach ($jsoncontent as $item) {
        if (isset($item['token']) && $item['token'] === $token) {
        $userOrders = &$userData['orders'];
        foreach ($userOrders as &$order) {
            if ($order['ordernumber'] === $orderNumber) {
                $order['paid'] = $state;
                // حفظ التغييرات إلى ملف JSON
                file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));
                return response()->json(['message' => 'تم تحديث حالة الدفع بنجاح']);
            }
        }}}
        return response()->json(['error' => 'التسجيل مطلوب']);
    }

    // إذا لم يتم العثور على الرقم
    return response()->json(['error' => 'رقم الطلب غير صحيح']);
}
public function state(Request $request)
{
    $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
    $filecontent = file_get_contents($filepath);
    $jsoncontent = json_decode($filecontent, true);
    $token = $request->input('token');

    foreach ($jsoncontent as $item) {
        if (isset($item['token']) && $item['token'] === $token) {
    $orderNumber = $request->input('ordernumber');
    $state = $request->input('state');
    $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';
    $medicinesFilePath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';

    // Load existing orders data
    $ordersData = json_decode(file_get_contents($orderFilePath), true);
    // Load existing medicines data
    $medicinesData = json_decode(file_get_contents($medicinesFilePath), true);

    // تحديث حالة الطلب إلى "delivered" إذا كان الرقم صحيحًا
    foreach ($ordersData as $token => &$userData) {
        $userOrders = &$userData['orders'];
        foreach ($userOrders as &$order) {
            if ($order['ordernumber'] === $orderNumber) {
                // تحديث حالة الطلب
                $order['status'] = $state;

                // تحديث الكمية المتاحة للأدوية في المستودع
                if($state=="delivered"){
                foreach ($order['medicines'] as $medicine) {
                    $medicineId = $medicine['id'];
                    $quantityOrdered = $medicine['quantity_available'];
                    foreach ($medicinesData['categories'] as $category => $medicines) {
                        foreach ($medicines as  $medicine1) {
                            if ($medicine1['id'] === $medicineId) {
                              $key=  array_search($medicine1,$medicines);
                    $medicinesData['categories'][$medicine['category']][$key]['quantity_available'] -= $quantityOrdered;}}}
                }
            }
                // حفظ التغييرات إلى ملف JSON لكل من الطلبات والأدوية
                file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));
                file_put_contents($medicinesFilePath, json_encode($medicinesData, JSON_PRETTY_PRINT));

                return response()->json(['message' => 'تم تحديث حالة الطلب والمستودع بنجاح']);
            }
        }
    }
    return response()->json(['error' => 'رقم الطلب غير صحيح']);}}
    return response()->json(['error' => 'التسجيل مطلوب']);
}

}