<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    // 会員登録画面表示
    public function create()
    {
        return view('auth.register');
    }

    // 会員登録処理
    public function store(UserRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        // イベント
        event(new Registered($user));

        // ログイン状態にする
        Auth::login($user);

        // 勤怠登録画面へリダイレクト
        return redirect()->route('attendance.index');
    }
}