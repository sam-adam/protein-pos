@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('styles')
    <style type="text/css">
        .cart-table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    @parent
    <div id="app" v-cloak>
        <form method="post" action="{{ route('sales.do_refund', $sale->id) }}" onsubmit="return app.isCompletable && confirm('Completing refund! Continue?');">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-7">
                    <div class="panel panel-default">
                        <div class="panel-body" id="products-panel">
                            <table class="table cart-table table-hover">
                                <thead>
                                <tr class="register-items-header">
                                    <th>Item Name</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty.</th>
                                    <th class="text-center">Refund Qty.</th>
                                    <th class="text-center">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(productItem, index) in cart.products">
                                        <tr>
                                            <td style="vertical-align: middle;">@{{ productItem.product.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ productItem.product.price }}</td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + productItem.id + '][id]'" type="hidden" v-model="productItem.id"/>
                                                @{{ productItem.quantity }}
                                            </td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + productItem.id + '][quantity]'" type="number" class="form-control" v-model="productItem.refundedQuantity" min="0" v-bind:max="productItem.quantity" />
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(productItem) }}</td>
                                        </tr>
                                    </template>
                                    <template v-for="(packageItem, index) in cart.packages">
                                        <tr>
                                            <td style="vertical-align: middle;">@{{ packageItem.package.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ packageItem.package.price }}</td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'packages[' + packageItem.id + '][id]'" type="hidden" v-model="packageItem.id"/>
                                                @{{ packageItem.quantity }}
                                            </td>
                                            <td class="text-center">
                                                <input v-bind:name="'packages[' + packageItem.id + '][quantity]'" type="number" class="form-control" v-model="packageItem.refundedQuantity" min="0" v-bind:max="packageItem.quantity" />
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(packageItem) }}</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-default">
                        <div class="panel-body" id="search-customer-panel">
                            <div class="customer-info" v-show="isCustomerSelected">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <input type="hidden" name="customer_id" v-model="customer.id"/>
                                        <h4 class="name">
                                            @{{ customer.name }}
                                        </h4>
                                        <div>
                                        <span class="label label-success" v-show="isCustomerInGroup" v-if="isCustomerInGroup">
                                            <i class="fa fa-star"></i>
                                            @{{ customer.group.label }}
                                        </span>
                                        </div>
                                        <br/>
                                        <div class="row">
                                            <div class="col-xs-2">Phone:</div>
                                            <div class="col-xs-6">@{{ customer.phone || "-" }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-2">E-Mail:</div>
                                            <div class="col-xs-6">@{{ customer.email || "-" }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-2">Points:</div>
                                            <div class="col-xs-6">@{{ customer.points }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body" id="sales-summary-panel">
                            <table id="sales-summary-table" class="table">
                                <tbody>
                                <tr>
                                    <td>Customer Discount:</td>
                                    <td>
                                        @{{ isCustomerInGroup ? customer.group.discount + "%" : "-" }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sales Discount:</td>
                                    <td>
                                        @{{ salesDiscount.amount + (salesDiscount.type === 'PERCENTAGE' ? '%' : ' AED') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Subtotal:</td>
                                    <td>
                                        <strong>@{{ subTotal }}</strong>
                                    </td>
                                </tr>
                                <tr class="success">
                                    <td>Previous Total:</td>
                                    <td>
                                        <strong>@{{ saleTotal }}</strong>
                                    </td>
                                </tr>
                                <tr v-show="this.payment.method === 'credit_card'">
                                    <td>Credit Card Tax:</td>
                                    <td>
                                        @{{ creditCardTax }}%
                                    </td>
                                </tr>
                                <tr class="separator">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr class="dashed" id="summary-grand-total">
                                    <td colspan="2">
                                        <table class="table">
                                            <tr>
                                                <td style="width: 50%;">
                                                    <h5 class="sales-info">New Total</h5>
                                                    <strong class="text-success">@{{ grandTotal }}</strong>
                                                </td>
                                                <td>
                                                    <h5 class="sales-info">Refund Due</h5>
                                                    <strong class="text-warning">@{{ refundDue }}</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <button type="submit" class="btn btn-block btn-primary" v-bind:disabled="!isCompletable">
                                            Make Refund
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                saleTotal: {{ $sale->calculateTotal() }},
                refundDue: {{ $sale->calculateTotal() }},
                cart: {
                    products: {!! json_encode($products) !!},
                    packages: {!! json_encode($packages) !!}
                },
                customer: {!! json_encode($customerData) !!},
                payment: {
                    method: 'cash',
                    amount: 0,
                    cardNumber: ''
                },
                creditCardTax: {{ $creditCardTax }},
                salesDiscount: {
                    type: "{{ $sale->sales_discount_type }}",
                    amount: {{ $sale->sales_discount }}
                }
            },
            watch: {
                cart: {
                    deep: true,
                    handler: function (oldItems, newItems) {
                        var $this = this;

                        newItems.products.forEach(function (item, index) {
                            if (item.refundedQuantity > item.quantity) {
                                $this.notify("error", "Invalid quantity");

                                item.refundedQuantity = item.quantity;
                            } else if (item.refundedQuantity == '') {
                                item.refundedQuantity = 0;
                            }
                        });

                        newItems.packages.forEach(function (item, index) {
                            if (item.refundedQuantity > item.quantity) {
                                $this.notify("error", "Invalid quantity");

                                item.refundedQuantity = item.quantity;
                            } else if (item.refundedQuantity == '') {
                                item.refundedQuantity = 0;
                            }
                        });
                    }
                }
            },
            computed: {
                isCustomerInGroup: function () {
                    return this.customer
                            && this.customer.hasOwnProperty('group')
                            && this.customer.group;
                },
                isCartEmpty: function () {
                    return this.cart.products.length === 0
                            && this.cart.packages.length === 0
                            && this.cart.persistentItems.length === 0;
                },
                isCustomerSelected: function () {
                    return this.customer.hasOwnProperty('id');
                },
                isCompletable: function () {
                    return this.isCustomerSelected
                            && this.refundDue > 0;
                },
                itemsTotal: function () {
                    var itemsTotal = 0,
                        $this = this;

                    this.cart.products.forEach(function (cartItem) {
                        itemsTotal += $this.calculateItemPrice(cartItem);
                    });

                    this.cart.packages.forEach(function (cartItem) {
                        itemsTotal += $this.calculateItemPrice(cartItem);
                    });

                    return itemsTotal;
                },
                preSaleTotal: function () {
                    return this.isCustomerInGroup
                        ? this.applyDiscount(this.itemsTotal, this.customer.group.discount)
                        : this.itemsTotal;
                },
                subTotal: function () {
                    var preSaleTotal = this.preSaleTotal;

                    return this.salesDiscount.type === 'PERCENTAGE'
                        ? this.applyDiscount(preSaleTotal, this.salesDiscount.amount)
                        : preSaleTotal - this.salesDiscount.amount;
                },
                grandTotal: function () { return this.subTotal; },
                refundDue: function () { return this.saleTotal - this.grandTotal; }
            },
            methods: {
                applyDiscount: function (original, discount) {
                    return original * (100 - discount) / 100;
                },
                applyTax: function (original, tax) {
                    return original * (100 + tax) / 100
                },
                calculateItemPrice: function (item) {
                    var cartItem = item.hasOwnProperty("product") ? item.product : item.package;

                    return this.applyDiscount(cartItem.price * (item.quantity - item.refundedQuantity), 0);
                },
                addProductToCart: function (product, quantity, availableQuantity) {
                    var sameProduct = false,
                        shouldAdd = true,
                        $this = this;

                    this.cart.products.forEach(function (cartItem) {
                        if (cartItem.product.id === product.id) {
                            if (cartItem.quantity + quantity > availableQuantity) {
                                shouldAdd = false;

                                $this.notify('error', 'Not enough stock');
                            } else {
                                cartItem.quantity += quantity;
                                sameProduct = true;
                            }
                        }
                    });

                    if (!sameProduct && shouldAdd) {
                        product.availableQuantity = availableQuantity;

                        this.cart.products.push({
                            product: product,
                            quantity: quantity,
                            discount: 0
                        })
                    }
                },
                addPackageToCart: function (package, quantity) {
                    var samePackage = false,
                        shouldAdd = true;

                    this.cart.packages.forEach(function (cartItem) {
                        if (cartItem.package.id === package.id) {
                            cartItem.quantity += quantity;
                            samePackage = true;
                        }
                    });

                    if (!samePackage && shouldAdd) {
                        this.cart.packages.push({
                            package: package,
                            quantity: quantity,
                            discount: 0
                        })
                    }
                },
                removeProductFromCart: function (index) {
                    this.cart.products.splice(index, 1);
                },
                removePackageFromCart: function (index) {
                    this.cart.packages.splice(index, 1);
                },
                notify: function (type, message) {
                    var fn = window.toastr[type];

                    fn(message);
                },
                viewPackage: function ($event, packageId) {
                    if ($event.x !== 0) {
                        var $this = this,
                            features = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes',
                            packageWindow =  window.open("/packages/" + packageId + "?external=1&intent=getPackage", "choose_package_window", features);

                        packageWindow.addEventListener("package-selected", function (event) {
                            $this.addPackageToCart(event.detail.package, 1, event.detail.availableQuantity);
                        });
                    }
                }
            }
        });
    </script>
@endsection