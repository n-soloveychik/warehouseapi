<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device' => 'required|max:200|min:3'
        ]);

        $request->device = $request->device . '|' . $request->ip();

        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->get('password'), $user->password)) {
            return response([
                'message' => ['Неверный Email или пароль']
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken($request->get('device'))->plainTextToken;

        $response = [
            //'user' => $user,
            'token' => $token
        ];

        return response($response, Response::HTTP_CREATED);
    }

    public function check(){
        return response(['ok'], Response::HTTP_ACCEPTED);
    }
}
