(function ($) {
    'use strict';

function setVisible(elements, shouldShow) {
    if (!elements) {
        return;
    }

    Array.from(elements).forEach((element) => {
        if (element && element.style) {
            element.style.display = shouldShow ? '' : 'none';
        }
    });
}

    function initMediaUploader() {
        $(document).on('click', '.mpc-media-upload', function (event) {
            event.preventDefault();

            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            const button = $(this);
            const targetId = button.data('target');
            const mediaType = button.data('type');
            const input = $('#' + targetId);
            const preview = $('[data-preview-for="' + targetId + '"]');

            if (!targetId || !input.length) {
                return;
            }

            const frame = wp.media({
                title: 'Choose File',
                button: {
                    text: 'Use this file'
                },
                library: mediaType ? { type: mediaType } : {},
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();

                input.val(attachment.id);
                preview.empty();

                if (mediaType === 'image') {
                    const imageUrl = attachment.sizes && attachment.sizes.medium
                        ? attachment.sizes.medium.url
                        : attachment.url;

                    $('<img>', {
                        src: imageUrl,
                        alt: ''
                    }).appendTo(preview);
                } else {
                    const fileNotice = $('<p>');
                    $('<strong>').text('Selected file:').appendTo(fileNotice);
                    $('<br>').appendTo(fileNotice);
                    fileNotice.append(document.createTextNode(attachment.filename || attachment.url));

                    preview.append(fileNotice);
                }

                button.text('Replace File');
            });

            frame.open();
        });

        $(document).on('click', '.mpc-media-remove', function (event) {
            event.preventDefault();

            const button = $(this);
            const targetId = button.data('target');
            const input = $('#' + targetId);
            const preview = $('[data-preview-for="' + targetId + '"]');

            if (!targetId || !input.length) {
                return;
            }

            input.val('');
            preview.empty();
        });
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
    const list = $('.mpc-section-list');
    const orderInput = $('.mpc-section-order-input');

    console.log('[MPC] Section Manager init', {
        listFound: list.length,
        orderInputFound: orderInput.length,
        sortableLoaded: !!$.fn.sortable
    });

    if (!list.length || !orderInput.length) {
        return;
    }

    function updateOrderInput() {
        const sections = [];

        list.find('[data-section]').each(function () {
            const section = $(this).attr('data-section');

            if (section) {
                sections.push(section);
            }
        });

        orderInput.val(sections.join(','));

        console.log('[MPC] Section order:', orderInput.val());
    }

    if (!$.fn.sortable) {
        console.warn('[MPC] jQuery UI Sortable is not loaded.');
        updateOrderInput();
        return;
    }

    list.sortable({
        items: '.mpc-section-list__item',
        axis: 'y',
        tolerance: 'pointer',
        cursor: 'move',
        opacity: 0.85,
        update: updateOrderInput,
        stop: updateOrderInput
    });

    list.disableSelection();

    list.on('change', 'input, select, textarea', updateOrderInput);

    updateOrderInput();
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

    function initMPCAdmin() {
        initMediaUploader();
        initHeroAdminToggles();
        initFeaturedAdminToggles();
        initBlogAdminToggles();
        initSectionManager();
        initStickySubmit();
        initAdminPanels();
    }

    $(initMPCAdmin);
})(jQuery);

function initAdminPanels() {
    const panels = document.querySelectorAll('.mpc-admin-panel[data-panel-id]');

    if (!panels.length || !window.localStorage) {
        return;
    }

    panels.forEach((panel) => {
        const panelId = panel.getAttribute('data-panel-id');

        if (!panelId) {
            return;
        }

        const storageKey = 'mpc_admin_panel_' + panelId;
        const savedState = window.localStorage.getItem(storageKey);

        if (savedState === 'open') {
            panel.setAttribute('open', '');
        }

        if (savedState === 'closed') {
            panel.removeAttribute('open');
        }

        panel.addEventListener('toggle', () => {
            window.localStorage.setItem(
                storageKey,
                panel.open ? 'open' : 'closed'
            );
        });
    });
}