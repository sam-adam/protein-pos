@if($movements->count() > 0)
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Time & Date</th>
                <th>Cashier / User</th>
                <th>Product</th>
                <th class="text-right">Movement Quantity</th>
                <th>Container</th>
                <th class="text-right">Container Quantity</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->date->toDayDateTimeString() }}</td>
                    <td>{{ $movement->actor }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="text-right">{{ number_format($movement->quantity) }}</td>
                    <td>{{ $movement->container ? $movement->container->name : '-' }}</td>
                    <td class="text-right">{{ $movement->container ? number_format($movement->containerQuantity).' ('.number_format($movement->containerItemQuantity).' pcs per container)' : '-' }}</td>
                    <td>{{ $movement->remark }}</td>
                </tr>
            @endforeach
        <tr>
            <td colspan="5"></td>
            <td class="text-right">
                <strong>Total In: {{ number_format($movements->map(function ($movement) { return $movement->direction === 'add' ? $movement->quantity : 0; })->sum()) }}</strong>
            </td>
            <td class="text-right">
                <strong>Total Out: {{ number_format($movements->map(function ($movement) { return $movement->direction === 'sub' ? $movement->quantity : 0; })->sum()) }}</strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
    <span class="label label-primary">No $movements found</span>
@endif