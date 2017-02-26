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