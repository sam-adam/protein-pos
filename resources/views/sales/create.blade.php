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
                    <search-product src="{{ route('products.xhr.search') }}" v-on:product-selected="addProduct($event.product, 1)"></search-product>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body" id="products-panel">
                    <div v-show="isCartEmpty">
                        <span class="label label-primary">No items on cart</span>
                    </div>
                    <table class="table table-hover" v-show="!isCartEmpty">
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
                                <td style="width: 70px;">
                                    <input type="text" class="form-control" v-model="cartItem.quantity" />
                                </td>
                                <td style="width: 70px;">
                                    <input type="text" class="form-control" v-model="cartItem.discount" />
                                </td>
                                <td>@{{ (cartItem.product.price * cartItem.quantity) * (100 - cartItem.discount) / 100 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-body" id="search-panel">
                    <search-customer src="{{ route('customers.xhr.search') }}" v-on:product-selected="setCustomer($event.customer)"></search-customer>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                cart: [],
                customer: {}
            },
            computed: {
                isCartEmpty: function () {
                    return this.cart.length === 0;
                }
            },
            methods: {
                setCustomer: function (customer) {
                    this.customer = customer;
                },
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
                            quantity: quantity,
                            discount: 0
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