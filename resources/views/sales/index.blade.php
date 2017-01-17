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
                        @include('sales.table', ['sales' => $sales])
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ url('sales/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                New sale
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