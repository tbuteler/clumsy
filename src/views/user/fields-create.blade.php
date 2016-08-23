<div class="row">

    <div class="col-md-12">

        <div class="row">
            <div class="col-md-6">
                @field('name', trans('clumsy::fields.name'))
            </div>
            <div class="col-md-6">
                @field('email', trans('clumsy::fields.email'))
            </div>
        </div>

        @if ($item->isGroupable())
            @dropdown('group_ids', trans('clumsy::fields.role'), ['options' => $groups])
        @endif

        <h3 class="page-header">@lang('clumsy::fields.password')</h3>

        <div class="row">
            <div class="col-md-6">
                @password('password', trans('clumsy::fields.password'), 'type:password')
            </div>
            <div class="col-md-6">
                @password('password_confirmation', trans('clumsy::fields.password_confirmation'), 'type:password')
            </div>
        </div>

    </div>

    @hidden('last_login', Carbon\Carbon::now())

</div>
