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

                            if (strpos($slug, ':')) {

                                list($slug, $type) = explode(':', $slug);
                            }

                            $value = $type === 'boolean' ? ($item->$slug == 1 ? 'Yes' : 'No') : $item->$slug;

                            ?>

                            <td><a href="{{ URL::route("admin.$resource.edit", $item->id); }}">{{ $value }}</a></td>

                        @endforeach
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>

    @else

        <div class="panel-body">
            <h5>{{ sprintf('No %s found.', str_replace('_', ' ', str_plural($resource))) }}</h5>
        </div>

    @endif

</div>