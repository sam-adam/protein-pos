@extends('layouts.app')

@section('title')
    - Dashboard
@endsection

@section('content')
    @parent
    @includeIf('dashboard.components.'.Auth::user()->role)
@endsection