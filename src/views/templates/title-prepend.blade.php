@if (isset($backLink) && $backLink)
    <a href="{{ $backLink }}" class="with-tooltip material-icons title-btn" title="{{ trans('clumsy::buttons.back-to-index') }}">&#xE314;</a>
@endif
