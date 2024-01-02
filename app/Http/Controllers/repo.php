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
use Carbon\Carbon;

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


        foreach ($jsoncontent as $item) {
            if ($phone == $item['phone']) {
                return response()->json([
                    'message' => 'User already exists'
                ]);
            }
        }
        $token = Str::random(60);

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
            if ($phone == $item['phone']) {
                if (Hash::check($password, $item['password']))
                    return response()->json([
                        'message' => 'Login successful',
                        'token' => $item['token'],
                        'username' => $item['username'],
                    ]);
                else
                    return response()->json([
                        'message' => 'password is incorrect'
                    ], 401);
            }
        }

        return response()->json([
            'message' => 'phone number is not exist'
        ], 401);
    }
    public function addMedicine(Request $request)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Admins.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);
        $token = $request->input('token');

        foreach ($jsoncontent as $item) {
            if (isset($item['token']) && $item['token'] === $token) {

                $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';
                $jsonContent = file_get_contents($jsonFile);
                $medicinesData = json_decode($jsonContent, true);


                $category = $request->input('category');
                if (isset($medicinesData['categories'][$category])) {

                    $newMedicine = [
                        'id' => uniqid(),
                        'category' => $request->input('category'),
                        'scientific_name' => $request->input('scientific_name'),
                        'trade_name' => $request->input('trade_name'),
                        'manufacturer' => $request->input('manufacturer'),
                        'quantity_available' => $request->input('quantity_available'),
                        'expiry_date' => $request->input('expiry_date'),
                        'price' => $request->input('price'),
                    ];


                    $medicinesData['categories'][$category][] = $newMedicine;


                    file_put_contents($jsonFile, json_encode($medicinesData, JSON_PRETTY_PRINT));

                    return response()->json(['message' => 'added succecfully']);
                } else {
                    return response()->json(['error' => 'categoty is not exist']);
                }
            }
        }


        return response()->json(['error' => 'registeration is required']);
    }
    public function getAllOrders()
    {
        $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';


        $ordersData = json_decode(file_get_contents($orderFilePath), true);

        if (!empty($ordersData)) {
            $allOrders = [];

            foreach ($ordersData as $userData) {
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


    public function generateReport($endDate)
    {

        $ordersFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';
        $ordersContent = file_get_contents($ordersFilePath);
        $ordersData = json_decode($ordersContent, true);


        $totalSales = 0;
        $totalOrders = 0;


        $endDate = Carbon::parse($endDate);


        $startDate = $endDate->copy()->subMonth();

        foreach ($ordersData as $token => $userOrders) {
            foreach ($userOrders['orders'] as $order) {

                $orderDate = Carbon::parse($order['date']);


                if ($orderDate->between($startDate, $endDate)) {

                    $totalSales += $order['total_price'];


                    $totalOrders++;
                }
            }
        }

        $report = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
        ];

        return response()->json($report);
    }

    public function Paidstate(Request $request)
    {
        $orderNumber = $request->input('ordernumber');
        $state = $request->input('state');
        $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';

        $ordersData = json_decode(file_get_contents($orderFilePath), true);


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

                            file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));
                            return response()->json(['message' => 'updated succecfully']);
                        }
                    }
                }
            }
            return response()->json(['error' => 'registeration is required']);
        }


        return response()->json(['error' => 'order number is not correct']);
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


                $ordersData = json_decode(file_get_contents($orderFilePath), true);

                $medicinesData = json_decode(file_get_contents($medicinesFilePath), true);


                foreach ($ordersData as $token => &$userData) {
                    $userOrders = &$userData['orders'];
                    foreach ($userOrders as &$order) {
                        if ($order['ordernumber'] === $orderNumber) {

                            $order['status'] = $state;


                            if ($state == "received") {
                                foreach ($order['medicines'] as $medicine) {
                                    $medicineId = $medicine['id'];
                                    $quantityOrdered = $medicine['quantity_available'];
                                    foreach ($medicinesData['categories'] as $category => $medicines) {
                                        foreach ($medicines as $medicine1) {
                                            if ($medicine1['id'] === $medicineId) {
                                                $key = array_search($medicine1, $medicines);
                                                $medicinesData['categories'][$medicine['category']][$key]['quantity_available'] -= $quantityOrdered;
                                            }
                                        }
                                    }
                                }
                            }

                            file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));
                            file_put_contents($medicinesFilePath, json_encode($medicinesData, JSON_PRETTY_PRINT));

                            return response()->json(['message' => 'updated succecfully']);
                        }
                    }
                }
                return response()->json(['error' => 'order number is not correct']);
            }
        }
        return response()->json(['error' => 'registeration is required']);
    }

}