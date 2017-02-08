@extends('layouts.app')

@section('title')
    - Packages List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Packages List - Record {{ $packages->firstItem() }} to {{ $packages->lastItem() }} from {{ $packages->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Customizable ?</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($packages as $package)
                                <tr>
                                    <td>{{ $package->id }}</td>
                                    <td>
                                        {{ $package->name }}
                                        <br/>
                                        <br/>
                                        <table class="table table-condensed table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($package->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product->name }}</td>
                                                        <td>{{ number_format($item->quantity) }}</td>
                                                    </tr>
                                                @endforeach
                                                @foreach($package->variants as $variant)
                                                    <tr>
                                                        <td>{{ $variant->variant->name }}</td>
                                                        <td>{{ number_format($variant->variant->quantity) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>@money($package->price)</td>
                                    <td class="text-{{ $package->is_customizable ? 'success' : 'danger' }}"><i class="fa fa-{{ $package->is_customizable ? 'check' : 'times' }}"></i></td>
                                    <td>
                                        <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <a href="{{ route('packages.show', $package->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i>
                                            View
                                        </a>
                                        <form method="post" action="{{ route('packages.destroy', $package->id) }}" style="display: inline;" onsubmit="return confirm('Deleting package! Are you sure?');">
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
                            <a href="{{ url('packages/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new package
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $packages->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection