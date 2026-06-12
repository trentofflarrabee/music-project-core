jQuery(function ($) {
    $('.mpc-media-upload').on('click', function (event) {
        event.preventDefault();

        const button = $(this);
        const targetId = button.data('target');
        const mediaType = button.data('type');
        const input = $('#' + targetId);
        const preview = $('[data-preview-for="' + targetId + '"]');

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

            if (mediaType === 'image') {
                const imageUrl = attachment.sizes && attachment.sizes.medium
                    ? attachment.sizes.medium.url
                    : attachment.url;

                preview.html('<img src="' + imageUrl + '" alt="">');
            } else {
                preview.html('<p><strong>Selected file:</strong><br>' + attachment.filename + '</p>');
            }

            button.text('Replace File');
        });

        frame.open();
    });

    $('.mpc-media-remove').on('click', function (event) {
        event.preventDefault();

        const button = $(this);
        const targetId = button.data('target');
        const input = $('#' + targetId);
        const preview = $('[data-preview-for="' + targetId + '"]');

        input.val('');
        preview.html('');
    });
});

(function () {
    function initHeroAdminToggles() {
        const layoutSelect = document.querySelector('.mpc-hero-layout-select');

        if (!layoutSelect) {
            return;
        }

        const fullBleedRows = document.querySelectorAll('.mpc-hero-full-bleed-row');
        const splitHelpers = document.querySelectorAll('.mpc-admin-helper--split');
        const fullBleedHelpers = document.querySelectorAll('.mpc-admin-helper--full-bleed');

        function setVisible(elements, shouldShow) {
            elements.forEach((element) => {
                element.style.display = shouldShow ? '' : 'none';
            });
        }

        function updateHeroFields() {
            const isFullBleed = layoutSelect.value === 'full_bleed';

            setVisible(fullBleedRows, isFullBleed);
            setVisible(splitHelpers, !isFullBleed);
            setVisible(fullBleedHelpers, isFullBleed);
        }

        layoutSelect.addEventListener('change', updateHeroFields);
        updateHeroFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroAdminToggles);
    } else {
        initHeroAdminToggles();
    }
})();

(function () {
    function initFeaturedAdminToggles() {
        const mediaTypeSelect = document.querySelector('.mpc-featured-media-type-select');

        if (!mediaTypeSelect) {
            return;
        }

        const videoRows = document.querySelectorAll('.mpc-featured-video-row');

        function setVisible(elements, shouldShow) {
            elements.forEach((element) => {
                element.style.display = shouldShow ? '' : 'none';
            });
        }

        function updateFeaturedFields() {
            const isVideo = mediaTypeSelect.value === 'video';

            setVisible(videoRows, isVideo);
        }

        mediaTypeSelect.addEventListener('change', updateFeaturedFields);
        updateFeaturedFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFeaturedAdminToggles);
    } else {
        initFeaturedAdminToggles();
    }
})();

(function () {
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSectionManager);
    } else {
        initSectionManager();
    }
})();