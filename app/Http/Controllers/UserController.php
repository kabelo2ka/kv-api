<?php

namespace App\Http\Controllers;

use Froiden\RestAPI\ApiController;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use Auth;
use Tymon\JWTAuth\Exceptions\JWTException;


class UserController extends Controller
{
    //protected $model = User::class;

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|min:3|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        $user = new User([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function signIn(Request $request)
    {
        $this->validate($request, [
            //'username' => 'required_without:email',
            //'email' => 'required_without:username|email',
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // grab credentials from the request
        $credentials = $request->only('email', 'password');
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid Credentials!'
                ], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'error' => 'Could not create token!'
            ], 500);
        }
        // all good so return the token
        return response()->json([
            'user' => Auth::user(),
            'token' => $token
        ],200);
    }

    public function checkToken()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }


}
