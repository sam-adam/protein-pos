<div class="row">
    <div class="col-xs-7">
        @include('widgets.incomplete_delivery')
    </div>
    <div class="col-xs-5">
        <a href="{{ route('sales.create') }}" class="btn btn-block btn-lg btn-primary">
            <i class="fa fa-fw fa-cart-plus"></i>
            Start New Sale
        </a>
        <a href="{{ route('sales.create', ['type' => 'delivery']) }}" class="btn btn-block btn-lg btn-primary">
            <i class="fa fa-fw fa-cart-plus"></i>
            Book New Delivery
        </a>
        <a href="{{ route('shifts.viewOut', ['redirect-to' => url('logout')]) }}" class="btn btn-block btn-lg btn-warning">
            <i class="fa fa-fw fa-sign-out"></i>
            Clock Out
        </a>
    </div>
</div>
<div class="row">
    <div class="col-xs-7">
        @include('widgets.sales.simple')
    </div>
</div>
<div class="row">
    <div class="col-xs-6">
        @foreach($branches as $branch)
            @include('widgets.inventories.expiry_reminder')
        @endforeach
    </div>
    <div class="col-xs-6">
        @foreach($branches as $branch)
            @include('widgets.inventories.expired')
        @endforeach
    </div>
</div>