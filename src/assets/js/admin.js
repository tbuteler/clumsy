$(function(){
    $('.delete').click(function(e){
        $(this).closest('form').next('.delete-form').submit();
    });
    $('.delete-form').submit(function(e){
        return $(this).hasClass('user') ? confirm(handover.admin.strings.delete_confirm_user) : confirm(handover.admin.strings.delete_confirm);
    });

    if ($('.rich-text').length) {
        tinymce.init($.extend(
        {
            selector: ".rich-text",
            content_css: handover.admin.urls.base+'/../packages/clumsy/cms/css/tinymce.css',
            menubar : false,
            toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat",
            statusbar : false,
            plugins: "autoresize"
        },
        typeof handover.admin.tinymce === 'undefined' ? {} : handover.admin.tinymce
        ));
    }

    // If a translatable panel pane has an error that prevents saving, switch to it
    if ($('.panel-translatable').length && $('.panel-translatable .has-error').length) {
        $('.tab-pane').each(function(i,el){
            if ($('.has-error', el).length) {
                var target = $(el).attr('id');
                $('a[href="#'+target+'"], a[data-target="'+target+'"]').tab('show');
                return false;
            }
        });
    }

    if ($('.datepicker').length) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
    }

    if ($('.datetimepicker').length) {
        $('.datetimepicker').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss'
        });
    }

    if ($('.timepicker').length) {
        $('.timepicker').timepicker({
            timeFormat: 'HH:mm:ss'
        });
    }

    if ($('.colorpicker').length) {
        $('.colorpicker').iris(
            $.extend(
                {
                    hide: true,
                },
                typeof handover.admin.colorpicker === 'undefined' ? {} : handover.admin.colorpicker
            )
        );

        $(document).on('click',function(e){
            if ($(e.target).attr('class') == 'form-control colorpicker') {
                $(e.target).iris('show');
            }
            else{
                var container = $('.iris-picker, .iris-picker-inner');
                if (typeof e === 'undefined' || (!container.is(e.target) && container.has(e.target).length === 0)){
                    $('.colorpicker').iris('hide');
                }
            }
        });
    }

    $booleans = $('.active-boolean');
    if ($booleans.length) {
        $booleans.click(function(e){
            e.stopPropagation();
            $.post(handover.admin.urls.update,
            {
                _token: $('input[name="_token"]').val(),
                model: handover.admin.model,
                id: $(this).data('id'),
                column: $(this).data('column'),
                column_type: 'boolean',
                value: $(this).prop('checked')
            });
        });
        $booleans.closest('td').click(function(){
            $(this).find('.active-boolean').click();
        });
    }
});