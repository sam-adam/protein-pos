<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protein POS @yield('title')</title>
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="preloader" class="hidden">
        <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
    </div>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
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
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
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
                            <a href="{{ url('users') }}">
                                <i class="fa fa-fw fa-users"></i>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('branches') }}">
                                <i class="fa fa-fw fa-building"></i>
                                Branches
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('brands') }}">
                                <i class="fa fa-fw fa-tags"></i>
                                Brands
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('categories') }}">
                                <i class="fa fa-fw fa-folder"></i>
                                Categories
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('products') }}">
                                <i class="fa fa-fw fa-cube"></i>
                                Products
                            </a>
                        </li>
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
    <script type="text/javascript" src="{{ elixir('js/app.js') }}"></script>
</body>
</html>