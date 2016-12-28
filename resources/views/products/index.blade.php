@extends('layouts.app')

@section('title')
    - Products List
@endsection

@section('content')
    @parent
    <div id="app" class="row">
        <div class="col-md-12">
            <div class="row">
                <form class="form-horizontal" method="get">
                    <div class="col-md-4">
                        <div class="form-group form-group-lg">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-fw fa-search"></i>
                                    </div>
                                    <input type="text" id="query" class="form-control" name="query" placeholder="Input product name, code, or scan barcode" value="{{ Request::get('query') }}" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group form-group-lg">
                            <div class="col-md-12">
                                <select class="form-control" name="category">
                                    <option value>All Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if(Request::get('category') == $category->id) selected @endif>{{ $category->displayName }}</option>
                                    @endforeach
                                    <option value="uncategorized" @if(Request::get('category') == 'uncategorized') selected @endif>Not Categorized</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group form-group-lg">
                            <div class="col-md-12">
                                <select class="form-control" name="brand">
                                    <option value>All Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @if(Request::get('brand') == $brand->id) selected @endif>{{ $brand->name}}</option>
                                    @endforeach
                                    <option value="unbranded" @if(Request::get('brand') == 'unbranded') selected @endif>Not Branded</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fa fa-fw fa-search"></i>
                                    Search
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('products.index') }}" class="btn btn-danger btn-lg btn-block">
                                    <i class="fa fa-fw fa-times"></i>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <br/>
            <div class="row product-list">
                @if($showMode === 'product')
                    @foreach($products as $product)
                        <div class="col-md-3">
                            <div class="btn btn-default btn-lg btn-block product-item {{ $product->isBulkContainer() ? 'btn-success' : ''}}" href="{{ route('products.show', $product->id) }}">
                                <div class="product-name">
                                    {{ ($product->isBulkContainer() ? '(Bulk) ' : '').$product->name }}
                                </div>
                                <br/>
                                <div class="product-price">
                                    <i class="fa fa-fw fa-money"></i>
                                    {{ number_format($product->price) }}
                                </div>
                                @if($product->is_service)
                                    <div>
                                        <i class="fa fa-fw fa-lightbulb-o"></i>
                                        Service Item
                                    </div>
                                @elseif($product->isBulkContainer())
                                    <div class="product-stock">
                                        <i class="fa fa-fw fa-clone"></i>
                                        {{ number_format($product->product_item_quantity).' x '.$product->item->name }}
                                    </div>
                                @else
                                    <div class="product-stock">
                                        <i class="fa fa-fw fa-cubes"></i>
                                        {{ number_format($product->stock) }}
                                    </div>
                                @endif
                            </div>
                            <br/>
                        </div>
                    @endforeach
                @else
                    <div class="col-md-3" v-if="selectedCategory.children.length === 0" v-for="parent in categories">
                        <div class="btn btn-info btn-lg btn-block product-item" v-on:click="selectParentCategory(parent)">
                            <h3 class="product-name">
                                @{{ parent.name }}
                            </h3>
                        </div>
                        <br/>
                    </div>
                    <div class="col-md-3" v-if="selectedCategory.children.length === 0">
                        <a href="{{ route('products.index').'?category=uncategorized' }}" class="btn btn-info btn-lg btn-block product-item">
                            <h3 class="product-name">
                                All Not Categorized
                            </h3>
                        </a>
                        <br/>
                    </div>
                    <div class="col-md-3" v-if="selectedCategory.children.length > 0" v-for="child in selectedCategory.children">
                        <a v-bind:href="'{{ route('products.index') }}?category=' + child.id" class="btn btn-info btn-lg btn-block product-item">
                            <h3 class="product-name">
                                @{{ child.name }}
                            </h3>
                        </a>
                        <br/>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <a href="{{ url('products/create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        Add new product
                    </a>
                </div>
                @if($showMode === 'product')
                    <div class="col-xs-6 text-right">
                        {{ $products->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var $queryBox = $("#query"),
                $queryForm = $queryBox.closest("form"),
                $productItems = $(".product-item");

            $queryBox[0].focus();

            $(window).on("paste", function (e) {
                if (e.target === $queryBox[0]) {
                    setTimeout(function () {
                        $queryForm.submit();
                    }, 100);
                }
            });

            @if($showMode === 'product')
                $productItems.on("click", function () {
                    window.location = $(this).attr("href");
                });
            @endif
        });

        var app = new Vue({
            el: "#app",
            data: {
                selectedCategory: {
                    children: []
                },
                categories: {!! json_encode($categoryTree) !!}
            },
            methods: {
                selectParentCategory: function (parentCategory) {
                    this.selectedCategory = parentCategory;
                }
            }
        });
    </script>
@endsection