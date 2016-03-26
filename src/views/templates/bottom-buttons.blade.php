<div class="bottom-buttons">
@section('bottom-buttons')
    {!! Form::button(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), ['type' => 'submit', 'class' => 'btn btn-lg btn-success submit-once']) !!}
    @if ($item->exists && !$suppressDelete)
        {!! Form::button('', ['type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete must-confirm btn btn-lg btn-default glyphicon glyphicon-trash']) !!}
    @endif
    @foreach ($buttons as $button)
        {!! $button !!}
    @endforeach
@show
</div>
