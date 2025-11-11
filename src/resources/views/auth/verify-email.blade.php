@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css')}}">
@endsection

@section('content')
<div class="verify-email__content">
  <div class="verify-email__inner">
    <p class="verify-email__message">
      登録していただいたメールアドレスに認証メールを送付しました。</br>
      メール認証を完了してください。
    </p>

    <a href="http://localhost:8025/" class="verify-email__btn">認証はこちらから</a>

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="verify-email__link-send">認証メールを再送する</button>
    </form>
    @if (session('status') == 'verification-link-sent')
      <p class="verify-email__success-message">新しい確認リンクを送信しました</p>
    @endif

  </div>
</div>
@endsection('content')