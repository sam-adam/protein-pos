@if($sales->count() > 0)
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Date</th>
            <th>Receipt SN</th>
            <th>Cashier / User</th>
            <th>Payment</th>
            <th>Client</th>
            <th class="text-right">Price</th>
            <th class="text-right">Discount</th>
            <th class="text-right">After Discount Price</th>
            <th class="text-right">Paid Amount</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->opened_at->toDayDateTimeString() }}</td>
                <td>{{ $sale->getCode() }}</td>
                <td>{{ $sale->openedBy->name }}</td>
                <td>{{ $sale->payments->first()->payment_method }}</td>
                <td>{{ $sale->customer->name }}</td>
                <td class="text-right">{{ number_format($sale->calculateSubTotal(), 1) }}</td>
                <td class="text-right">
                    @if($sale->sales_discount)
                        {{ number_format($sale->sales_discount, 1).($sale->sales_discount_type === 'PERCENTAGE' ? '%' : ' AED') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ number_format($sale->calculateTotal(), 1) }}</td>
                <td class="text-right">{{ number_format($sale->payments->first()->calculateTotal(), 1) }}</td>
                <td class="text-right">
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fa fa-search-plus"></i>
                        View
                    </a>
                    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-default btn-sm" target="_blank">
                        <i class="fa fa-print"></i>
                        Print
                    </a>
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="8"></td>
            <td class="text-right"><strong>Total</strong></td>
            <td class="text-right">
                <strong>{{ number_format($sales->map(function ($sale) { return $sale->calculateTotal(); })->sum()) }}</strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
    <span class="label label-primary">No sales found</span>
@endif