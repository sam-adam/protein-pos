<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protein POS @yield('title')</title>
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="preloader">
        <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
    </div>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>