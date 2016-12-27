@extends('layouts.app')

@section('title')
    - View Customer
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Customer - {{ $customer->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('customers.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->name }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->phone }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address" class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->address }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="points" class="col-sm-2 control-label">Points</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->points }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="customer-group-id" class="col-sm-2 control-label">Group</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $customer->group ? $customer->group->name.' ('.$customer->group->discount.'% discount)' : '-' }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary btn-block">
                                    <i class="fa fa-fw fa-pencil"></i>
                                    Edit
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ Session::get('last_customer_page') ?: route('customers.index') }}" class="btn btn-default btn-block">
                                    <i class="fa fa-arrow-left fa-fw"></i>
                                    Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection