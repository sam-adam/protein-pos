@extends('layouts.app')

@section('title')
    - Dashboard
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">Incomplete Deliveries</div>
                <div class="panel-body">
                    @if($incompleteDeliveries->count() > 0)
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incompleteDeliveries as $incompleteDelivery)
                                    <tr>
                                        <td>{{ $incompleteDelivery->opened_at->toDayDateTimeString() }}</td>
                                        <td>{{ $incompleteDelivery->customer->name }}</td>
                                        <td>
                                            <form method="post" action="{{ route('sales.complete', $incompleteDelivery->id) }}">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-success btn-xs">
                                                    <i class="fa fa-check"></i>
                                                    Completed
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <span class="label label-primary">No incomplete deliveries</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection