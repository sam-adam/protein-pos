@extends('layouts.app')

@section('title')
    - Sales Report
@endsection

@section('content')
    @parent
    <form>
        <div class="row">
            <div class="col-sm-3">
                <select name="branch" class="form-control">
                    <option value>Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @if($branchId == $branch->id) selected @endif>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input class="form-control daterange" value="{{ $from->toDateString() }} - {{ $to->toDateString() }}" />
                    <input name="from" value="{{ $from->timestamp }}" type="hidden" />
                    <input name="to" value="{{ $to->timestamp }}" type="hidden" />
                </div>
            </div>
            <div class="col-sm-3">
                <button type="submit" class="btn btn-block btn-primary">Submit</button>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <div class="panel-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Cashier / User</th>
                                    <th>Client</th>
                                    <th>Products</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">After Discount Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->opened_at->toDayDateTimeString() }}</td>
                                        <td>{{ $sale->openedBy->name }}</td>
                                        <td>{{ $sale->customer->name }}</td>
                                        <td>
                                            @foreach($sale->packages as $package)
                                                <div>{{ number_format($package->quantity) }} x {{ $package->package->name }}</div>
                                            @endforeach
                                            @foreach($sale->items as $item)
                                                <div>{{ number_format($item->quantity) }} x {{ $item->product->name }}</div>
                                            @endforeach
                                        </td>
                                        <td class="text-right">{{ number_format($sale->calculateSubTotal()) }}</td>
                                        <td class="text-right">{{ number_format($sale->calculateTotal()) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>Total</strong></td>
                                    <td class="text-right">
                                        <strong>{{ number_format($sales->map(function ($sale) { return $sale->calculateTotal(); })->sum()) }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    @parent
    <script type="text/javascript">
        $('.daterange').daterangepicker({
            format: 'YYYY-MM-DD',
            startDate: '{{ $from->toDateString() }}',
            endDate: '{{ $to->toDateString() }}',
            maxDate: '{{ \Carbon\Carbon::now()->toDateString() }}',
        });
    </script>
@endsection