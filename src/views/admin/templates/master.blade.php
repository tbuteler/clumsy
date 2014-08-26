@include('clumsy/cms::admin.templates.header')

<div class="container master">

    <h1 class="page-header first">

        {{ $title or '' }}

        @yield('after-title')
    
    </h1>

    @yield('before-content')

    <div class="clearfix">
    	@yield('before')
    	@yield('master')
    	@yield('after')
    </div>

    @yield('after-content')

</div>

@include('clumsy/cms::admin.templates.footer')