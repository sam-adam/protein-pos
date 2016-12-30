@extends('layouts.app')

@section('title')
    - Shifts List
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Shifts List - Record {{ $shifts->firstItem() }} to {{ $shifts->lastItem() }} from {{ $shifts->total() }} total records
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Started</th>
                                    <th>Closed</th>
                                    <th>Starting Cash</th>
                                    <th>Closing Cash</th>
                                    <th>Remark</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shifts as $shift)
                                    <tr class="{{ $shift->isSuspended() ? 'danger' : '' }}">
                                        <td>{{ $shift->branch->name }}</td>
                                        <td>
                                            <div>
                                                <i class="fa fa-user fa-fw"></i>
                                                {{ $shift->openedBy->name }}
                                            </div>
                                            <div>
                                                <i class="fa fa-clock-o fa-fw"></i>
                                                {{ $shift->opened_at->toDayDateTimeString() }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($shift->isClosed())
                                                <div>
                                                    <i class="fa fa-user fa-fw"></i>
                                                    {{ $shift->closedBy->name }}
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o fa-fw"></i>
                                                    {{ $shift->closed_at->toDayDateTimeString() }}
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ number_format($shift->opened_cash_balance) }}</td>
                                        <td>
                                            @if($shift->isClosed())
                                                {{ number_format($shift->closed_cash_balance) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $shift->remark }}</td>
                                        <td>
                                            @if(!$shift->isClosed())
                                                <a href="#clock-out-modal" class="btn btn-danger btn-sm" data-toggle="modal" data-shift-id="{{ $shift->id }}" data-opened-at="{{ $shift->opened_at->toDayDateTimeString() }}" data-opened-cash="{{ number_format($shift->opened_cash_balance) }}">
                                                    <i class="fa fa-fw fa-exclamation-circle"></i>
                                                    Clock out
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 text-right">
                            {{ $shifts->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="clock-out-modal" tabindex="-1" role="dialog" aria-labelledby="clock-out-modal-label">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('shifts.out', $shift->id) }}" class="form-horizontal">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="add-inventory-modal-label">Clock Out</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Opened at</label>
                            <div class="col-sm-8">
                                <p class="form-control-static" id="opened-at"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Opened cash</label>
                            <div class="col-sm-5">
                                <p class="form-control-static" id="opened-cash"></p>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('closing_balance') ? 'has-error' : '' }}">
                            <label for="closing-balance" class="col-sm-4 control-label">Closing cash</label>
                            <div class="col-sm-5">
                                <input type="text" id="closing-balance" name="closing_balance" class="form-control" placeholder="Eg: 100" value="{{ old('closing_balance') }}" required />
                                @foreach($errors->get('closing_balance') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('remark') ? 'has-error' : '' }}">
                            <label for="remark" class="col-sm-4 control-label">Remark</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="remark" id="remark" required>{{ old('remark') }}</textarea>
                                @foreach($errors->get('remark') as $error)
                                    <span class="label label-danger">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times fa-fw"></i>
                            Close
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i>
                            Clock out
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
            var $clockOutModal = $("#clock-out-modal"),
                clockOutRoute = "{{ route('shifts.out', 'PLACEHOLDER') }}";

            $clockOutModal.on("show.bs.modal", function (ev) {
                var $this = $(this),
                    $button = $(ev.relatedTarget);

                $this.find("#opened-at").text($button.data("openedAt"));
                $this.find("#opened-cash").text($button.data("openedCash"));
                $this.find("form").attr("action", clockOutRoute.replace("PLACEHOLDER", $button.data("shiftId")));
            })
        });
    </script>
@endsection