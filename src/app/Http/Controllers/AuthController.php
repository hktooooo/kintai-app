<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Condition;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    // 登録画面の表示
    public function show_register()
    {
        return view('auth.register');  
    }

    // 登録時の処理
    public function store_user(RegisterRequest $request)
    {
        // ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // メール認証用メール送信
        event(new Registered($user));

        Auth::login($user);

        // メール認証通知画面にリダイレクト
        return redirect()->route('verification.notice');
    }

    // メール認証通知画面
    public function verifyNotice() {
        return view('auth.verify-email');
    }

    // メール認証リンククリック時
    public function verifyEmail(Request $request, $id, $hash) {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/mypage/profile')->with('verified', true);
    }

    // 確認メール再送信
    public function resendVerification(Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }


    // プロフィール編集画面表示
    public function mypage_edit()
    {
        $auth_user = Auth::user();

        return view('auth.mypage_edit', compact('auth_user'));
    }

    // プロフィールの更新
    public function mypage_update(ProfileRequest $request)
    {
        $userId = Auth::id(); // ログインユーザーID
        $user = User::findOrFail($userId);

        $user->name = $request->name;
        $user->zipcode = $request->zipcode;
        $user->address = $request->address;
        $user->building = $request->building;

        // 新しい画像がアップロードされた場合
        if ($request->hasFile('img_url')) {
            $file     = $request->file('img_url');
            $filename = 'user_id-' . $user->id . '.' . $file->getClientOriginalExtension(); // 例: 5.jpg
            $path     = $file->storeAs('profile_images', $filename, 'public');

            // DBに保存
            $user->img_url = $path;
        }

        $user->save();

        return redirect('/');
    }

    // プロフィール画面表示
    public function mypage(Request $request)
    {
        $page = $request->query('page', 'sell');
        $userId = Auth::id();
        $auth_user = Auth::user()->refresh();

        if ($page === 'sell') {
            // 出品した商品
            $products = Product::with('condition')
                ->where('seller_id', $userId)->get();
        } else {
            // 購入した商品
            $products = Product::with('condition')
                ->where('buyer_id', $userId)->get();
        }

        return view('auth.mypage', compact('products', 'page', 'auth_user'));
    }

    // ログイン時の処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
