import { Controller } from '@hotwired/stimulus';
import { ModalDyn } from '/bundles/aropixeladmin/js/module/modal-dyn/modal-dyn.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["menu", "saveButton", "modalEdit", "itemLabel", "itemLink", "validEdit", "manualLinkLabel", "manualLinkUrl", "sectionLabel"];
    static values = {
        maxLevel: Number,
        url: String,
        type: String,
        name: String,
        requiredTitle: String,
        requiredMessage: String,
        strictMode: Boolean
    };

    connect() {
        this.initializeSortable();
    }

    initializeSortable() {
        $(this.menuTarget).nestedSortable({
            maxLevels: this.maxLevelValue,
            handle: 'div',
            items: 'li.li-movable',
            toleranceElement: '> .dd-handle',
            forcePlaceholderSize: true,
            placeholder: 'placeholder',
            helper: 'clone',
        });
    }

    addInputResource(event) {
        const button = event.currentTarget;
        const card = button.closest('.card');
        const checkedInputs = card.querySelectorAll('input[type="checkbox"]:checked');
        const manualLinkLabelInput = card.querySelector('[data-aropixel-menu-target="manualLinkLabel"]');
        const manualLinkUrlInput = card.querySelector('[data-aropixel-menu-target="manualLinkUrl"]');
        const sectionLabelInput = card.querySelector('[data-aropixel-menu-target="sectionLabel"]');
        const hiddenInfo = card.querySelector('input[type="hidden"]');

        if (checkedInputs.length > 0) {
            checkedInputs.forEach(input => {
                const title = input.closest('.custom-control').querySelector('span').innerHTML;
                const lineProperties = {
                    label: input.dataset.label,
                    color: input.dataset.color,
                    title: title,
                    originalTitle: title,
                    type: input.dataset.source,
                    payload: {
                        'type': input.dataset.type,
                        'value': input.value
                    }
                };

                if (!this.strictModeValue || !this.isIncluded(lineProperties)) {
                    this.addLine(lineProperties);
                } else {
                    this.showAlreadyIncludedAlert();
                }
                input.checked = false;
            });
        } else if (manualLinkUrlInput && manualLinkUrlInput.value) {
            const label = manualLinkLabelInput.value || manualLinkUrlInput.value;
            const link = manualLinkUrlInput.value;
            const lineProperties = {
                label: 'Lien manuel',
                color: hiddenInfo.dataset.color,
                title: label,
                originalTitle: 'Lien manuel',
                type: 'link',
                payload: {
                    'link': link
                }
            };
            this.addLine(lineProperties);
            manualLinkLabelInput.value = '';
            manualLinkUrlInput.value = '';
        } else if (sectionLabelInput && sectionLabelInput.value) {
            const label = sectionLabelInput.value;
            const lineProperties = {
                label: 'Section',
                color: hiddenInfo.dataset.color,
                title: label,
                originalTitle: 'Section',
                type: 'section',
                payload: {}
            };
            this.addLine(lineProperties);
            sectionLabelInput.value = '';
        } else {
            $('#modal_please_select').modal('show');
        }
    }

    showAlreadyIncludedAlert() {
        const buttons = {
            "Fermer": {
                'class': 'btn-default',
                'callback': function() {
                    $(this).closest('.modal').modal('hide');
                }
            }
        };
        new ModalDyn('Désolé', '<strong>Ce lien est déjà dans la liste.</strong><br />Vous ne pouvez pas l\'insérer qu\'une seule fois.', buttons, {modalClass: 'modal_mini', headerClass: 'bg-danger'});
    }

    deleteRow(event) {
        const line = event.currentTarget.closest('li.li-movable');
        $(line).fadeOut('fast', () => { line.remove(); });
    }

    saveMenu() {
        const panelMenu = this.element.querySelector('#panelMenu');
        $(panelMenu).block({
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

        const params = this.serialize();

        $.post(this.urlValue, {
            'type': this.typeValue,
            'name': this.nameValue,
            'menu': params
        }, () => {
            $(panelMenu).unblock();
            const buttons = {
                "Fermer": {
                    'class': 'btn-default',
                    'callback': function() {
                        $(this).closest('.modal').modal('hide');
                    }
                }
            };
            new ModalDyn('Le menu a bien été enregistré !', 'Vous pouvez continuer à modifier votre menu.', buttons, {modalClass: 'modal_mini', headerClass: 'bg-success'});
        });
    }

    isIncluded(lineProperties) {
        const items = this.menuTarget.querySelectorAll(`li.li-movable[data-type="${lineProperties.type}"]`);
        for (const item of items) {
            const payload = JSON.parse(item.dataset.payload || '{}');
            if (JSON.stringify(payload) === JSON.stringify(lineProperties.payload)) {
                return true;
            }
        }
        return false;
    }

    serialize() {
        const step = (level) => {
            const array = [];
            const items = $(level).children('li');

            items.each(function() {
                const li = $(this);
                const item = {};
                const sub = li.children('ol');
                
                // Copy all data attributes
                item.data = { ...li.data() };
                
                // Remove internal nestedSortable properties
                delete item.data["nestedSortableItem"];
                delete item.data["nestedSortable-item"];
                delete item.data["sortableItem"];
                delete item.data["sortable-item"];

                if (sub.length) {
                    item.children = step(sub);
                }
                array.push(item);
            });

            return array;
        };

        return step($(this.menuTarget));
    }

    addLine(lineProperties) {
        let subtitle = '';
        if (lineProperties.type === 'link') {
            const a = document.createElement('a');
            a.href = lineProperties.payload.link;
            subtitle = `<a href="${lineProperties.payload.link}" target="_blank">${a.hostname}</a>`;
        } else {
            subtitle = lineProperties.originalTitle;
        }

        const template = document.getElementById('template_row').innerHTML;
        const div = document.createElement('div');
        div.innerHTML = template.trim();
        const newLine = div.firstChild;

        newLine.setAttribute('data-type', lineProperties.type);
        newLine.setAttribute('data-payload', JSON.stringify(lineProperties.payload));
        newLine.setAttribute('data-title', lineProperties.title);
        newLine.setAttribute('data-original-title', lineProperties.originalTitle);

        newLine.querySelector('.title').innerHTML = lineProperties.title;
        newLine.querySelector('.link').innerHTML = subtitle;
        newLine.querySelector('.cell-label').innerHTML = `<span class="badge ${lineProperties.color}">${lineProperties.label}</span>`;

        this.menuTarget.appendChild(newLine);
    }

    openEditModal(event) {
        const button = event.currentTarget;
        const line = button.closest('li.li-movable');
        
        this.currentItem = line;

        const title = line.getAttribute('data-title');
        const originalTitle = line.getAttribute('data-original-title');
        const payload = JSON.parse(line.getAttribute('data-payload') || '{}');
        const label = line.querySelector('.badge').innerHTML;
        const type = line.getAttribute('data-type');

        this.itemLabelTarget.value = title;

        if (type !== 'link') {
            this.itemLinkTarget.setAttribute('disabled', 'disabled');
            this.itemLinkTarget.value = `${label} : ${originalTitle}`;
        } else {
            this.itemLinkTarget.removeAttribute('disabled');
            this.itemLinkTarget.value = payload.link;
        }

        $(this.modalEditTarget).modal('show');
    }

    submitEdit() {
        const title = this.itemLabelTarget.value;
        const line = this.currentItem;

        line.setAttribute('data-title', title);
        
        if (!this.itemLinkTarget.disabled) {
            const payload = JSON.parse(line.getAttribute('data-payload') || '{}');
            payload.link = this.itemLinkTarget.value;
            line.setAttribute('data-payload', JSON.stringify(payload));
        }

        line.querySelector('.title').innerHTML = title;
        $(this.modalEditTarget).modal('hide');
    }

    handleEditKeyup(event) {
        if (event.keyCode === 13) { // Enter
            this.submitEdit();
        } else if (event.keyCode === 27) { // Escape
            $(this.modalEditTarget).modal('hide');
        }
    }
}
