@extends('layouts.app')

@section('title')
    - Customer List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Customer List - Record {{ $customers->firstItem() }} to {{ $customers->lastItem() }} from {{ $customers->total() }} total records
                </div>
                <div class="panel-body">
                    <form method="get">
                        <div class="row">
                            <div class="form-group form-group-lg">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-fw fa-search"></i>
                                        </div>
                                        <input type="text" id="query" class="form-control" name="query" placeholder="Input customer name, phone, or address" value="{{ Request::get('query') }}" >
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" name="group">
                                        <option value>Select Group</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" @if(Request::get('group') == $group->id) selected @endif>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                                <i class="fa fa-fw fa-search"></i>
                                                Search
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('customers.index') }}" class="btn btn-danger btn-lg btn-block">
                                                <i class="fa fa-fw fa-times"></i>
                                                Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <br/>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                @foreach($headers as $name => $header)
                                    <th>
                                        {{ $header['label'] }}
                                        @if($orderBy === $name)
                                            <a href="{{ $header['url'] }}"><i class="fa fa-fw fa-sort-{{ $orderDir === 'asc' ? 'desc' : 'asc' }}"></i></a>
                                        @else
                                            <a href="{{ $header['url'] }}"><i class="fa fa-fw fa-sort"></i></a>
                                        @endif
                                    </th>
                                @endforeach
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->address }}</td>
                                    <td>{{ $customer->group ? $customer->group->name.' ('.$customer->group->discount.'%)' : '' }}</td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-default btn-sm">
                                            <i class="fa fa-eye"></i>
                                            Show
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <form method="post" action="{{ route('customers.destroy', $customer->id) }}" style="display: inline;" onsubmit="return confirm('Deleting group! Are you sure?');">
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
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new customer
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $customers->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection