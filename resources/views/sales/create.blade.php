@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('styles')
    <style type="text/css">
        .cart-table {
            margin-bottom: 0;
        }
        input[type=number] {
            text-align: right;
        }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
@endsection

@section('content')
    @parent
    <div id="app" v-cloak>
        <form method="post" action="{{ route('sales.store') }}" onsubmit="return app.isCompletable && confirm('Completing sales! Continue?');">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-7">
                    <div class="panel panel-default">
                        <div class="panel-body" id="search-product-panel">
                            <div class="col-sm-12">
                                <search-product
                                        src="{{ route('products.xhr.search') }}"
                                        :existing-items="cart.products"
                                        v-on:product-selected="addProductToCart($event.product, 1, $event.availableQuantity)"
                                        v-on:insufficient-stock="notify('error', $event.remark)"
                                ></search-product>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body" id="products-panel">
                            <div v-show="isCartEmpty">
                                <span class="label label-primary">No items on cart</span>
                            </div>
                            <table class="table cart-table table-hover" v-show="!isCartEmpty">
                                <thead>
                                <tr class="register-items-header">
                                    <th class="text-center"></th>
                                    <th>Item Name</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty.</th>
                                    <th class="text-center">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(product, index) in cart.persistentItems">
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-center"></td>
                                            <td style="vertical-align: middle;">@{{ product.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ product.price }}</td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + product.id + '][id]'" type="hidden" v-model="product.id"/>
                                                <input v-bind:name="'products[' + product.id + '][quantity]'" type="hidden" class="form-control" value="1" />
                                                <p class="form-control-static">1</p>
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice({"product": product, "quantity": 1, "discount": 0}) }}</td>
                                        </tr>
                                    </template>
                                    <template v-for="(productItem, index) in cart.products">
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-center">
                                                <a class="btn btn-xs text-danger" v-on:click="removeProductFromCart(index)">
                                                    <i class="fa fa-times-circle fa-2x"></i>
                                                </a>
                                            </td>
                                            <td style="vertical-align: middle;">@{{ productItem.product.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ productItem.product.price }}</td>
                                            <td class="text-center" style="width: 130px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + productItem.product.id + '][id]'" type="hidden" v-model="productItem.product.id"/>
                                                <div class="input-group">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary" type="button" v-on:click="productItem.quantity--">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </span>
                                                    <input v-bind:name="'products[' + productItem.product.id + '][quantity]'" type="number" class="form-control" v-model="productItem.quantity" min="0" v-bind:max="productItem.availableQuantity"/>
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary" type="button" v-on:click="productItem.quantity++">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(productItem) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="5">
                                                <table class="table cart-table table-condensed">
                                                    <tr>
                                                        <td><strong>Barcode</strong></td>
                                                        <td>@{{ productItem.product.barcode || "-" }}</td>
                                                        <td><strong>Available Sets</strong></td>
                                                        <td>
                                                            <template v-for="package in productItem.product.inPackages">
                                                                <button v-on:click="viewPackage($event, package.id)" class="btn btn-primary btn-xs" style="margin-right: 5px;" data-placement="top" v-tooltip="'Click to view detail'">
                                                                    <i class="fa fa-eye"></i>
                                                                    @{{ package.name }}
                                                                </button>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-for="(packageItem, index) in cart.packages">
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-center">
                                                <a class="btn btn-xs text-danger" v-on:click="removePackageFromCart(index)">
                                                    <i class="fa fa-times-circle fa-2x"></i>
                                                </a>
                                            </td>
                                            <td style="vertical-align: middle;">@{{ packageItem.package.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ packageItem.package.price }}</td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'packages[' + packageItem.package.id + '][id]'" type="hidden" v-model="packageItem.package.id"/>
                                                <div class="input-group">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary" type="button" v-on:click="packageItem.quantity--">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </span>
                                                    <input v-bind:name="'packages[' + packageItem.package.id + '][quantity]'" type="number" class="form-control" v-model="packageItem.quantity" min="0"/>
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary" type="button" v-on:click="packageItem.quantity++">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(packageItem) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="5">
                                                <table class="table table-condensed table-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                Package Item
                                                                <span class="label label-success" v-show="packageItem.package.isCustomizable">Customizable</span>
                                                            </th>
                                                            <th class="text-center">Quantity</th>
                                                            <th class="text-center">Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <template v-for="packageProduct in packageItem.package.items">
                                                            <tr>
                                                                <td>
                                                                    <select v-if="packageItem.package.isCustomizable" class="form-control" v-bind:name="'packages[' + packageItem.package.id + '][products][]'">
                                                                        <option v-for="variant in packageProduct.product.allVariants" v-model="variant.id">
                                                                            @{{ variant.name }}
                                                                        </option>
                                                                    </select>
                                                                    <p v-if="!packageItem.package.isCustomizable" class="form-control-static">
                                                                        @{{ packageProduct.product.name }}
                                                                        <input type="hidden" v-bind:name="'packages[' + packageItem.package.id + '][products][]'" v-model="packageProduct.product.id" />
                                                                    </p>
                                                                </td>
                                                                <td class="text-center">@{{ packageProduct.quantity }}</td>
                                                                <td class="text-center">@{{ packageProduct.product.stock }}</td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-default" data-toggle="dropdown" style="padding-left: 20px; padding-right: 20px; border-bottom-right-radius: 4px; border-top-right-radius: 4px;">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu sales-dropdown" role="menu">
                                        <li>
                                            <a href="#look-up-receipt" class="look-up-receipt" data-toggle="modal">
                                                <i class="fa fa-file-o"></i>
                                                &nbsp;&nbsp;&nbsp;
                                                Lookup Receipt
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <hr style="margin: 0;" />
                        <div class="panel-body" id="search-customer-panel">
                            <div class="row" v-show="!isCustomerSelected">
                                <div class="col-sm-7">
                                    <search-customer src="{{ route('customers.xhr.search') }}" v-on:customer-selected="setCustomer($event)"></search-customer>
                                </div>
                                <div class="col-sm-1">
                                    <p class="form-control-static">Or</p>
                                </div>
                                <div class="col-sm-4">
                                    <button class="btn btn-block btn-primary" v-on:click="findCustomer()">
                                        <i class="fa fa-fw fa-search"></i>
                                        Find Customer
                                    </button>
                                </div>
                            </div>
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
                                <br/>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <a v-bind:href="'{{ route('customers.show', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', this.customer.id)" target="_blank" class="btn btn-primary btn-block">
                                            <i class="fa fa-search-plus"></i>
                                            Show details
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <button class="btn btn-default btn-block" v-on:click="setCustomer({})">
                                            <i class="fa fa-times"></i>
                                            Change customer
                                        </button>
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
                                <tr v-show="discountSetting.isEnabled">
                                    <td>
                                        Sales Discount:
                                        <div class="btn-group" style="margin-left: 5px;">
                                            <label class="btn btn-primary btn-sm" v-bind:class="{'active': salesDiscountType === 'PERCENTAGE'}">
                                                <input type="radio" name="sales_discount_type" autocomplete="off" value="PERCENTAGE" v-model="salesDiscountType" /> Percent
                                            </label>
                                            <label class="btn btn-primary btn-sm" v-bind:class="{'active': salesDiscountType === 'PRICE'}">
                                                <input type="radio" name="sales_discount_type" autocomplete="off" value="PRICE" v-model="salesDiscountType" /> Price
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" type="button" v-on:click="salesDiscount--">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </span>
                                            <input type="number" name="sales_discount" class="form-control text-right" v-model="salesDiscount" min="0" v-bind:max="maxSaleDiscount"/>
                                            <span class="input-group-addon">
                                                @{{ this.salesDiscountType === 'PERCENTAGE' ? '%' : 'AED' }}
                                            </span>
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" type="button" v-on:click="salesDiscount++">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="success">
                                    <td>Subtotal:</td>
                                    <td>
                                        <strong>@{{ subTotal }}</strong>
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
                                                    <h5 class="sales-info">Total</h5>
                                                    <strong class="text-success">@{{ grandTotal }}</strong>
                                                </td>
                                                <td>
                                                    <h5 class="sales-info">Change</h5>
                                                    <strong class="text-warning">@{{ change }}</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @if($immediatePayment)
                                    <tr class="dashed">
                                        <td colspan="2">
                                            <h5 class="sales-info">Payment</h5>
                                            <br/>
                                            <div class="row">
                                                <div class="col-xs-8 text-left">
                                                    <button type="button" class="btn" v-on:click="setPaymentMethod('cash')" v-bind:class="{ 'btn-success': payment.method === 'cash' }">Cash</button>
                                                    <button type="button" class="btn" v-on:click="setPaymentMethod('credit_card')" v-bind:class="{ 'btn-success': payment.method === 'credit_card' }">Credit Card</button>
                                                </div>
                                                <div class="col-xs-4">
                                                    <div class="input-group" v-show="payment.method === 'cash'">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-primary" type="button" v-on:click="payment.amount--">
                                                                <i class="fa fa-minus"></i>
                                                            </button>
                                                        </span>
                                                        <input
                                                            type="number"
                                                            name="payment_amount"
                                                            v-model="payment.amount"
                                                            class="form-control text-right"
                                                            placeholder="Enter payment amount"
                                                            min="0"
                                                            v-bind:required="payment.method === 'cash'"
                                                        />
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-primary" type="button" v-on:click="payment.amount++">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                    <input
                                                            type="hidden"
                                                            name="credit_card_number"
                                                            class="form-control text-right"
                                                            placeholder="Enter credit card num."
                                                            v-model="payment.cardNumber"
                                                            v-show="payment.method === 'credit_card'"
                                                    />
                                                </div>
                                            </div>
                                            <input type="hidden" name="immediate_payment" value="{{ $immediatePayment }}" />
                                            <input type="hidden" name="payment_method" v-model="payment.method"/>
                                        </td>
                                    </tr>
                                @else
                                    <input type="hidden" name="immediate_payment" value="{{ $immediatePayment ? 1 : 0 }}" />
                                    <input type="hidden" name="payment_method" value="cash" />
                                @endif
                                <tr>
                                    <td colspan="2">
                                        <button type="submit" class="btn btn-block btn-primary" v-bind:disabled="!isCompletable">
                                            {{ $immediatePayment ? 'Complete Sale' : 'Book Delivery' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr class="dashed">
                                    <td colspan="2">
                                        <h5 class="sales-info">Remark</h5>
                                        <br/>
                                        <textarea name="remark" class="form-control" placeholder="Additional info"></textarea>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal fade" id="look-up-receipt" tabindex="-1" role="dialog" aria-labelledby="look-up-receipt-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="look-up-receipt-label">Find Receipt</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-horizontal">
                            <div class="form-group">
                                <label class="control-label col-sm-4">Enter receipt number</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary">
                        <i class="fa fa-fw fa-search-plus"></i>
                        View Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const childWindowFeature = "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes";
        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                salesDiscount: 0,
                salesDiscountType: '{{ $defaultDiscountType }}',
                discountSetting: {!! json_encode($discount) !!},
                cart: {
                    products: [],
                    packages: [],
                    persistentItems: {!! json_encode($persistentItems) !!}
                },
                customer: {!! json_encode($customerData) !!},
                payment: {
                    method: 'cash',
                    amount: 0,
                    cardNumber: ''
                },
                creditCardTax: {{ $creditCardTax }}
            },
            watch: {
                cart: {
                    deep: true,
                    handler: function (oldItems, newItems) {
                        var $this = this;

                        newItems.products.forEach(function (item, index) {
                            if (item.quantity === 0) {
                                $this.removeProductFromCart(index);
                            } else if (item.quantity > item.product.stock) {
                                $this.notify("error", "Insufficient stock");

                                item.quantity = item.product.stock;
                            }
                        });

                        newItems.packages.forEach(function (item, index) {
                            if (item.quantity === 0) {
                                $this.removePackageFromCart(index);
                            } else if (item.quantity > item.package.stock) {
                                $this.notify("error", "Insufficient stock");

                                item.quantity = item.package.stock;
                            }
                        });
                    }
                },
                salesDiscount: {
                    handler: function (oldDiscount, newDiscount) {
                        var $this = this,
                            maxSaleDiscount = $this.maxSaleDiscount;

                        if (oldDiscount < 0 || oldDiscount === '') {
                            $this.salesDiscount = 0;
                        }

                        if (oldDiscount > maxSaleDiscount) {
                            $this.salesDiscount = maxSaleDiscount;

                            $this.notify('error', 'Max discount is ' + maxSaleDiscount);
                        }
                    }
                },
                salesDiscountType: {
                    handler: function () { this.salesDiscount = 0; }
                }
            },
            computed: {
                maxSaleDiscount: function () {
                    if (this.discountSetting.isUnlimited) {
                        return (this.salesDiscountType === 'PERCENTAGE')
                            ? 100
                            : this.preSaleTotal;
                    }

                    return (this.salesDiscountType === 'PERCENTAGE')
                        ? this.discountSetting.maxPercentage
                        : this.discountSetting.maxPrice;
                },
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
                isAnyProductSelected: function () {
                    return (this.cart.products.length + this.cart.packages.length + this.cart.persistentItems.length) > this.cart.persistentItems.length;
                },
                isCustomerSelected: function () {
                    return this.customer.hasOwnProperty('id');
                },
                isPaymentCompleted: function () {
                    if ({{ $immediatePayment ? 'false' : 'true' }}) {
                        return true;
                    }

                    if (this.payment.method === 'cash') {
                        return this.payment.amount >= this.grandTotal;
                    } else if (this.payment.method === 'credit_card') {
                        return true;
                    }

                    return false;
                },
                isCompletable: function () {
                    return this.isAnyProductSelected
                            && this.isCustomerSelected
                            && this.isPaymentCompleted;
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

                    this.cart.persistentItems.forEach(function (product) {
                        itemsTotal += $this.calculateItemPrice({
                            product: product,
                            quantity: 1,
                            discount: 0
                        });
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

                    return this.salesDiscountType === 'PERCENTAGE'
                        ? this.applyDiscount(preSaleTotal, this.salesDiscount)
                        : preSaleTotal - this.salesDiscount;
                },
                grandTotal: function () { return this.subTotal; },
                change: function () {
                    return this.payment.method === 'cash'
                        ? Math.max(this.payment.amount - this.grandTotal, 0)
                        : 0;
                }
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

                    return this.applyDiscount(cartItem.price * item.quantity, item.discount);
                },
                setCustomer: function (customer) {
                    this.customer = customer;
                },
                setPaymentMethod: function (paymentMethod) {
                    this.payment.method = paymentMethod;

                    if (this.payment.method === 'cash') {
                        this.payment.amount = 0;
                    } else {
                        this.payment.amount = this.grandTotal;
                    }
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
                findProduct: function () {
                    var $this = this,
                        lastUrl = "{!! route('products.index', ['external' => 1, 'intent' => 'select']) !!}",
                        productWindow =  window.open(lastUrl, "choose_product_window", childWindowFeature),
                        attachListener = function () {
                            productWindow.addEventListener("product-selected", function (event) { $this.addProductToCart(event.detail.product, 1, event.detail.product.availableQuantity); });
                            productWindow.addEventListener('should-rebind', function () {
                                var timeout = setTimeout(function () {
                                    if (lastUrl !== window.location.href) {
                                        lastUrl = window.location.href;

                                        productWindow.addEventListener("product-selected", function (event) { $this.addProductToCart(event.detail.product, 1, event.detail.product.availableQuantity); });

                                        attachListener();
                                    }

                                    clearTimeout(timeout);
                                }, 500);
                            });
                        };

                    attachListener();
                },
                findCustomer: function () {
                    var $this = this,
                        lastUrl = "{!! route('customers.index', ['external' => 1, 'intent' => 'select']) !!}",
                        customerWindow =  window.open(lastUrl, "choose_customer_window", childWindowFeature),
                        attachListener = function () {
                            customerWindow.addEventListener("customer-selected", function (event) { $this.setCustomer(event.detail.customer); });
                            customerWindow.addEventListener('should-rebind', function () {
                                var timeout = setTimeout(function () {
                                    if (lastUrl !== window.location.href) {
                                        lastUrl = window.location.href;

                                        customerWindow.addEventListener("customer-selected", function (event) { $this.setCustomer(event.detail.customer); });

                                        attachListener();
                                    }

                                    clearTimeout(timeout);
                                }, 500);
                            });
                        };

                    attachListener();
                },
                viewPackage: function ($event, packageId) {
                    if ($event.x !== 0) {
                        var $this = this,
                            packageWindow =  window.open("/packages/" + packageId + "?external=1&intent=getPackage", "choose_package_window", childWindowFeature);

                        packageWindow.addEventListener("package-selected", function (event) {
                            $this.addPackageToCart(event.detail.package, 1, event.detail.availableQuantity);
                        });
                    }
                }
            }
        });
    </script>
@endsection