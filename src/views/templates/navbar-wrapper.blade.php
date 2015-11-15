<nav class="navbar navbar-default" role="navigation">
    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">{{ trans('clumsy::buttons.toggle_navbar') }}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            @include($navbarHome)
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

            @include($navbar)

            @include($navbarButtons)

        </div>
    </div>
</nav>