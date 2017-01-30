@if($inventories->count() > 0)
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Priority</th>
                <th>Global Priority</th>
                <th>Stock</th>
                @can('seeCost', \App\Models\Product::class)
                    <th>Cost</th>
                @endcan
                <th>Expired At</th>
                <th>Reminder Expired At</th>
                <th>Imported Date</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inventory)
                @foreach($inventory->branchItems as $branchItem)
                    @if($branchItem->stock > 0)
                        <tr class="{{ $inventory->expired_at->lte($now) ? 'danger' : ($inventory->expiry_reminder_date && $inventory->expiry_reminder_date->lte($now) ? 'warning' : 'default') }}">
                            <td>{{ number_format($branchItem->priority) }}</td>
                            <td>{{ number_format($inventory->priority) }}</td>
                            <td>{{ number_format($branchItem->stock) }}</td>
                            @can('seeCost', \App\Models\Product::class)
                                <td>@money($inventory->cost)</td>
                            @endcan
                            <td>{{ $inventory->expired_at->toFormattedDateString() }}</td>
                            <td>{{ $inventory->expiry_reminder_date ? $inventory->expiry_reminder_date->toFormattedDateString() : '-' }}</td>
                            <td>{{ $inventory->created_at->toDayDateTimeString() }}</td>
                            <td>{{ $inventory->creator->name }}</td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>
@else
    <p>No inventory yet</p>
@endif