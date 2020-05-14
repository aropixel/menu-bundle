
$(document).ready(function() {


    // activate Nestable for list 1
    $('#menu').nestedSortable({
        maxLevels: max_level,
        handle: 'div',
        items: 'li.li-movable',
        toleranceElement: '> .dd-handle',
        forcePlaceholderSize: true,
        placeholder: 'placeholder',
        helper:	'clone',
    });

    $('#add-pages').click(function() {

        var checked_inputs = control_select($(this))

        if (checked_inputs) {

            checked_inputs.each(function() {

                var title = $(this).parent().find('span').html();
                var line_properties = {}
                line_properties.label = 'Page générale';
                line_properties.color = 'bg-pink';
                line_properties.title = title;
                line_properties.originalTitle = title;
                line_properties.static = $(this).attr('data-type') === 'static' ? $(this).attr('value') : '';
                line_properties.page = $(this).attr('data-type') === 'page' ? $(this).attr('value') : '';
                line_properties.link = '';
                line_properties.type = 'page';

                if (!is_strict_mode() || !is_included(line_properties)) {
                    add_line(line_properties);
                }
                else {

                    //
                    var _buttons = {
                        "Fermer": function() {

                            $(this).closest('.modal').modal('hide');

                        }
                    }

                    modalDyn('Désolé', '<strong>Ce lien est déjà dans la liste.</strong><br />Vous ne pouvez pas l\'insérer qu\'une seule fois.', _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
                    return;
                }

            });
            checked_inputs.removeAttr('checked');
        }

    });


    $('#add-link').click(function() {

        var label = $(this).closest('.card').find('input[name="manual_label"]');
        var link = $(this).closest('.card').find('input[name="manual_link"]');

        var line_properties = {}
        line_properties.label = 'Lien manuel';
        line_properties.color = 'bg-teal';
        line_properties.title = label.val();
        line_properties.originalTitle = 'Element de menu vide';
        line_properties.static = '';
        line_properties.page = '';
        line_properties.link = link.val();
        line_properties.type = 'link';


        add_line(line_properties);
        label.val('');
        link.val('');

    });


    $('#add-section').click(function() {

        var section = $(this).closest('.card').find('input[name="manual_section"]');

        var line_properties = {}
        line_properties.label = 'Section';
        line_properties.color = 'bg-dark-grey';
        line_properties.title = section.val();
        line_properties.originalTitle = 'Section';
        line_properties.static = '';
        line_properties.page = '';
        line_properties.link = '';
        line_properties.type = 'section';


        add_line(line_properties);
        section.val('');

    });


    $('#panelMenu').on('click', '.deleteRow', function() {

        var line = $(this).closest('li[data-title]');
        if (line.attr('data-static')) {

            if (line.attr('data-required') === '1') {

                var remain = false;
                $('#menu li').each(function() {

                    if ($(this).not(line) && $(this).attr('data-static') == line.attr('data-static')) {
                        var remain = true;
                    }

                });

                if (!remain) {

                    //
                    var _buttons = {
                        "Fermer": function() {

                            $(this).closest('.modal').modal('hide');

                        }
                    }

                    modalDyn('Désolé', '<strong>Désolé, ce lien est obligatoire.</strong><br />Vous ne pouvez pas le supprimer.', _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
                    return;
                }

            }
            var checkbox = $('#panel-pages .panel-body input[value="'+line.attr('data-static')+'"]');
            checkbox.removeAttr('disabled');
        }

        if (line.attr('data-page')) {

            var checkbox = $('#add-pages').closest('.panel').find('input[value="'+line.attr('data-page')+'"]');
            checkbox.removeAttr('disabled');
        }
        line.fadeOut('fast', function() { $(this).remove() })
    });


    $('#save_menu').click(function() {

        //
        $('#panelMenu').block({
            message: '<i class="icon-spinner4 spinner"></i>',
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                cursor: 'wait'
            },
            css: {
                border: 0,
                padding: 0,
                backgroundColor: 'none'
            }
        });

        //
        var type = $(this).data('type');
        var name = $(this).data('name');
        var url = $(this).data('url');
        var params = serialize();

        $.post(url, {'type':type, 'name':name, 'menu': params}, function() {

            $('#panelMenu').unblock();

        });

    })



    function is_strict_mode() {
        return $('#add-pages').attr('data-strict') == '1';
    }



    function is_included(line_properties) {

        if (line_properties.page.length) {
            return $('[data-page="'+line_properties.page+'"]').length;
        }

        if (line_properties.static.length) {
            return $('[data-static="'+line_properties.static+'"]').length;
        }

        return false;
    }



    function control_select(button) {

        var checked = button.closest('.card').find('input:checked');
        if (!checked.length) {
            $('#modal_please_select').modal('show');
            return false;
        }

        return checked;
    }


    function serialize()
    {
        var data,
            depth = 0,
            list  = this;
        step  = function(level, depth)
        {
            var array = [ ],
                items = level.children('li');

            items.each(function()
            {
                var li   = $(this),
                    item = {},
                    sub  = li.children('ol');
                item.data = $.extend({}, li.data());
                delete item.data["nestedSortableItem"];
                delete item.data["nestedSortable-item"];
                delete item.data["sortableItem"];
                delete item.data["sortable-item"];
                if (sub.length) {
                    item.children = step(sub, depth + 1);
                }
                array.push(item);
            });

            return array;
        };
        data = step($( "#menu" ), depth);
        return data;
    }




    function add_line(line_properties) {

        var hostname = '';
        if (line_properties.link) {
            hostname = $('<a>').prop('href', line_properties.link).prop('hostname');
        }

        var template = $('#template_row').html();
        var new_line = $(template);
        new_line.attr('data-static', line_properties.static);
        new_line.attr('data-page', line_properties.page);
        new_line.attr('data-link', line_properties.link);
        new_line.attr('data-title', line_properties.title);
        new_line.attr('data-original-title', line_properties.originalTitle);
        new_line.attr('data-type', line_properties.type);

        new_line.find('.title').html(line_properties.title);
        new_line.find('.link').html(line_properties.link ? '<a href="'+line_properties.link+'" target="_blank">'+hostname+'</a>' : line_properties.originalTitle);
        new_line.find('.cell-label').html('<span class="badge '+line_properties.color+'">'+line_properties.label+'</span>');

        if (line_properties.static) {
            var checkbox = $('#panel-pages .panel-body input[value="'+line_properties.static+'"]');
            checkbox.attr('disabled', 'disabled');
        }

        $(new_line).appendTo('#menu');
    }




    $('#modal_edit').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var line = button.closest('li[data-title]');
        var modal = $(this);
        $('#valid_edit').data('line', line);


        var title = line.attr('data-title');
        var original_title = line.attr('data-original-title');
        var link = line.attr('data-link');
        var label = line.find('.badge').html();

        var type = line.attr('data-type');
        var static_key = line.attr('data-static');
        var page_id = line.attr('data-page');


        modal.find('.modal-body input[name="item_label"]').val(title);

        var input_link = modal.find('.modal-body input[name="item_link"]');
        if (static_key || page_id || (type=='section' && link.length==0)) {
            input_link.attr('disabled', 'disabled');
            input_link.val(label + ' : ' + original_title);
        }
        else {
            input_link.removeAttr('disabled');
            input_link.val(link);
        }

    });

    $('input[name="item_label"], input[name="item_link"]').keyup(function(e) {

        var code = (e.keyCode ? e.keyCode : e.which);
        if (code==13) {
            $('#valid_edit').trigger('click');
        }
        if (code==27) {
            $('#modal_edit').modal('hide');
        }

    })

    $('#valid_edit').click(function() {

        var line = $('#valid_edit').data('line');
        var modal = $('#modal_edit');

        var title = modal.find('.modal-body input[name="item_label"]').val();
        var linkInput = modal.find('.modal-body input[name="item_link"]');

        line.attr('data-title', title);
        if (!linkInput.is(':disabled')) {
            line.attr('data-link', linkInput.val());
        }

        line.find('> div .title').html(title);
        modal.modal('hide');
    });

});
