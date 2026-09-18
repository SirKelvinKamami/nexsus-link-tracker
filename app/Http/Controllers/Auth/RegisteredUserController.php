<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function validateHandle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'littlelink_name' => 'required|string|max:50|unique:users|regex:/^[\p{L}0-9-_]+$/u',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['valid' => false]);
        }
    
        return response()->json(['valid' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'littlelink_name' => 'required|string|max:50|unique:users|regex:/^[\p{L}0-9-_]+$/u',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $manualVerification = filter_var(env('MANUAL_USER_VERIFICATION', false), FILTER_VALIDATE_BOOLEAN);
        $block = $manualVerification ? 'yes' : 'no';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'littlelink_name' => $request->littlelink_name,
            'password' => Hash::make($request->password),
            'role' => 'viewer',
            'block' => $block,
        ]);

        Auth::login($user);

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Exception $e) {}

        event(new Registered($user));

        return redirect(url('dashboard'));
    }
}
