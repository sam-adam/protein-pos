@if($movements->count() > 0)
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th></th>
            <th>Date</th>
            <th class="text-right">Movement In</th>
            <th class="text-right">Movement Out</th>
        </tr>
        </thead>
        <tbody>
        @foreach($movements->groupBy(function ($movement) { return $movement->date->toFormattedDateString(); }) as $date => $groupedMovement)
            <tr>
                <td width="50px">
                    <a class="btn btn-primary btn-xs" href="{{ route('reports.stock', ['branch' => $branchId, 'product' => $productId, 'from' => $from->timestamp, 'to' => $to->timestamp, 'mode' => 'daily']) }}">
                        <i class="fa fa-search-plus"></i>
                        See detail
                    </a>
                </td>
                <td>{{ $date }}</td>
                <td class="text-right">
                    {{ number_format($groupedMovement->map(function ($movement) { return $movement->direction === 'add' ? $movement->quantity : 0; })->sum()) }}
                </td>
                <td class="text-right">
                    {{ number_format($groupedMovement->map(function ($movement) { return $movement->direction === 'sub' ? $movement->quantity : 0; })->sum()) }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2"></td>
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
    <span class="label label-primary">No movement found</span>
@endif