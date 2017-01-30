@extends('layouts.app')

@section('title')
    - Clock In
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-4">
            @include('widgets.sales.simple')
        </div>
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form method="post" action="{{ route('shifts.out', [$shift->id, 'redirect-to' => $redirectTo]) }}" class="form-horizontal" onsubmit="return confirm('Ending shift! Are you sure?');">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Opened at</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">
                                    {{ $shift->opened_at->toDayDateTimeString().' ('.$shift->opened_at->diffForHumans().')' }}
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Opened cash</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">
                                    @money($shift->opened_cash_balance)
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-5">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fa fa-check"></i>
                                    Clock out
                                </button>
                            </div>
                            <div class="col-sm-5">
                                <a href="{{ URL::previous() }}" class="btn btn-default btn-block">
                                    <i class="fa fa-arrow-left"></i>
                                    Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection