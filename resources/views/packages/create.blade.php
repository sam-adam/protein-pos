@extends('layouts.app')

@section('title')
    - Create New Package
@endsection

@section('content')
    @parent
    <div id="app" class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New Package
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('packages.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: Complete Protein Set" required value="{{ old('name') }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                            <label for="code" class="col-sm-2 control-label">Code</label>
                            <div class="col-sm-5">
                                <input type="text" id="code" name="code" class="form-control" placeholder="Eg: pack-cps" required value="{{ old('code') }}" />
                                @foreach($errors->get('code') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                            <label for="price" class="col-sm-2 control-label">Price</label>
                            <div class="col-sm-5">
                                <input type="text" id="price" name="price" class="form-control" placeholder="Eg: 1200" required value="{{ old('price') }}" />
                                @foreach($errors->get('price') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="checkbox col-sm-offset-2 {{ $errors->has('is_customizable') ? 'has-error' : '' }}">
                            <label>
                                <input type="checkbox" name="is_customizable" value="1" @if(old('is_customizable')) checked @endif> Is Customizable
                                @foreach($errors->get('is_customizable') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </label>
                        </div>
                        <br/>
                        <div class="form-group {{ $errors->has('products') ? 'has-error' : '' }}">
                            <label for="price" class="col-sm-2 control-label">Products</label>
                            <div class="col-sm-5">
                                <table class="table table-condensed table-middle table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in items">
                                            <td>
                                                @{{ item.name }}
                                                <input type="hidden" v-bind:name="'products[' + item.id + '][id]'" v-bind:value="item.id" />
                                            </td>
                                            <td>@{{ parseInt(item.price).toLocaleString() }}</td>
                                            <td style="width: 20px;">
                                                <input type="text" value="0" v-bind:name="'products[' + item.id + '][quantity]'" class="form-control" />
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" v-on:click="removeItem(item)">
                                                    <i class="fa fa-trash"></i>
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <input id="search-box" class="form-control" placeholder="Search a product" v-model="query">
                                @foreach($errors->get('products') as $error)
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
        var products = {!! $products->toJson() !!}.map(function (product) { return {value: product.name, data: product}; }),
            app = new Vue({
                el: "#app",
                data: {
                    items: [],
                    query: ''
                },
                computed: {
                    subtotal: function () {
                        return this.items.reduce(function (firstItem, secondItem) {
                            return firstItem.price + secondItem.price;
                        });
                    }
                },
                methods: {
                    addItem: function (product) {
                        if (this.items.filter(function (item) { return item.id === product.id}).length === 0) {
                            this.items.push(product);
                        }

                        this.query = '';
                    },
                    removeItem: function (item) {
                        this.items.splice(this.items.indexOf(item), 1);
                    }
                }
            });

        @if(old('products'))
            for (productId in {!! json_encode(old('products')) !!}) {
                var foundInSuggestion = products.find(product => product.data.id == productId);

                if (foundInSuggestion !== undefined) {
                    app.addItem(foundInSuggestion.data);
                }
            }
        @endif

        $("#search-box").autocomplete({
            lookup: products,
            onSelect: function (suggestion) {
                app.addItem(suggestion.data);
            }
        });
    </script>
@endsection