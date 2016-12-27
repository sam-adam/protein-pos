@extends('layouts.app')

@section('title')
    - Update Customer
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Update Customer - {{ $customer->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('customers.update', $customer->id) }}" class="form-horizontal">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: John Doe" required value="{{ old('name') ?: $customer->name }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                            <label for="phone" class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-5">
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="Eg: +1-881-556-0649" value="{{ old('phone') ?: $customer->phone }}" />
                                @foreach($errors->get('phone') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-5">
                                <input type="text" id="email" name="email" class="form-control" placeholder="Eg: john@doe.com" value="{{ old('email') ?: $customer->email }}" />
                                @foreach($errors->get('email') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label for="address" class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-5">
                                <textarea name="address" class="form-control" placeholder="Address">{{ old('address') ?: $customer->address }}</textarea>
                                @foreach($errors->get('address') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('customer_group_id') ? 'has-error' : '' }}">
                            <label for="customer-group-id" class="col-sm-2 control-label">Group</label>
                            <div class="col-sm-5">
                                <select id="customer-group-id" name="customer_group_id" class="form-control">
                                    <option value>Select Customer Group</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" @if((old('customer_group_id') ?: $customer->customer_group_id) == $group->id) selected @endif>{{ $group->name.' ('.$group->discount.'% discount)' }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('customer_group_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Update</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ Session::get('last_customer_page') ?: route('customers.index') }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection