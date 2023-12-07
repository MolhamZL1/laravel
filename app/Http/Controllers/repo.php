<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
