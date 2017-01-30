@extends('layouts.app')

@section('title')
    - Products Report
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
                <button type="submit" class="btn btn-block btn-primary">Submit</button>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    @if($product)
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label">Name</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static">{{ $product->name }}</p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label">Created At</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static">{{ $product->created_at->toFormattedDateString() }}</p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label">Quantity Sold</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static">{{ number_format($totalSold) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <product-performance-chart :data="this.performance" :options="{maintainAspectRatio: false, responsive: true, showLines: true, borderColor: 'black'}"></product-performance-chart>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <span class="label label-primary">No product selected</span>
                                </div>
                            </div>
                        </div>
                    @endif
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
            data: {
                performance: {!! json_encode($chart) !!},
                productId: "{{ $productId }}"
            },
            mounted: function () {
                var $date = $('.daterange');

                $date.daterangepicker({
                    format: 'YYYY-MM-DD',
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
            }
        });
    </script>
@endsection