<div class="bottom-buttons">
@section('bottom-buttons')
    {{ Form::button(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), array('type' => 'submit', 'class' => 'btn btn-lg btn-success submit-once')) }}
    @if ($item->exists && (!isset($suppress_delete) || !$suppress_delete))
        {{ Form::button('', array('type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete must-confirm btn btn-lg btn-default glyphicon glyphicon-trash')) }}
    @endif
    @foreach ($buttons as $button)
        {{ $button }}
    @endforeach
@show
</div>