<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function getTermsOfUse()
    {
        return response()->json([
            'data' => [
                'html' => view('policies.terms')->render()
            ]
        ]);
    }

    public function getPrivacyPolicy()
    {
        return response()->json([
            'data' => [
                'html' => view('policies.privacy')->render()
            ]
        ]);
    }
}
