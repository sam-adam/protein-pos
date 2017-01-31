@if(count($movements) > 0)
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
        @foreach($movements as $date => $movement)
            <tr>
                <td width="50px">
                    <a class="btn btn-primary btn-xs" href="{{ route('reports.stock', ['branch' => $branchId, 'product' => $productId, 'from' => $movement['from']->startOfDay()->timestamp, 'to' => $movement['to']->endOfDay()->timestamp, 'mode' => 'daily']) }}">
                        <i class="fa fa-search-plus"></i>
                        See detail
                    </a>
                </td>
                <td>{{ $date }}</td>
                <td class="text-right">
                    {{ number_format($movement['in']) }}
                </td>
                <td class="text-right">
                    {{ number_format($movement['out']) }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" class="text-right">
                <strong>
                    Total In: {{ number_format(array_sum(array_column($movements, 'in'))) }}
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    Total Out: {{ number_format(array_sum(array_column($movements, 'out'))) }}
                </strong>
            </td>
        </tr>
        </tbody>
    </table>
@else
    <span class="label label-primary">No movement found</span>
@endif