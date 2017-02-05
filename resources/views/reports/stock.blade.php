@extends('layouts.app')

@section('title')
    - Stocks Report
@endsection

@section('content')
    @parent
    <form id="app" v-cloak>
        <div class="row">
            <div class="col-sm-2">
                <select name="branch" class="form-control">
                    <option value>Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @if($branchId == $branch->id) selected @endif>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-7">
                <div class="row">
                    <div class="col-sm-6">
                        <search-product
                                src="{{ route('products.xhr.search') }}"
                                :show-last-result="true"
                                :initial-value="'{{ $product ? $product->name : '' }}'"
                                :autofocus="false"
                                v-on:product-selected="productId = $event.product.id"
                                v-on:insufficient-stock="productId = $event.product.id"
                        ></search-product>
                        <input type="hidden" name="product" v-model="productId" />
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                            <input class="form-control daterange" value="{{ $from->toDateString() }} - {{ $to->toDateString() }}" />
                            <input name="from" value="{{ $from->timestamp }}" type="hidden" />
                            <input name="to" value="{{ $to->timestamp }}" type="hidden" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-1">
                <select name="mode" class="form-control">
                    <option value="daily" @if($mode == 'daily') selected @endif>Daily</option>
                    <option value="weekly" @if($mode == 'weekly') selected @endif>Weekly</option>
                    <option value="monthly" @if($mode == 'monthly') selected @endif>Monthly</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('reports.stock') }}" class="btn btn-danger">
                    <i class="fa fa-times"></i>
                    Reset
                </a>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <div class="panel-body">
                        @if($mode === 'weekly' || $mode === 'monthly')
                            @include('reports.components.stocks.weekly', ['branchId' => $branchId, 'movements' => $movements])
                        @else
                            @include('reports.components.stocks.daily', ['branchId' => $branchId, 'movements' => $movements])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    @parent
    <script type="text/javascript">
        var app = new Vue({
            el: "#app",
            mounted: function () {
                var $date = $('.daterange');

                $date.daterangepicker({
                    autoApply: true,
                    autoUpdateInput: true,
                    alwaysShowCalendars: true,
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                    startDate: '{{ $from->toDateString() }}',
                    endDate: '{{ $to->toDateString() }}',
                    maxDate: '{{ \Carbon\Carbon::now()->toDateString() }}',
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                });
                $date.on('apply.daterangepicker', function(ev, picker) {
                    $("input[name='from']").val(picker.startDate.unix());
                    $("input[name='to']").val(picker.endDate.unix());
                });
            },
            data: {
                productId: "{{ $productId }}"
            }
        });
    </script>
@endsection