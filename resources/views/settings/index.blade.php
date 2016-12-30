@extends('layouts.app')

@section('title')
    - Settings
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal" method="post" action="{{ route('settings.update') }}">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('credit_card_tax') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-2" for="credit-card-tax">Credit Card Tax (%)</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="credit_card_tax" value="{{ old('credit_card_tax') ?: $creditCardTax }}" placeholder="In percent" />
                                @foreach($errors->get('credit_card_tax') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <button type="submit" class="btn btn-success btn-lg btn-block">
                                    <i class="fa fa-disk"></i>
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection