@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('content')
    @parent
    <div class="row" id="app">
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-body" id="search-panel">
                    <div class="form-group form-group-lg">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-fw fa-search"></i></div>
                            <input type="text" id="query" placeholder="Input product name, code, or scan barcode" class="form-control" v-model="query" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body" id="products-panel">
                    <table class="table table-hover">
                        <thead>
                            <tr class="register-items-header">
                                <th></th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Qty.</th>
                                <th>Disc %</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cartItem in cart">
                                <td></td>
                                <td>@{{ cartItem.product.name }}</td>
                                <td>@{{ cartItem.product.price }}</td>
                                <td>@{{ cartItem.quantity }}</td>
                                <td>@{{ 0 }}</td>
                                <td>@{{ cartItem.product.price * cartItem.quantity }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        var $queryBox = $("#query");

        $queryBox[0].focus();

        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                cart: []
            },
            methods: {
                addProduct: function (product, quantity) {
                    var sameProduct = false;

                    this.cart.forEach(function (cartItem) {
                        if (cartItem.product.id === product.id) {
                            cartItem.quantity += quantity;
                            sameProduct = true;
                        }
                    });

                    if (!sameProduct) {
                        this.cart.push({
                            product: product,
                            quantity: quantity
                        })
                    }
                },
                findByBarcode: function(query) {
                    var $this = this;

                    $.get("{{ url('/products/xhr-search') }}", {
                        query: query
                    }, function (response) {
                        $this.addProduct(response.product, 1);
                        $this.query = "";
                    });
                }
            }
        });

        $(document).ready(function () {
            $(window).on("paste", function (e) {
                if ($(e.target).attr("id") === $queryBox.attr("id")) {
                    setTimeout(function () {
                        app.findByBarcode($queryBox.val());
                    }, 100);
                }
            });
        });
    </script>
@endsection