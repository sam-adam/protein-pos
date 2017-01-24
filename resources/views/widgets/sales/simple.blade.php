<div class="panel panel-default">
    <div class="panel-heading">Today Sales Summary</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="text-center">Cash</h4>
                <h3 class="text-center">
                    <span class="text-primary">{{ number_format($salesSummary['cash']) }}</span> AED
                </h3>
            </div>
            <div class="col-sm-6">
                <h4 class="text-center">Credit</h4>
                <h3 class="text-center">
                    <span class="text-primary">{{ number_format($salesSummary['credit']) }}</span> AED
                </h3>
            </div>
        </div>
    </div>
</div>