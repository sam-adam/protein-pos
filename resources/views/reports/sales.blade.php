@extends('layouts.app')

@section('title')
    - Sales Report
@endsection

@section('content')
    @parent
    <form>
        <div class="row">
            <div class="col-sm-2">
                <select name="branch" class="form-control">
                    <option value>Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @if($branchId == $branch->id) selected @endif>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input class="form-control daterange" value="{{ $from->toDateString() }} - {{ $to->toDateString() }}" />
                    <input name="from" value="{{ $from->timestamp }}" type="hidden" />
                    <input name="to" value="{{ $to->timestamp }}" type="hidden" />
                </div>
            </div>
            <div class="col-sm-2">
                <select name="type" class="form-control">
                    <option value>All Type</option>
                    <option value="walkin" @if($type === 'walkin') selected @endif>Walk In</option>
                    <option value="delivery" @if($type === 'delivery') selected @endif>Delivery</option>
                </select>
            </div>
            <div class="col-sm-3">
                <select name="mode" class="form-control">
                    <option @if($mode == 'daily') selected @endif value="daily">Daily</option>
                    <option @if($mode == 'weekly') selected @endif value="weekly">Weekly</option>
                    <option @if($mode == 'monthly') selected @endif value="monthly">Monthly</option>
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
                    <div class="panel-body">
                        @if($mode === 'weekly' || $mode === 'monthly')
                            @include('reports.components.sales.weekly')
                        @else
                            @include('reports.components.sales.daily')
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
            $("input[name='to']").val(picker.endDate.unix();
        });
    </script>
@endsection