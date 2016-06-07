@if ($item->exists && !$suppressDelete)
    {!! Form::button('', ['type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete must-confirm btn btn-lg btn-default glyphicon glyphicon-trash', 'data-confirm-text' => trans('clumsy::alerts.user.delete-confirm')]) !!}
@endif
