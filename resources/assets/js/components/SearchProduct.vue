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
                        autofocus
                        v-model="query"
                        autocomplete="off"
                        @keydown.down="down"
                        @keydown.up="up"
                        @keydown.enter="hit"
                        @keydown.esc="reset"
                        @blur="reset"
                        @input="update" />
            </div>
        </div>
        <ul v-show="hasItems">
            <li v-for="(product, index) in items" :class="activeClass(index)" @mousedown="hit" @mousemove="setActive(index)">
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
        </ul>
    </div>
</template>

<script>
    import VueTypeahead from 'vue-typeahead'

    export default {
        extends: VueTypeahead,
        props: ['src', 'existingItems'],
        data () {
            return {
                queryParamName: 'query',
                selectFirst: true,
                limit: 5,
                minChars: 3
            }
        },
        methods: {
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
            onHit (product) {
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

                this.reset();
            },
            prepareResponseData (response) {
                if (response.method === 'barcode') {
                    this.onHit(response.products[0]);
                } else {
                    return response.products;
                }
            }
        }
    }
</script>
<style scoped>
    .typeahead {
        position: relative;
    }

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

    .active {
        background-color: #3aa373;
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