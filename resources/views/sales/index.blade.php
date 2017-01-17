@extends('layouts.app')

@section('title')
    - Sales List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Sales List - Record {{ $sales->firstItem() }} to {{ $sales->lastItem() }} from {{ $sales->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Remark</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ $sale->opened_at->toDayDateTimeString() }}</td>
                                    <td>{{ $sale->customer->name }}</td>
                                    <td>{{ $sale->getType() }}</td>
                                    <td>{{ $sale->remark }}</td>
                                    <td>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-search-plus"></i>
                                            View
                                        </a>
                                        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-default btn-sm">
                                            <i class="fa fa-print"></i>
                                            Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ url('sales/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new sale
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $sales->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection