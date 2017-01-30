@if($sales->count() > 0)
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th></th>
            <th>Date</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sales->groupBy(function ($sale) { return $sale->opened_at->toFormattedDateString(); }) as $date => $groupedSales)
            <tr>
                <td width="50px">
                    <a class="btn btn-primary btn-xs" href="{{ route('reports.sales', ['branch' => $branchId, 'from' => $from->timestamp, 'to' => $to->timestamp, 'mode' => 'daily']) }}">
                        <i class="fa fa-search-plus"></i>
                        See detail
                    </a>
                </td>
                <td>{{ $date }}</td>
                <td class="text-right">
                    {{
                        $groupedSales->map(function ($byDateSales) {
                            return $byDateSales->calculateTotal();
                        })->sum()
                    }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" class="text-right"><strong>Total</strong></td>
            <td class="text-right">
                <strong>@money($sales->map(function ($sale) { return $sale->calculateTotal(); })->sum())</strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
    <span class="label label-primary">No sales found</span>
@endif