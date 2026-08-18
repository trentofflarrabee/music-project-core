(function ($) {
    'use strict';

    const adminI18n = (
        window.mpcAdminI18n
        && typeof window.mpcAdminI18n === 'object'
    )
        ? window.mpcAdminI18n
        : {};

    /**
     * Get one translated admin string.
     *
     * @param {string} key      Translation object key.
     * @param {string} fallback English fallback.
     * @returns {string}
     */
    function getAdminString(
        key,
        fallback = ''
    ) {
        const value = adminI18n[key];

        return (
            typeof value === 'string'
            && value !== ''
        )
            ? value
            : fallback;
    }

    /**
     * Show or hide a collection of elements.
     *
     * @param {NodeList|HTMLElement[]} elements   Elements to update.
     * @param {boolean}                shouldShow Whether elements are visible.
     */
    function setVisible(
        elements,
        shouldShow
    ) {
        if (!elements) {
            return;
        }

        Array.from(elements).forEach(
            (element) => {
                if (element && element.style) {
                    element.style.display = shouldShow
                        ? ''
                        : 'none';
                }
            }
        );
    }

    /**
     * Initialize WordPress media fields.
     */
    function initMediaUploader() {
        $(document).on(
            'click',
            '.mpc-media-upload',
            function (event) {
                event.preventDefault();

                if (
                    typeof wp === 'undefined'
                    || !wp.media
                ) {
                    return;
                }

                const button = $(this);
                const targetId = String(
                    button.data('target') || ''
                );

                const mediaType = String(
                    button.data('type') || ''
                );

                const inputElement = targetId
                    ? document.getElementById(targetId)
                    : null;

                if (!inputElement) {
                    return;
                }

                const input = $(inputElement);
                const field = button.closest(
                    '.mpc-media-field'
                );

                const preview = field.find(
                    '[data-preview-for]'
                );

                const currentButtonText = String(
                    button.text() || ''
                ).trim();

                const frame = wp.media({
                    title: getAdminString(
                        'chooseFile',
                        currentButtonText
                    ),

                    button: {
                        text: getAdminString(
                            'useThisFile',
                            currentButtonText
                        ),
                    },

                    library: mediaType
                        ? {
                            type: mediaType,
                        }
                        : {},

                    multiple: false,
                });

                frame.on('select', function () {
                    const selection = frame
                        .state()
                        .get('selection')
                        .first();

                    if (!selection) {
                        return;
                    }

                    const attachment = selection.toJSON();

                    input
                        .val(attachment.id || '')
                        .trigger('change');

                    preview.empty();

                    if (mediaType === 'image') {
                        const imageUrl = (
                            attachment.sizes
                            && attachment.sizes.medium
                        )
                            ? attachment.sizes.medium.url
                            : attachment.url;

                        $('<img>', {
                            src: imageUrl || '',
                            alt: '',
                        }).appendTo(preview);
                    } else {
                        const fileNotice = $('<p>');

                        $('<strong>')
                            .text(
                                getAdminString(
                                    'selectedFile',
                                    'Selected file:'
                                )
                            )
                            .appendTo(fileNotice);

                        $('<br>').appendTo(fileNotice);

                        fileNotice.append(
                            document.createTextNode(
                                attachment.filename
                                || attachment.url
                                || ''
                            )
                        );

                        preview.append(fileNotice);
                    }

                    button.text(
                        getAdminString(
                            'replaceFile',
                            currentButtonText
                        )
                    );
                });

                frame.open();
            }
        );

        $(document).on(
            'click',
            '.mpc-media-remove',
            function (event) {
                event.preventDefault();

                const button = $(this);
                const targetId = String(
                    button.data('target') || ''
                );

                const inputElement = targetId
                    ? document.getElementById(targetId)
                    : null;

                if (!inputElement) {
                    return;
                }

                const field = button.closest(
                    '.mpc-media-field'
                );

                const preview = field.find(
                    '[data-preview-for]'
                );

                const uploadButton = field.find(
                    '.mpc-media-upload'
                );

                $(inputElement)
                    .val('')
                    .trigger('change');

                preview.empty();

                uploadButton.text(
                    getAdminString(
                        'chooseFile',
                        String(
                            uploadButton.text() || ''
                        ).trim()
                    )
                );
            }
        );
    }

    function initHeroAdminToggles() {

        const heroLayoutField = $('[name="mpc_homepage_settings[hero_layout]"]');
const fullBleedOnlyFields = $('.mpc-hero-full-bleed-field');

const getHeroLayoutValue = () => {
    const checkedValue = heroLayoutField.filter(':checked').val();

    if (checkedValue) {
        return checkedValue;
    }

    return heroLayoutField.val();
};

const toggleFullBleedOnlyFields = () => {
    const isFullBleed = getHeroLayoutValue() === 'full_bleed';

    setVisible(fullBleedOnlyFields, isFullBleed);
};

heroLayoutField.on('change', toggleFullBleedOnlyFields);
toggleFullBleedOnlyFields();

        const layoutSelect = document.querySelector('.mpc-hero-layout-select');

        if (!layoutSelect) {
            return;
        }

        const fullBleedRows = document.querySelectorAll('.mpc-hero-full-bleed-row');
        const splitHelpers = document.querySelectorAll('.mpc-admin-helper--split');
        const fullBleedHelpers = document.querySelectorAll('.mpc-admin-helper--full-bleed');

        function updateHeroFields() {
            const isFullBleed = layoutSelect.value === 'full_bleed';

            setVisible(fullBleedRows, isFullBleed);
            setVisible(splitHelpers, !isFullBleed);
            setVisible(fullBleedHelpers, isFullBleed);
        }

        layoutSelect.addEventListener('change', updateHeroFields);
        updateHeroFields();
    }

    function initFeaturedAdminToggles() {
        const mediaTypeSelect = document.querySelector('.mpc-featured-media-type-select');

        if (!mediaTypeSelect) {
            return;
        }

        const videoRows = document.querySelectorAll('.mpc-featured-video-row');

        function updateFeaturedFields() {
            const isVideo = mediaTypeSelect.value === 'video';

            setVisible(videoRows, isVideo);
        }

        mediaTypeSelect.addEventListener('change', updateFeaturedFields);
        updateFeaturedFields();
    }

    function initBlogAdminToggles() {
        const layoutSelect = document.querySelector('.mpc-blog-layout-select');
        const featuredSourceSelect = document.querySelector('.mpc-blog-featured-source-select');

        if (!layoutSelect) {
            return;
        }

        const featuredRows = document.querySelectorAll('.mpc-blog-featured-first-row');
        const manualRows = document.querySelectorAll('.mpc-blog-manual-featured-row');

        function updateBlogFields() {
            const isFeaturedFirst = layoutSelect.value === 'featured_first';
            const isManual = featuredSourceSelect && featuredSourceSelect.value === 'manual';

            setVisible(featuredRows, isFeaturedFirst);
            setVisible(manualRows, isFeaturedFirst && isManual);
        }

        layoutSelect.addEventListener('change', updateBlogFields);

        if (featuredSourceSelect) {
            featuredSourceSelect.addEventListener('change', updateBlogFields);
        }

        updateBlogFields();
    }

function initSectionManager() {
    const manager = $('.mpc-section-manager');
    const list = manager.find('.mpc-section-list');
    const orderInput = manager.find('.mpc-section-order-input');
    const status = manager.find('.mpc-section-manager__status');

    if (
        !manager.length
        || !list.length
        || !orderInput.length
    ) {
        return;
    }

    const movedTemplate = manager.attr(
        'data-moved-template'
    ) || getAdminString(
        'sectionMoved',
        '%1$s moved to position %2$d of %3$d.'
    );

    /**
     * Get the current ordered collection of section rows.
     *
     * @returns {JQuery}
     */
    function getItems() {
        return list.children(
            '.mpc-section-list__item'
        );
    }

    /**
     * Keep the first and last movement controls disabled appropriately.
     */
    function updateMoveButtons() {
        const items = getItems();
        const lastIndex = items.length - 1;

        items.each(function (index) {
            const item = $(this);

            item.find(
                '.mpc-section-list__move--up'
            ).prop(
                'disabled',
                index === 0
            );

            item.find(
                '.mpc-section-list__move--down'
            ).prop(
                'disabled',
                index === lastIndex
            );
        });
    }

    /**
     * Synchronize the hidden setting with the visible list order.
     *
     * @param {boolean} markDirty Whether to mark the settings form changed.
     */
    function updateOrderInput(markDirty = false) {
        const sections = [];

        getItems().each(function () {
            const section = $(this).attr(
                'data-section'
            );

            if (section) {
                sections.push(section);
            }
        });

        orderInput.val(sections.join(','));
        updateMoveButtons();

        if (markDirty) {
            const inputElement = orderInput.get(0);

            if (inputElement) {
                inputElement.dispatchEvent(
                    new Event(
                        'input',
                        {
                            bubbles: true,
                        }
                    )
                );
            }
        }
    }

    /**
     * Announce a completed movement to assistive technology.
     *
     * @param {HTMLElement} item Moved section row.
     */
    function announceMovement(item) {
        if (!item || !status.length) {
            return;
        }

        const items = getItems();
        const position = items.index(item);
        const label = item.getAttribute(
            'data-section-label'
        ) || item.getAttribute('data-section') || '';

        if (position < 0 || !label) {
            return;
        }

        const message = movedTemplate
            .replace('%1$s', label)
            .replace('%2$d', String(position + 1))
            .replace('%3$d', String(items.length));

        /*
         * Clearing first ensures repeated movements of the same item are
         * announced reliably.
         */
        status.text('');

        window.setTimeout(() => {
            status.text(message);
        }, 20);
    }

    /**
     * Reorder using the explicit keyboard-accessible controls.
     */
    list.on(
        'click',
        '.mpc-section-list__move',
        function (event) {
            event.preventDefault();

            const button = $(this);

            if (button.prop('disabled')) {
                return;
            }

            const item = button.closest(
                '.mpc-section-list__item'
            );

            const direction = button.attr(
                'data-direction'
            );

            let target;

            if (direction === 'up') {
                target = item.prev(
                    '.mpc-section-list__item'
                );

                if (!target.length) {
                    return;
                }

                item.insertBefore(target);
            } else if (direction === 'down') {
                target = item.next(
                    '.mpc-section-list__item'
                );

                if (!target.length) {
                    return;
                }

                item.insertAfter(target);
            } else {
                return;
            }

            updateOrderInput(true);
            announceMovement(item.get(0));

            /*
             * Keep focus on the same movement control while another movement
             * in that direction remains possible. At the boundary, move
             * focus to the opposite control for the same section.
             */
            window.requestAnimationFrame(() => {
                const sameButton = item.find(
                    `.mpc-section-list__move--${direction}`
                );

                if (!sameButton.prop('disabled')) {
                    sameButton.trigger('focus');
                    return;
                }

                const oppositeDirection = direction === 'up'
                    ? 'down'
                    : 'up';

                item.find(
                    `.mpc-section-list__move--${oppositeDirection}`
                ).trigger('focus');
            });
        }
    );

    /*
     * Pointer dragging remains available when jQuery UI Sortable is loaded.
     * The move buttons continue to work even if Sortable is unavailable.
     */
    if ($.fn.sortable) {
        list.sortable({
            items: '.mpc-section-list__item',
            handle: '.mpc-section-list__handle',
            axis: 'y',
            tolerance: 'pointer',
            cursor: 'grabbing',
            opacity: 0.85,

            start(event, ui) {
                ui.item.addClass('is-dragging');
            },

            update(event, ui) {
                updateOrderInput(true);
                announceMovement(ui.item.get(0));
            },

            stop(event, ui) {
                ui.item.removeClass('is-dragging');
                updateMoveButtons();
            },
        });
    }

    /*
     * Establish the correct hidden value and disabled-button state without
     * marking the freshly loaded form as changed.
     */
    updateOrderInput(false);
}

function initServicesEditor() {
    const editor = $('.mpc-services-editor');

    if (!editor.length) {
        return;
    }

    const list = editor.find(
        '.mpc-services-editor__list'
    );

    const template = editor.find(
        'template.mpc-service-item-template'
    ).get(0);

    const addButton = editor.find(
        '.mpc-services-editor__add'
    );

    const emptyMessage = editor.find(
        '.mpc-services-editor__empty'
    );

    const countOutput = editor.find(
        '[data-service-count]'
    );

    const status = editor.find(
        '.mpc-services-editor__status'
    );

    const maxItems = parseInt(
        editor.attr('data-service-max'),
        10
    ) || 8;

const itemLabel = editor.attr(
    'data-service-item-label'
) || getAdminString(
    'service',
    'Service'
);

const addedMessage = editor.attr(
    'data-service-added-message'
) || getAdminString(
    'serviceAdded',
    'Service item added.'
);

const removedTemplate = editor.attr(
    'data-service-removed-template'
) || getAdminString(
    'serviceRemoved',
    '%s removed.'
);

const movedTemplate = editor.attr(
    'data-service-moved-template'
) || getAdminString(
    'serviceMoved',
    '%1$s moved to position %2$d of %3$d.'
);

const limitTemplate = editor.attr(
    'data-service-limit-message'
) || getAdminString(
    'serviceLimit',
    'You can add up to %d services.'
);

const limitMessage = limitTemplate.replace(
    '%d',
    String(maxItems)
);

const dragTemplate = editor.attr(
    'data-service-drag-template'
) || getAdminString(
    'dragService',
    'Drag %s to reorder'
);

const controlsTemplate = editor.attr(
    'data-service-controls-template'
) || getAdminString(
    'serviceControls',
    'Reorder or remove %s'
);

const removeTemplate = editor.attr(
    'data-service-remove-template'
) || getAdminString(
    'removeService',
    'Remove %s'
);

    function getItems() {
        return list.children(
            '.mpc-service-item[data-service-item]'
        );
    }

    function formatMessage(
        message,
        replacements
    ) {
        let formatted = String(message || '');

        Object.keys(replacements).forEach(
            (placeholder) => {
                formatted = formatted.replace(
                    placeholder,
                    String(replacements[placeholder])
                );
            }
        );

        return formatted;
    }

    function announce(message) {
        if (!status.length || !message) {
            return;
        }

        status.text('');

        window.setTimeout(() => {
            status.text(message);
        }, 20);
    }

    function markFormDirty() {
        const listElement = list.get(0);

        if (!listElement) {
            return;
        }

        listElement.dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true,
                }
            )
        );
    }

    function getItemLabel(item) {
        const items = getItems();
        const position = items.index(item) + 1;

        const title = String(
            item.find(
                '[data-service-field="title"]'
            ).val() || ''
        ).trim();

        return title || `${itemLabel} ${position}`;
    }

    function updateEditorState() {
        const items = getItems();
        const itemCount = items.length;
        const lastIndex = itemCount - 1;

        items.each(function (index) {
            const item = $(this);
            const visibleNumber = index + 1;
            const fallbackLabel = `${itemLabel} ${visibleNumber}`;

            item.attr(
                'data-service-index',
                index
            );

            item.find(
                '[data-service-number]'
            ).text(visibleNumber);

            item.find(
                '[data-service-field]'
            ).each(function () {
                const field = $(this);
                const fieldName = field.attr(
                    'data-service-field'
                );

                if (!fieldName) {
                    return;
                }

                const fieldId = [
                    'mpc_service_item',
                    index,
                    fieldName,
                ].join('_');

                field.attr({
                    id: fieldId,
                    name: `mpc_homepage_settings[services_items][${index}][${fieldName}]`,
                });

                item.find(
                    `[data-service-label-for="${fieldName}"]`
                ).attr(
                    'for',
                    fieldId
                );
            });

            item.find(
                '.mpc-service-item__move--up'
            ).prop(
                'disabled',
                index === 0
            );

            item.find(
                '.mpc-service-item__move--down'
            ).prop(
                'disabled',
                index === lastIndex
            );

            item.find(
                '.mpc-service-item__handle'
            ).attr(
                'aria-label',
                formatMessage(
                    dragTemplate,
                    {
                        '%s': fallbackLabel,
                    }
                )
            );

            item.find(
                '.mpc-service-item__controls'
            ).attr(
                'aria-label',
                formatMessage(
                    controlsTemplate,
                    {
                        '%s': fallbackLabel,
                    }
                )
            );

            item.find(
                '.mpc-service-item__remove'
            ).attr(
                'aria-label',
                formatMessage(
                    removeTemplate,
                    {
                        '%s': fallbackLabel,
                    }
                )
            );
        });

        countOutput.text(itemCount);

        emptyMessage.prop(
            'hidden',
            itemCount > 0
        );

        addButton.prop(
            'disabled',
            itemCount >= maxItems
        );

        list.toggleClass(
            'is-empty',
            itemCount === 0
        );
    }

    function announceMovement(item) {
        if (!item || !item.length) {
            return;
        }

        const items = getItems();
        const position = items.index(item) + 1;
        const label = getItemLabel(item);

        announce(
            formatMessage(
                movedTemplate,
                {
                    '%1$s': label,
                    '%2$d': position,
                    '%3$d': items.length,
                }
            )
        );
    }

    addButton.on('click', function (event) {
        event.preventDefault();

        if (getItems().length >= maxItems) {
            announce(limitMessage);
            return;
        }

        if (
            !template
            || !template.content
            || !list.get(0)
        ) {
            return;
        }

        const fragment = template.content.cloneNode(
            true
        );

        list.get(0).appendChild(fragment);

        updateEditorState();
        markFormDirty();
        announce(addedMessage);

        const newItem = getItems().last();

        window.requestAnimationFrame(() => {
            newItem.find(
                '[data-service-field="title"]'
            ).trigger('focus');
        });
    });

    list.on(
        'click',
        '.mpc-service-item__remove',
        function (event) {
            event.preventDefault();

            const item = $(this).closest(
                '.mpc-service-item'
            );

            if (!item.length) {
                return;
            }

            const label = getItemLabel(item);
            const nextItem = item.next(
                '.mpc-service-item'
            );

            const previousItem = item.prev(
                '.mpc-service-item'
            );

            item.remove();

            updateEditorState();
            markFormDirty();

            announce(
                formatMessage(
                    removedTemplate,
                    {
                        '%s': label,
                    }
                )
            );

            const focusItem = nextItem.length
                ? nextItem
                : previousItem;

            window.requestAnimationFrame(() => {
                if (focusItem.length) {
                    focusItem.find(
                        '[data-service-field="title"]'
                    ).trigger('focus');

                    return;
                }

                addButton.trigger('focus');
            });
        }
    );

    list.on(
        'click',
        '.mpc-service-item__move',
        function (event) {
            event.preventDefault();

            const button = $(this);

            if (button.prop('disabled')) {
                return;
            }

            const item = button.closest(
                '.mpc-service-item'
            );

            const direction = button.attr(
                'data-service-direction'
            );

            if (direction === 'up') {
                const previousItem = item.prev(
                    '.mpc-service-item'
                );

                if (!previousItem.length) {
                    return;
                }

                item.insertBefore(previousItem);
            } else if (direction === 'down') {
                const nextItem = item.next(
                    '.mpc-service-item'
                );

                if (!nextItem.length) {
                    return;
                }

                item.insertAfter(nextItem);
            } else {
                return;
            }

            updateEditorState();
            markFormDirty();
            announceMovement(item);

            window.requestAnimationFrame(() => {
                const sameDirectionButton = item.find(
                    `.mpc-service-item__move--${direction}`
                );

                if (!sameDirectionButton.prop('disabled')) {
                    sameDirectionButton.trigger('focus');
                    return;
                }

                const oppositeDirection = direction === 'up'
                    ? 'down'
                    : 'up';

                item.find(
                    `.mpc-service-item__move--${oppositeDirection}`
                ).trigger('focus');
            });
        }
    );

    if ($.fn.sortable) {
        list.sortable({
            items: '.mpc-service-item',
            handle: '.mpc-service-item__handle',
            axis: 'y',
            tolerance: 'pointer',
            cursor: 'grabbing',
            opacity: 0.85,
            forcePlaceholderSize: true,
            placeholder:
                'mpc-service-item mpc-service-item--placeholder',

            start(event, ui) {
                ui.item.addClass('is-dragging');
            },

            update(event, ui) {
                updateEditorState();
                markFormDirty();
                announceMovement(ui.item);
            },

            stop(event, ui) {
                ui.item.removeClass('is-dragging');
                updateEditorState();
            },
        });
    }

    updateEditorState();
}

function initLinkHubEditor() {
    const editor = $('.mpc-link-hub-editor');

    if (!editor.length) {
        return;
    }

    const list = editor.find(
        '.mpc-link-hub-editor__list'
    );

    const emptyMessage = editor.find(
        '.mpc-link-hub-editor__empty'
    );

    const countOutput = editor.find(
        '[data-link-hub-count]'
    );

    const status = editor.find(
        '.mpc-link-hub-editor__status'
    );

    const addButtons = editor.find(
        '[data-link-hub-add]'
    );

    const maxItems = parseInt(
        editor.attr('data-link-hub-max'),
        10
    ) || 30;

    const expandLabel =
        editor.attr('data-link-hub-expand')
        || 'Edit';

    const collapseLabel =
        editor.attr('data-link-hub-collapse')
        || 'Collapse';

    function getItems() {
        return list.children(
            '.mpc-link-hub-item[data-link-hub-item]'
        );
    }

    function formatMessage(
        message,
        replacements
    ) {
        let output = String(
            message || ''
        );

        Object.keys(
            replacements
        ).forEach(
            (placeholder) => {
                output = output.replace(
                    placeholder,
                    String(
                        replacements[
                            placeholder
                        ]
                    )
                );
            }
        );

        return output;
    }

    function announce(message) {
        if (
            !status.length
            || !message
        ) {
            return;
        }

        status.text('');

        window.setTimeout(
            () => {
                status.text(message);
            },
            20
        );
    }

    function markDirty() {
        const element = list.get(0);

        if (!element) {
            return;
        }

        element.dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true,
                }
            )
        );
    }

    function getItemType(item) {
        const type = String(
            item.find(
                '[data-link-hub-field="type"]'
            ).val() || ''
        );

        return type === 'section'
            ? 'section'
            : 'link';
    }

    function getItemLabel(item) {
        const label = String(
            item.find(
                '[data-link-hub-field="label"]'
            ).val() || ''
        ).trim();

        if (label) {
            return label;
        }

        const position =
            getItems().index(item) + 1;

        return getItemType(item)
            === 'section'
                ? `Section ${position}`
                : `Link ${position}`;
    }

    function setExpanded(
        item,
        expanded
    ) {
        const toggle = item.find(
            '.mpc-link-hub-item__toggle'
        );

        const body = item.find(
            '.mpc-link-hub-item__body'
        );

        toggle.attr(
            'aria-expanded',
            expanded
                ? 'true'
                : 'false'
        );

        toggle.text(
            expanded
                ? collapseLabel
                : expandLabel
        );

        body.prop(
            'hidden',
            !expanded
        );

        item.toggleClass(
            'is-collapsed',
            !expanded
        );
    }

    function updateEditorState() {
        const items = getItems();
        const count = items.length;
        const lastIndex = count - 1;

        items.each(
            function (index) {
                const item = $(this);

                item.attr(
                    'data-link-hub-index',
                    index
                );

                const bodyId =
                    `mpc_link_hub_item_${index}_body`;

                item.find(
                    '.mpc-link-hub-item__body'
                ).attr(
                    'id',
                    bodyId
                );

                item.find(
                    '.mpc-link-hub-item__toggle'
                ).attr(
                    'aria-controls',
                    bodyId
                );

                item.find(
                    '[data-link-hub-field]'
                ).each(
                    function () {
                        const field =
                            $(this);

                        const fieldName =
                            field.attr(
                                'data-link-hub-field'
                            );

                        if (!fieldName) {
                            return;
                        }

                        const fieldId =
                            `mpc_link_hub_item_${index}_${fieldName}`;

                        field.attr({
                            id: fieldId,
                            name:
                                `mpc_link_hub_items[${index}][${fieldName}]`,
                        });

                        item.find(
                            `[data-link-hub-label-for="${fieldName}"]`
                        ).attr(
                            'for',
                            fieldId
                        );
                    }
                );

                item.find(
                    '[data-link-hub-summary-label]'
                ).text(
                    getItemLabel(item)
                );

                item.find(
                    '.mpc-link-hub-item__move--up'
                ).prop(
                    'disabled',
                    index === 0
                );

                item.find(
                    '.mpc-link-hub-item__move--down'
                ).prop(
                    'disabled',
                    index === lastIndex
                );
            }
        );

        countOutput.text(count);

        emptyMessage.prop(
            'hidden',
            count > 0
        );

        addButtons.prop(
            'disabled',
            count >= maxItems
        );
    }

    function announceMovement(item) {
        const items = getItems();

        const position =
            items.index(item) + 1;

        const message =
            editor.attr(
                'data-link-hub-moved'
            )
            || '%1$s moved to position %2$d of %3$d.';

        announce(
            formatMessage(
                message,
                {
                    '%1$s':
                        getItemLabel(item),
                    '%2$d':
                        position,
                    '%3$d':
                        items.length,
                }
            )
        );
    }

    addButtons.on(
        'click',
        function (event) {
            event.preventDefault();

            if (
                getItems().length
                >= maxItems
            ) {
                const message =
                    editor.attr(
                        'data-link-hub-limit'
                    )
                    || 'You can add up to %d Link Hub items.';

                announce(
                    message.replace(
                        '%d',
                        String(maxItems)
                    )
                );

                return;
            }

            const type = String(
                $(this).attr(
                    'data-link-hub-add'
                ) || ''
            );

            if (
                type !== 'link'
                && type !== 'section'
            ) {
                return;
            }

            const template =
                editor.find(
                    `template[data-link-hub-template="${type}"]`
                ).get(0);

            if (
                !template
                || !template.content
                || !list.get(0)
            ) {
                return;
            }

            const fragment =
                template.content.cloneNode(
                    true
                );

            list.get(0).appendChild(
                fragment
            );

            updateEditorState();
            markDirty();

            const newItem =
                getItems().last();

            setExpanded(
                newItem,
                true
            );

            const message = type === 'section'
                ? (
                    editor.attr(
                        'data-link-hub-section-added'
                    )
                    || 'Section added.'
                )
                : (
                    editor.attr(
                        'data-link-hub-link-added'
                    )
                    || 'Link added.'
                );

            announce(message);

            window.requestAnimationFrame(
                () => {
                    newItem.find(
                        '[data-link-hub-field="label"]'
                    ).trigger('focus');
                }
            );
        }
    );

    list.on(
        'click',
        '.mpc-link-hub-item__toggle',
        function (event) {
            event.preventDefault();

            const button =
                $(this);

            const item =
                button.closest(
                    '.mpc-link-hub-item'
                );

            const expanded =
                button.attr(
                    'aria-expanded'
                ) === 'true';

            setExpanded(
                item,
                !expanded
            );
        }
    );

    list.on(
        'click',
        '.mpc-link-hub-item__remove',
        function (event) {
            event.preventDefault();

            const item =
                $(this).closest(
                    '.mpc-link-hub-item'
                );

            if (!item.length) {
                return;
            }

            const label =
                getItemLabel(item);

            const nextItem =
                item.next(
                    '.mpc-link-hub-item'
                );

            const previousItem =
                item.prev(
                    '.mpc-link-hub-item'
                );

            item.remove();

            updateEditorState();
            markDirty();

            const message =
                editor.attr(
                    'data-link-hub-removed'
                )
                || '%s removed.';

            announce(
                message.replace(
                    '%s',
                    label
                )
            );

            const focusItem =
                nextItem.length
                    ? nextItem
                    : previousItem;

            window.requestAnimationFrame(
                () => {
                    if (focusItem.length) {
                        focusItem.find(
                            '.mpc-link-hub-item__toggle'
                        ).trigger('focus');

                        return;
                    }

                    addButtons
                        .first()
                        .trigger('focus');
                }
            );
        }
    );

    list.on(
        'click',
        '.mpc-link-hub-item__move',
        function (event) {
            event.preventDefault();

            const button =
                $(this);

            if (
                button.prop(
                    'disabled'
                )
            ) {
                return;
            }

            const item =
                button.closest(
                    '.mpc-link-hub-item'
                );

            const direction =
                button.attr(
                    'data-link-hub-direction'
                );

            if (direction === 'up') {
                const target =
                    item.prev(
                        '.mpc-link-hub-item'
                    );

                if (!target.length) {
                    return;
                }

                item.insertBefore(target);
            } else if (
                direction === 'down'
            ) {
                const target =
                    item.next(
                        '.mpc-link-hub-item'
                    );

                if (!target.length) {
                    return;
                }

                item.insertAfter(target);
            } else {
                return;
            }

            updateEditorState();
            markDirty();
            announceMovement(item);

            window.requestAnimationFrame(
                () => {
                    const sameButton =
                        item.find(
                            `.mpc-link-hub-item__move--${direction}`
                        );

                    if (
                        !sameButton.prop(
                            'disabled'
                        )
                    ) {
                        sameButton.trigger(
                            'focus'
                        );

                        return;
                    }

                    const opposite =
                        direction === 'up'
                            ? 'down'
                            : 'up';

                    item.find(
                        `.mpc-link-hub-item__move--${opposite}`
                    ).trigger('focus');
                }
            );
        }
    );

    list.on(
        'input',
        '[data-link-hub-field="label"]',
        function () {
            const item =
                $(this).closest(
                    '.mpc-link-hub-item'
                );

            item.find(
                '[data-link-hub-summary-label]'
            ).text(
                getItemLabel(item)
            );
        }
    );

    /*
     * Give immediate feedback when another link is marked Featured.
     * Core still enforces this rule server-side during normalization.
     */
    list.on(
        'change',
        '[data-link-hub-field="variant"]',
        function () {
            const current =
                $(this);

            if (
                current.val()
                !== 'featured'
            ) {
                return;
            }

            getItems()
                .filter(
                    '[data-link-hub-type="link"]'
                )
                .each(
                    function () {
                        const item =
                            $(this);

                        const select =
                            item.find(
                                '[data-link-hub-field="variant"]'
                            );

                        if (
                            select.get(0)
                            === current.get(0)
                        ) {
                            return;
                        }

                        select.val(
                            'standard'
                        );
                    }
                );
        }
    );

    if ($.fn.sortable) {
        list.sortable({
            items:
                '.mpc-link-hub-item',
            handle:
                '.mpc-link-hub-item__handle',
            axis: 'y',
            tolerance: 'pointer',
            cursor: 'grabbing',
            opacity: 0.85,
            forcePlaceholderSize: true,
            placeholder:
                'mpc-link-hub-item mpc-link-hub-item--placeholder',

            start(event, ui) {
                ui.item.addClass(
                    'is-dragging'
                );
            },

            update(event, ui) {
                updateEditorState();
                markDirty();
                announceMovement(
                    ui.item
                );
            },

            stop(event, ui) {
                ui.item.removeClass(
                    'is-dragging'
                );

                updateEditorState();
            },
        });
    }

    updateEditorState();

    /*
     * Collapse saved items after enhancement. If JavaScript fails, every
     * field remains visible and editable.
     */
    getItems().each(
        function () {
            setExpanded(
                $(this),
                false
            );
        }
    );
}

/**
 * Initialize optional Homepage quote color fields.
 *
 * A cleared field intentionally means "inherit the current
 * theme presentation".
 */
function initHomepageColorPickers() {
    const fields = $('.mpc-homepage-color-field');

    if (
        !fields.length
        || typeof $.fn.wpColorPicker !== 'function'
    ) {
        return;
    }

    fields.wpColorPicker();
}

/**
 * Initialize the Theme Style semantic color preview.
 */
function initThemeStyleColorPreview() {
    const preview = document.querySelector(
        '[data-theme-color-preview]'
    );

    if (!preview) {
        return;
    }

    const panel = document.getElementById(
        'mpc-theme-style-panel-colors'
    );

    if (!panel) {
        return;
    }

    const variableMap = {
        color_background:
            '--mpc-preview-background',
        color_alt_background:
            '--mpc-preview-alt-background',
        color_surface:
            '--mpc-preview-surface',
        color_text:
            '--mpc-preview-text',
        color_heading:
            '--mpc-preview-heading',
        color_muted:
            '--mpc-preview-muted',
        color_accent:
            '--mpc-preview-accent',
        color_link:
            '--mpc-preview-link',
        color_button_background:
            '--mpc-preview-button-bg',
        color_button_text:
            '--mpc-preview-button-text',
        color_selection:
            '--mpc-preview-selection',
    };

    /**
     * Return a readable dark/light foreground for a hex color.
     *
     * @param {string} hex Background color.
     * @returns {string}
     */
    function getContrastTextColor(hex) {
        if (
            typeof hex !== 'string'
            || !/^#[0-9a-f]{6}$/i.test(hex)
        ) {
            return '#111111';
        }

        const channels = [
            parseInt(hex.slice(1, 3), 16) / 255,
            parseInt(hex.slice(3, 5), 16) / 255,
            parseInt(hex.slice(5, 7), 16) / 255,
        ].map(
            (channel) => (
                channel <= 0.04045
                    ? channel / 12.92
                    : Math.pow(
                        (channel + 0.055) / 1.055,
                        2.4
                    )
            )
        );

        const luminance =
            (0.2126 * channels[0])
            + (0.7152 * channels[1])
            + (0.0722 * channels[2]);

        return luminance > 0.179
            ? '#111111'
            : '#ffffff';
    }

    /**
     * Update one preview value.
     *
     * @param {HTMLInputElement} field Color input.
     */
    function updateField(field) {
        const key = field.getAttribute(
            'data-theme-color'
        );

        if (!key) {
            return;
        }

        const value = String(
            field.value || ''
        ).toLowerCase();

        if (!/^#[0-9a-f]{6}$/i.test(value)) {
            return;
        }

        const variable = variableMap[key];

        if (variable) {
            preview.style.setProperty(
                variable,
                value
            );
        }

        const valueDisplay = panel.querySelector(
            '[data-theme-color-value="'
            + key
            + '"]'
        );

        if (valueDisplay) {
            valueDisplay.textContent = value;
        }

        if (key === 'color_selection') {
            preview.style.setProperty(
                '--mpc-preview-selection-text',
                getContrastTextColor(value)
            );
        }
    }

    const fields = Array.from(
        panel.querySelectorAll(
            '[data-theme-color]'
        )
    );

    fields.forEach(
        (field) => {
            updateField(field);

            field.addEventListener(
                'input',
                () => {
                    updateField(field);
                }
            );

            field.addEventListener(
                'change',
                () => {
                    updateField(field);
                }
            );
        }
    );
}

function initStickySubmit() {
    const form = document.querySelector('.mpc-homepage-settings-form');

    if (!form) {
        return;
    }

    let isDirty = false;

    function markDirty() {
        if (isDirty) {
            return;
        }

        isDirty = true;
        form.classList.add('is-dirty');
    }

    form.addEventListener('change', markDirty);
    form.addEventListener('input', markDirty);

    form.addEventListener('submit', function () {
        form.classList.remove('is-dirty');
    });
}

/**
 * Initialize the accessible Music Project admin tab interface.
 *
 * Without JavaScript, tab navigation remains hidden and every
 * settings section remains visible.
 */
function initAdminTabs() {
    const root = document.querySelector(
        '[data-theme-style-tabs], [data-homepage-tabs]'
    );

        if (!root) {
            return;
        }

        const tablist = root.querySelector(
            '[role="tablist"]'
        );

const tabs = Array.from(
    root.querySelectorAll(
        '[role="tab"]'
    )
);

const panels = Array.from(
    root.querySelectorAll(
        '[role="tabpanel"]'
    )
);

        if (
            !tablist
            || !tabs.length
            || !panels.length
        ) {
            return;
        }

       const pageKey = (
    new URLSearchParams(
        window.location.search
    ).get('page')
    || window.location.pathname
);

const storageKey = [
    'mpc_admin_active_tab',
    pageKey,
].join(':');

        /**
         * Get the panel controlled by a tab.
         *
         * @param {HTMLElement} tab Tab button.
         * @returns {HTMLElement|null}
         */
        function getPanelForTab(tab) {
            if (!tab) {
                return null;
            }

            const panelId = tab.getAttribute(
                'aria-controls'
            );

            if (!panelId) {
                return null;
            }

            const panel = document.getElementById(
                panelId
            );

            if (
                !panel
                || !root.contains(panel)
            ) {
                return null;
            }

            return panel;
        }

        /**
         * Get the tab associated with a panel.
         *
         * @param {HTMLElement} panel Tab panel.
         * @returns {HTMLElement|null}
         */
        function getTabForPanel(panel) {
            if (!panel || !panel.id) {
                return null;
            }

            return tabs.find(
                (tab) => (
                    tab.getAttribute(
                        'aria-controls'
                    ) === panel.id
                )
            ) || null;
        }

        /**
         * Safely read the remembered tab from session storage.
         *
         * @returns {string}
         */
        function getStoredPanelId() {
            try {
                return window.sessionStorage.getItem(
                    storageKey
                ) || '';
            } catch (error) {
                return '';
            }
        }

        /**
         * Safely remember the active tab for this browser session.
         *
         * @param {string} panelId Active panel ID.
         */
        function storePanelId(panelId) {
            if (!panelId) {
                return;
            }

            try {
                window.sessionStorage.setItem(
                    storageKey,
                    panelId
                );
            } catch (error) {
                /*
                 * Storage can be unavailable in restricted browser modes.
                 * The tab interface remains fully functional without it.
                 */
            }
        }

        /**
         * Find the appropriate tab for the current URL hash.
         *
         * The hash may reference either a panel or an element inside it.
         *
         * @returns {HTMLElement|null}
         */
        function getHashTab() {
            if (!window.location.hash) {
                return null;
            }

            let targetId = window.location.hash.slice(
                1
            );

            try {
                targetId = decodeURIComponent(
                    targetId
                );
            } catch (error) {
                return null;
            }

            const target = document.getElementById(
                targetId
            );

            if (!target || !root.contains(target)) {
                return null;
            }

const panel = target.matches(
    '[role="tabpanel"]'
)
    ? target
    : target.closest(
        '[role="tabpanel"]'
    );

            return getTabForPanel(panel);
        }

        /**
         * Update the URL without adding a browser-history entry.
         *
         * @param {HTMLElement} panel Active panel.
         */
        function updateLocationHash(panel) {
            if (!panel || !panel.id) {
                return;
            }

            const newHash = `#${panel.id}`;

            if (
                window.history
                && typeof window.history.replaceState === 'function'
            ) {
                window.history.replaceState(
                    null,
                    '',
                    newHash
                );

                return;
            }

            window.location.hash = panel.id;
        }

        /**
         * Activate one tab and its corresponding panel.
         *
         * @param {HTMLElement} tab Tab to activate.
         * @param {Object} options Activation options.
         */
        function activateTab(
            tab,
            options = {}
        ) {
            if (!tabs.includes(tab)) {
                return;
            }

            const activePanel = getPanelForTab(tab);

            if (!activePanel) {
                return;
            }

            tabs.forEach((candidate) => {
                const isActive = candidate === tab;

                candidate.setAttribute(
                    'aria-selected',
                    isActive ? 'true' : 'false'
                );

                candidate.setAttribute(
                    'tabindex',
                    isActive ? '0' : '-1'
                );
            });

            panels.forEach((panel) => {
                panel.hidden = panel !== activePanel;
            });

            storePanelId(activePanel.id);

            if (options.updateLocation !== false) {
                updateLocationHash(activePanel);
            }

            if (options.focus) {
                tab.focus();
            }
        }

        /**
         * Activate a tab using its position in the tab collection.
         *
         * @param {number} index Tab index.
         */
        function activateTabAtIndex(index) {
            const tabCount = tabs.length;

            if (!tabCount) {
                return;
            }

            const normalizedIndex = (
                index + tabCount
            ) % tabCount;

            activateTab(
                tabs[normalizedIndex],
                {
                    focus: true,
                    updateLocation: true,
                }
            );
        }

        tabs.forEach((tab, index) => {
            tab.addEventListener(
                'click',
                () => {
                    activateTab(
                        tab,
                        {
                            focus: false,
                            updateLocation: true,
                        }
                    );
                }
            );

            tab.addEventListener(
                'keydown',
                (event) => {
                    let targetIndex = null;

                    switch (event.key) {
                        case 'ArrowLeft':
                            targetIndex = index - 1;
                            break;

                        case 'ArrowRight':
                            targetIndex = index + 1;
                            break;

                        case 'Home':
                            targetIndex = 0;
                            break;

                        case 'End':
                            targetIndex = tabs.length - 1;
                            break;

                        default:
                            return;
                    }

                    event.preventDefault();
                    activateTabAtIndex(targetIndex);
                }
            );
        });

        root.classList.add('is-enhanced');
        tablist.hidden = false;

        let initialTab = getHashTab();

        if (!initialTab) {
            const storedPanelId = getStoredPanelId();
            const storedPanel = storedPanelId
                ? document.getElementById(
                    storedPanelId
                )
                : null;

            initialTab = getTabForPanel(
                storedPanel
            );
        }

        if (!initialTab) {
            initialTab = tabs[0];
        }

        activateTab(
            initialTab,
            {
                focus: false,
                updateLocation: false,
            }
        );

        window.addEventListener(
            'hashchange',
            () => {
                const hashTab = getHashTab();

                if (!hashTab) {
                    return;
                }

                activateTab(
                    hashTab,
                    {
                        focus: false,
                        updateLocation: false,
                    }
                );
            }
        );
    }

    /**
 * Keep Homepage section-tab status badges synchronized with
 * Section Manager visibility checkboxes.
 */
function initHomepageTabStates() {
    const root = document.querySelector(
        '[data-homepage-tabs]'
    );

    if (!root) {
        return;
    }

    const tabs = Array.from(
        root.querySelectorAll(
            '[data-homepage-section-tab]'
        )
    );

    const sectionItems = Array.from(
        document.querySelectorAll(
            '.mpc-section-list__item[data-section]'
        )
    );

    sectionItems.forEach((item) => {
        const section = item.getAttribute(
            'data-section'
        );

        if (!section) {
            return;
        }

        const field = item.querySelector(
            'input[type="checkbox"]'
        );

        const tab = tabs.find(
            (candidate) => (
                candidate.getAttribute(
                    'data-homepage-section-tab'
                ) === section
            )
        );

        if (!field || !tab) {
            return;
        }

        const status = tab.querySelector(
            '[data-homepage-tab-status]'
        );

        if (!status) {
            return;
        }

        function syncState() {
            const isEnabled = field.checked;

            status.hidden = isEnabled;

            tab.classList.toggle(
                'is-section-disabled',
                !isEnabled
            );
        }

        syncState();

        field.addEventListener(
            'change',
            syncState
        );
    });
}

    /**
     * Restore and remember expandable admin-panel states.
     */
    function initAdminPanels() {
        const panels = document.querySelectorAll(
            '.mpc-admin-panel[data-panel-id]'
        );

        if (!panels.length) {
            return;
        }

        let storage = null;

        try {
            storage = window.localStorage;
        } catch (error) {
            /*
             * Browsers can block storage in private or restricted contexts.
             * The panels remain fully functional without persistence.
             */
            return;
        }

        if (!storage) {
            return;
        }

        panels.forEach((panel) => {
            const panelId = panel.getAttribute(
                'data-panel-id'
            );

            if (!panelId) {
                return;
            }

            const storageKey = (
                'mpc_admin_panel_'
                + panelId
            );

            let savedState = '';

            try {
                savedState = storage.getItem(
                    storageKey
                ) || '';
            } catch (error) {
                return;
            }

            if (savedState === 'open') {
                panel.setAttribute('open', '');
            } else if (savedState === 'closed') {
                panel.removeAttribute('open');
            }

            panel.addEventListener(
                'toggle',
                () => {
                    try {
                        storage.setItem(
                            storageKey,
                            panel.open
                                ? 'open'
                                : 'closed'
                        );
                    } catch (error) {
                        /*
                         * Losing persistence should never prevent the panel
                         * itself from opening or closing.
                         */
                    }
                }
            );
        });
    }

    /**
     * Initialize shared Music Project admin behavior.
     */
        function initMPCAdmin() {
            initAdminTabs();
            initHomepageTabStates();
            initThemeStyleColorPreview();
            initMediaUploader();
            initHeroAdminToggles();
            initFeaturedAdminToggles();
            initBlogAdminToggles();
            initSectionManager();
            initServicesEditor();
            initLinkHubEditor();
            initHomepageColorPickers();
            initStickySubmit();
            initAdminPanels();
        }

    $(initMPCAdmin);
})(jQuery);