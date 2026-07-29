(() => {
    const containers = Array.from(
        document.querySelectorAll('.mpc-bandsintown-events')
    );

    if (!containers.length) {
        return;
    }

    const strings = window.MPCBandsintownShowsL10n || {};
    const requestCache = new Map();

    const getString = (key, fallback) => {
        const value = strings[key];

        return typeof value === 'string' && value
            ? value
            : fallback;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    /**
     * Normalize a URL before inserting it into generated markup.
     *
     * Relative URLs resolve against the current site. Event-provider links
     * permit only HTTP and HTTPS. Signup links may also be page fragments
     * or mailto links.
     */
    const getSafeUrl = (
        value,
        {
            allowHash = false,
            allowMailto = false,
        } = {}
    ) => {
        const candidate = String(value ?? '').trim();

        if (!candidate) {
            return '';
        }

        if (
            allowHash
            && /^#[A-Za-z][A-Za-z0-9_:.-]*$/.test(candidate)
        ) {
            return candidate;
        }

        try {
            const url = new URL(
                candidate,
                window.location.href
            );

            const allowedProtocols = [
                'http:',
                'https:',
            ];

            if (allowMailto) {
                allowedProtocols.push('mailto:');
            }

            return allowedProtocols.includes(url.protocol)
                ? url.href
                : '';
        } catch (error) {
            return '';
        }
    };

    const setContainerContent = (container, html) => {
        container.innerHTML = html;
        container.setAttribute('aria-busy', 'false');
    };

    const getSignupLink = (container) => {
        const signupUrl = getSafeUrl(
            container.dataset.signupTarget,
            {
                allowHash: true,
                allowMailto: true,
            }
        );

        if (!signupUrl) {
            return '';
        }

        return `
            <a
                href="${escapeHtml(signupUrl)}"
                class="show-btn show-btn--primary btn btn-dark"
            >
                ${escapeHtml(
                    getString(
                        'emailUpdates',
                        'Get Email Updates'
                    )
                )}
            </a>
        `;
    };

    const renderLoading = (container) => {
        container.setAttribute('aria-busy', 'true');

        container.innerHTML = `
            <div class="shows-empty">
                <p class="fs-300">
                    ${escapeHtml(
                        getString(
                            'loading',
                            'Loading shows…'
                        )
                    )}
                </p>
            </div>
        `;
    };

    const renderEmpty = (container) => {
        setContainerContent(
            container,
            `
                <div class="shows-empty">
                    <p class="fs-300">
                        <strong>
                            ${escapeHtml(
                                getString(
                                    'emptyTitle',
                                    'Nothing on the calendar just yet.'
                                )
                            )}
                        </strong>
                    </p>

                    <p class="fs-300">
                        ${escapeHtml(
                            getString(
                                'emptyText',
                                'Join the mailing list for new dates.'
                            )
                        )}
                    </p>

                    ${getSignupLink(container)}
                </div>
            `
        );
    };

    const renderError = (container) => {
        setContainerContent(
            container,
            `
                <div class="shows-empty">
                    <p class="fs-300">
                        <strong>
                            ${escapeHtml(
                                getString(
                                    'errorTitle',
                                    'Shows are temporarily unavailable.'
                                )
                            )}
                        </strong>
                    </p>

                    <p class="fs-300">
                        ${escapeHtml(
                            getString(
                                'errorText',
                                'Please try again later.'
                            )
                        )}
                    </p>

                    ${getSignupLink(container)}
                </div>
            `
        );
    };

    const getLocalDateParts = (isoDatetime) => {
        const date = new Date(isoDatetime);

        if (Number.isNaN(date.getTime())) {
            return {
                month: '',
                day: '',
                localDate: '',
                localTime: '',
            };
        }

        const locale = getString(
            'locale',
            document.documentElement.lang || undefined
        );

        try {
            return {
                month: date.toLocaleString(
                    locale,
                    {
                        month: 'short',
                    }
                ),
                day: date.toLocaleString(
                    locale,
                    {
                        day: 'numeric',
                    }
                ),
                localDate: date.toLocaleDateString(
                    locale,
                    {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric',
                    }
                ),
                localTime: date.toLocaleTimeString(
                    locale,
                    {
                        hour: 'numeric',
                        minute: '2-digit',
                    }
                ),
            };
        } catch (error) {
            return {
                month: date.toLocaleString(
                    undefined,
                    {
                        month: 'short',
                    }
                ),
                day: date.getDate(),
                localDate: date.toLocaleDateString(),
                localTime: date.toLocaleTimeString(
                    undefined,
                    {
                        hour: 'numeric',
                        minute: '2-digit',
                    }
                ),
            };
        }
    };

    const requestEvents = (artist, appId) => {
        const cacheKey = `${artist}\u0000${appId}`;

        if (requestCache.has(cacheKey)) {
            return requestCache.get(cacheKey);
        }

        const endpoint = new URL(
            `https://rest.bandsintown.com/artists/${encodeURIComponent(
                artist
            )}/events`
        );

        endpoint.searchParams.set('date', 'upcoming');
        endpoint.searchParams.set('app_id', appId);

        const request = fetch(
            endpoint.toString(),
            {
                credentials: 'omit',
                headers: {
                    Accept: 'application/json',
                },
            }
        ).then((response) => {
            if (!response.ok) {
                throw new Error(
                    `Bandsintown request failed: ${response.status}`
                );
            }

            return response.json();
        });

        requestCache.set(cacheKey, request);

        return request;
    };

    const renderEvents = (container, events) => {
        const cards = events.map((event) => {
            const venue = event && event.venue
                ? event.venue
                : {};

            const venueName = venue.name
                || getString('venueTba', 'Venue TBA');

            const city = venue.city
                ? `${venue.city}${
                    venue.region
                        ? `, ${venue.region}`
                        : ''
                }`
                : '';

            const {
                month,
                day,
                localDate,
                localTime,
            } = getLocalDateParts(event.datetime);

            const eventUrl = getSafeUrl(event.url);
            const offers = Array.isArray(event.offers)
                ? event.offers
                : [];

            let ctaLabel = getString(
                'notifyMe',
                'Notify Me'
            );

            let ctaUrl = eventUrl;

            if (eventUrl) {
                const url = new URL(eventUrl);

                if (
                    offers.some(
                        (offer) => (
                            offer
                            && offer.status === 'sold_out'
                        )
                    )
                ) {
                    ctaLabel = getString(
                        'waitlist',
                        'Waitlist'
                    );

                    url.searchParams.set(
                        'waitlist',
                        'true'
                    );
                } else if (offers.length) {
                    ctaLabel = getString(
                        'rsvp',
                        'RSVP'
                    );

                    url.searchParams.set(
                        'trigger',
                        'rsvp_going'
                    );
                } else {
                    url.searchParams.set(
                        'trigger',
                        'notify_me'
                    );
                }

                ctaUrl = url.href;
            }

            const eventLink = ctaUrl
                ? `
                    <a
                        href="${escapeHtml(ctaUrl)}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        ${escapeHtml(ctaLabel)}
                    </a>
                `
                : '';

            return `
                <article class="event-card spotify-style mpc-event-card">
                    <div
                        class="event-date"
                        aria-hidden="true"
                    >
                        <span class="month">
                            ${escapeHtml(month)}
                        </span>

                        <span class="day">
                            ${escapeHtml(day)}
                        </span>
                    </div>

                    <div class="event-body">
                        <h3>
                            ${escapeHtml(venueName)}
                        </h3>

                        <div class="event-meta">
                            ${
                                city
                                    ? `
                                        <span class="city">
                                            ${escapeHtml(city)}
                                        </span>
                                    `
                                    : ''
                            }

                            <span class="time">
                                ${escapeHtml(localDate)}
                                ${
                                    localTime
                                        ? ` · ${escapeHtml(localTime)}`
                                        : ''
                                }
                            </span>
                        </div>

                        ${eventLink}
                    </div>
                </article>
            `;
        });

        setContainerContent(
            container,
            `<div class="events-list">${cards.join('')}</div>`
        );
    };

    const loadContainer = async (container) => {
        const artist = String(
            container.dataset.artist || ''
        ).trim();

        const appId = String(
            container.dataset.appId || ''
        ).trim();

        if (!artist || !appId) {
            renderError(container);
            return;
        }

        renderLoading(container);

        try {
            const events = await requestEvents(
                artist,
                appId
            );

            if (!Array.isArray(events) || !events.length) {
                renderEmpty(container);
                return;
            }

            renderEvents(container, events);
        } catch (error) {
            renderError(container);
        }
    };

    containers.forEach(loadContainer);
})();