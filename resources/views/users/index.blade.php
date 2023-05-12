<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        div {
            border: 1px solid black;
            margin: 10px;
            padding: 10px;
        }

        p {
            margin: 0;
        }

        .user-name {
            font-weight: bold;
        }

        .user-field {
            font-style: italic;
        }

        .user-id {
            font-size: 10px;
        }
    </style>
</head>

<body>
    <h1>Kullanicilar</h1>
    @foreach ($users as $user)
        <div>
            <p class="user-id">Kullanici ID : {{ $user->id }}</p>
            <p class="user-name">Kullanici Adi : {{ $user->name }}</p>
            <p class="user-field">Kullanici Aciklamasi : {{ $user->field }}</p>
            <p class="user-clubs">Dahil Oldugu Kulupler :
                @foreach ($user->clubs as $club)
                    {{ $club->name }}
                @endforeach
            </p>
        </div>
    @endforeach

</body>

</html>
