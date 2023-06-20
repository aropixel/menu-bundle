import {ModalDyn} from '../../aropixeladmin/js/module/modal-dyn/modal-dyn.js';

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

    $('.add-input-ressource').click(function() {

        let checked_inputs = control_select($(this))

        if (checked_inputs) {

            checked_inputs.each(function() {

                let title = $(this).parent().find('span').html();

                let line_properties = {}
                line_properties.label = $(this).attr('data-label');
                line_properties.color = $(this).attr('data-color');
                line_properties.title = title;
                line_properties.originalTitle = title;
                line_properties.static = $(this).attr('data-type') === 'static' ? $(this).attr('value') : '';
                line_properties.page = $(this).attr('data-type') === 'page' ? $(this).attr('value') : '';
                line_properties.category = $(this).attr('data-type') === 'category' ? $(this).attr('value') : '';
                line_properties.selection = $(this).attr('data-type') === 'selection' ? $(this).attr('value') : '';
                line_properties.link = '';
                line_properties.type = $(this).attr('data-ressourceType');

                if (!is_strict_mode() || !is_included(line_properties)) {
                    add_line(line_properties);
                }
                else {

                    let _buttons = {
                        "Fermer": {

                            'class' : 'btn-default',
                            'callback' : function() {
                                $(this).closest('.modal').modal('hide');
                            }

                        }
                    }

                    new ModalDyn('Désolé', '<strong>Ce lien est déjà dans la liste.</strong><br />Vous ne pouvez pas l\'insérer qu\'une seule fois.', _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
                    return;
                }

            });
            checked_inputs.removeAttr('checked');
        }

    });


    $('#add-link').click(function() {

        let label = $(this).closest('.card').find('input[name="manual_label"]');
        let link = $(this).closest('.card').find('input[name="manual_link"]');

        let line_properties = {}
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

        let section = $(this).closest('.card').find('input[name="manual_section"]');

        let line_properties = {}
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

        let line = $(this).closest('li[data-title]');
        if (line.attr('data-static')) {

            if (line.attr('data-required') === '1') {

                let remain = false;
                $('#menu li').each(function() {

                    if ($(this).not(line) && $(this).attr('data-static') == line.attr('data-static')) {
                        let remain = true;
                    }

                });

                if (!remain) {

                    let _buttons = {
                        "Fermer": {

                            'class' : 'btn-default',
                            'callback' : function() {
                                $(this).closest('.modal').modal('hide');
                            }

                        }
                    }

                    new ModalDyn(required_title, required_message, _buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
                    return;
                }

            }
            let checkbox = $('#panel-pages .panel-body input[value="'+line.attr('data-static')+'"]');
            checkbox.removeAttr('disabled');
        }

        if (line.attr('data-page')) {

            let checkbox = $('.add-input-ressource').closest('.panel').find('input[value="'+line.attr('data-page')+'"]');
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
        let type = $(this).data('type');
        let name = $(this).data('name');
        let url = $(this).data('url');
        let params = serialize();

        $.post(url, {'type':type, 'name':name, 'menu': params}, function() {

            $('#panelMenu').unblock();

            let _buttons = {
                "Fermer": {

                    'class' : 'btn-default',
                    'callback' : function() {
                        $(this).closest('.modal').modal('hide');
                    }

                }
            }
            new ModalDyn('Le menu a bien été enregistré !', 'Vous pouvez continuer à modifier votre menu.', _buttons, {modalClass: 'modal_mini', headerClass: 'bg-success'});


        });

    })



    function is_strict_mode() {
        return $('.add-input-ressource').attr('data-strict') == '1';
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

        let checked = button.closest('.card').find('input:checked');
        if (!checked.length) {
            $('#modal_please_select').modal('show');
            return false;
        }

        return checked;
    }


    function serialize()
    {
        let data,
            depth = 0,
            list  = this;
        let step = function(level, depth)
        {
            let array = [ ],
                items = level.children('li');

            items.each(function()
            {
                let li   = $(this),
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

        let hostname = '';
        if (line_properties.link) {
            hostname = $('<a>').prop('href', line_properties.link).prop('hostname');
        }

        let template = $('#template_row').html();
        let new_line = $(template);
        new_line.attr('data-static', line_properties.static);
        new_line.attr('data-page', line_properties.page);
        new_line.attr('data-category', line_properties.category);
        new_line.attr('data-selection', line_properties.selection);
        new_line.attr('data-link', line_properties.link);
        new_line.attr('data-title', line_properties.title);
        new_line.attr('data-original-title', line_properties.originalTitle);
        new_line.attr('data-type', line_properties.type);

        new_line.find('.title').html(line_properties.title);
        new_line.find('.link').html(line_properties.link ? '<a href="'+line_properties.link+'" target="_blank">'+hostname+'</a>' : line_properties.originalTitle);
        new_line.find('.cell-label').html('<span class="badge '+line_properties.color+'">'+line_properties.label+'</span>');

        if (line_properties.static) {
            let checkbox = $('#panel-pages .panel-body input[value="'+line_properties.static+'"]');
            checkbox.attr('disabled', 'disabled');
        }

        $(new_line).appendTo('#menu');
    }




    $('#modal_edit').on('show.bs.modal', function (event) {

        let button = $(event.relatedTarget);
        let line = button.closest('li[data-title]');
        let modal = $(this);
        $('#valid_edit').data('line', line);


        let title = line.attr('data-title');
        let original_title = line.attr('data-original-title');
        let link = line.attr('data-link');
        let label = line.find('.badge').html();

        let type = line.attr('data-type');
        let static_key = line.attr('data-static');
        let page_id = line.attr('data-page');


        modal.find('.modal-body input[name="item_label"]').val(title);

        let input_link = modal.find('.modal-body input[name="item_link"]');
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

        let code = (e.keyCode ? e.keyCode : e.which);
        if (code==13) {
            $('#valid_edit').trigger('click');
        }
        if (code==27) {
            $('#modal_edit').modal('hide');
        }

    })

    $('#valid_edit').click(function() {

        let line = $('#valid_edit').data('line');
        let modal = $('#modal_edit');

        let title = modal.find('.modal-body input[name="item_label"]').val();
        let linkInput = modal.find('.modal-body input[name="item_link"]');

        line.attr('data-title', title);
        if (!linkInput.is(':disabled')) {
            line.attr('data-link', linkInput.val());
        }

        line.find('> div .title').html(title);
        modal.modal('hide');
    });

});
