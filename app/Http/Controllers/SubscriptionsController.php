<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'min:6', 'max:255', 'email', 'unique:subscribers']
        ]);

        Subscriber::create(['email' => $request->input('email')]);

        return redirect()->back()->with('subscribed');
    }
}
