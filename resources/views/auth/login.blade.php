@extends('layouts.base')

@section('content')
    @parent
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
@endsection