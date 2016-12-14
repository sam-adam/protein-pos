@extends('layouts.app')

@section('title')
    - Create New Product
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
                            <a href="#branch-stock" aria-controls="branch-stock" role="tab" data-toggle="tab">
                                Branch Stock
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#movement" aria-controls="movement" role="tab" data-toggle="tab">
                                Movements
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="branch-stock">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Branch</th>
                                        <th>Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                        <tr>
                                            <td>{{ $branch->name }}</td>
                                            <td>{{ number_format($branch->stockRemaining) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="movement"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection