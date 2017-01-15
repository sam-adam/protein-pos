@extends('layouts.app')

@section('title')
    - Packages Detail
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Package - {{ $package->name }}
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('packages.store') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ $package->name }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="col-sm-2 control-label">Price</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ number_format($package->price) }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="col-sm-2 control-label">Actual Price</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">{{ number_format($package->getActualPrice()) }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-2 control-label">Is Customizable</label>
                            <div class="col-sm-5">
                                <p class="form-control-static">
                                    @if($package->is_customizable)
                                        <i class="fa fa-check"></i>
                                    @else
                                        <i class="fa fa-times"></i>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="price" class="col-sm-2 control-label">Products</label>
                            <div class="col-sm-7">
                                <table class="table table-condensed table-middle table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($package->items as $item)
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ number_format($item->product->price) }}</td>
                                                <td style="width: 20px;">{{ number_format($item->quantity) }}</td>
                                                <td>{{ number_format($stocks[$item->product->id]) }}</td>
                                            </tr>
                                            @if($package->is_customizable && $item->product->variantGroup)
                                                @foreach($item->product->variantGroup->products as $variant)
                                                    @if($variant->id !== $item->product_id)
                                                        <tr>
                                                            <td colspan="4">
                                                                <strong>Variants</strong>
                                                                <table class="table table-condensed">
                                                                    <tbody>
                                                                        <td></td>
                                                                        <td>{{ $variant->name }}</td>
                                                                        <td>{{ number_format($variant->price) }}</td>
                                                                        <td style="width: 20px;">{{ number_format($item->quantity) }}</td>
                                                                        <td>{{ number_format($stocks[$variant->id]) }}</td>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-3">
                                <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-primary btn-block">
                                    <i class="fa fa-fw fa-pencil"></i>
                                    Edit
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ Session::get('last_package_page') ?: route('packages.index') }}" class="btn btn-default btn-block">
                                    <i class="fa fa-arrow-left fa-fw"></i>
                                    Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection