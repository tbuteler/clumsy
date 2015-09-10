@if (isset($back_link) && $back_link)
    <a href="{{ $back_link }}" class="with-tooltip glyphicon glyphicon-chevron-left title-btn" title="{{ trans('clumsy::buttons.back-to-index') }}"></a>
@endif