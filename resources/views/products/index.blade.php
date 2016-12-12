@extends('layouts.app')

@section('title')
    - Products List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <form class="form-horizontal" method="get">
                    <div class="col-md-6">
                        <div class="form-group form-group-lg">
                            <label class="col-md-2 control-label">Search</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="query" placeholder="Input product name, code, or scan barcode" autofocus>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group form-group-lg">
                            <div class="col-md-12">
                                <select class="form-control" name="category">
                                    <option value>All Category</option>
                                    <option value="uncategorized" @if(Request::get('category') == 'uncategorized') selected @endif>Not Categorized</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if(Request::get('category') == $category->id) selected @endif>{{ $category->isRoot() ? 'All '.$category->name  : $category->parent->name.' - '.$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group form-group-lg">
                            <div class="col-md-12">
                                <select class="form-control" name="brand">
                                    <option value>All Brand</option>
                                    <option value="unbranded" @if(Request::get('brand') == 'unbranded') selected @endif>Not Branded</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @if(Request::get('brand') == $brand->id) selected @endif>{{ $brand->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fa fa-fw fa-search"></i>
                            Search
                        </button>
                    </div>
                </form>
            </div>
            <br/>
            <div class="row product-list">
                @foreach($products as $product)
                    <div class="col-md-3">
                        <div class="btn btn-default btn-lg btn-block product-item">
                            <div class="product-name">
                                {{ $product->name }}
                            </div>
                            <br/>
                            <div class="product-price">
                                <i class="fa fa-fw fa-money"></i>
                                {{ number_format($product->price) }}
                            </div>
                            <div class="product-stock">
                                <i class="fa fa-fw fa-cubes"></i>
                                {{ number_format($product->stock) }}
                            </div>
                        </div>
                        <br/>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <a href="{{ url('products/create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        Add new product
                    </a>
                </div>
                <div class="col-xs-6 text-right">
                    {{ $products->render() }}
                </div>
            </div>
        </div>
    </div>
@endsection