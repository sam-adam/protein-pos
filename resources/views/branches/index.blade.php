@extends('layouts.app')

@section('title')
    - Branches List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Branches List - Record {{ $branches->firstItem() }} to {{ $branches->lastItem() }} from {{ $branches->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>CP Name</th>
                                <th>CP Phone</th>
                                <th># Cash Counters</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($branches as $branch)
                                <tr>
                                    <td>{{ $branch->id }}</td>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->address }}</td>
                                    <td>{{ $branch->contact_person_name }}</td>
                                    <td>{{ $branch->contact_person_phone }}</td>
                                    <td>{{ $branch->cash_counters_count }}</td>
                                    <td>
                                        @if($branch->isLicensed())
                                            <span class="label label-success">Licensed at {{ $branch->licensed_at->toDayDateTimeString() }}</span>
                                        @else
                                            <span class="label label-danger">Unlicensed</span>
                                        @endif
                                        @if($branch->isActive())
                                            <span class="label label-success">Activated at {{ $branch->activated_at->toDayDateTimeString() }}</span>
                                        @else
                                            <span class="label label-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        @if(!$branch->isLicensed())
                                            <form method="post" action="{{ route('branches.license', $branch->id) }}">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fa fa-flash"></i>
                                                    License
                                                </button>
                                            </form>
                                        @endif
                                        @if($branch->isLicensed() && !$branch->isActive())
                                            <form method="post" action="{{ route('branches.activate', $branch->id) }}">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i>
                                                    Activate
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ url('branches/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new branch
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $branches->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection