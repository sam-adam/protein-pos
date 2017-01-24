@extends('layouts.app')

@section('title')
    - Clock In
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @if($canClockIn)
                        <form method="post" action="{{ route('shifts.in') }}" class="form-horizontal">
                            {{ csrf_field() }}
                            <div class="form-group {{ $errors->has('opening_balance') ? 'has-error' : '' }}">
                                <label for="opening-balance" class="col-sm-2 control-label">Opening cash</label>
                                <div class="col-sm-5">
                                    <input type="text" id="opening-balance" name="opening_balance" class="form-control" placeholder="Eg: 100" value="{{ old('opening_balance') }}" />
                                    @foreach($errors->get('opening_balance') as $error)
                                        <span class="label label-danger">{{ $error }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-5">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fa fa-check"></i>
                                        Clock in
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <strong>Unable to clock in!</strong>
                            Your previous shift is suspended, please contact your admin
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection