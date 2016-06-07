<div class="bottom-buttons">
@section('bottom-buttons')
    {!! Form::button(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), ['type' => 'submit', 'class' => 'btn btn-lg btn-success submit-once']) !!}
    @include($view->resolve('delete-button'))
    @foreach ($buttons as $button)
        {!! $button !!}
    @endforeach
@show
</div>
