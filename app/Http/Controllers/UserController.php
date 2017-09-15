<?php

namespace App\Http\Controllers;

use Froiden\RestAPI\ApiController;
use Illuminate\Auth\Events\Registered;
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
        $user = User::forceCreate([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'confirmation_token' => str_limit(md5($request->input('email')).str_random(), 25, ''),
        ]);
        event(new Registered($user));
        return response()->json([
            'message' => 'Successfully created user!'
        ], 200);
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

    /**
     * Update user account data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);
        // User can only change their username once in 60 days
        //@ todo: Check if user hasn't changed username in the last 60 days
        // if($user->canChangeUsername()); // returns boolean
        // Validate new user email
        //@ todo: Send verification email to user before updating their email address
        // $user->verifyEmail(); // Sends email to user
        $user->fill($request->all())->save();
        return response()->json(['data'=>$user], 200);
    }

}
