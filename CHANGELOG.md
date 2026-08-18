# Changelog

All notable changes to Music Project Core will be documented in this file.

This project follows the Keep a Changelog structure and intends to use Semantic Versioning for production releases.

## [Unreleased]

## [1.4.0] - 2026-08-18

### Added

- Added curated Compact, Standard, and Large heading-size controls for individual homepage sections.
- Added independent quote-size controls for Featured Content quotes and the Quotes / Testimonials homepage section.
- Added optional custom quote-text colors for Featured Content and Quotes / Testimonials with inherited-color fallback.
- Added Theme Style Color System v2 with clearer semantic color roles.
- Added a dedicated Heading Color separate from normal body Text Color.
- Added a dedicated Link Color for ordinary editorial and text-style links.
- Added a configurable browser Text Selection Color.
- Added automatic readable foreground selection for browser text selection.
- Added a live Theme Style color preview with palette swatches and representative heading, body, link, accent, button, muted-text, and selection examples.

### Changed

- Reorganized Theme Style colors into Foundation, Brand & Interaction, and Hero groups.
- Added explanatory descriptions to Theme Style color controls.
- Narrowed Accent / Highlight Color to decorative emphasis, focus treatment, icons, editorial accents, and other branded details rather than ordinary text links.
- Homepage typography emphasis is configured per section rather than through one blanket homepage heading size.
- Hero typography remains independent from homepage section-heading controls.
- Shows and Newsletter heading-size settings remain owned by Integrations alongside their existing section content.
- Homepage quote typography remains separate from blog and editorial blockquotes.

### Security

- Added strict allowlist normalization for homepage typography-size presets.
- Added WordPress hex-color sanitization for optional homepage quote colors and new Theme Style semantic colors.

## [1.3.0] - 2026-08-17

### Added

- Added curated Page Title Presentation controls for standard WordPress Pages.
- Added Standard, Editorial Panel, and Minimal Overlay Page title styles.
- Added global Page title panel tone controls using existing Theme Style palette roles.
- Added Soft, Strong, and Solid title-panel strength presets.
- Added Compact, Standard, and Large responsive Page-title size presets.
- Added per-Page title-style overrides with a Use Theme Default option.
- Added the public `mpc_get_page_title_style()` resolver for rendering-neutral Page presentation state.

### Changed

- Page Title Presentation is explicitly scoped to ordinary WordPress Pages.
- The static Homepage, Posts page, and assigned Link Hub Page are excluded from Page Title Presentation.
- Existing sites continue to use the Standard Page presentation by default.
- Per-Page overrides preserve global panel tone, strength, and title-size settings rather than creating Page-specific design systems.

### Security

- Page Title Presentation settings are restricted to curated allowlists.
- Per-Page presentation metadata is protected by WordPress capability and nonce checks.
- Invalid Page presentation values fall back safely to the Standard presentation.

## [1.2.0] - 2026-08-17

### Added

- Added the first-party Link Hub feature, presented in WordPress administration as **Link in Bio**.
- Added the dedicated `mpc_link_hub_settings` option for persistent Link Hub configuration.
- Added an explicit **Configure Link Hub Page** action that preserves valid assignments, reuses suitable existing Pages, and creates a new `Links` Page only when explicitly requested.
- Added profile-image modes for automatic Custom Logo reuse, a custom Media Library image, or no profile image.
- Added optional display-name and tagline overrides.
- Added Spotlight, Stack, and Poster layout selections.
- Added an ordered Link Hub editor supporting links and section headings.
- Added keyboard-accessible Move Up / Move Down controls and drag-and-drop enhancement for item ordering.
- Added curated Link Hub icons, optional subtitles, enabled states, new-window behavior, and one featured-link presentation state.
- Added a 30-item server-side Link Hub limit.
- Added public Link Hub data helpers:
  - `mpc_get_link_hub_settings()`
  - `mpc_get_link_hub_setting()`
  - `mpc_get_link_hub_items()`
  - `mpc_get_link_hub_url()`
  - `mpc_is_link_hub_enabled()`
  - `mpc_get_link_hub_page_id()`
- Added `mpc_link_hub_items` and `mpc_link_hub_url` extension filters.
- Added Link Hub schema-version tracking.

### Changed

- Link Hub reuses existing Music Project Social Links rather than storing duplicate social-account configuration.
- Link Hub page routing is based on the assigned WordPress Page ID rather than a hard-coded `/links/` slug.
- Link Hub settings remain non-destructive across plugin deactivation, deletion, theme changes, and page reassignment.

### Security

- Added strict Link Hub normalization and sanitization for settings and ordered items.
- Restricted Link Hub URL protocols to validated `http`, `https`, `mailto`, and `tel` destinations.
- Restricted icons, layouts, item types, and visual variants to curated allowlists.
- Added capability and nonce protection for Link Hub administration and Page configuration actions.
- Prevented malformed or incomplete enabled links from being exposed through the frontend data API.

## [1.1.0] - 2026-08-13

### Added

- Added separate Blog / Editorial Body and Site Branding typography role assignments.
- Added Compact, Standard, and Large Single Post Body Size presets.
- Added Theme Style guidance clarifying that the WordPress Custom Logo is separate from the Site Icon and linking administrators to the Custom Logo control.

### Changed

- Split Navigation typography from textual site branding so each can use its own configured font role.
- Recalibrated texture intensity presets to Soft (`0.45`), Standard (`0.72`), and Strong (`0.99`), with Standard as the default.

## [1.0.0] - 2026-08-08

- WordPress administration screens for Homepage, Theme Style, Social Links, Footer, Integrations, and Site Status configuration.
- A homepage section registry with configurable visibility and ordering.
- Keyboard-accessible homepage section reordering controls.
- Configuration for Hero, Featured Content, Services, Quotes / Testimonials, Blog / News, Shows, and Newsletter sections.
- A reusable Quotes / Testimonials custom post type with attribution, featured-status, source-link, and manual-order metadata.
- Provider-neutral Shows and Newsletter integration fields.
- An optional Bandsintown shows shortcode adapter.
- Versioned settings-schema migrations with activation and administration update checks.
- Translation support using the `music-project-core` text domain.
- Site Status modes for Coming Soon and Maintenance pages.
- Administrative preview support for Site Status pages.
- Administrator-assisted WordPress Homepage and Posts-page routing setup that preserves valid existing page assignments and creates only missing pages when explicitly requested.

### Changed

- Reusable content and site configuration remain owned by Music Project Core rather than the active theme.
- Frontend asset versions use file modification times when available for cache busting.
- Deactivation, deletion, and reinstallation preserve plugin settings, schema-version data, Quotes / Testimonials, and quote metadata.
- Footer and Social Links configuration use consolidated reusable settings.
- Integration settings remain provider-neutral so providers can be changed without restructuring stored content.
- Homepage Blog / News routing can follow the Posts page assigned in WordPress Reading Settings when no custom View All URL is supplied.

### Security

- Added capability checks for privileged administration actions.
- Added nonce verification for protected requests and metadata updates.
- Added settings sanitization, strict allowlists, and defensive scalar handling.
- Added context-appropriate escaping for stored values rendered in administration and public output.
- Hardened Site Status request handling and administrator bypass behavior.