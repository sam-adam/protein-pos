@if($sales->count() > 0)
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
                <td class="text-right">{{ number_format($sale->payments->first()->calculateTotal()) }}</td>
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
@else
    <span class="label label-primary">No sales found</span>
@endif