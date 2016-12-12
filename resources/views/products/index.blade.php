@extends('layouts.app')

@section('title')
    - Products List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Products List - Record {{ $products->firstItem() }} to {{ $products->lastItem() }} from {{ $products->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach($products as $product)
                            <button class="btn btn-default btn-lg btn-block">
                                {{ $product->name }}
                                <br/>
                                {{ $product->price }}
                            </button>
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
    </div>
@endsection