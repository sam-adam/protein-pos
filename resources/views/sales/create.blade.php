@extends('layouts.app')

@section('title')
    - Create Sales
@endsection

@section('content')
    @parent
    <div class="row" id="app">
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-body" id="search-panel">
                    <div class="form-group form-group-lg">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-fw fa-search"></i></div>
                            <input type="text" id="search-input" placeholder="Input product name, code, or scan barcode" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body" id="products-panel">
                    <table class="table table-hover">
                        <thead>
                            <tr class="register-items-header">
                                <th></th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Qty.</th>
                                <th>Disc %</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            methods: {
                findByBarcode: function(query) {

                }
            }
        });
    </script>
@endsection