@extends('layout.app')
@section('title', ' - Kullanıcılar')
@section('content')
    <div>
        <a class="btn btn-danger btn-block" onclick="openPopup('{{ url('auth/google') }}', 'Google');">
            <i class="fa fa-google"></i> Google ile Giriş Yap
        </a>
    </div>
    <script>
        function openPopup(url, provider) {
            // Popup özellikleri
            var width = 600;
            var height = 800;
            var left = (window.screen.width - width) / 2;
            var top = (window.screen.height - height) / 2;

            var features =
                'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=' + width +
                ',height=' + height + ',top=' + top + ',left=' + left;

            // Yeni pencere aç
            var popup = window.open(url, provider, features);
        }
    </script>
@endsection
