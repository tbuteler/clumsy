const sweetalert = require('sweetalert')

sweetalert.setDefaults({
    showCancelButton: true,
    cancelButtonText: handover.admin.translations.cancel,
    closeOnConfirm: false,
    allowEscapeKey: true
});

var _alert = function(params) {
    return sweetalert($.extend(params, params.type === 'warning' ? {confirmButtonColor: '#d9534f'} : {}));
};

$(function(){

    var clickOnce = function($el) {
        $el.add($el.siblings(':button, :submit')).prop('disabled', true);
        $el.addClass('loading');
    };

    $('.click-once').click(function(){
        clickOnce($(this));
    });

    $('form').submit(function(){
        var $submit = $(':submit', this);
        if ($submit.hasClass('submit-once')) {
            clickOnce($submit);
        }
    });

    $('.delete').click(function(){
        $(this).closest('form').next('.delete-form').submit();
    });
    $('.delete-form').submit(function(){
        var $form = $(this).prev('form'),
            $del = $('.delete', $form),
            msg = $(this).hasClass('user') ? handover.admin.translations.confirmUser : handover.admin.translations.confirm,
            btn = $(this).hasClass('user') ? handover.admin.translations.removeUser : handover.admin.translations.remove;

        _alert({
            title: handover.admin.translations.alert,
            text: msg,
            type: "warning",
            confirmButtonText: btn
        },
        function(){
            clickOnce($del);
            return true;
        });
        return false;
    });

    var $pivots = $('.pivot-field');
    if ($pivots.length) {
        $pivots.chosen({
            width: '100%'
        });
    }

    $('.with-tooltip').tooltip();
    $('.navbar').on('show.bs.dropdown', function(event) {
        var $el = $(event.target);
        if ($el.hasClass('with-tooltip')){
            $el.tooltip('destroy');
            $el.one('hide.bs.dropdown', function() {
                $el.tooltip();
            });
        }
    });

    if ($('.rich-text').length) {
        tinymce.init($.extend({
            selector: ".rich-text",
            content_css: handover.admin.urls.base+'/../vendor/clumsy/cms/css/tinymce.css',
            menubar : false,
            toolbar: "undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link | removeformat",
            statusbar : false,
            plugins: "autoresize,link",
            language_url: handover.admin.locale !== 'en' ? handover.admin.urls.base+'/../vendor/clumsy/utils/js/tinymce/'+handover.admin.locale+'.min.js' : null
        }, typeof handover.admin.tinymce === 'undefined' ? {} : handover.admin.tinymce));
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

    if ($('.location').length){

        $('.location').each(function(){

            var $wrapper = $('.coordinates', this),
                map,
                marker = null,
                geocoder = new google.maps.Geocoder(),
                $lat = $wrapper.find('input').eq(0),
                $lng = $wrapper.find('input').eq(1),
                $geocoderInput = $('.geocoder-btn', this).closest('.form-group').find('input');

            var setMarker = function(LatLng, updateAddress) {
                if (marker === null) {
                    marker = new google.maps.Marker({
                        map: map,
                        draggable: true,
                        position: LatLng
                    });
                    map.panTo(marker.position);

                    google.maps.event.addListener(marker, 'dragend', function() {
                        updateMapInputs(marker.getPosition(), true);
                    });
                }
                else {
                    marker.setPosition(LatLng);
                    map.panTo(marker.position);
                }

                updateMapInputs(LatLng, updateAddress);
            };

            var updateMap = function(event) {
                setMarker(event.latLng);
            };

            var updateMapInputs = function(LatLng, updateAddress) {
                if (updateAddress) {
                    geocoder.geocode({
                        'location': LatLng
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                $geocoderInput.val(results[0].formatted_address);
                            }
                        }
                    });
                }

                $lat.val(LatLng.lat());
                $lng.val(LatLng.lng());
            };

            var updateMapFromAddress = function(address) {
                if (String(address).trim() !== '') {
                    geocoder.geocode({
                        'address': address
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            setMarker(results[0].geometry.location, false);
                        }
                    });
                }
            };

            var initializeMap = function() {
                var mapOptions = $.extend(
                    {
                        scrollwheel: false,
                        center: {
                            lat: 38.709792,
                            lng: -9.133609
                        },
                        zoom: 14
                    },
                    typeof handover.admin.mapOptions === 'undefined' ? {} : handover.admin.mapOptions
                );

                map = new google.maps.Map($wrapper.find('.map')[0], mapOptions);

                var lat = $lat.val();
                var lng = $lng.val();
                if (lat !== '' && lng !== '') {
                    var position = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
                    setMarker(position, (typeof $geocoderInput.attr('name') === 'undefined'));
                    map.setZoom(16);
                }

                google.maps.event.addListener(map, 'rightclick', updateMap);
            };

            google.maps.event.addDomListener(window, 'load', initializeMap);

            $('.drop-pin', this).click(function(){
                setMarker(map.getCenter(), true);
            });

            $('.geocoder-btn', this).click(function(){
                updateMapFromAddress($geocoderInput.val());
            });
        });
    }

    var $editableInline = $('.editable-inline');
    if ($editableInline.length) {
        $editableInline.click(function(e){
            e.stopPropagation();
            var data = {};
            data._method = 'PUT';
            data._token = $('input[name="_token"]').val();
            data[$(this).data('column')] = $(this).is(':checkbox') ? ($(this).prop('checked') ? 1 : 0) : $(this).val();
            $.post($(this).closest('[data-update-url]').data('update-url').replace(':id', $(this).data('id')), data);
        });
        $editableInline.closest('td').click(function(){
            $(this).find('.editable-inline').click();
        });
    }

    if ($('.filter-box').length) {
        var $filterSelects = $('.filter-box select'),
            chosenOptions = {
            width: '100%',
            no_results_text: $filterSelects.first().data('no-results-text')
        };

        $filterSelects.chosen(chosenOptions).change(function (e, data) {
            $('#filter-submit-btn').prop('disabled', false);
            if (typeof data.selected !== 'undefined') {
               $('#filter-form').append('<input type="hidden" name="'+$(this).data('name')+'[]" value="'+data.selected+'">');
               $(this).parents('.filter-box').find('button').prop('disabled', false);
               return true;
            }

            $('input[value="'+data.deselected+'"]').remove();
            if ($('input[name="'+$(this).data('name')+'[]"]').length === 0) {
                $(this).parents('.filter-box').find('button').prop('disabled', true);
            }
        });

        $('#filter-submit-btn').click(function(){
            $('#filter-form').submit();
        });

        $('#filter-clear-btn').click(function(){
            $('#filter-form input:not([name="_token"],.filter-nested)').remove();
            $('#filter-form').submit();
        });
    }

    $('a[role="tab"]').on('shown.bs.tab', function (e) {
        var tab_id = $(e.target).attr('href').substring(1);
        var $tab = $('div.tab-pane#' + tab_id);

        $tab.find('.photoset-row').attr('style','overflow: hidden;');
    });

    if ($('.reorder-table').length) {

        var reorder = $(".reorder-table tbody").sortable({
            helper: "clone",
            scroll: false,
            axis: "y",
            placeholder: "sortable-placeholder"

        }).disableSelection();

        reorder.on('sortupdate',function(event,ui){
            $('.reorder-table tbody > tr td:first-child').fadeOut().promise().done(function(){
                $('.reorder-table tbody > tr').each(function(index){
                    $(this).find(' > td:first').text(index + 1);
                }).promise().done(function(){
                    $('.reorder-table tbody > tr td:first-child').fadeIn();
                });
            });
        });
    }
});
