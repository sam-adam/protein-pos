@extends('layouts.app')

@section('title')
    - Create New Product
@endsection

@section('content')
    @parent
    <div id="add-product" class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New Product
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('products.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: Coca Cola" required value="{{ old('name') }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                            <label for="price" class="col-sm-2 control-label">Price</label>
                            <div class="col-sm-5">
                                <input type="text" id="price" name="price" class="form-control" placeholder="Eg: 1250" required value="{{ old('price') }}" />
                                @foreach($errors->get('price') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                            <label for="code" class="col-sm-2 control-label">Code</label>
                            <div class="col-sm-2">
                                <input type="text" id="code" name="code" class="form-control" placeholder="Eg: ccl" value="{{ old('code') }}" />
                                @foreach($errors->get('code') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                            <label for="barcode" class="col-sm-1 control-label">Barcode</label>
                            <div class="col-sm-2">
                                <input type="text" id="barcode" name="barcode" class="form-control" value="{{ old('barcode') }}" />
                                @foreach($errors->get('barcode') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="checkbox col-sm-offset-2 {{ $errors->has('is_service') ? 'has-error' : '' }}">
                            <label>
                                <input type="checkbox" name="is_service" value="1" v-model="is_service"> Is Service
                                @foreach($errors->get('is_service') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </label>
                        </div>
                        <br/>
                        <div class="form-group {{ $errors->has('brand') ? 'has-error' : '' }}" v-bind:class="{hidden: is_service}">
                            <label for="brand" class="col-sm-2 control-label">Brand</label>
                            <div class="col-sm-5">
                                <select id="brand" name="brand" class="form-control" v-bind:required="!is_service">
                                    <option value>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @if(old('brand_id') == $brand->id) selected @endif>{{ $brand->name}}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('brand') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}" v-bind:class="{hidden: is_service}">
                            <label for="category" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-5">
                                <select id="category" name="category" class="form-control" v-bind:required="!is_service">
                                    <option value>Select Category</option>
                                    @foreach($categories as $category)
                                        @if(!$category->isRoot())
                                            <option value="{{ $category->id }}" @if(old('category_id') == $category->id) selected @endif>
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
                        <div class="form-group {{ $errors->has('product_variant_group_id') ? 'has-error' : '' }}" v-bind:class="{hidden: is_service}">
                            <label for="product_variant_group_id" class="col-sm-2 control-label">Variant</label>
                            <div class="col-sm-5">
                                <select id="product_variant_group_id" name="product_variant_group_id" class="form-control">
                                    <option value>Select Variant</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->id }}" @if(old('product_variant_group_id') == $variant->id) selected @endif>
                                            {{ $variant->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('product_variant_group_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Save</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ URL::previous() }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        const app = new Vue({
            el: '#add-product',
            data: {
                is_service: {{ old('is_service') ? 'true' : 'false' }}
            }
        });
    </script>
@endsection