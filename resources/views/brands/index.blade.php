@extends('layouts.app')

@section('title')
    - Brands List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Brands List - Record {{ $brands->firstItem() }} to {{ $brands->lastItem() }} from {{ $brands->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td>{{ $brand->name }}</td>
                                    <td>
                                        <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ url('brands/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new brand
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $brands->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection