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
                    <ul>
                        @foreach($roots as $root)
                            <li>
                                <div>
                                    {{ $root->name }}
                                    <a href="{{ route('categories.edit', $root->id) }}" class="btn btn-primary btn-xs">
                                        <i class="fa fa-pencil"></i>
                                        Edit
                                    </a>
                                </div>
                                @if($root->children->count() > 0)
                                    <ul>
                                        @foreach($root->children as $child)
                                            <li>
                                                <div>
                                                    {{ $child->name }}
                                                    <a href="{{ route('categories.edit', $child->id) }}" class="btn btn-primary btn-xs">
                                                        <i class="fa fa-pencil"></i>
                                                        Edit
                                                    </a>
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