@extends('layouts.app')

@section('title')
    - View Sale
@endsection

@section('styles')
    <style type="text/css">
        .form-group {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Sale # {{ $sale->id }}
                    @if($sale->is_delivery)
                        &nbsp;
                        <span class="label label-primary">
                            <i class="fa fa-info"></i>
                            Delivery
                        </span>
                    @endif
                    &nbsp;
                    @if($sale->isCancelled())
                        <span class="label label-danger">
                            <i class="fa fa-times"></i>
                            Cancelled
                        </span>
                    @elseif($sale->isPaid())
                        <span class="label label-success">
                            <i class="fa fa-check"></i>
                            Finished
                        </span>
                    @elseif($sale->is_delivery && $sale->isFinished())
                        <span class="label label-danger">
                            <i class="fa fa-exclamation-circle"></i>
                            Delivery Pending
                        </span>
                    @endif
                </div>
                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label">Date</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->opened_at->toDayDateTimeString() }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="col-sm-4 control-label">Opened By</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->openedBy->name }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Closed By</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->isFinished() ? $sale->closedBy->name : '-' }}</p>
                        </div>
                    </div>
                    @if($sale->isCancelled())
                        <div class="form-group">
                            <label for="email" class="col-sm-4 control-label">Cancelled At</label>
                            <div class="col-sm-8">
                                <p class="form-control-static">{{ $sale->cancelled_at->toDayDateTimeString() }}</p>
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <label for="email" class="col-sm-4 control-label">Closed At</label>
                            <div class="col-sm-8">
                                <p class="form-control-static">{{ $sale->isFinished() ? $sale->closed_at->toDayDateTimeString() : '-' }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="address" class="col-sm-4 control-label">Branch</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->branch->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Customer Data
                </div>
                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label">Name</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->customer->name }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="col-sm-4 control-label">Group</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->customer->group ? $sale->customer->group->name : '-' }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Discount</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ $sale->customer_discount }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @if(!$sale->isCancelled())
                <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary btn-lg btn-block">
                    <i class="fa fa-print fa-fw"></i>
                    Print
                </a>
            @endif
            <a href="{{ Session::get('last_sale_page') ?: route('sales.index') }}" class="btn btn-default btn-lg btn-block">
                <i class="fa fa-arrow-left fa-fw"></i>
                Back
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Items</div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="text-left">Name</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Discount</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->packages as $package)
                                <tr>
                                    <td></td>
                                </tr>
                            @endforeach
                            @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->discount) }}%</td>
                                    <td class="text-right">{{ number_format($item->price) }}</td>
                                    <td class="text-right">{{ number_format($item->calculateSubtotal()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection