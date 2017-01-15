@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('content')
    @parent
    <div id="app" v-cloak>
        <form method="post" action="{{ route('sales.store') }}" onsubmit="return app.isCompletable;">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-7">
                    <div class="panel panel-default">
                        <div class="panel-body" id="search-product-panel">
                            <search-product
                                    src="{{ route('products.xhr.search') }}"
                                    :existing-items="cart.products"
                                    v-on:product-selected="addProductToCart($event.product, 1, $event.availableQuantity)"
                                    v-on:insufficient-stock="notify('error', $event.remark)"
                            ></search-product>
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
                                    <th class="text-center"></th>
                                    <th>Item Name</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty.</th>
                                    <th class="text-center">Disc %</th>
                                    <th class="text-center">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(productItem, index) in cart.products">
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-center">
                                                <a class="btn btn-xs text-danger" v-on:click="removeProductFromCart(index)">
                                                    <i class="fa fa-times-circle fa-2x"></i>
                                                </a>
                                            </td>
                                            <td style="vertical-align: middle;">@{{ productItem.product.name }}</td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ productItem.product.price }}</td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + productItem.product.id + '][id]'" type="hidden" v-model="productItem.product.id"/>
                                                <input v-bind:name="'products[' + productItem.product.id + '][quantity]'" type="number" class="form-control" v-model="productItem.quantity" min="0" v-bind:max="productItem.availableQuantity"/>
                                            </td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'products[' + productItem.product.id + '][discount]'" type="number" class="form-control" v-model="productItem.discount" min="0"/>
                                            </td>
                                            <td style="vertical-align: middle;" class="text-center">@{{ calculateItemPrice(productItem) }}</td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="5">
                                                <table class="table table-condensed">
                                                    <tr>
                                                        <td style="width: 25%;"><strong>Barcode</strong></td>
                                                        <td style="width: 25%;">@{{ productItem.product.barcode || "-" }}</td>
                                                        <td style="width: 25%;"><strong>Category</strong></td>
                                                        <td style="width: 25%;">@{{ productItem.product.category ? productItem.product.category.name : "-" }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Available Stock</strong></td>
                                                        <td>@{{ productItem.product.availableQuantity }}</td>
                                                        <td><strong>Brand</strong></td>
                                                        <td>@{{ productItem.product.brand ? productItem.product.brand.name : "-" }}</td>
                                                    </tr>
                                                    <tr v-if="productItem.product.inPackages">
                                                        <td><strong>Available Sets</strong></td>
                                                        <td colspan="3">
                                                            <template v-for="package in productItem.product.inPackages">
                                                                <button v-on:click="viewPackage(package.id)" class="btn btn-primary btn-xs" style="margin-right: 5px;" data-placement="top" v-tooltip="'Click to view detail'">
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
                                                <input v-bind:name="'packages[' + packageItem.package.id + '][quantity]'" type="number" class="form-control" v-model="packageItem.quantity" min="0"/>
                                            </td>
                                            <td class="text-center" style="width: 80px; vertical-align: middle;">
                                                <input v-bind:name="'packages[' + packageItem.package.id + '][discount]'" type="number" class="form-control" v-model="packageItem.discount" min="0"/>
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
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <template v-for="packageProduct in packageItem.package.items">
                                                            <tr>
                                                                <td>
                                                                    <select v-if="packageItem.package.isCustomizable" class="form-control" v-bind:name="'packages[' + packageItem.package.id + '][product_id]'">
                                                                        <option v-for="variant in packageProduct.product.allVariants" v-model="variant.id">
                                                                            @{{ variant.name }}
                                                                        </option>
                                                                    </select>
                                                                    <p v-if="!packageItem.package.isCustomizable" class="form-control-static">
                                                                        @{{ packageProduct.product.name }}
                                                                    </p>
                                                                </td>
                                                                <td class="text-center">@{{ packageProduct.quantity }}</td>
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
                        <div class="panel-body" id="search-customer-panel">
                            <search-customer src="{{ route('customers.xhr.search') }}" v-on:customer-selected="setCustomer($event)" v-show="!isCustomerSelected"></search-customer>
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
                                        <button class="btn btn-primary btn-block">
                                            <i class="fa fa-search-plus"></i>
                                            Show details
                                        </button>
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
                                <tr>
                                    <td>Sales Discount:</td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="sales_discount" class="form-control" v-model="salesDiscount" min="0" max="100"/>
                                            <span class="input-group-addon">%</span>
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
                                <tr class="dashed">
                                    <td colspan="2">
                                        <h5 class="sales-info">Payment</h5>
                                        <br/>
                                        <div class="row">
                                            <div class="col-xs-offset-1 col-xs-5">
                                                <button type="button" class="btn" v-on:click="setPaymentMethod('cash')" v-bind:class="{ 'btn-success': payment.method === 'cash' }">Cash</button>
                                                <button type="button" class="btn" v-on:click="setPaymentMethod('credit_card')" v-bind:class="{ 'btn-success': payment.method === 'credit_card' }">Credit Card</button>
                                            </div>
                                            <div class="col-xs-6">
                                                <input
                                                        type="number"
                                                        name="payment_amount"
                                                        v-model="payment.amount"
                                                        class="form-control text-right"
                                                        placeholder="Enter payment amount"
                                                        min="0"
                                                        v-show="payment.method === 'cash'"
                                                        v-bind:required="payment.method === 'cash'"
                                                />
                                                <input
                                                        type="text"
                                                        name="credit_card_number"
                                                        class="form-control text-right"
                                                        placeholder="Enter credit card num."
                                                        v-model="payment.cardNumber"
                                                        v-show="payment.method === 'credit_card'"
                                                        v-bind:required="payment.method === 'credit_card'"
                                                />
                                            </div>
                                        </div>
                                        <input type="hidden" name="payment_method" v-model="payment.method"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <button type="submit" class="btn btn-block btn-primary" v-bind:disabled="!isCompletable">Complete Sale</button>
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
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            data: {
                query: "",
                salesDiscount: 0,
                cart: {
                    products: [],
                    packages: []
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
                            && this.cart.packages.length === 0;
                },
                isCustomerSelected: function () {
                    return this.customer.hasOwnProperty('id');
                },
                isPaymentCompleted: function () {
                    if (this.payment.method === 'cash') {
                        return this.payment.amount >= this.grandTotal;
                    } else if (this.payment.method === 'credit_card') {
                        return this.payment.cardNumber;
                    }
                },
                isCompletable: function () {
                    return this.isCartEmpty === false
                            && this.isCustomerSelected
                            && this.isPaymentCompleted;
                },
                subTotal: function () {
                    var itemsTotal = 0,
                        $this = this;

                    this.cart.products.forEach(function (cartItem) {
                        itemsTotal += $this.calculateItemPrice(cartItem);
                    });

                    this.cart.packages.forEach(function (cartItem) {
                        itemsTotal += $this.calculateItemPrice(cartItem);
                    });

                    if ($this.isCustomerInGroup) {
                        itemsTotal = $this.applyDiscount(itemsTotal, this.customer.group.discount);
                    }

                    itemsTotal = $this.applyDiscount(itemsTotal, this.salesDiscount);

                    return itemsTotal;
                },
                grandTotal: function () {
                    var total = this.subTotal;

                    if (this.payment.method === 'credit_card') {
                        total = this.applyTax(this.subTotal, this.creditCardTax)
                    }

                    return total;
                },
                change: function () {
                    return Math.max(this.payment.amount - this.grandTotal, 0);
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
                notify: function (type, message) {
                    var fn = window.toastr[type];

                    fn(message);
                },
                viewPackage: function (packageId) {
                    var $this = this,
                        features = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes',
                        packageWindow =  window.open("/packages/" + packageId + "?external=1&intent=getPackage", "choose_package_window", features);

                    packageWindow.addEventListener("package-selected", function (event) {
                        $this.addPackageToCart(event.detail.package, 1, event.detail.availableQuantity);
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