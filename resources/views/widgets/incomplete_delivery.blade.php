<div class="panel panel-default">
    <div class="panel-heading">Incomplete Deliveries</div>
    <div class="panel-body">
        @if($incompleteDeliveries->count() > 0)
            <table class="table table-condensed table-striped">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($incompleteDeliveries as $incompleteDelivery)
                    <tr>
                        <td>{{ $incompleteDelivery->opened_at->toDayDateTimeString() }}</td>
                        <td>{{ $incompleteDelivery->customer->name }}</td>
                        <td class="text-right">
                            <form style="display: inline-block;" method="post" action="{{ route('sales.complete', $incompleteDelivery->id) }}" onsubmit="return confirm('Completing delivery! Are you sure?');">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success btn-xs">
                                    <i class="fa fa-check"></i>
                                    Complete
                                </button>
                            </form>
                            <form style="display: inline-block;" method="post" action="{{ route('sales.cancel', $incompleteDelivery->id) }}" onsubmit="return confirm('Cancelling delivery! Are you sure?');">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash"></i>
                                    Cancel
                                </button>
                            </form>
                            <a href="{{ route('sales.show', $incompleteDelivery->id) }}" class="btn btn-primary btn-xs">
                                <i class="fa fa-search-plus"></i>
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <span class="label label-primary">No incomplete deliveries</span>
        @endif
    </div>
</div>