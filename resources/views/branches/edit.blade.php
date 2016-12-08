@extends('layouts.app')

@section('title')
    - Update Branch
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Update Branch - {{ $branch->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('branches.update', $branch->id) }}" class="form-horizontal">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: John Doe" required value="{{ old('name') ?: $branch->name }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label for="address" class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-5">
                                <textarea id="address" name="address" class="form-control" placeholder="Eg: Baker Street">{{ old('address') ?: $branch->address }}</textarea>
                                @foreach($errors->get('address') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('contact_person_name') ? 'has-error' : '' }}">
                            <label for="contact_person_name" class="col-sm-2 control-label">Contact Person Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="contact_person_name" name="contact_person_name" class="form-control" placeholder="Eg: John Doe" value="{{ old('contact_person_name') ?: $branch->contact_person_name }}" />
                                @foreach($errors->get('contact_person_name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('contact_person_phone') ? 'has-error' : '' }}">
                            <label for="contact_person_phone" class="col-sm-2 control-label">Contact Person Phone</label>
                            <div class="col-sm-5">
                                <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-control" placeholder="Eg: 123123123" value="{{ old('contact_person_phone') ?: $branch->contact_person_phone }}" />
                                @foreach($errors->get('contact_person_phone') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('cash_counters_count') ? 'has-error' : '' }}">
                            <label for="cash_counters_count" class="col-sm-2 control-label">Cash Counter Count</label>
                            <div class="col-sm-1">
                                <input type="number" id="cash_counters_count" name="cash_counters_count" class="form-control" placeholder="Eg: 2" value="{{ old('cash_counters_count') ?: $branch->cash_counters_count }}" />
                                @foreach($errors->get('cash_counters_count') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" class="btn btn-success btn-block">Update</button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ URL::previous() === route('branches.edit', $branch->id) ? route('branches.index') : URL::previous() }}" class="btn btn-danger btn-block">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection