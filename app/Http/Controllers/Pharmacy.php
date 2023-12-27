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
        if (strlen($phone) > 10 || strlen($phone) < 10) {
            return response()->json([
                'message' => 'phone number must be 10 numbers'
            ], 400);
        }
        if (strlen($password) < 8) {
            return response()->json([
                'message' => 'password must be 8 char at least'
            ], 400);
        }

        // Check if the user already exists
        foreach ($jsoncontent as $item) {
            if ($phone == $item['phone']) {
                return response()->json([
                    'message' => 'User already exists'
                ], 400);
            }
        }
        $token = Str::random(60);
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
            'username' => $username,
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
        return response()->json($medicines);
    }
    public function searchByCategory($category)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Medicines.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);

        if (isset($jsoncontent['categories'][$category])) {
            return response()->json([$category]);
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
        if (!empty($results))
            return response()->json($results);
        else
            return response()->json();

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
                    foreach ($medicines as $medicine) {
                        if ($medicine['id'] === $medicineId) {

                            if ($quantity > $medicine['quantity_available']) {
                                return response()->json(['error' => 'Not available'], 404);
                            }
                            $cartItem = [
                                'id' => $medicine['id'],
                                'category' => $category,
                                'scientific_name' => $medicine['scientific_name'],
                                'trade_name' => $medicine['trade_name'],
                                'quantity_available' => $quantity,
                                "manufacturer" => $medicine['manufacturer'],
                                'price' => $medicine['price'],
                                "expiry_date" => $medicine["expiry_date"],
                            ];
                            $cartpath = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
                            $cartcontent = file_get_contents($cartpath);
                            $cart = json_decode($cartcontent, true);

                            if (!isset($cart['carts'][$token])) {
                                $cart['carts'][$token] = [];
                            }


                            $cart['carts'][$token][] = $cartItem;


                            file_put_contents($cartpath, json_encode($cart, JSON_PRETTY_PRINT));

                            return response()->json(['message' => 'added succecfully']);
                        }
                    }
                }
                return response()->json(['error' => 'not found'], 404);
            }
        }
        return response()->json(['error' => 'registeration is required'], 404);
    }
    public function removeFromCart(Request $request)
    {
        $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
        $content = file_get_contents($jsonFile);
        $cartData = json_decode($content, true);

        $token = $request->input('token');
        $medicineId = $request->input('id');

        if (isset($cartData['carts'][$token])) {
            $medicines = &$cartData['carts'][$token];

            foreach ($medicines as $index => $medicine) {
                if ($medicine['id'] == $medicineId) {
                    unset($medicines[$index]);
                    $medicines = array_values($medicines);
                    file_put_contents($jsonFile, json_encode($cartData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    return response()->json(['message' => 'deleted']);
                }
            }

            return response()->json(['error' => 'not found'], 404);
        }

        return response()->json(['error' => 'registeration is required'], 404);
    }


    public function getCart($token)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);
        $medicines = $jsoncontent['carts'][$token] ?? [];
        return response()->json($medicines);
    }
    public function order(Request $request)
    {
        $totalQuantity = 0;
        $totalPrice = 0;
        $jsonFilePath = 'C:\xampp\htdocs\laravel\jsons\Cart.json';
        $orderFilePath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';

        $cartData = json_decode(file_get_contents($jsonFilePath), true);

        $token = $request->input('token');
        $username = $request->input('username');

        if (isset($cartData['carts'][$token])) {
            $medicines = $cartData['carts'][$token];

            foreach ($medicines as $medicine) {
                $totalQuantity += $medicine['quantity_available'];
                $totalPrice += $medicine['price'];
            }

            $orderItem = [
                'ordernumber' => uniqid(),
                'username' => $username,
                'status' => 'preparing',
                'total_price' => $totalPrice,
                'total_quantity' => $totalQuantity,
                'paid' => "Not Paid",
                'medicines' => $medicines,
            ];


            $ordersData = json_decode(file_get_contents($orderFilePath), true);


            if (!isset($ordersData[$token])) {
                $ordersData[$token] = ['orders' => []];
            }

            $ordersData[$token]['orders'][] = $orderItem;


            file_put_contents($orderFilePath, json_encode($ordersData, JSON_PRETTY_PRINT));

            unset($cartData['carts'][$token]);


            file_put_contents($jsonFilePath, json_encode($cartData, JSON_PRETTY_PRINT));

            return response()->json(['message' => "added succecfully"]);
        } else {
            return response()->json(['error' => "registeration is required"], 404);
        }
    }


    public function getOrders($token)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Orders.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);
        $orders = $jsoncontent[$token]['orders'] ?? [];
        return response()->json($orders);
    }
    public function addFavorite(Request $request)
    {
        $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Favorites.json';
        $content = file_get_contents($jsonFile);
        $favoritesData = json_decode($content, true);

        $token = $request->input('token');
        $medicineId = $request->input('id');


        if (!isset($favoritesData[$token])) {
            $favoritesData[$token] = [];
        }


        if (!in_array($medicineId, $favoritesData[$token])) {

            $favoritesData[$token][] = $medicineId;

            $message = "added succecfully";
        } else {

            $favoritesData[$token] = array_diff($favoritesData[$token], [$medicineId]);

            $message = "deleted succecfully";
        }

        // حفظ التغييرات في ملف JSON
        file_put_contents($jsonFile, json_encode($favoritesData, JSON_PRETTY_PRINT));

        return response()->json(['message' => $message]);
    }
    public function getAllFavorites($token)
    {
        $jsonFile = 'C:\xampp\htdocs\laravel\jsons\Favorites.json';
        $content = file_get_contents($jsonFile);
        $favoritesData = json_decode($content, true);




        if (isset($favoritesData[$token])) {
            $favoriteIds = $favoritesData[$token];


            $medicinesData = json_decode(file_get_contents('C:\xampp\htdocs\laravel\jsons\Medicines.json'), true);

            $favoriteMedicines = [];


            foreach ($favoriteIds as $medicineId) {
                foreach ($medicinesData['categories'] as $category => $medicines) {
                    foreach ($medicines as $medicine) {
                        if ($medicine['id'] == $medicineId) {
                            $favoriteMedicines[] = $medicine;
                        }
                    }
                }
            }

            return response()->json($favoriteMedicines);
        } else {
            return response()->json([]);
        }
    }
    public function getfavorite($token)
    {
        $filepath = 'C:\xampp\htdocs\laravel\jsons\Favorites.json';
        $filecontent = file_get_contents($filepath);
        $jsoncontent = json_decode($filecontent, true);
        $ids = $jsoncontent[$token] ?? [];
        return response()->json($ids);
    }
}
