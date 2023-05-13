@extends('layout.app')
@section('title', ' - Kullanıcılar')
@section('content')
    <center><h1>Kullanıcılar</h1></center>
    @foreach ($users as $user)
        <div>
            <p class="user-id">Kullanıcı ID : {{ $user->id }}</p>
            <p class="user-name">Kullanıcı Adi : {{ $user->name }}</p>
            <p class="user-field">Kullanıcı Aciklamasi : {{ $user->field }}</p>
            <p class="user-clubs">Dahil Oldugu Kulupler :
                @foreach ($user->clubs as $club)
                    {{ $club->name }}
                @endforeach
            </p>
        </div>
    @endforeach
@endsection