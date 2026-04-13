<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function redirectTo()
    {
        return route('dashboard.index');
    }

    public function __construct()
    {
        $this->middleware('guest')->except(['logout']);
    }

    // Normal login success
    protected function authenticated(Request $request, $user)
    {
        session([
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'user_email' => $user->email,
            'otp_verified' => true,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        session()->forget(['user_id', 'user_name', 'user_email']);
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Google login
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogle()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'      => $googleUser->getName(),
                'id_google' => $googleUser->getId(),
                'password'  => bcrypt(Str::random(16)),
            ]
        );

        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp]);

        // Kirim OTP
        Mail::raw("Kode OTP Login Anda: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Kode OTP Login');
        });

        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.form');
    }

    // OTP
    public function showOtpForm()
    {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $user = User::find(session('otp_user_id'));

        if ($user && $user->otp == $request->otp) {

            Auth::login($user);

            // Regenerate session supaya auth middleware mengenali login
            $request->session()->regenerate();

            // Hapus OTP di DB
            $user->update(['otp' => null]);

            // Tandai OTP sudah diverifikasi
            session([
                'user_id'      => $user->id,
                'user_name'    => $user->name,
                'user_email'   => $user->email,
                'otp_verified' => true,
            ]);

            // Hapus session sementara
            session()->forget('otp_user_id');

            return redirect()->route('dashboard.index');
        }

        return back()->withErrors(['otp' => 'Kode OTP salah']);
    }
}
