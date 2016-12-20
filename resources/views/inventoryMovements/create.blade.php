@extends('layouts.app')

@section('title')
    - Create New Movement
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New Movement
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('inventory-movements.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="control-label col-sm-4">From Branch</label>
                            <div class="col-sm-8">
                                <p class="form-control-static">{{ Auth::user()->branch->name }}</p>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('branch_id') ? 'has-error' : '' }}">
                            <label class="control-label col-sm-4" for="branch-id">To Branch</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="branch_id" id="branch-id" required>
                                    <option value @if(old('branch_id') === null) selected @endif>Select Branch</option>
                                    @foreach($otherBranches as $otherBranch)
                                        <option value="{{ $otherBranch->id }}" @if(old('branch_id') == $otherBranch->id) selected @endif>{{ $otherBranch->name }}</option>
                                    @endforeach
                                </select>
                                @foreach($errors->get('branch_id') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection