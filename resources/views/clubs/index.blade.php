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

        .club-name {
            font-weight: bold;
        }

        .club-field {
            font-style: italic;
        }

        .club-id {
            font-size: 10px;
        }
    </style>
</head>

<body>
    <h1>Kulupler</h1>
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

</body>

</html>
