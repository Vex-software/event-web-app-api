@extends('layout.app')
@section('title', ' - Kullanıcılar')
@section('content')
    <div>
        <a href="{{ url('auth/google') }}" class="btn btn-danger btn-block">
            <i class="fa fa-google"></i> Google ile Giriş Yap
        </a>
    </div>
    <div>
        <a href="{{ url('auth/github') }}">
            <i class="fa fa-github"></i> Github ile Giriş Yap
        </a>
    </div>
@endsection
