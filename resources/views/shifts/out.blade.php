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
                    <form method="post" action="{{ route('shifts.out', [$shift->id, 'redirect-to' => $redirectTo]) }}" class="form-horizontal">
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
                                    {{ number_format($shift->opened_cash_balance) }}
                                </p>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('closing_balance') ? 'has-error' : '' }}">
                            <label for="closing-balance" class="col-sm-2 control-label">Closing cash</label>
                            <div class="col-sm-5">
                                <input type="text" id="closing-balance" name="closing_balance" class="form-control" placeholder="Eg: 100" value="{{ old('closing_balance') }}" required />
                                @foreach($errors->get('closing_balance') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-5">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fa fa-check"></i>
                                    Clock out
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection