(() => {
    const config = window.MPCBandsintownShows || {};

    const ARTIST_NAME = config.artistName || '';
    const APP_ID = config.appId || '';
    const SIGNUP_TARGET = config.signupTarget || '#signup';

    const container = document.getElementById('mpc-bandsintown-events');

    if (!container) {
        console.warn('[Bandsintown] Missing events container. No shows will render.');
        return;
    }

    const escapeHtml = (s) => String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const refreshAOS = () => {
        if (window.AOS && typeof window.AOS.refreshHard === 'function') {
            window.AOS.refreshHard();
        }
    };

    const renderLoading = () => {
        container.innerHTML = `
            <div class="shows-empty">
                <p class="fs-300">Loading shows…</p>
            </div>
        `;
    };

    const renderEmpty = () => {
        container.innerHTML = `
            <div class="shows-empty" data-aos="fade-up" data-aos-delay="100">
                <p class="fs-300">Nothing on the calendar just yet.</p>
                <p class="fs-300">Join the mailing list and be the first to hear about new dates.</p>
                <a href="${escapeHtml(SIGNUP_TARGET)}" class="show-btn show-btn--primary btn btn-dark">Get Email Updates</a>
            </div>
        `;

        refreshAOS();
    };

    const renderError = () => {
        container.innerHTML = `
            <div class="shows-empty" data-aos="fade-up" data-aos-delay="100">
                <p class="fs-300">Sorry — something went wrong while loading shows.</p>
                <p class="fs-300">Try again later, or follow us for updates.</p>
                <a href="${escapeHtml(SIGNUP_TARGET)}" class="show-btn show-btn--primary btn btn-dark">Get Email Updates</a>
            </div>
        `;

        refreshAOS();
    };

    const toLocalDateBits = (isoDatetime) => {
        const dt = new Date(isoDatetime);

        if (Number.isNaN(dt.getTime())) {
            return {
                month: '',
                day: '',
                localDate: '',
                localTime: ''
            };
        }

        return {
            month: dt.toLocaleString('en-US', { month: 'short' }),
            day: dt.getDate(),
            localDate: dt.toLocaleDateString([], {
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            }),
            localTime: dt.toLocaleTimeString([], {
                hour: 'numeric',
                minute: '2-digit'
            })
        };
    };

    async function loadEventsWithCTAs() {
        renderLoading();

        if (!ARTIST_NAME || !APP_ID) {
            console.warn('[Bandsintown] Missing artist name or app ID.');
            renderError();
            return;
        }

        try {
            const artistEnc = encodeURIComponent(ARTIST_NAME);

            const evRes = await fetch(
                `https://rest.bandsintown.com/artists/${artistEnc}/events?date=upcoming&app_id=${encodeURIComponent(APP_ID)}`
            );

            if (!evRes.ok) {
                throw new Error(`Events API error: ${evRes.status} ${evRes.statusText}`);
            }

            const events = await evRes.json();

            if (!Array.isArray(events) || events.length === 0) {
                renderEmpty();
                return;
            }

            let html = '<div class="events-list">';

            for (const ev of events) {
                const venue = ev.venue || {};
                const venueName = venue.name || 'Venue TBA';
                const city = venue.city
                    ? `${venue.city}${venue.region ? ', ' + venue.region : ''}`
                    : '';

                const { month, day, localDate, localTime } = toLocalDateBits(ev.datetime);
                const evUrl = ev.url || '#';

                let ctaLabel = 'Notify Me';
                let ctaUrl = `${evUrl}${evUrl.includes('?') ? '&' : '?'}trigger=notify_me`;

                if (Array.isArray(ev.offers) && ev.offers.length > 0) {
                    if (ev.offers.some((offer) => offer && offer.status === 'sold_out')) {
                        ctaLabel = 'Waitlist';
                        ctaUrl = `${evUrl}${evUrl.includes('?') ? '&' : '?'}waitlist=true`;
                    } else {
                        ctaLabel = 'RSVP';
                        ctaUrl = `${evUrl}${evUrl.includes('?') ? '&' : '?'}trigger=rsvp_going`;
                    }
                }

                html += `
                    <div class="event-card spotify-style">
                        <div class="event-date" aria-hidden="true">
                            <span class="month">${escapeHtml(month)}</span>
                            <span class="day">${escapeHtml(day)}</span>
                        </div>

                        <div class="event-body">
                            <h3>${escapeHtml(venueName)}</h3>

                            <div class="event-meta">
                                <span class="city">${escapeHtml(city)}</span>
                                <span class="time">${escapeHtml(localDate)} · ${escapeHtml(localTime)}</span>
                            </div>

                            <a href="${escapeHtml(ctaUrl)}" target="_blank" rel="noopener">
                                ${escapeHtml(ctaLabel)}
                            </a>
                        </div>
                    </div>
                `;
            }

            html += '</div>';

            container.innerHTML = html;
            refreshAOS();
        } catch (err) {
            console.error('[Bandsintown] Error:', err);
            renderError();
        }
    }

    loadEventsWithCTAs();
})();