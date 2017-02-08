@extends('layouts.app')

@section('title')
    - Product Variants List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Product Variants List - Record {{ $productVariants->firstItem() }} to {{ $productVariants->lastItem() }} from {{ $productVariants->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th class="text-right">Quantity</th>
                                    <th>Products</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($productVariants as $productVariant)
                                <tr>
                                    <td>{{ $productVariant->id }}</td>
                                    <td>{{ $productVariant->name }}</td>
                                    <td class="text-right">{{ $productVariant->quantity }}</td>
                                    <td>
                                        @if($productVariant->items->count() > 0)
                                            <ul>
                                                @foreach($productVariant->items as $variantItem)
                                                    <li>
                                                        <a href="{{ route('products.show', $variantItem->product->id) }}">{{ $variantItem->product->name }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('product-variants.edit', $productVariant->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <form method="post" action="{{ route('product-variants.destroy', $productVariant->id) }}" style="display: inline;" onsubmit="return confirm('Deleting variant! Are you sure?');">
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
                            <a href="{{ route('product-variants.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new variant
                            </a>
                        </div>
                        <div class="col-xs-6 text-right">
                            {{ $productVariants->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection