@if($sales->count() > 0)
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Id</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Branch</th>
                <th>Type</th>
                <th>Remark</th>
                <th>Payment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr class="{{ $sale->isCancelled() ? 'warning' : (!$sale->isFinished() ? 'danger' : '') }}">
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->opened_at->toDayDateTimeString() }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>{{ $sale->branch->name }}</td>
                    <td>{{ $sale->getType() }}</td>
                    <td>{{ $sale->remark }}</td>
                    <td>
                        @if($sale->isPaid())
                            {{ $sale->payments->first()->payment_method }}
                        @else
                            <span class="label label-danger">
                                Unpaid
                            </span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-search-plus"></i>
                            View
                        </a>
                        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-default btn-sm">
                            <i class="fa fa-print"></i>
                            Print
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <span class="label label-primary">
        Sales not found
    </span>
@endif