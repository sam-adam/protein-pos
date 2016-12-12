@extends('layouts.app')

@section('title')
    - Create New User
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New User
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('users.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: John Doe" required value="{{ old('name') }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                            <label for="username" class="col-sm-2 control-label">Username</label>
                            <div class="col-sm-5">
                                <input type="text" id="username" name="username" class="form-control" placeholder="Eg: john_doe" required value="{{ old('username') }}" />
                                @foreach($errors->get('username') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="password" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-5">
                                <input type="text" id="password" name="password" class="form-control" placeholder="Eg: lJq124!MMv" required value="{{ old('password') }}" />
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
                                        <option value="{{ $role }}" @if(old('role') === $role) selected @endif>{{ title_case($role) }}</option>
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
                                        <option value="{{ $branch->id }}" @if(old('branch_id') == $branch->id) selected @endif>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('branch_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('can_give_discount') ? 'has-error' : '' }}">
                            <div class="col-sm-offset-2 col-sm-5">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="can_give_discount" value="1" @if(old('can_give_discount')) checked @endif> Can give discount?
                                    </label>
                                </div>
                                @foreach($errors->get('can_give_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('can_give_unlimited_discount') ? 'has-error' : '' }}">
                            <div class="col-sm-offset-2 col-sm-5">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="can_give_unlimited_discount" value="1" @if(old('can_give_unlimited_discount')) checked @endif> Can give unlimited discount?
                                    </label>
                                </div>
                                @foreach($errors->get('can_give_unlimited_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('max_price_discount') || $errors->has('max_percentage_discount') ? 'has-error' : '' }}">
                            <label for="max_price_discount" class="col-sm-2 control-label">Max Discount (Price)</label>
                            <div class="col-sm-2">
                                <input type="text" name="max_price_discount" id="max_price_discount" class="form-control" placeholder="Eg: 1000" value="{{ old('max_price_discount') }}" />
                                @foreach($errors->get('max_price_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                            <label for="max_percentage_discount" class="col-sm-1 control-label">Percentage</label>
                            <div class="col-sm-2">
                                <input type="text" name="max_percentage_discount" id="max_percentage_discount" class="form-control" placeholder="0 - 100" value="{{ old('max_percentage_discount') }}" />
                                @foreach($errors->get('max_percentage_discount') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Save</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ URL::previous() }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection