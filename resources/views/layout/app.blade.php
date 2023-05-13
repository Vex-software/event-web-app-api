<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content=" {{ csrf_token() }} ">
    <meta name="csrf-param" content="_token" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv=”Content-Security-Policy” content=”upgrade-insecure-requests”>
    <meta name="description"
        content="Kulüplerin ve kullanıcıların buluştuğu sanal mekan: VEX Software! Gelişmeleri takip edin, paylaşımlarda bulunun ve yeni etkinlikler keşfedin!">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="stylesheet" href="{{ asset('style/app.css') }}">
    <title>{{ env('APP_NAME') }} @yield('title')</title>
</head>

<body>
    <hr />
    <h2><a href="{{ url('/clubs') }}">Kulüpler</a></h2>
    <h2><a href="{{ url('/users') }}">Kullanıcılar</a></h2>
    <h2><a href="{{ url('/login') }}">Login</a></h2>
    <hr />
    @yield('content')
</body>

</html>
