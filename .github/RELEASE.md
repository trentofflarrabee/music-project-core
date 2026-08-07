# Music Project Release Guide

This document is the product-level release checklist for:

- Music Project Core
- Music Project Base

Canonical branch for both repositories:

```text
main
```

Slim Volume is outside the scope of this release process.

This file is repository-only release documentation. The `.github/` directory is excluded from archives created with `git archive`.

Release Goal
------------

The release is ready only when:

- Core and Base pass repository inspection.
- Required documentation is current.
- Clean-install and upgrade testing pass.
- Core activation, deactivation, reactivation, deletion, and reinstall behavior pass.
- Base remains non-fatal when Core is inactive.
- Accessibility and responsive checks pass.
- PHP warnings, notices, and browser-console errors are resolved.
- Release ZIPs install cleanly.
- CHANGELOG files are finalized.
- Versions are bumped consistently only after all other gates pass.

Recommended First Production Version
------------------------------------

The recommended first production version for both Music Project Core and Music Project Base is:

```text
1.0.0
```

Rationale:

- The product is intended for real production use rather than an experimental prerelease.
- Core owns persistent user data and documented public extension boundaries.
- Schema migrations and upgrade behavior are versioned.
- Core and Base have a defined ownership boundary.
- Public hooks and data access functions are documented.
- A `1.0.0` release clearly establishes the point at which documented public APIs should be treated as stable.

Do not bump to `1.0.0` until all required release gates in this document pass.

Repository-Inspection Checks
----------------------------

These checks can be verified by repository inspection without running WordPress.

### Music Project Core

- [ ] Plugin header contains the intended production version.
- [ ] `MPC_VERSION` matches the plugin header version.
- [ ] `Requires at least` is accurate.
- [ ] `Requires PHP` is accurate.
- [ ] `Update URI` is present and correct.
- [ ] Text domain is `music-project-core`.
- [ ] `Domain Path` is `/languages`.
- [ ] `README.md` reports the same product version.
- [ ] `CHANGELOG.md` contains the release entry.
- [ ] `LICENSE` is present.
- [ ] `.gitattributes` contains release-export exclusions.
- [ ] `uninstall.php` remains explicitly non-destructive.
- [ ] Schema migrations remain versioned and non-destructive.
- [ ] Activation still invokes outstanding migrations.
- [ ] Normal administration requests still check for outstanding migrations.
- [ ] No destructive deactivation hook has been introduced.
- [ ] Core-owned settings remain in the plugin.
- [ ] Quotes / Testimonials remain plugin-owned.
- [ ] Public extension boundaries documented in the README match the code.
- [ ] No telemetry has been added by default.
- [ ] No payment, billing, or license-enforcement code has been added.
- [ ] No arbitrary custom-CSS setting has been introduced.
- [ ] No unexpected large dependency or framework has been introduced.

### Music Project Base

- [ ] Theme header contains the intended production version.
- [ ] Theme asset-version fallback matches the theme version.
- [ ] `Requires at least` is accurate.
- [ ] `Requires PHP` is accurate.
- [ ] `Update URI` is present and correct.
- [ ] Text domain is `music-project-base`.
- [ ] `Domain Path` is `/languages`.
- [ ] `README.md` reports the same product version.
- [ ] `CHANGELOG.md` contains the release entry.
- [ ] `LICENSE` is present.
- [ ] `screenshot.png` is present at the theme root.
- [ ] `.gitattributes` contains release-export exclusions.
- [ ] Parent theme CSS loads correctly with and without a child theme.
- [ ] Core-dependent function calls remain defensive.
- [ ] No reusable content or persistent site settings have moved into Base.
- [ ] No duplicate ownership of Core settings has been introduced.

Manual Test Environment
-----------------------

Record the environment used for final testing:

```text
Release candidate:
WordPress version:
PHP version:
Database:
Web server:
Browser(s):
Desktop viewport(s):
Mobile viewport(s):
Core commit:
Base commit:
Tester:
Date:
```

At minimum, test against:

- The minimum supported WordPress version when practical.
- The current stable WordPress release.
- PHP 7.4 when practical.
- A currently supported PHP 8.x release.
- A Chromium-based browser.
- Firefox.
- Safari or WebKit when practical.
- Desktop and mobile viewport sizes.

Fresh Install Checklist
-----------------------

Perform these checks on a new WordPress installation with no Music Project data present.

### Core Installation

- [ ] Install Core from the release ZIP.
- [ ] Activate Core.
- [ ] No activation fatal error occurs.
- [ ] No PHP warning or notice is logged.
- [ ] Music Project administration menu appears.
- [ ] Homepage settings screen opens.
- [ ] Theme Style screen opens.
- [ ] Social Links screen opens.
- [ ] Footer screen opens.
- [ ] Integrations screen opens.
- [ ] Site Status screen opens.
- [ ] Quotes / Testimonials administration works.
- [ ] Schema-version option is created only as expected by migrations.
- [ ] Activation does not create unexpected destructive or project-specific data.

### Base Installation

- [ ] Install Base from the release ZIP.
- [ ] Activate Base.
- [ ] No activation fatal error occurs.
- [ ] Theme screenshot appears in WordPress.
- [ ] Primary and Footer menu locations are available.
- [ ] Custom Logo support is available.
- [ ] Frontend stylesheet loads.
- [ ] Navigation JavaScript loads.
- [ ] No asset request returns 404.

### Initial Configuration

- [ ] Assign a static homepage.
- [ ] Assign a posts page.
- [ ] Assign a Primary Menu.
- [ ] Assign a Footer Menu.
- [ ] Add a custom logo.
- [ ] Configure at least one value on every Core settings screen.
- [ ] Create at least two Quotes / Testimonials.
- [ ] Publish at least three blog posts.
- [ ] Add featured images to representative content.

Upgrade Checklist
-----------------

Perform these checks on a copy of an existing installation containing real pre-release Music Project data.

Before upgrade:

- [ ] Back up the database.
- [ ] Record current Core version.
- [ ] Record current Base version.
- [ ] Record `mpc_schema_versions`.
- [ ] Export or record representative Homepage settings.
- [ ] Export or record representative Theme Style settings.
- [ ] Export or record Social Links.
- [ ] Export or record Footer settings.
- [ ] Export or record Integration settings.
- [ ] Export or record Site Status settings.
- [ ] Record existing Quotes / Testimonials and metadata.
- [ ] Capture representative frontend screenshots.

Upgrade:

- [ ] Replace Core with the release candidate without deactivating first.
- [ ] Visit WordPress administration so outstanding migrations can run.
- [ ] Replace Base with the release candidate.
- [ ] Do not manually reset any Music Project option.

After upgrade:

- [ ] No fatal error occurs.
- [ ] No PHP warning or notice occurs.
- [ ] Existing Homepage settings remain intact.
- [ ] Homepage visibility remains intact.
- [ ] Homepage section order remains intact.
- [ ] Hero settings remain intact.
- [ ] Featured Content settings remain intact.
- [ ] Services remain intact and in order.
- [ ] Quotes / Testimonials remain intact.
- [ ] Blog settings remain intact.
- [ ] Shows settings remain intact.
- [ ] Newsletter settings remain intact.
- [ ] Social Links remain intact.
- [ ] Footer settings remain intact.
- [ ] Theme Style settings remain intact.
- [ ] Site Status settings remain intact.
- [ ] Schema versions are current.
- [ ] Unknown extension-owned scalar settings used for testing remain intact where supported.
- [ ] Frontend presentation remains materially consistent with the pre-upgrade screenshots.

Core Lifecycle Checklist
------------------------

### Activation

- [ ] Core activates without fatal errors.
- [ ] Outstanding migrations run.
- [ ] Existing data is preserved.
- [ ] Repeated migration checks are safe.

### Deactivation

- [ ] Deactivate Core.
- [ ] No Core options are deleted.
- [ ] No Quotes / Testimonials are deleted.
- [ ] Quote metadata remains stored.
- [ ] Base does not produce a fatal error.
- [ ] Standard pages remain reachable.
- [ ] Standard posts remain reachable.
- [ ] Archives remain reachable.
- [ ] Search remains reachable.
- [ ] Header navigation remains usable.
- [ ] Mobile navigation remains usable.
- [ ] Footer remains non-fatal.

Expected limitation:

Base provides degraded fallback presentation when Core is inactive. It is not required to reproduce the configured Core-powered homepage.

### Reactivation

- [ ] Reactivate Core.
- [ ] Saved settings return.
- [ ] Quotes / Testimonials return unchanged.
- [ ] Homepage configuration returns.
- [ ] Theme Style configuration returns.
- [ ] Social Links return.
- [ ] Footer configuration returns.
- [ ] Integrations return.
- [ ] Site Status configuration returns.

### Plugin Deletion and Reinstall

On a disposable test environment:

- [ ] Deactivate Core.
- [ ] Delete Core through WordPress.
- [ ] Confirm Core settings remain in the database.
- [ ] Confirm schema-version data remains.
- [ ] Confirm Quotes / Testimonials remain.
- [ ] Confirm quote metadata remains.
- [ ] Reinstall Core from the release ZIP.
- [ ] Reactivate Core.
- [ ] Confirm preserved settings and Quotes / Testimonials are usable.

Homepage Test Matrix
--------------------

Test while logged in and logged out.

### Section Manager

- [ ] All sections enabled.
- [ ] Each section disabled individually.
- [ ] Multiple sections disabled.
- [ ] All optional sections disabled.
- [ ] Re-enable hidden sections.
- [ ] Drag-and-drop ordering.
- [ ] Keyboard ordering.
- [ ] Saved order persists after reload.
- [ ] Visibility remains independent from order.

### Hero

- [ ] Split layout.
- [ ] Full-bleed layout.
- [ ] Supported height variants.
- [ ] Desktop video.
- [ ] Mobile image.
- [ ] No media while logged in as administrator.
- [ ] No media while logged out.
- [ ] CTA.
- [ ] Social Links.
- [ ] Overlay settings.
- [ ] Content placement.
- [ ] Reduced-motion behavior.

### Featured Content

- [ ] Image content.
- [ ] Supported YouTube URL.
- [ ] Supported Vimeo URL.
- [ ] Invalid provider URL is rejected.
- [ ] HTTP supported URL is normalized to HTTPS where applicable.
- [ ] Featured quote behavior.
- [ ] CTA behavior.

### Services

- [ ] Add service.
- [ ] Remove service.
- [ ] Reorder services with pointer input.
- [ ] Reorder services with keyboard controls.
- [ ] Maximum item limit.
- [ ] Service links.
- [ ] Empty optional fields.
- [ ] Saved order persists.

### Quotes / Testimonials

- [ ] Create quote.
- [ ] Edit quote.
- [ ] Delete quote.
- [ ] Featured toggle.
- [ ] Source name.
- [ ] HTTP source URL.
- [ ] HTTPS source URL.
- [ ] Invalid protocol rejected.
- [ ] Manual ordering.
- [ ] Homepage quote count.
- [ ] Attribution visibility.
- [ ] Existing quote metadata survives normal edits.

### Shows

- [ ] Shows visible.
- [ ] Shows hidden.
- [ ] Heading.
- [ ] Shortcode integration.
- [ ] Supported embed integration.
- [ ] Supported oEmbed URL.
- [ ] Safe fallback link for non-embeddable HTTP/HTTPS URL.
- [ ] Empty integration state.
- [ ] Optional Bandsintown adapter when credentials are available.

### Blog / News

- [ ] Grid layout.
- [ ] Compact layout.
- [ ] Featured First layout.
- [ ] Latest featured post.
- [ ] Manual featured post.
- [ ] Invalid manual featured post falls back safely.
- [ ] Secondary posts do not duplicate the featured post.
- [ ] Featured images.
- [ ] Dates.
- [ ] Excerpts.
- [ ] Read More link.
- [ ] View All link.
- [ ] No published posts.

### Newsletter

- [ ] Newsletter visible.
- [ ] Newsletter hidden.
- [ ] Heading.
- [ ] Supporting text.
- [ ] Shortcode integration.
- [ ] Supported embed integration.
- [ ] Empty integration state.

Social Links Test Matrix
------------------------

- [ ] Built-in URL platforms save.
- [ ] Email saves.
- [ ] Invalid URL protocols are rejected.
- [ ] Hero display modes.
- [ ] Footer display modes.
- [ ] Mobile-navigation display behavior.
- [ ] Site Status social links.
- [ ] External links use expected target/rel behavior.
- [ ] Email uses `mailto:` and is not treated as an external web URL.
- [ ] Extension-provided platform definitions fail safely when malformed.
- [ ] Filtered rendered links fail safely when malformed.

Footer Test Matrix
------------------

- [ ] Tagline.
- [ ] Copyright.
- [ ] `{year}` token.
- [ ] `{site_name}` token.
- [ ] Brand enabled and disabled.
- [ ] Footer menu enabled and disabled.
- [ ] Social Links enabled and disabled.
- [ ] Simple layout.
- [ ] Stacked layout.
- [ ] Split layout.
- [ ] Empty optional values.
- [ ] Core inactive fallback is non-fatal.

Theme Style Test Matrix
-----------------------

- [ ] Core palette colors.
- [ ] Header colors.
- [ ] Mobile-navigation colors.
- [ ] Footer colors.
- [ ] Brand-display options.
- [ ] Header behavior options.
- [ ] Font library slots.
- [ ] Body typography role.
- [ ] General heading typography role.
- [ ] Blog/editorial heading typography role.
- [ ] Hero heading typography role.
- [ ] Navigation typography role.
- [ ] Button typography role.
- [ ] Accent typography role.
- [ ] Quote typography role.
- [ ] Heading text treatment.
- [ ] Hero text treatment.
- [ ] Corner styles.
- [ ] Card shadows.
- [ ] Border strength.
- [ ] Texture enabled and disabled.
- [ ] Texture image.
- [ ] Texture opacity.
- [ ] Texture sizing and repeat.
- [ ] Desktop header texture.
- [ ] Mobile navigation texture.
- [ ] Footer texture.
- [ ] Homepage section texture.
- [ ] Page/post texture.
- [ ] Valid Google Fonts HTTPS URL.
- [ ] Google Fonts HTTP URL normalizes to HTTPS.
- [ ] Non-Google Fonts host is rejected.

Site Status Test Matrix
-----------------------

### Disabled

- [ ] Public site renders normally.

### Coming Soon

- [ ] Logged-out visitor receives Coming Soon page.
- [ ] Administrator bypasses status page.
- [ ] Preview works.
- [ ] Heading.
- [ ] Message.
- [ ] Button.
- [ ] HTTP/HTTPS button URL.
- [ ] Invalid protocol rejected.
- [ ] Social Links enabled and disabled.
- [ ] Machine-readable routes receive expected behavior.

### Maintenance

- [ ] Logged-out visitor receives Maintenance page.
- [ ] HTTP status is `503`.
- [ ] `Retry-After` header is present as expected.
- [ ] Administrator bypasses Maintenance page.
- [ ] Preview works.
- [ ] Machine-readable routes receive expected behavior.

Editorial and Standard Template Matrix
--------------------------------------

- [ ] Static page.
- [ ] Page with featured image.
- [ ] Paginated page content.
- [ ] Posts page.
- [ ] Single post.
- [ ] Post with featured image.
- [ ] Previous/next post navigation.
- [ ] Archive.
- [ ] Category archive.
- [ ] Tag archive.
- [ ] Search with post results.
- [ ] Search with page results.
- [ ] Search with custom post type result when applicable.
- [ ] Search with no results.
- [ ] 404.
- [ ] General index fallback.
- [ ] WordPress block content.
- [ ] Blockquote.
- [ ] Long headings.
- [ ] Long body content.
- [ ] Empty optional content.

Navigation and Header Matrix
----------------------------

- [ ] Primary navigation desktop.
- [ ] Mobile navigation opens and closes.
- [ ] Mobile navigation keyboard operation.
- [ ] Focus is contained while mobile navigation is open.
- [ ] Focus returns to the toggle after closing.
- [ ] Escape closes mobile navigation.
- [ ] Expanded state is synchronized.
- [ ] Menu labels are synchronized.
- [ ] No-JavaScript navigation fallback.
- [ ] Standard header.
- [ ] Sticky header.
- [ ] Transparent header.
- [ ] Transparent-on-scroll behavior.
- [ ] Logged-in admin-bar offset desktop.
- [ ] Logged-in admin-bar offset mobile.
- [ ] Custom logo.
- [ ] Site-name fallback.
- [ ] Child theme stylesheet loads after Base when a child theme is active.

Accessibility Checklist
-----------------------

Perform keyboard-only testing.

- [ ] Skip link is first useful keyboard control.
- [ ] Skip link becomes visible on focus.
- [ ] Skip link targets `#site-main`.
- [ ] Visible focus treatment is present throughout.
- [ ] Primary navigation is keyboard usable.
- [ ] Mobile navigation is keyboard usable.
- [ ] Buttons have accessible names.
- [ ] Social links have understandable accessible names.
- [ ] Images use appropriate alternative text supplied through WordPress.
- [ ] Decorative presentation does not require pointer input.
- [ ] Heading hierarchy is reasonable on representative templates.
- [ ] Landmark structure is reasonable.
- [ ] Reduced-motion preference disables or reduces animated behavior.
- [ ] Hero video does not ignore reduced-motion behavior.
- [ ] No keyboard trap occurs outside the intentionally managed open mobile menu.
- [ ] Zoom to 200% remains usable.
- [ ] Narrow mobile layout does not require horizontal page scrolling.

Translation Readiness Checklist
-------------------------------

Repository inspection:

- [ ] Core text domain is consistent.
- [ ] Base text domain is consistent.
- [ ] JavaScript user-facing Core admin strings are localized from PHP.
- [ ] Translator comments exist for placeholder strings where required.
- [ ] No obvious public hard-coded English remains in PHP templates or administration screens.
- [ ] Local `languages/` path is documented.

Manual:

- [ ] Change the WordPress site language.
- [ ] Confirm Core and Base continue to load without warnings.
- [ ] Test with a generated pseudo-translation or real translation when available.
- [ ] Check long translated labels for layout overflow.
- [ ] Check mobile navigation with longer strings.
- [ ] Check buttons and administration labels with longer strings.

Performance and Asset Checklist
-------------------------------

- [ ] Base stylesheet loads once.
- [ ] Child stylesheet loads only when a child theme is active.
- [ ] Navigation JavaScript loads once.
- [ ] Local assets use file modification time for cache busting when available.
- [ ] Core admin assets load only where required.
- [ ] Homepage Blog custom queries avoid unnecessary found-row calculations.
- [ ] No obvious repeated remote request occurs on every frontend request without a feature requiring it.
- [ ] Google Fonts stylesheet loads only when configured.
- [ ] Hero video does not autoplay contrary to reduced-motion behavior.
- [ ] Browser network panel contains no unexpected 404s.
- [ ] Browser console contains no errors.
- [ ] Representative pages do not issue duplicate CSS or JS requests.

PHP and WordPress Compatibility Checklist
-----------------------------------------

- [ ] Test PHP 7.4 when practical.
- [ ] Test a current PHP 8.x release.
- [ ] Test the declared minimum WordPress release when practical.
- [ ] Test current stable WordPress.
- [ ] No PHP deprecation warnings appear in the tested environments.
- [ ] No PHP notices or warnings appear with `WP_DEBUG` enabled.
- [ ] No direct access to plugin/theme PHP files produces unintended output.
- [ ] Settings capability checks behave correctly.
- [ ] Nonce-protected administration actions behave correctly.
- [ ] Malformed scalar/array test values fail safely on disposable installations.
- [ ] Unsupported URL protocols are rejected where web URLs are required.
- [ ] Core and Base remain compatible with normal WordPress caching behavior.

Release ZIP Procedure
---------------------

Before creating either ZIP:

- [ ] Working tree is clean.
- [ ] Intended release commit is on `main`.
- [ ] CHANGELOG is finalized.
- [ ] Version bump is finalized.
- [ ] No temporary test files exist.
- [ ] No local database dumps exist.
- [ ] No test ZIP is tracked.

### Core

From the Core repository root:

```bash
git archive \
  --format=zip \
  --prefix=music-project-core/ \
  --output=../music-project-core.zip \
  HEAD
```

Verify:

- [ ] Exactly one top-level `music-project-core/` directory.
- [ ] `music-project-core.php` present.
- [ ] `uninstall.php` present.
- [ ] `includes/` present.
- [ ] `assets/` present.
- [ ] `README.md` present.
- [ ] `CHANGELOG.md` present.
- [ ] `LICENSE` present.
- [ ] `.gitattributes` absent.
- [ ] `.gitignore` absent.
- [ ] `.github/` absent.
- [ ] `.git/` absent.
- [ ] No test ZIPs or local files are present.

### Base

From the Base repository root:

```bash
git archive \
  --format=zip \
  --prefix=music-project-base/ \
  --output=../music-project-base.zip \
  HEAD
```

Verify:

- [ ] Exactly one top-level `music-project-base/` directory.
- [ ] `style.css` present.
- [ ] `functions.php` present.
- [ ] Required templates present.
- [ ] `assets/` present.
- [ ] `inc/` present.
- [ ] `template-parts/` present.
- [ ] `screenshot.png` present.
- [ ] `README.md` present.
- [ ] `CHANGELOG.md` present.
- [ ] `LICENSE` present.
- [ ] `.gitattributes` absent.
- [ ] `.gitignore` absent.
- [ ] `.github/` absent.
- [ ] `.git/` absent.
- [ ] No test ZIPs or local files are present.

Clean ZIP Installation Gate
---------------------------

Do not treat a locally checked-out repository as sufficient release validation.

On a clean WordPress installation:

- [ ] Upload and install the Core ZIP through WordPress administration.
- [ ] Activate Core.
- [ ] Upload and install the Base ZIP through WordPress administration.
- [ ] Activate Base.
- [ ] Complete a representative configuration.
- [ ] Confirm no missing-file errors.
- [ ] Confirm no permissions errors.
- [ ] Confirm no PHP warnings or notices.
- [ ] Confirm no browser-console errors.
- [ ] Confirm frontend CSS and JavaScript load.
- [ ] Confirm theme screenshot displays.
- [ ] Confirm both ZIPs can be removed and recreated reproducibly from the same release commits.

Version-Bump Procedure
----------------------

Version bumping is the final code/documentation change after all other release gates pass.

Use the same product version for Core and Base for the first coordinated production release unless there is a specific reason to version them independently.

Recommended first production release:

```text
1.0.0
```

### Music Project Core

Update all of the following in the same contained release change:

1. `music-project-core.php`

   Plugin header:

   ```text
   Version: 1.0.0
   ```

2. `music-project-core.php`

   Constant:

   ```php
   define('MPC_VERSION', '1.0.0');
   ```

3. `README.md`

   Current version:

   ```text
   1.0.0
   ```

4. `CHANGELOG.md`

   Move the completed release entries from `[Unreleased]` under:

   ```text
   [1.0.0] - YYYY-MM-DD
   ```

   Leave a new empty `[Unreleased]` section above the released version for future work.

### Music Project Base

Update all of the following in the same contained release change:

1. `style.css`

   Theme header:

   ```text
   Version: 1.0.0
   ```

2. `functions.php`

   Asset-version fallback:

   ```php
   return $version ?: '1.0.0';
   ```

3. `README.md`

   Current version:

   ```text
   1.0.0
   ```

4. `CHANGELOG.md`

   Move the completed release entries from `[Unreleased]` under:

   ```text
   [1.0.0] - YYYY-MM-DD
   ```

   Leave a new empty `[Unreleased]` section above the released version for future work.

### Version Verification

After the bump:

- [ ] Search Core repository for `0.1.0`.
- [ ] Search Base repository for `0.1.0`.
- [ ] Confirm any remaining occurrence is intentionally historical.
- [ ] WordPress Plugins screen reports Core `1.0.0`.
- [ ] WordPress Themes screen reports Base `1.0.0`.
- [ ] READMEs report `1.0.0`.
- [ ] CHANGELOG release headings report `1.0.0`.
- [ ] Rebuild both release ZIPs after the version bump.
- [ ] Perform the final ZIP installation smoke test again.

Release Notes Template
----------------------

Use this template for GitHub release notes.

```markdown
# Music Project Core / Music Project Base 1.0.0

## Summary

Music Project Core and Music Project Base provide a reusable WordPress foundation for music project websites, with persistent configuration and content owned by Core and frontend presentation owned by Base.

## Highlights

- [Highlight]
- [Highlight]
- [Highlight]

## Core

### Added

- [Core addition]

### Changed

- [Core change]

### Fixed

- [Core fix]

## Base

### Added

- [Base addition]

### Changed

- [Base change]

### Fixed

- [Base fix]

## Upgrade Notes

Existing Music Project settings and Quotes / Testimonials are preserved.

Core runs versioned, non-destructive schema migrations when required.

Back up the WordPress database before updating a production site.

## Requirements

- WordPress 6.8 or newer
- PHP 7.4 or newer

## Known Limitations

- Music Project Base provides only degraded fallback presentation when Music Project Core is inactive.
- Third-party fonts and provider embeds may create separate privacy, cookie, network, or consent obligations for the site owner.
- Commercial license validation, billing, proprietary updates, and telemetry are not included.

## Installation

Install Music Project Core first, then install and activate Music Project Base.

## Support

Report reproducible issues through the appropriate GitHub repository and include WordPress version, PHP version, steps to reproduce, and relevant PHP or browser-console errors.
```

Known Limitations
-----------------

The following limitations are accepted for the first production release unless explicitly changed before release:

1. **Base without Core is a degraded mode.**  
   Base must remain non-fatal and keep ordinary WordPress content reachable, but the designed Core-powered homepage is not reproduced when Core is inactive.

2. **Third-party integrations are site-owner choices.**  
   Google Fonts, Bandsintown, newsletter providers, oEmbed providers, and other configured services may make external requests or introduce their own privacy/cookie obligations.

3. **No commercial licensing platform exists yet.**  
   Core contains no license enforcement, subscription billing, payment processing, customer accounts, or proprietary update server.

4. **No telemetry is enabled by default.**  
   Future analytics or telemetry must be separately designed, disclosed, and intentionally enabled where required.

5. **Menus are intentionally curated.**  
   Base is not intended to become a general-purpose page builder or unrestricted navigation framework.

6. **Homepage sections are curated.**  
   The built-in section registry is explicit. Arbitrary template-path selection is intentionally not exposed as an extension mechanism.

7. **Core deletion is non-destructive.**  
   Removing the plugin files does not remove stored Music Project data. Permanent cleanup would require a future explicit opt-in mechanism.

8. **Multisite settings are per-site.**  
   Core does not currently provide a network-wide shared settings model.

Deferred Commercialization Tasks
--------------------------------

These tasks are intentionally outside the first production-readiness release:

- License-key storage.
- License validation.
- Paid entitlement checks.
- Subscription billing.
- Payment processing.
- Customer accounts.
- Proprietary update delivery.
- Package signing.
- Commercial download authorization.
- Premium-feature packaging.
- Automated license activation/deactivation.
- Customer support portal integration.
- Remote telemetry or product analytics.
- White-label administration UI.
- Automated commercial provisioning.

If implemented later, these features must preserve the existing Core/Base ownership boundary and must not require destructive migration of reusable user content.

Final Release Gate
------------------

Do not publish until every item below is true:

- [ ] Repository-inspection checks complete.
- [ ] Fresh-install checklist complete.
- [ ] Upgrade checklist complete.
- [ ] Core lifecycle checklist complete.
- [ ] Homepage matrix complete.
- [ ] Social Links matrix complete.
- [ ] Footer matrix complete.
- [ ] Theme Style matrix complete.
- [ ] Site Status matrix complete.
- [ ] Editorial/template matrix complete.
- [ ] Navigation/header matrix complete.
- [ ] Accessibility checklist complete.
- [ ] Translation-readiness checklist complete.
- [ ] Performance/assets checklist complete.
- [ ] PHP/WordPress compatibility checklist complete.
- [ ] Core release ZIP passes inspection and clean installation.
- [ ] Base release ZIP passes inspection and clean installation.
- [ ] No unresolved PHP warnings, notices, or deprecations remain in supported environments.
- [ ] No unresolved browser-console errors remain.
- [ ] Known limitations are accepted and documented.
- [ ] CHANGELOG files are finalized.
- [ ] Release notes are drafted.
- [ ] Final version bump is complete and consistent.
- [ ] Final ZIPs are rebuilt after the version bump.
- [ ] Final post-version-bump smoke test passes.

Only after this gate passes should Git tags and public release artifacts be created.
