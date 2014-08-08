$(function(){
    $('.delete-form').submit(function(e){
        return confirm(handover.admin.delete_confirm);
    });

    if ($('.rich-text').length) {
        tinymce.init({
            selector: ".rich-text",
            content_css: handover.admin.base_url+'/../packages/clumsy/cms/css/tinymce.css',
            menubar : false,
            toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist",
            statusbar : false
        });
    }

    $('#is_video_radio input').change(function(){
        var id = '#'+($(this).val() === '1' ? 'is_video' : 'is_not_video');
        $('.tab-pane').removeClass('active');
        $(id).addClass('active')
            .find('.fileupload')
            .mediaBox('update');
    });
});