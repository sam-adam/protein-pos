@extends('layouts.app')

@section('title')
    - Settings
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal" method="post" action="{{ route('settings.update') }}">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('credit_card_tax') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-3" for="credit-card-tax">Credit Card Tax (%)</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="credit_card_tax" value="{{ old('credit_card_tax') ?: $creditCardTax }}" placeholder="In percent" />
                                @foreach($errors->get('credit_card_tax') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('sales_point_baseline') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-3" for="sales-point-baseline">Sales Point Earning Baseline</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="sales_point_baseline" value="{{ old('sales_point_baseline') ?: $salesPointBaseline }}" placeholder="How much a customer need to purchase for a point" />
                                @foreach($errors->get('sales_point_baseline') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('delivery_product_id') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-3" for="Delivery Product Id">Delivery Service</label>
                            <div class="col-sm-3">
                                <select name="delivery_product_id" class="form-control">
                                    <option value>Select Product</option>
                                    @foreach($serviceProducts as $serviceProduct)
                                        <option value="{{ $serviceProduct->id }}" @if((old('delivery_product_id') ?: $deliveryProductId) == $serviceProduct->id) selected @endif>{{ $serviceProduct->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('delivery_product_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('walk_in_customer_id') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-3" for="Delivery Product Id">Walk In Customer</label>
                            <div class="col-sm-3">
                                <select name="walk_in_customer_id" class="form-control">
                                    <option value>Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" @if((old('walk_in_customer_id') ?: $walkInCustomerId) == $customer->id) selected @endif>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('walk_in_customer_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <button type="submit" class="btn btn-success btn-lg btn-block">
                                    <i class="fa fa-disk"></i>
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection