@if($movements->count() > 0)
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Time & Date</th>
                <th>Cashier / User</th>
                <th class="text-right">Product</th>
                <th class="text-right">Movement Quantity</th>
                <th>Container</th>
                <th class="text-right">Container Quantity</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->opened_at->toDayDateTimeString() }}</td>
                    <td>{{ $movement->openedBy->name }}</td>
                    <td>{{ $movement->customer->name }}</td>
                    <td>
                        @foreach($movement->packages as $package)
                            <div>{{ number_format($package->quantity) }} x {{ $package->package->name }}</div>
                        @endforeach
                        @foreach($movement->items as $item)
                            <div>{{ number_format($item->quantity) }} x {{ $item->product->name }}</div>
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($movement->calculateSubTotal()) }}</td>
                    <td class="text-right">{{ number_format($movement->calculateTotal()) }}</td>
                </tr>
            @endforeach
        <tr>
            <td colspan="4"></td>
            <td class="text-right"><strong>Total</strong></td>
            <td class="text-right">
                <strong>{{ number_format($movements->map(function ($movement) { return $movement->calculateTotal(); })->sum()) }}</strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
    <span class="label label-primary">No $movements found</span>
@endif