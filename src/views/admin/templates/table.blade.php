<?php if (!isset($properties)) $properties = array('title' => 'Title'); ?>

<div class="panel panel-default">

@if (count($items))

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            @foreach ($properties as $slug => $name)
                <th>{{ $name }}</th>
            @endforeach
            </thead>

            <tbody>
            @foreach ($items as $item)

                <tr>
                @foreach ($properties as $slug => $name)

                    <?php

                    $type = 'text';
                    if (strpos($slug, ':'))
                    {
                        list($slug, $type) = explode(':', $slug);
                    }
                    $value = $type === 'boolean' ? ($item->$slug == 1 ? 'Yes' : 'No') : $item->$slug;

                    ?>

                    <td>
                    @if (!isset($readonly) || !$readonly)
                    
                        <a href="{{ route("admin.$resource.edit", $item->id); }}">{{ $value }}</a>
                    
                    @else
                    
                        {{ $value }}
                    
                    @endif
                    </td>

                @endforeach
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

@else

    <div class="panel-body">
        <h5>{{ trans_choice('clumsy/cms::alerts.count', 0, array('resource' => $display_name, 'resources' => $display_name_plural)) }}</h5>
    </div>

@endif

</div>