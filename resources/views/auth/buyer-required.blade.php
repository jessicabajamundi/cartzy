@extends('layouts.auth')
@section('title', 'Buyer account required | cartzy')
@section('auth_form')
    <h2 class="auth-title">Use a buyer account to shop</h2>
    <p>Your {{ auth()->user()->role }} account cannot add items to a cart or place orders.</p>
    <p>Log out and sign in to your buyer account. If you do not have one, register a separate account with the <strong>Buyer</strong> role and a different email address.</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-primary">Log out to use a buyer account</button>
    </form>
    <p class="auth-foot"><a href="{{ route(auth()->user()->getDashboardRoute()) }}">Back to my dashboard</a></p>
@endsection
