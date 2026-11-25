<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * method view login page
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * method login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:5',
        ]);

        $attempt = Auth::attempt([
            'email'    => Str::of($request->email)->trim(),
            'password' => $request->password,
        ], $request->has('remember'));

        if ($attempt) {
            return redirect()->intended('home');
        } else {
            $res = [
                'type' => 'error',
                'msg'  => 'Invalid email or password.',
            ];

            return redirect()->route('login')->withInput()->with('response', $res);
        }

    }

    /**
     * method logout
     */
    public function logout()
    {
        Auth::logout();

        $res = [
            'type' => 'success',
            'msg'  => 'Signed out.',
        ];

        return redirect()->route('login')->with('response', $res);
    }
}
