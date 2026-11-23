import { Controller } from '@hotwired/stimulus';
// Imported by absolute web path, like the admin-bundle modules: AssetMapper serves it from
// public/bundles, Webpack Encore resolves it through an alias (see doc/installation.md).
import Sortable from '/bundles/aropixelmenu/js/vendor/sortablejs/sortable.esm.js';

const SORTABLE_GROUP = 'aropixel-menu';

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
        this.sortables = [];
        this.initializeSortable();
    }

    disconnect() {
        this.sortables.forEach((sortable) => sortable.destroy());
        this.sortables = [];
    }

    /* ------------------------------------------------------------------ Drag & drop */

    initializeSortable() {
        this.makeSortable(this.menuTarget);
        this.menuTarget.querySelectorAll('li.li-movable').forEach((li) => this.ensureChildList(li));
    }

    /**
     * Every item carries a child list, empty or not: it is where another item can be dropped
     * to become its child. Empty lists only take room while dragging (see menu.css).
     */
    ensureChildList(li) {
        let list = li.querySelector(':scope > ol');
        if (!list) {
            list = document.createElement('ol');
            li.appendChild(list);
        }
        if (!Sortable.get(list)) {
            this.makeSortable(list);
        }
    }

    makeSortable(list) {
        this.sortables.push(Sortable.create(list, {
            group: SORTABLE_GROUP,
            draggable: 'li.li-movable',
            handle: '.dd-handle',
            // The item's action menu stays clickable.
            filter: '.cell-menu',
            preventOnFilter: false,
            animation: 150,
            // Mouse-driven dragging rather than native HTML5 drag & drop: the clone follows the
            // cursor the same way in every browser, and is appended to <body> so that nested
            // lists do not clip it.
            forceFallback: true,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            // Empty child lists get a drop zone of their own while dragging (menu.css): keep the
            // extra margin small, or every item border would nest instead of reorder.
            emptyInsertThreshold: 4,
            ghostClass: 'placeholder',
            onStart: () => this.menuTarget.classList.add('is-dragging'),
            onEnd: () => this.menuTarget.classList.remove('is-dragging'),
            onMove: (event) => this.fitsMaxLevel(event.dragged, event.to),
        }));
    }

    /** Depth of a list: 1 for the menu root, +1 per nested list. */
    levelOf(list) {
        let level = 1;
        let node = list;
        while (node && node !== this.menuTarget) {
            node = node.parentElement;
            if (node && node.tagName === 'OL') {
                level++;
            }
        }
        return level;
    }

    /** Number of levels an item brings along: itself plus its deepest branch. */
    depthOf(li) {
        const children = li.querySelectorAll(':scope > ol > li.li-movable');
        let deepest = 0;
        children.forEach((child) => { deepest = Math.max(deepest, this.depthOf(child)); });
        return 1 + deepest;
    }

    fitsMaxLevel(item, targetList) {
        if (!this.maxLevelValue) {
            return true;
        }
        return this.levelOf(targetList) + this.depthOf(item) - 1 <= this.maxLevelValue;
    }

    /* ------------------------------------------------------------------ Adding items */

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
            this.modal(document.getElementById('modal_please_select')).show();
        }
    }

    showAlreadyIncludedAlert() {
        this.notify('Désolé', '<strong>Ce lien est déjà dans la liste.</strong><br />Vous ne pouvez pas l\'insérer qu\'une seule fois.', 'bg-danger');
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
        this.ensureChildList(newLine);
    }

    deleteRow(event) {
        const line = event.currentTarget.closest('li.li-movable');
        // A timer rather than the Web Animations promise: a hidden tab pauses animations, and the
        // line must go even then.
        line.style.transition = 'opacity .2s';
        line.style.opacity = '0';
        setTimeout(() => line.remove(), 200);
    }

    /* ------------------------------------------------------------------ Saving */

    async saveMenu() {
        const panelMenu = this.element.querySelector('#panelMenu');
        panelMenu.classList.add('is-saving');
        if (this.hasSaveButtonTarget) {
            this.saveButtonTarget.disabled = true;
        }

        const body = new URLSearchParams();
        encodeParams(body, 'type', this.typeValue);
        encodeParams(body, 'name', this.nameValue);
        encodeParams(body, 'menu', this.serialize());

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body,
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            this.notify('Le menu a bien été enregistré !', 'Vous pouvez continuer à modifier votre menu.', 'bg-success');
        } catch (error) {
            console.error('Menu save failed', error);
            this.notify('Le menu n\'a pas été enregistré', 'Une erreur est survenue. Vos modifications sont toujours à l\'écran : réessayez.', 'bg-danger');
        } finally {
            panelMenu.classList.remove('is-saving');
            if (this.hasSaveButtonTarget) {
                this.saveButtonTarget.disabled = false;
            }
        }
    }

    serialize() {
        const step = (list) => Array.from(list.querySelectorAll(':scope > li')).map((li) => {
            const item = {
                data: {
                    type: li.getAttribute('data-type'),
                    payload: JSON.parse(li.getAttribute('data-payload') || '{}'),
                    title: li.getAttribute('data-title'),
                    originalTitle: li.getAttribute('data-original-title'),
                    required: li.getAttribute('data-required')
                }
            };

            const sub = li.querySelector(':scope > ol');
            if (sub) {
                item.children = step(sub);
            }

            return item;
        });

        return step(this.menuTarget);
    }

    /* ------------------------------------------------------------------ Edit modal */

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

        this.modal(this.modalEditTarget).show();
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
        this.modal(this.modalEditTarget).hide();
    }

    handleEditKeyup(event) {
        if (event.key === 'Enter') {
            this.submitEdit();
        } else if (event.key === 'Escape') {
            this.modal(this.modalEditTarget).hide();
        }
    }

    /* ------------------------------------------------------------------ Modals */

    /** Bootstrap 5 modal instance — the admin loads Bootstrap as a global. */
    modal(element) {
        return globalThis.bootstrap.Modal.getOrCreateInstance(element);
    }

    /** One-off message modal, removed from the DOM once closed. */
    notify(title, message, headerClass) {
        const element = document.createElement('div');
        element.className = 'modal fade';
        element.tabIndex = -1;
        element.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal_mini">
                <div class="modal-content">
                    <div class="modal-header ${headerClass}">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>`;
        element.querySelector('.modal-title').textContent = title;
        element.querySelector('.modal-body').innerHTML = message;

        document.body.appendChild(element);
        element.addEventListener('hidden.bs.modal', () => element.remove());
        this.modal(element).show();
    }
}

/**
 * Form-encodes a value with PHP's bracket notation, as jQuery.param did: the save endpoints read
 * `menu[0][data][title]`, `menu[0][children][0][data][type]`… Empty objects and arrays send
 * nothing, null/undefined send an empty string.
 */
function encodeParams(params, prefix, value) {
    if (Array.isArray(value)) {
        value.forEach((item, index) => {
            const key = (item !== null && typeof item === 'object') ? index : '';
            encodeParams(params, `${prefix}[${key}]`, item);
        });
    } else if (value !== null && typeof value === 'object') {
        Object.keys(value).forEach((name) => encodeParams(params, `${prefix}[${name}]`, value[name]));
    } else {
        params.append(prefix, value ?? '');
    }
}
