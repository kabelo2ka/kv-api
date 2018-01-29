<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use App\User;
use Auth;


class RegisterConfirmationController extends Controller
{
    public function index()
    {
        $user = User::where('confirmation_token', request('token'))->first();
        if (! $user) {
            return redirect('/')->with(['error' => 'unknown_token']);
        }
        $user->confirmed = true;
        $user->confirmation_token = null;
        $user->save();
        return redirect('/')->with(['msg' => 'email_confirmed']);
    }

    public function resend()
    {
        $user = Auth::user();
        event(new Registered($user));
        return response()->json([
            'user' => $user,
            'message' => 'SUCCESS'
        ]);
    }
}
