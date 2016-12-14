@extends('layouts.app')

@section('title')
    - Update Product
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Update Product - {{ $product->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('products.update', $product->id) }}" class="form-horizontal">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: Coca Cola" required value="{{ old('name') ?: $product->name }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                            <label for="price" class="col-sm-2 control-label">Price</label>
                            <div class="col-sm-5">
                                <input type="text" id="price" name="price" class="form-control" placeholder="Eg: 1250" required value="{{ old('price') ?: $product->price }}" />
                                @foreach($errors->get('price') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                            <label for="code" class="col-sm-2 control-label">Code</label>
                            <div class="col-sm-2">
                                <input type="text" id="code" name="code" class="form-control" placeholder="Eg: ccl" value="{{ old('code') ?: $product->code }}" />
                                @foreach($errors->get('code') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                            <label for="barcode" class="col-sm-1 control-label">Barcode</label>
                            <div class="col-sm-2">
                                <input type="text" id="barcode" name="barcode" class="form-control" value="{{ old('barcode') ?: $product->barcode }}" />
                                @foreach($errors->get('barcode') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('brand') ? 'has-error' : '' }}">
                            <label for="brand" class="col-sm-2 control-label">Brand</label>
                            <div class="col-sm-5">
                                <select id="brand" name="brand" class="form-control" required>
                                    <option value @if($product->brand_id === null) selected @endif>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @if((old('brand_id') ?: $product->brand_id) == $brand->id) selected @endif>{{ $brand->name}}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('brand') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                            <label for="category" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-5">
                                <select id="category" name="category" class="form-control" required>
                                    <option value @if($product->product_category_id === null) selected @endif>Select Category</option>
                                    @foreach($categories as $category)
                                        @if(!$category->isRoot())
                                            <option value="{{ $category->id }}" @if((old('category_id') ?: $product->product_category_id) == $category->id) selected @endif>
                                                {{ $category->isRoot() ? $category->name : $category->parent->name.' - '.$category->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @foreach($errors->get('category') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Save</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ URL::previous() === route('products.edit', $product->id) ? route('products.index') : URL::previous() }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection