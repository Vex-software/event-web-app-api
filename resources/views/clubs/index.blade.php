@extends('layout.app')
@section('title', ' - Kulupler')
@section('content')
    <center><h1>Kulupler</h1></center>
    @foreach ($clubs as $club)
        <div>
            <p class="club-id">Kulup ID : {{ $club->id }}</p>
            <p class="club-name">Kulup Adi : {{ $club->name }}</p>
            <p class="club-field">Kulup Aciklamasi : {{ $club->field }}</p>
            <p class="club-users">Kulup Uyeleri :
                @foreach ($club->users as $user)
                    {{ $user->name }}
                @endforeach
            </p>
        </div>
    @endforeach
@endsection