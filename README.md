# Music Project Core

Music Project Core is a WordPress plugin that provides reusable settings, content tools, integrations, routing state, and site-management features for music project websites.

It is designed to pair with the **Music Project Base** theme.

## Requirements

- WordPress 6.8 or newer
- PHP 7.4 or newer
- Administrator access for plugin configuration

## Architecture

Music Project Core owns reusable site configuration and reusable content.

The companion Music Project Base theme owns frontend templates, markup, responsive layout, accessibility presentation, animation, and visual styling.

This separation allows important content and configuration to remain available when the active theme changes.

### Music Project Core owns

- Homepage configuration
- Homepage section visibility and order
- Hero configuration
- Featured Content configuration
- Services configuration
- Quotes / Testimonials
- Integrations
- Social Links
- Theme Style
- Footer configuration
- Site Status
- Link Hub / Link in Bio configuration
- Link Hub routing state
- Link Hub sanitization and normalization
- Settings schemas and migrations
- Public rendering-neutral data APIs

### Music Project Base owns

- Frontend templates
- Header and navigation markup
- Footer markup
- Homepage section markup
- Link Hub frontend markup
- Link Hub layouts and styling
- Blog and editorial templates
- Responsive behavior
- Frontend JavaScript
- Accessibility presentation
- Theme presentation defaults

Core should not contain Base-specific frontend markup or visual layout assumptions.

Themes and extensions should consume documented Core APIs rather than reading or modifying Core option arrays directly.

# Features

## Homepage Section Manager

Control the visibility and order of these homepage sections:

- Hero
- Featured Content
- Services
- Quotes / Testimonials
- Shows
- Blog / News
- Newsletter

Sections can be reordered through drag-and-drop or keyboard-accessible move controls.

## Hero

Configure split or full-bleed layouts, responsive media, overlay treatment, content placement, heading, supporting text, calls to action, and Social Links.

## Featured Content

Create promotional sections for releases, videos, announcements, featured messages, images, supported video URLs, and optional embedded featured quotes.

## Services

Create, remove, and reorder up to eight service items.

Each service supports:

- Heading
- Description
- Link text
- Link URL

## Quotes / Testimonials

Quotes and testimonials are stored as reusable WordPress content and support featured status, attribution, source URLs, ordering, homepage counts, layout, and background tone.

## Blog / News

Configure homepage blog presentation including Grid, Featured First, or Compact layouts; featured-post selection; post counts; featured images; dates; excerpts; and call-to-action labels.

## Shows and Newsletter

The Integrations screen provides provider-neutral fields for shows/event embeds and newsletter signup embeds.

An optional Bandsintown adapter is available through:

```text
[mpc_bandsintown_shows artist="Artist Name" app_id="your-app-id" signup_target="#signup"]
```

The application ID may also be supplied through the `BIT_APP_ID` environment variable.

## Social Links

Manage reusable social and contact links for supported placements such as:

- Homepage hero
- Mobile navigation
- Footer
- Site Status page
- Link Hub / Link in Bio

Display modes include labels, icons, and icons with labels.

Social account data is stored once in Core and reused by compatible presentation surfaces.

## Link in Bio / Link Hub

Music Project Core includes a first-party, artist-focused Link Hub intended to provide a canonical link-in-bio destination on the artist's own WordPress site.

Configure it under:

```text
Music Project → Link in Bio
```

The Link Hub is intentionally a curated microsite feature rather than a general-purpose page builder.

### Link Hub capabilities

- One Link Hub per site
- One assigned WordPress Page
- Explicit Page configuration
- Enable / disable control
- Auto, Custom, or None profile-image modes
- Custom Media Library profile image
- Display-name override
- Site Title fallback
- Optional tagline
- Spotlight layout
- Stack layout
- Poster layout
- Ordered links
- Ordered section headings
- Enabled / disabled link states
- Optional link subtitles
- Curated icons
- One Featured link
- Optional new-window behavior
- Keyboard-accessible Move Up / Move Down controls
- Drag-and-drop ordering enhancement
- Reuse of existing Music Project Social Links
- Optional minimal footer branding
- Maximum of 30 total ordered items

### Link Hub routing

The assigned WordPress Page ID is the routing contract.

The Link Hub does **not** depend on a hard-coded `/links/` slug.

The Page slug may be changed without breaking Link Hub configuration.

The explicit **Configure Link Hub Page** action:

1. Preserves a valid existing assignment.
2. Otherwise reuses a suitable existing published `Links` Page when possible.
3. Otherwise creates a new published `Links` Page.
4. Never runs merely because Core was activated or updated.
5. Never silently replaces a valid assigned Page.
6. Never uses the WordPress Homepage or Posts page as the Link Hub route.

Administrators may also assign another suitable existing published Page manually.

### Link Hub ownership boundary

Core owns:

- `mpc_link_hub_settings`
- Defaults
- Sanitization
- Normalization
- Item ordering
- Media Library selections
- Page assignment
- Page configuration
- Routing state
- Public data helpers
- Public extension filters
- Schema tracking

Compatible themes such as Music Project Base own:

- Specialized frontend template
- Markup
- Layouts
- Responsive behavior
- Icon markup
- Image rendering
- Social-link presentation
- Focus states
- Reduced-motion behavior
- Visual styling

### Link Hub security

Link Hub settings do not accept arbitrary HTML, JavaScript, raw SVG markup, arbitrary CSS, or inline event handlers.

Supported destination protocols are intentionally limited to:

```text
http
https
mailto
tel
```

Icons, layouts, item types, and visual variants are restricted to curated allowlists.

Malformed or incomplete enabled links are prevented from reaching the frontend data API.

At most one enabled link may use the Featured visual variant.

### Link Hub privacy

The first-party Link Hub does not record visitor or click analytics.

No third-party social feed, analytics SDK, tracking pixel, or external embed is loaded merely because a visitor views the Link Hub.

External services are contacted only through normal visitor actions such as following a configured external link.

## Theme Style

Theme Style provides curated controls for colors, header behavior, brand display, corner styles, card shadows, border strength, font stacks, semantic typography assignments, and environmental texture.

Compatible themes may reuse these shared values for additional curated presentation surfaces such as Link Hub.

## Footer

Configure tagline, copyright text, token replacement, brand visibility, footer menu visibility, Social Links visibility, and curated footer layouts.

## Site Status

Temporarily place the public site into Coming Soon or Maintenance mode.

Administrators retain access and can preview the status page before enabling it publicly.

# Installation

1. Download or clone this repository.
2. Place the plugin directory at:

   ```text
   wp-content/plugins/music-project-core
   ```

3. Activate **Music Project Core** under **Plugins** in WordPress.
4. Install and activate the companion Music Project Base theme for the complete presentation experience.
5. Open the **Music Project** menu in WordPress administration.

# Suggested Setup Order

1. Configure Homepage section visibility and order.
2. Add Hero and Featured Content.
3. Add Services and Quotes / Testimonials.
4. Configure Shows and Newsletter under Integrations.
5. Add Social Links.
6. Configure Theme Style.
7. Configure the Footer.
8. Configure Link in Bio if required.
9. Review Site Status settings.

# Stored Data

Music Project Core stores configuration in these WordPress options:

```text
mpc_homepage_settings
mpc_theme_style_settings
mpc_integrations_settings
mpc_footer_settings
mpc_social_links_settings
mpc_site_status_settings
mpc_link_hub_settings
mpc_schema_versions
```

Quotes and testimonials use the custom post type:

```text
mpc_press_quote
```

Link Hub destinations are stored as bounded configuration within `mpc_link_hub_settings`.

Individual Link Hub links are not WordPress posts and do not use a custom post type.

The assigned Link Hub WordPress Page remains an ordinary WordPress Page.

# Activation and Updates

Outstanding schema migrations run when the plugin is activated.

A lightweight migration check also runs in WordPress administration so normal plugin updates can apply schema changes without requiring deactivation and reactivation.

Activation and updates do not automatically create a Link Hub Page.

Link Hub Page creation occurs only through an explicit administrator action.

# Deactivation and Uninstall Policy

Deactivating Music Project Core does not delete settings or content.

Deleting the plugin also preserves plugin settings, Link Hub settings, Link Hub Page assignment data, schema-version information, Quotes / Testimonials, and quote metadata.

The Link Hub WordPress Page is not deleted automatically.

# Theme Changes

Changing away from Music Project Base does not delete Core-owned data.

The assigned Link Hub WordPress Page remains in WordPress.

A different theme may render that Page using its ordinary Page template.

Returning to Music Project Base restores the specialized Link Hub presentation when Link Hub remains enabled and the assigned Page is valid.

# Translation

The plugin uses the text domain:

```text
music-project-core
```

Local translation files may be placed in:

```text
languages/
```

# Packaging

Create a release ZIP from a clean, committed checkout of the intended release branch:

```bash
git archive \
  --format=zip \
  --prefix=music-project-core/ \
  --output=../music-project-core.zip \
  HEAD
```

The release ZIP must contain one top-level directory:

```text
music-project-core/
```

The archive should include these production files and directories:

```text
music-project-core/
├── assets/
├── includes/
├── CHANGELOG.md
├── LICENSE
├── README.md
├── music-project-core.php
└── uninstall.php
```

Install and test the generated ZIP on a clean WordPress site before publishing it.

# Development

The plugin uses WordPress-native APIs, including the Settings API, custom post types, Media Library, shortcodes, nonces, capabilities, translation functions, activation hooks, WordPress hooks and filters, Page APIs, and URL sanitization APIs.

Asset versions use file modification times when available for cache busting.

# Extension Boundaries

Music Project Core provides curated extension points for integrations and project-specific functionality.

Extensions should use documented public functions and WordPress hooks rather than directly modifying Core option arrays or including Core files manually.

## Public Data Access

The following functions are intended as stable read boundaries for Core-owned data:

```php
mpc_get_homepage_settings()
mpc_get_homepage_setting()
mpc_get_homepage_section_order()
mpc_get_homepage_section_visibility()
mpc_is_homepage_section_visible()

mpc_get_theme_style_settings()
mpc_get_theme_style_setting()

mpc_get_social_links_settings()
mpc_get_social_links_setting()
mpc_get_social_links()
mpc_get_social_display_mode()

mpc_get_footer_settings()
mpc_get_footer_setting()
mpc_parse_footer_tokens()

mpc_get_integrations_settings()
mpc_get_integrations_setting()
mpc_render_integration_content()

mpc_get_site_status_settings()
mpc_get_site_status_setting()

mpc_get_link_hub_settings()
mpc_get_link_hub_setting()
mpc_get_link_hub_items()
mpc_get_link_hub_url()
mpc_is_link_hub_enabled()
mpc_get_link_hub_page_id()
```

Callers should check `function_exists()` when Core may be inactive.

Themes should consume the public Link Hub functions instead of reading `mpc_link_hub_settings` directly.

## Public Filters

Core exposes intentional extension filters including:

- `mpc_social_link_items`
- `mpc_social_links`
- `mpc_social_display_mode`
- `mpc_link_hub_items`
- `mpc_link_hub_url`
- `mpc_footer_token_replacements`
- `mpc_render_integration_content`
- `mpc_site_status_preview_url`
- `mpc_site_status_request_should_bypass`
- `mpc_site_status_is_machine_request`
- `mpc_site_status_retry_after`
- `mpc_site_status_template`

### `mpc_link_hub_items`

Filters the normalized ordered Link Hub item collection before frontend cleanup.

Filtered values are normalized again before they are returned to frontend consumers.

Extensions should use this boundary rather than directly modifying `mpc_link_hub_settings`.

### `mpc_link_hub_url`

Filters the resolved public URL of the assigned Link Hub WordPress Page.

The assigned Page ID remains the routing authority.

Consumers should use this API/filter rather than infer a `/links/` slug.

# Extension Compatibility

Extensions should:

1. Avoid renaming or deleting existing Core option keys.
2. Avoid overwriting complete Core option arrays when changing one extension-owned value.
3. Use unique extension-owned keys where supported.
4. Preserve unknown values when processing shared settings.
5. Use documented filters rather than replacing Core rendering functions.
6. Check Core availability before calling Core functions.
7. Treat undocumented helpers as internal implementation details.
8. Avoid depending on physical file locations under `includes/`.
9. Avoid requiring Base or another theme to inspect raw Link Hub option data.
10. Keep presentation markup outside Core.

# Schema and Upgrade Compatibility

Core uses:

```text
mpc_schema_versions
```

to track settings-schema versions.

Schema migrations are versioned separately from the public plugin version.

Current schema groups include Link Hub.

Future migrations must preserve existing settings and content, be idempotent, tolerate malformed legacy values, preserve extension-owned data where appropriate, and update schema versions only after successful migration.

Extensions should not modify `mpc_schema_versions`.

# Public Versioning

Music Project Core intends to use Semantic Versioning for production releases.

- Patch releases should contain backward-compatible fixes.
- Minor releases may add backward-compatible settings, functions, filters, and features.
- Breaking changes to documented public APIs should be reserved for a major version.

# Privacy and Remote Services

Music Project Core does not send product analytics or telemetry by default.

Some user-configured features may intentionally communicate with third-party services, including Google Fonts, Shows or Newsletter providers, Bandsintown, WordPress oEmbed providers, Social Links, and Link Hub destinations.

The Link Hub itself does not bundle a third-party analytics SDK or load third-party social content merely because the Link Hub is viewed.

# Companion Theme

Music Project Base:

```text
https://github.com/trentofflarrabee/music-project-base
```

# Support and Issues

Report reproducible problems through:

```text
https://github.com/trentofflarrabee/music-project-core/issues
```

Include WordPress version, PHP version, active theme, Music Project Base version when relevant, reproduction steps, and relevant PHP/browser errors.

# Version

Current version:

```text
1.4.0
```

# License

Music Project Core is licensed under the GNU General Public License, version 2 or any later version.

SPDX license identifier:

```text
GPL-2.0-or-later
```

See `LICENSE` for the complete license terms.
