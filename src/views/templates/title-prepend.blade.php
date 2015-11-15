@if (isset($backLink) && $backLink)
    <a href="{{ $backLink }}" class="with-tooltip glyphicon glyphicon-chevron-left title-btn" title="{{ trans('clumsy::buttons.back-to-index') }}"></a>
@endif