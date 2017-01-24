<div class="row">
    <div class="col-xs-6">
        @include('widgets.incomplete_delivery')
    </div>
    <div class="col-xs-6">
        <a href="{{ route('sales.create') }}" class="btn btn-block btn-lg btn-primary">
            <i class="fa fa-fw fa-cart-plus"></i>
            Start New Sale
        </a>
    </div>
</div>