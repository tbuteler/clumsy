@include('clumsy::templates.head')

<div class="container master">

    @section('breadcrumb')
        @include($view->resolve('breadcrumb'))
    @show

    @section('page-header')
        @include($view->resolve('page-header'))
    @show

    @yield('before-content')

    <div class="main-content clearfix">
        @yield('before')
        @yield('master')
        @yield('after')
    </div>

    @yield('after-content')

</div>

@include('clumsy::templates.footer')