<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreLoginRequest;
use App\Interfaces\AuthRepositoryInterface;

class LoginController extends Controller
{

    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function index()
    {
        return view('pages.auth.login');
    }


       public function store(StoreLoginRequest $request) {
    $credentials = $request->validated();

    if($this->authRepository->login($credentials)) {
        // Kalau berhasil login
        if(Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Kalau bukan admin, arahkan ke home
        return redirect()->route('home');
    }

    // Kalau gagal login, balikin ke login page + error
    return redirect()->route('login')->withErrors([
        'email' => 'Email atau password salah',
    ]);
}


        public function logout()
        {
            $this->authRepository->logout();

            return redirect()->route('user.report.map');
        }

}
