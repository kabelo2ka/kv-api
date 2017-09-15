<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

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
}
