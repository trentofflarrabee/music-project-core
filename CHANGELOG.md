# Changelog

All notable changes to Music Project Core will be documented in this file.

This project follows the Keep a Changelog structure and intends to use Semantic Versioning for production releases.

## \[Unreleased\]

### Added

- WordPress administration screens for Homepage, Theme Style, Social Links, Footer, Integrations, and Site Status configuration.
- A homepage section registry with configurable visibility and ordering.
- Keyboard-accessible homepage section reordering controls.
- Configuration for Hero, Featured Content, Services, Quotes / Testimonials, Blog / News, Shows, and Newsletter sections.
- A reusable Quotes / Testimonials custom post type with attribution, featured-status, source-link, and manual-order metadata.
- Provider-neutral Shows and Newsletter integration fields.
- An optional Bandsintown shows shortcode adapter.
- Versioned settings-schema migrations with activation and administration update checks.
- Translation support using the music-project-core text domain.
- Site Status modes for Coming Soon and Maintenance pages.
- Administrative preview support for Site Status pages.

### Changed

- Reusable content and site configuration remain owned by Music Project Core rather than the active theme.
- Frontend asset versions use file modification times when available for cache busting.
- Deactivation, deletion, and reinstallation preserve plugin settings, schema-version data, Quotes / Testimonials, and quote metadata.
- Footer and Social Links configuration use consolidated reusable settings.
- Integration settings remain provider-neutral so providers can be changed without restructuring stored content.

### Security

- Added capability checks for privileged administration actions.
- Added nonce verification for protected requests and metadata updates.
- Added settings sanitization, strict allowlists, and defensive scalar handling.
- Added context-appropriate escaping for stored values rendered in administration and public output.
- Hardened Site Status request handling and administrator bypass behavior.