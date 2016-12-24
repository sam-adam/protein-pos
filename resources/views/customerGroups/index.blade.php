@extends('layouts.app')

@section('title')
    - Customer Groups List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Customer Groups List - Record {{ $groups->firstItem() }} to {{ $groups->lastItem() }} from {{ $groups->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Discount (%)</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->discount }}%</td>
                                    <td>
                                        <a href="{{ route('customer-groups.edit', $group->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <form method="post" action="{{ route('customer-groups.destroy', $group->id) }}" style="display: inline;" onsubmit="return confirm('Deleting group! Are you sure?');">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ route('customer-groups.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new customer group
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $groups->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection