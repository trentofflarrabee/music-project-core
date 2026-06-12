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