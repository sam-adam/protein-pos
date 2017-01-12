@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <br/>
        </div>
    </div>
    @foreach(Session::get('flashes', []) as $type => $value)
        <div class="hidden-print alert alert-{{ $type === 'error' ? 'danger' : $type }}">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ $value }}
        </div>
    @endforeach
@endsection