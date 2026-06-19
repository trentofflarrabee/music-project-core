(function ($) {
    'use strict';

    function setVisible(elements, shouldShow) {
        elements.forEach((element) => {
            element.style.display = shouldShow ? '' : 'none';
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
        const list = document.querySelector('.mpc-section-list');
        const orderInput = document.querySelector('.mpc-section-order-input');

        if (!list || !orderInput) {
            return;
        }

        let draggedItem = null;

        function updateOrderInput() {
            const sections = Array.from(list.querySelectorAll('[data-section]'))
                .map((item) => item.getAttribute('data-section'))
                .filter(Boolean);

            orderInput.value = sections.join(',');
        }

        list.addEventListener('dragstart', function (event) {
            const item = event.target.closest('.mpc-section-list__item');

            if (!item) {
                return;
            }

            draggedItem = item;
            item.classList.add('is-dragging');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', item.getAttribute('data-section'));
            }
        });

        list.addEventListener('dragend', function () {
            if (draggedItem) {
                draggedItem.classList.remove('is-dragging');
            }

            draggedItem = null;
            updateOrderInput();
        });

        list.addEventListener('dragover', function (event) {
            event.preventDefault();

            if (!draggedItem) {
                return;
            }

            const targetItem = event.target.closest('.mpc-section-list__item');

            if (!targetItem || targetItem === draggedItem) {
                return;
            }

            const targetRect = targetItem.getBoundingClientRect();
            const shouldInsertAfter = event.clientY > targetRect.top + targetRect.height / 2;

            if (shouldInsertAfter) {
                targetItem.after(draggedItem);
            } else {
                targetItem.before(draggedItem);
            }

            updateOrderInput();
        });

        list.addEventListener('change', updateOrderInput);
        updateOrderInput();
    }

    function initMPCAdmin() {
        initMediaUploader();
        initHeroAdminToggles();
        initFeaturedAdminToggles();
        initBlogAdminToggles();
        initSectionManager();
    }

    $(initMPCAdmin);
})(jQuery);