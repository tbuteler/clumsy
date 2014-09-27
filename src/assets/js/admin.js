$(function(){
    $('.delete').click(function(e){
        $(this).closest('form').next('.delete-form').submit();
    });
    $('.delete-form').submit(function(e){
        return $(this).hasClass('user') ? confirm(handover.admin.delete_confirm_user) : confirm(handover.admin.delete_confirm);
    });

    if ($('.rich-text').length) {
        tinymce.init($.extend(
        {
            selector: ".rich-text",
            content_css: handover.admin.base_url+'/../packages/clumsy/cms/css/tinymce.css',
            menubar : false,
            toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist",
            statusbar : false,
            plugins: "autoresize"
        },
        typeof handover.admin.tinymce === 'undefined' ? {} : handover.admin.tinymce
        ));
    }

    $('#is_video_radio input').change(function(){
        var id = '#'+($(this).val() === '1' ? 'is_video' : 'is_not_video');
        $('.tab-pane').removeClass('active');
        $(id).addClass('active')
            .find('.fileupload')
            .mediaBox('update');
    });

    if ($('.active-boolean').length) {
        $booleans = $('.active-boolean');
        $booleans.click(function(e){
            e.stopPropagation();
            $.post(handover.admin.update_url,
            {
                _token: $('input[name="_token"]').val(),
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