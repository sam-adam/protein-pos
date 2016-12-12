@extends('layouts.app')

@section('title')
    - Categories List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <ul class="tree">
                        @foreach($roots as $root)
                            <li>
                                <div>
                                    <a href="{{ route('categories.edit', $root->id) }}" class="btn btn-primary btn-xs">
                                        <i class="fa fa-pencil"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('categories.destroy', $root->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure to delete this category?');">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fa fa-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                    {{ $root->name }}
                                </div>
                                @if($root->children->count() > 0)
                                    <ul>
                                        @foreach($root->children as $child)
                                            <li>
                                                <div>
                                                    <a href="{{ route('categories.edit', $child->id) }}" class="btn btn-primary btn-xs">
                                                        <i class="fa fa-pencil"></i>
                                                        Edit
                                                    </a>
                                                    <form method="post" action="{{ route('categories.destroy', $child->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure to delete this category?');">
                                                        {{ csrf_field() }}
                                                        {{ method_field('DELETE') }}
                                                        <button type="submit" class="btn btn-danger btn-xs">
                                                            <i class="fa fa-trash"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                    {{ $child->name }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ url('categories/create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Add new category
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection