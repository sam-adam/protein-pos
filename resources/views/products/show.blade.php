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

                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="movement">
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
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="add-inventory-modal-label">Add Inventory</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form class="form-horizontal">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Product</label>
                                    <div class="col-sm-5">
                                        <p class="form-control-static">{{ $product->name }}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Date</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="movement_effective_at" class="form-control datepicker" value="{{ \Carbon\Carbon::now()->toDateString() }}" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection