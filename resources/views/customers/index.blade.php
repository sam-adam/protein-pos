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
                    <div id="search-panel">
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
                    </div>
                    <div id="action-panel" class="hidden">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="#bulk-change-group-modal" class="btn btn-primary btn-lg btn-block" data-toggle="modal">
                                    <i class="fa fa-arrow-right"></i>
                                    Bulk set group
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#bulk-delete-modal" class="btn btn-danger btn-lg btn-block" data-toggle="modal">
                                    <i class="fa fa-trash"></i>
                                    Bulk delete
                                </a>
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="mass-selector" />
                                </th>
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
                                    <td>
                                        <input type="checkbox" value="{{ $customer->id }}" class="customer-checkbox" />
                                    </td>
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
    <div class="modal fade" id="bulk-change-group-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-change-group-modal-label">
        <div class="modal-dialog" role="document">
            <form class="form-horizontal bulk-customer-action-form" method="post" action="{{ route('customers.bulk_change_group') }}">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="bulk-change-group-modal-label">Bulk Change Group</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group {{ $errors->has('customer_group_id') ? 'has-error' : '' }}">
                                    <label class="control-label col-sm-4">Select Group</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="customer_group_id">
                                            <option value>No Group</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}" @if(old('customer_group_id') == $group->id) selected @endif>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                        @foreach($errors->get('customer_group_id') as $error)
                                            <span class="label label-danger">{{ $error }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times fa-fw"></i>
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-floppy-o fa-fw"></i>
                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="bulk-delete-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-delete-modal-label">
        <div class="modal-dialog" role="document">
            <form class="form-horizontal bulk-customer-action-form" method="post" action="{{ route('customers.bulk_delete') }}">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="bulk-delete-modal-label">Bulk Delete Customer</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <p>Deleting multiple customers! Are you sure?</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times fa-fw"></i>
                            Close
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash fa-fw"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        $(document).ready(function () {
            var $selectors = $(".customer-checkbox"),
                $massSelector = $("#mass-selector"),
                $searchPanel = $("#search-panel"),
                $actionPanel = $("#action-panel"),
                $bulkCustomerForms = $(".bulk-customer-action-form"),
                customerSelectedToggleHandler = function () {
                    var isAnyCustomerSelected = false;

                    $selectors.filter(function () {
                        if ($(this).is(":checked")) {
                            isAnyCustomerSelected = true;
                        }
                    });

                    $searchPanel.toggleClass("hidden", isAnyCustomerSelected);
                    $actionPanel.toggleClass("hidden", !isAnyCustomerSelected);
                };

            $selectors.on("change", customerSelectedToggleHandler);
            $massSelector.on("change", function () {
                $selectors.prop("checked", $(this).is(":checked"));

                customerSelectedToggleHandler();
            });
            $bulkCustomerForms.on("submit", function () {
                var $form = $(this);

                $form.find(".customer-input-list").remove();

                $selectors.each(function () {
                    var $customer = $(this);

                    if ($customer.is(":checked")) {
                        $form.append($("<input />", {
                            "name": "customer_ids[]",
                            "type": "hidden",
                            "class": "customer-input-list"
                        }).val($customer.val()));
                    }
                });

                return true;
            });
        });
    </script>
@endsection