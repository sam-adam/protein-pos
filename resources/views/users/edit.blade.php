@extends('layouts.app')

@section('title')
    - Update User
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Update User - {{ $user->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('users.update', $user->id) }}" class="form-horizontal">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                            <label for="username" class="col-sm-2 control-label">Username</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $user->username }}</p>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="username" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-5">
                                <input type="text" id="password" name="password" class="form-control" placeholder="Leave empty on no change" value="{{ old('password') }}" />
                                @foreach($errors->get('password') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                            <label for="role" class="col-sm-2 control-label">Role</label>
                            <div class="col-sm-5">
                                <select class="form-control" id="role" name="role" required>
                                    <option value>Select Role</option>
                                    @foreach(['cashier', 'manager', 'admin', 'tech_admin'] as $role)
                                        <option value="{{ $role }}" @if((old('role') ?: $user->role) === $role) selected @endif>{{ title_case($role) }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('role') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('branch_id') ? 'has-error' : '' }}">
                            <label for="branch" class="col-sm-2 control-label">Branch</label>
                            <div class="col-sm-5">
                                <select class="form-control" id="branch" name="branch_id" required>
                                    <option value>Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @if((old('branch_id') ?: $user->branch_id) == $branch->id) selected @endif>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('branch_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('minimum_discount') || $errors->has('minimum_discount_type') ? 'has-error' : '' }}">
                            <label for="minimum_discount" class="col-sm-2 control-label">Min Discount</label>
                            <div class="col-sm-2">
                                <input type="text" name="minimum_discount" id="minimum_discount" class="form-control" placeholder="Min discount limit" value="{{ old('minimum_discount') ?: $user->minimum_discount }}" />
                                @foreach($errors->get('minimum_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control" id="minimum_discount_type" name="minimum_discount_type">
                                    <option value>Discount Type</option>
                                    @foreach(['percent', 'price'] as $type)
                                        <option value="{{ $type }}" @if(old('minimum_discount_type') ?: $user->minimum_discount_type === $type) selected @endif>{{ title_case($type) }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('minimum_discount_type') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('maximum_discount') || $errors->has('maximum_discount_type') ? 'has-error' : '' }}">
                            <label for="maximum_discount" class="col-sm-2 control-label">Max Discount</label>
                            <div class="col-sm-2">
                                <input type="text" name="maximum_discount" id="maximum_discount" class="form-control" placeholder="Max discount limit" value="{{ old('maximum_discount') ?: $user->maximum_discount }}" />
                                @foreach($errors->get('maximum_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control" id="maximum_discount_type" name="maximum_discount_type">
                                    <option value>Discount Type</option>
                                    @foreach(['percent', 'price'] as $type)
                                        <option value="{{ $type }}" @if(old('maximum_discount_type') ?: $user->maximum_discount_type === $type) selected @endif>{{ title_case($type) }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('maximum_discount_type') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Update</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ URL::previous() === route('users.edit', $user->id) ? route('users.index') : URL::previous() }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection