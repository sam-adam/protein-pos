<template>
    <div class="typeahead">
        <div class="form-group form-group-lg">
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
            <li v-for="(inventory, index) in items" :class="activeClass(index)" @mousedown="hit" @mousemove="setActive(index)">
                <div class="row">
                    <div class="col-xs-10">
                        <div class="name">
                            {{ inventory.actualProduct.name }}
                            <span v-show="!inventory.canBeSold" class="label label-danger">
                                <i class="fa fa-fw fa-exclamation-circle"></i>
                                {{ inventory.remark }}
                            </span>
                        </div>
                        <div class="screen-name">{{ inventory.actualProduct.brand ? inventory.actualProduct.brand.name : 'Not branded' }}</div>
                        <div class="screen-name">
                            Price: {{ inventory.actualProduct.price }}, Category: {{ inventory.actualProduct.category ? inventory.actualProduct.category.name : 'Uncategorized' }}
                        </div>
                    </div>
                    <div class="col-xs-2">
                        <div class="stock text-center">
                            {{ inventory.availableStock }}
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
        props: ['src'],
        data () {
            return {
                queryParamName: 'query',
                selectFirst: true,
                limit: 5,
                minChars: 3
            }
        },
        methods: {
            onHit (inventory) {
                if (inventory.canBeSold) {
                    this.$emit('product-selected', inventory);
                }
            },
            prepareResponseData (response) {
                if (response.hasOwnProperty('items')) {
                    return response.items;
                }

                return response;
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