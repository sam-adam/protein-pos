@extends('layouts.app')

@section('title')
    - {{ $product->name }}
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    General - {{ $product->name }}
                </div>
                <div class="panel-body form-horizontal">
                    <div class="row">
                        <label for="name" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <p class="form-control-static">{{ $product->name }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <label for="price" class="col-sm-2 control-label">Price</label>
                        <div class="col-sm-10">
                            <p class="form-control-static">{{ number_format($product->price) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <label for="code" class="col-sm-2 control-label">Code</label>
                        <div class="col-sm-2">
                            <p class="form-control-static">{{ $product->code }}</p>
                        </div>
                        <label for="barcode" class="col-sm-2 control-label">Barcode</label>
                        <div class="col-sm-6">
                            <p class="form-control-static">{{ $product->barcode }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <label for="brand" class="col-sm-2 control-label">Brand</label>
                        <div class="col-sm-10">
                            @if($product->brand)
                                <p class="form-control-static">{{ $product->brand->name }}</p>
                            @else
                                <p class="form-control-static"></p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label for="category" class="col-sm-2 control-label">Category</label>
                        <div class="col-sm-10">
                            @if($product->category)
                                <p class="form-control-static">{{ $product->category->parent->name.', '.$product->category->name }}</p>
                            @else
                                <p class="form-control-static"></p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-5">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-block">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Inventory Details
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#breakdown" aria-controls="breakdown" role="tab" data-toggle="tab">
                                Breakdown
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#movement" aria-controls="movement" role="tab" data-toggle="tab">
                                Movements
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <br/>
                        <div role="tabpanel" class="tab-pane active" id="breakdown">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Stock</th>
                                        <th>Cost</th>
                                        <th>Expired At</th>
                                        <th>Reminder Expired At</th>
                                        <th>Created At</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventories as $inventory)
                                        <tr class="{{ $inventory->expired_at->lte($now) ? 'danger' : ($inventory->expiry_reminder_date && $inventory->expiry_reminder_date->lte($now) ? 'warning' : 'default') }}">
                                            <td>{{ number_format($inventory->stock) }}</td>
                                            <td>{{ number_format($inventory->cost) }}</td>
                                            <td>{{ $inventory->expired_at->toDateString() }}</td>
                                            <td>{{ $inventory->expiry_reminder_date ? $inventory->expiry_reminder_date->toDateString() : '-' }}</td>
                                            <td>{{ $inventory->created_at->toDateString() }}</td>
                                            <td>{{ $inventory->creator->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="movement">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Date</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        <tr class="{{ $movement->direction === 'In' ? 'success' : 'danger' }}">
                                            <td>{{ $movement->direction }}</td>
                                            <td>{{ $movement->from ? $movement->from->name : '-' }}</td>
                                            <td>{{ $movement->to->name }}</td>
                                            <td>{{ $movement->created_at->toDateTimeString() }}</td>
                                            <td>{{ $movement->creator->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-sm-4">
                                    <a href="#add-inventory-modal" class="btn btn-primary" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                        Add Inventory
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-inventory-modal" tabindex="-1" role="dialog" aria-labelledby="add-inventory-modal-label">
        <div class="modal-dialog" role="document">
            <form class="form-horizontal" method="post" action="{{ route('products.addInventory', $product->id) }}">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="add-inventory-modal-label">Add Inventory</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Product</label>
                                    <div class="col-sm-8">
                                        <p class="form-control-static">{{ $product->name }}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="movement_effective_at" class="form-control datepicker" value="{{ old('movement_effective_at') ?: $defaultMovementDate->toDateString() }}" required/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Cost / Item</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="cost" class="form-control" value="{{ old('cost') }}" placeholder="Eg: 10000" required/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Quantity</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="quantity" class="form-control" value="{{ old('quantity') }}" placeholder="Eg: 12" required/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Expire Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="expire_date" class="form-control datepicker" value="{{ old('expire_date') ?: $defaultExpiredDate->toDateString() }}" required/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Expiry Reminder Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="expiry_reminder_date" class="form-control datepicker" value="{{ old('expiry_reminder_date') ?: $defaultExpiryReminderDate->toDateString() }}" required/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-4">Remark</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="remark">{{ old('remark') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var $movementEffectiveAt = $("input[name='movement_effective_at']"),
                $expireDate = $("input[name='expire_date']"),
                $expiryReminderDate = $("input[name='expiry_reminder_date']"),
                defaultMovementDate = moment("{{ $defaultMovementDate->toDateString() }}"),
                defaultExpiredDate = moment("{{ $defaultExpiredDate->toDateString() }}"),
                defaultExpiryReminderDate = moment("{{ $defaultExpiryReminderDate->toDateString() }}");

            $movementEffectiveAt.datepicker("setEndDate", defaultMovementDate.toDate());
            $expireDate.datepicker("setStartDate", defaultMovementDate.add(1, "days").toDate());
            $expiryReminderDate.datepicker("setStartDate", defaultMovementDate.add(1, "days").toDate());
            $expiryReminderDate.datepicker("setEndDate", defaultExpiredDate.subtract(1, "days").toDate());

            $movementEffectiveAt.on("changeDate", function (e) {
                $expireDate.datepicker("setStartDate", moment(e.date).add(7, 'days').toDate());
                $expiryReminderDate.datepicker("setStartDate", moment(e.date).add(7, 'days').toDate());
            });

            $expireDate.on("changeDate", function (e) {
                $expiryReminderDate.datepicker("setEndDate", e.date);
            });
        });
    </script>
@endsection