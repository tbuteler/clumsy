@include('clumsy/cms::admin.templates.header')

<div class="container master">

    @section('heading')
    <h1 class="page-header first">
        <div class="row">
			<div class="col-sm-9">
	        @section('title')
	            {{ $title or '' }}
	        @show
	        </div>
			<div class="col-sm-3 after-title">
	        @yield('after-title')
			</div>
    	</div>
    </h1>
    @show

    @yield('before-content')

    <div class="clearfix">
        @yield('before')
        @yield('master')
        @yield('after')
    </div>

    @yield('after-content')

</div>

@include('clumsy/cms::admin.templates.footer')