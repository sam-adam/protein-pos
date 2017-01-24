<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protein POS @yield('title')</title>
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body class="{{ $fullScreen ? 'fullscreen' : '' }}">
    <div id="preloader" class="hidden">
        <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
    </div>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-static-top " role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ url('/') }}">Protein PoS</a>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>
                        {{ Auth::user()->name }} - {{ Auth::user()->branch->name }}
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="{{ url('logout') }}"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="{{ url('/') }}"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-fw fa-shopping-cart"></i>
                                Sales
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ url('sales') }}">
                                        List
                                    </a>
                                </li>
                                @can('create', \App\Models\Sale::class)
                                    <li>
                                        <a href="{{ url('sales/create') }}">
                                            Create - Walk In
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('sales/create?type=delivery') }}">
                                            Create - Delivery
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('sales/create?type=wholesale') }}">
                                            Create - Wholesale
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                        @can('access', \App\Models\User::class)
                            <li>
                                <a href="{{ url('users') }}">
                                    <i class="fa fa-fw fa-user-circle"></i>
                                    Users
                                </a>
                            </li>
                        @endcan
                        @can('access', \App\Models\Branch::class)
                            <li>
                                <a href="{{ url('branches') }}">
                                    <i class="fa fa-fw fa-building"></i>
                                    Branches
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="#">
                                <i class="fa fa-cube fa-fw"></i>
                                Inventories
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ url('products') }}">Products</a>
                                </li>
                                <li>
                                    <a href="{{ url('categories') }}">Categories</a>
                                </li>
                                <li>
                                    <a href="{{ url('brands') }}">Brands</a>
                                </li>
                                <li>
                                    <a href="{{ url('product-variants') }}">Product Variants</a>
                                </li>
                                <li>
                                    <a href="{{ url('packages') }}">Packages</a>
                                </li>
                                {{--<li>--}}
                                    {{--<a href="{{ url('inventory-movements') }}">Inventory Movements</a>--}}
                                {{--</li>--}}
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-fw fa-users"></i>
                                Customers
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ url('customers') }}">
                                        List
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('customer-groups') }}">
                                        Groups
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="{{ url('shifts') }}">
                                <i class="fa fa-fw fa-clock-o"></i>
                                Shifts
                            </a>
                        </li>
                        @can('access', \App\Models\Setting::class)
                            <li>
                                <a href="{{ url('settings') }}">
                                    <i class="fa fa-fw fa-gear"></i>
                                    Settings
                                </a>
                            </li>
                        @endcan
                        @can('access', \App\DataObjects\Report::class)
                            <li>
                                <a href="#">
                                    <i class="fa fa-fw fa-book"></i>
                                    Reports
                                    <span class="fa arrow"></span>
                                </a>
                                <ul class="nav nav-second-level">
                                    <li>
                                        <a href="{{ url('reports/sales') }}">
                                            Sales
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/stock') }}">
                                            Stock
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/product') }}">
                                            Product
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </nav>
        <div id="page-wrapper">
            <div id="app">
                @yield('content')
            </div>
        </div>
    </div>
    <script type="text/javascript">
        window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!};
    </script>
    <script type="text/javascript" src="{{ elixir('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>