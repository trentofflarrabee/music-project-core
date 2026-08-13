# Changelog

All notable changes to Music Project Core will be documented in this file.

This project follows the Keep a Changelog structure and intends to use Semantic Versioning for production releases.

## [Unreleased]

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