<template>
    <div class="typeahead">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-fw fa-spinner fa-spin" v-if="loading"></i>
                    <template v-else>
                        <i class="fa fa-fw fa-search" v-show="!isDirty"></i>
                        <i class="fa fa-fw fa-times" v-show="isDirty" @click="reset"></i>
                    </template>
                </div>
                <input type="text"
                        id="query"
                        placeholder="Input product name, code, or scan barcode"
                        class="form-control"
                        v-bind:autofocus="autofocus"
                        v-model="query"
                        autocomplete="off"
                        @keydown.down="down"
                        @keydown.up="up"
                        @keydown.enter.prevent="hit"
                        @keydown.esc="reset"
                        @blur="reset"
                        @focus="prepareInput"
                        @input="update" />
            </div>
        </div>
        <ul v-show="hasItems">
            <li v-for="(product, index) in items.products" :class="activeClass('products', index)" @mousedown="hit" @mousemove="setActive('products', index)">
                <div class="row">
                    <div class="col-xs-10">
                        <div class="name">
                            {{ product.name }}
                            <span v-show="product.stock === 0" class="label label-danger">
                                <i class="fa fa-fw fa-exclamation-circle"></i>
                                Out of stock
                            </span>
                        </div>
                        <div class="screen-name">{{ product.brand ? product.brand.name : 'Not branded' }}</div>
                        <div class="screen-name">
                            Price: {{ product.price }}, Category: {{ product.category ? product.category.name : 'Uncategorized' }}
                        </div>
                    </div>
                    <div class="col-xs-2">
                        <div class="stock text-center">
                            {{ calculateAvailable(product) }}
                        </div>
                        <div class="stock-message text-center">item(s) avail.</div>
                    </div>
                </div>
            </li>
            <li class="divider" v-if="hasPackages && hasProducts"></li>
            <li v-for="(package, index) in items.packages" :class="activeClass('packages', index)" @mousedown="hit" @mousemove="setActive('packages', index)">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="name">
                            {{ package.name }}
                            <span v-show="!package.canBeSold" class="label label-danger">
                                <i class="fa fa-fw fa-exclamation-circle"></i>
                                Out of stock
                            </span>
                        </div>
                        <div class="screen-name">{{ package.label }}</div>
                        <div class="screen-name">Price: {{ package.price }}</div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
    import VueTypeahead from 'vue-typeahead'

    export default {
        extends: VueTypeahead,
        props: {
            src: {},
            existingItems: {},
            includePackage: {"default": false},
            showLastResult: {},
            autofocus: {"default": true},
            initialValue: {"default": null}
        },
        computed: {
            hasProducts () {
                return this.items.hasOwnProperty("products") && this.items.products.length > 0;
            },
            hasPackages () {
                return this.items.hasOwnProperty("packages") && this.items.packages.length > 0;
            },
            hasItems () {
                return this.hasProducts || this.hasPackages;
            }
        },
        data () {
            return {
                queryParamName: 'query',
                selectFirst: true,
                minChars: 3,
                lastSelectedResult: null
            }
        },
        mounted: function () {
            if (this.initialValue) {
                this.lastSelectedResult = {name: this.initialValue};
            }

            this.reset();
        },
        methods: {
            setActive (list, index) {
                this.current = {
                    list: list,
                    index: index
                }
            },
            activeClass (list, index) {
                return {
                    [list]: true,
                    active: this.current.list === list && this.current.index === index
                }
            },
            calculateAvailable (product) {
                var inCartQuantity = 0;

                if (Array.isArray(this.existingItems)) {
                    this.existingItems.forEach(function (item) {
                        if (item.product.id === product.id) {
                            inCartQuantity = item.quantity;
                        }
                    });
                }

                return product.stock - inCartQuantity;
            },
            hit () {
                if (this.current !== -1) {
                    this.onHit(this.current.list, this.items[this.current.list][this.current.index]);
                }
            },
            onHit (list, item) {
                this.lastSelectedList = list;
                this.lastSelectedResult = item;

                if (this.lastSelectedList === 'products') {
                    var product = this.lastSelectedResult;

                    if (product.stock > 0) {
                        this.$emit('product-selected', {
                            product: product,
                            availableQuantity: product.stock
                        });
                    } else if (product.stock === 0) {
                        this.$emit('insufficient-stock', {
                            product: product,
                            remark: "Out of stock"
                        })
                    }
                } else {
                    var packageObj = this.lastSelectedResult;

                    if (packageObj.canBeSold) {
                        this.$emit('package-selected', {
                            "package": packageObj
                        });
                    } else {
                        this.$emit('insufficient-stock', {
                            "package": packageObj,
                            remark: "Out of stock"
                        })
                    }
                }
            },
            prepareResponseData (response) {
                if (response.method === 'barcode') {
                    this.onHit(response.products[0]);
                } else {
                    return response;
                }
            },
            reset () {
                if (this.lastSelectedResult && this.showLastResult) {
                    this.query = this.lastSelectedResult.name;
                } else {
                    this.query = "";
                }

                this.items = [];
                this.loading = false;
            },
            prepareInput () {
                this.query = "";
            }
        }
    }
</script>
<style scoped>
    ul {
        position: absolute;
        padding: 0;
        margin-top: 8px;
        min-width: 100%;
        background-color: #fff;
        list-style: none;
        border-radius: 4px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);
        z-index: 1000;
    }

    li {
        color: #333333;
        padding: 10px 16px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }

    li:first-child {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    li:last-child {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        border-bottom: 0;
    }

    .active.products {
        background-color: #3aa373;
    }

    .active.packages {
        background-color: #4c64a3;
    }

    .active div {
        color: white;
    }

    .name {
        font-weight: 700;
        font-size: 18px;
    }

    .screen-name {
        font-style: italic;
    }

    .stock {
        font-size: 30px;
    }

    .stock-message {
        font-style: italic;
    }
</style>