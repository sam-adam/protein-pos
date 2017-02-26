<div class="panel panel-red">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-3">
                <i class="fa fa-exclamation-circle fa-3x"></i>
            </div>
            <div class="col-xs-9 text-right">
                <div class="huge">{{ $branch->name }} - {{ $branch->alreadyExpired->sum('stock') }}</div>
                <h4>Inventories already expired!</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-stripped">
            <thead>
                <tr>
                    <td>Name</td>
                    <td>Expired At</td>
                    <td class="text-right">Stock</td>
                </tr>
            </thead>
            <tbody>
                @foreach($branch->alreadyExpired as $branchInventory)
                    @if($branchInventory->inventory->product)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $branchInventory->inventory->product->id) }}">
                                    {{ $branchInventory->inventory->product->name }}
                                </a>
                            </td>
                            <td>{{ $branchInventory->expired_at->toFormattedDateString() }}</td>
                            <td class="text-right">{{ $branchInventory->stock }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>