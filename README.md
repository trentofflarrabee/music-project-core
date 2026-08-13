Music Project Core
==================

Music Project Core is a WordPress plugin that provides reusable settings, content tools, integrations, and site-management features for music project websites.

It is designed to pair with the [Music Project Base](https://github.com/trentofflarrabee/music-project-base) theme.

Requirements
------------

- WordPress 6.8 or newer
- PHP 7.4 or newer
- Administrator access for plugin configuration

Architecture
------------

Music Project Core owns site configuration and reusable content.

The companion Music Project Base theme owns frontend templates, markup, responsive layout, and visual presentation.

This separation allows important content and settings to remain available when the active theme changes.

Features
--------

### Homepage Section Manager

Control the visibility and order of these homepage sections:

- Hero
- Featured Content
- Services
- Quotes / Testimonials
- Shows
- Blog / News
- Newsletter

Sections can be reordered through drag-and-drop or keyboard-accessible move controls.

### Hero

Configure:

- Split or full-bleed layouts
- Compact, standard, or full-screen height
- Desktop video
- Mobile image
- Overlay style and opacity
- Content placement and alignment
- Heading, supporting text, and call to action
- Social-link display

### Featured Content

Create a promotional section for:

- Releases
- Videos
- Announcements
- Featured messages
- Images or supported video URLs
- Optional embedded featured quotes

### Services

Create, remove, and reorder up to eight service items.

Each service supports:

- Heading
- Description
- Link text
- Link URL

Curated layouts and column choices are included.

### Quotes / Testimonials

Quotes and testimonials are stored as reusable WordPress content.

Available controls include:

- Featured status
- Source or client
- Source URL
- Manual ordering
- Homepage count
- Layout
- Attribution visibility
- Background tone

### Blog / News

Configure homepage blog presentation, including:

- Grid, Featured First, or Compact layouts
- Latest or manually selected featured post
- Post counts
- Featured images
- Dates
- Excerpts
- Read More and View All labels

### Shows and Newsletter

The Integrations screen provides provider-neutral fields for:

- Shows or event embeds
- Newsletter signup embeds
- Section headings and supporting text

An optional Bandsintown adapter is included through the following shortcode:

```text
[mpc_bandsintown_shows artist="Artist Name" app_id="your-app-id" signup_target="#signup"]
```

The application ID may also be supplied through the `BIT_APP_ID` environment variable.

### Social Links

Manage reusable social and contact links for supported placements such as:

- Homepage hero
- Mobile navigation
- Footer
- Site Status page

Display modes include labels, icons, or icons with labels.

### Theme Style

Theme Style provides curated controls for:

- Core colors
- Header, mobile-navigation, and footer colors
- Header behavior
- Brand display
- Corner styles
- Card shadows
- Border strength
- Font stacks
- Semantic typography assignments
- Blog and editorial typography
- Hero typography
- Environmental background texture

Texture placements include:

- Desktop header
- Mobile navigation
- Footer
- Homepage section backgrounds
- Pages and posts

### Footer

Configure:

- Tagline
- Copyright text
- `{year}` and `{site_name}` tokens
- Brand visibility
- Footer menu visibility
- Social-link visibility
- Simple, Stacked, or Split layouts

### Site Status

Temporarily place the public site into:

- Coming Soon mode
- Maintenance mode

Administrators retain access and can preview the status page before enabling it publicly.

Installation
------------

1. Download or clone this repository.
2. Place the plugin directory at:

   ```text
   wp-content/plugins/music-project-core
   ```

3. Activate **Music Project Core** under **Plugins** in WordPress.
4. Install and activate the companion Music Project Base theme.
5. Open the **Music Project** menu in WordPress administration.

Suggested Setup Order
---------------------

1. Configure Homepage section visibility and order.
2. Add Hero and Featured Content.
3. Add Services and Quotes / Testimonials.
4. Configure Shows and Newsletter under Integrations.
5. Add Social Links.
6. Configure Theme Style.
7. Configure the Footer.
8. Review Site Status settings.

Stored Data
-----------

Music Project Core stores configuration in these WordPress options:

```text
mpc_homepage_settings
mpc_theme_style_settings
mpc_integrations_settings
mpc_footer_settings
mpc_social_links_settings
mpc_site_status_settings
mpc_schema_versions
```

Quotes and testimonials use the following custom post type:

```text
mpc_press_quote
```

Activation and Updates
----------------------

Outstanding schema migrations run when the plugin is activated.

A lightweight migration check also runs in WordPress administration so normal plugin updates can apply new schema changes without requiring deactivation and reactivation.

Deactivation and Uninstall Policy
---------------------------------

Deactivating Music Project Core does not delete settings or content.

Deleting the plugin also preserves:

- Plugin settings
- Schema-version information
- Quotes / Testimonials
- Quote metadata

This non-destructive policy prevents accidental data loss and allows the plugin to be reinstalled later.

A future explicitly opt-in cleanup tool may provide permanent data removal.

Translation
-----------

The plugin uses the text domain:

```text
music-project-core
```

Local translation files may be placed in:

```text
languages/
```

Packaging
---------

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

The repository's `.gitattributes` rules exclude `.gitattributes`, `.gitignore`, and `.github` from archives created with `git archive`.

Do not include development-only or local files such as:

```text
.git/
.DS_Store
Thumbs.db
node_modules/
vendor/
IDE project files
local database exports
environment files
temporary or test ZIP files
```

Install and test the generated ZIP on a clean WordPress site before publishing it.

Development
-----------

The plugin uses WordPress-native APIs, including:

- Settings API
- Custom post types
- Media Library
- Shortcodes
- Nonces and capabilities
- Translation functions
- Activation hooks
- WordPress hooks and filters

Source assets are loaded directly from the repository. Asset versions use file modification times when available for cache busting.

Extension Boundaries
--------------------

Music Project Core provides curated extension points for integrations and project-specific functionality.

Extensions should use documented public functions and WordPress hooks rather than directly modifying Core option arrays or including Core files manually.

### Public Data Access

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
```

Callers should check `function_exists()` when Core may be inactive.

Extensions should not assume that every stored value is present or that internal option-array ordering is significant.

### Public Filters

Core currently exposes these intentional extension filters.

#### `mpc_social_link_items`

Adds or modifies supported Social Links platforms.

Each platform definition uses:

```php
[
    'label'       => 'Platform Name',
    'type'        => 'url',
    'placeholder' => 'https://example.com/profile',
    'external'    => true,
]
```

Supported types are:

```text
url
email
```

Core normalizes filtered definitions before use.

#### `mpc_social_links`

Filters configured Social Links after Core has converted stored settings into a rendering-neutral list.

Each returned item contains:

```php
[
    'key'      => 'platform',
    'label'    => 'Platform',
    'url'      => 'https://example.com/profile',
    'type'     => 'url',
    'external' => true,
]
```

Filtered values are normalized again before frontend use.

#### `mpc_social_display_mode`

Filters the Social Links display mode for a rendering context.

Supported modes are limited to the values returned by:

```php
mpc_get_social_display_options()
```

Unsupported filter results fall back to the previously validated mode.

#### `mpc_footer_token_replacements`

Adds or changes Footer text tokens.

Core provides:

```text
{year}
{site_name}
```

Extensions may add additional token/value pairs without changing the stored Footer settings shape.

#### `mpc_render_integration_content`

Filters rendered Shows, Newsletter, or other provider content after Core processes the stored integration value.

The filter receives:

```text
rendered output
original stored content
integration context
```

Returning `null` suppresses output.

Malformed array or object results are ignored rather than passed to frontend rendering.

#### `mpc_site_status_preview_url`

Filters the administrator Site Status preview URL.

#### `mpc_site_status_request_should_bypass`

Filters whether the current request bypasses an active Coming Soon or Maintenance page.

Extensions adding API, authentication, monitoring, or other special routes may use this boundary when necessary.

#### `mpc_site_status_is_machine_request`

Filters whether a request should receive the machine-readable Site Status response rather than the public HTML status page.

This is intended for additional feed, sitemap, or similar machine-readable routes.

#### `mpc_site_status_retry_after`

Filters the `Retry-After` value used for Maintenance responses.

The default is:

```text
3600 seconds
```

#### `mpc_site_status_template`

Filters the Site Status template path.

Returning an empty path causes Core to use its plugin-owned minimal fallback instead.

Extension Compatibility
-----------------------

Extensions should follow these rules:

1. Do not rename or delete existing Core option keys.
2. Do not overwrite complete Core option arrays when changing one extension-owned value.
3. Use unique extension-owned keys when storing additional scalar data in an existing extensible Core option.
4. Preserve unknown values when processing shared settings.
5. Use documented filters rather than replacing Core rendering functions.
6. Check Core availability before calling Core functions from themes or other plugins.
7. Treat undocumented administration and rendering helpers as internal implementation details.
8. Do not depend on the physical location or load order of individual files under `includes/`.

Core sanitizers intentionally preserve unknown scalar settings in extensible option groups where appropriate so a temporarily unavailable extension does not silently lose its configuration.

Schema and Upgrade Compatibility
--------------------------------

Core uses the following option to track settings-schema versions:

```text
mpc_schema_versions
```

Schema migrations are versioned separately from the public plugin version.

Future migrations must:

- Preserve existing user settings.
- Preserve Quotes / Testimonials and their metadata.
- Avoid destructive resets.
- Be safe to run more than once.
- Tolerate malformed or legacy stored values.
- Preserve unknown extension-owned data where the option is designed to be extensible.
- Update the schema version only after the corresponding migration has completed.

Extensions should not modify `mpc_schema_versions`.

Public Versioning
-----------------

Music Project Core intends to use Semantic Versioning for production releases.

Once a production public API is declared stable:

- Patch releases should contain backward-compatible fixes.
- Minor releases may add backward-compatible settings, functions, filters, and features.
- Breaking changes to documented public APIs should be reserved for a major version.

Internal functions that are not documented as public extension boundaries may change between releases.

Future Update Strategy
----------------------

The plugin header contains an explicit `Update URI` so WordPress does not confuse Music Project Core with an unrelated plugin using the same slug.

A future commercial update system may replace or supplement the current distribution process, but it should remain separate from Core's content and settings architecture.

A future updater should:

- Use WordPress-native update APIs.
- Verify package integrity and authorization.
- Fail without deleting or resetting user data.
- Allow the installed plugin to continue operating when the update service is unavailable.
- Keep schema migrations independent from the transport used to deliver the update.
- Avoid requiring a proprietary update mechanism for local development builds.

No proprietary update server is currently implemented.

Future License Integration Boundary
-----------------------------------

Music Project Core does not currently contain license-key validation or license enforcement.

If commercial licensing is introduced later, license state should be isolated from reusable site content and normal Core settings.

A future licensing layer should:

- Use its own namespaced option or storage boundary.
- Avoid placing license credentials inside Homepage, Theme Style, Footer, Social Links, Integration, or Site Status settings.
- Avoid deleting or disabling stored user content when a license expires.
- Avoid preventing administrators from exporting or recovering their own content.
- Treat temporary licensing-server failures as recoverable failures.
- Avoid remote telemetry unless it is separately disclosed and intentionally enabled where required.
- Keep payment processing and subscription billing outside this plugin.

No payment processing, subscription billing, or license enforcement is currently implemented.

Privacy and Remote Services
---------------------------

Music Project Core does not send product analytics or telemetry by default.

Core stores its configuration in the site's WordPress database.

Some user-configured features may intentionally communicate with third-party services, including:

- Google Fonts when a Google Fonts stylesheet URL is configured.
- Shows or Newsletter providers when their embeds are configured.
- Bandsintown when its optional integration is used.
- WordPress oEmbed providers when a supported external URL is rendered.
- Social platforms when visitors follow configured external links.

Site owners are responsible for evaluating the privacy, cookie, consent, and disclosure requirements of third-party services they choose to configure.

Core itself does not require a remote Music Project service to operate.

Free, Premium, and Project-Specific Functionality
-------------------------------------------------

Future product tiers should preserve the current ownership boundary.

### Music Project Core

Owns reusable settings, reusable content, schemas, migrations, and provider-neutral integration data.

### Music Project Base

Owns frontend markup, templates, CSS, JavaScript, responsive behavior, accessibility behavior, and presentation defaults.

### Future Premium Extensions

Should add functionality through documented Core APIs, filters, additional modules, or separately namespaced settings rather than forking the Core data model.

### Project-Specific Functionality

Should live in a project plugin, child theme, or another appropriate extension rather than being hard-coded into the reusable Core or Base products.

Premium functionality should not require moving reusable user content into the theme.

Branding and White-Label Considerations
---------------------------------------

The current product uses the Music Project Core and Music Project Base names in plugin, theme, documentation, and administration interfaces.

Future white-label functionality, if offered, should change presentation and branding without changing:

- Option names
- Custom post type identifiers
- Schema-version keys
- Public function names
- Public filter names
- Stored reusable content

Changing those internal identifiers for branding purposes would create unnecessary upgrade and compatibility risk.

Deferred Commercialization Work
-------------------------------

The following work is intentionally deferred until a commercial distribution model is selected:

- License-key storage and validation
- Paid entitlement checks
- Subscription billing
- Payment processing
- Customer-account infrastructure
- Proprietary update delivery
- Package-signing infrastructure
- Commercial download authorization
- Optional product analytics or telemetry
- Premium-feature packaging strategy
- Automated license activation and deactivation
- Customer support portal integration

These features should not be added to the production foundation until their requirements are defined.

Companion Theme
---------------

Music Project Base:

```text
https://github.com/trentofflarrabee/music-project-base
```

Support and Issues
------------------

Report reproducible problems through the repository issue tracker:

```text
https://github.com/trentofflarrabee/music-project-core/issues
```

Include:

- WordPress version
- PHP version
- Active theme
- Steps to reproduce
- Relevant PHP or browser-console errors

Version
-------

Current version:

```text
1.1.0
```

License
-------

Music Project Core is licensed under the GNU General Public License, version 2 or any later version.

SPDX license identifier: `GPL-2.0-or-later`.

See [LICENSE](LICENSE) for the complete license terms.
