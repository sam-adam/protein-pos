<template>
    <div class="typeahead">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-fw fa-spinner fa-spin" v-if="loading"></i>
                    <template v-else>
                        <i class="fa fa-fw fa-user" v-show="!isDirty"></i>
                        <i class="fa fa-fw fa-times" v-show="isDirty" @click="reset"></i>
                    </template>
                </div>
                <input type="text"
                        id="query"
                        placeholder="Input customer name or email"
                        class="form-control"
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
            <li v-for="(customer, index) in items" :class="activeClass(index)" @mousedown="hit" @mousemove="setActive(index)">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="name">
                            {{ customer.name }}
                            <span class="label label-success" v-if="customer.group">
                                <i class="fa fa-star"></i>
                                {{ customer.group.label }}
                            </span>
                        </div>
                        <div class="screen-name">
                            <i class="fa fa-phone"></i> {{ customer.phone || "-" }} &nbsp; <i class="fa fa-envelope"></i> {{ customer.email }}
                        </div>
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
            onHit (customer) {
                this.$emit('customer-selected', customer);
            },
            prepareResponseData (response) {
                return response.customers;
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