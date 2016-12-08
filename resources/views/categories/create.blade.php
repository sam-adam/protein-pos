@extends('layouts.app')

@section('title')
    - Create New Category
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New Category
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('categories.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <input type="text" id="name" name="name" class="form-control" placeholder="Eg: Drinks" required value="{{ old('name') }}" />
                                @foreach($errors->get('name') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('parent_id') ? 'has-error' : '' }}">
                            <label for="parent_id" class="col-sm-2 control-label">Root Category (Optional)</label>
                            <div class="col-sm-5">
                                <select class="form-control" id="parent_id" name="parent_id">
                                    <option value>Select Parent</option>
                                    @foreach($roots as $root)
                                        <option value="{{ $root->id }}" @if(old('parent_id') === $root->id) selected @endif>{{ $root->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('parent_id') as $error)
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