<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protein POS - Login</title>
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="preloader" class="hidden">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            @foreach(Session::get('flashes', []) as $type => $value)
                <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }}">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ $value }}
                </div>
            @endforeach
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Please Sign In</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="post">
                        {{ csrf_field() }}
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Username" name="username" type="text" autofocus>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="password" type="password" value="">
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>