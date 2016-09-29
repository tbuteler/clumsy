@if ($item->exists && !$suppressDelete)
    {!! Form::button('&#xE92B;', ['type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete must-confirm btn btn-lg btn-default btn-icon material-icons']) !!}
@endif
