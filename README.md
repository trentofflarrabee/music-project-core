# Music Project Core

Music Project Core is a WordPress plugin that provides reusable settings, content tools, integrations, and site-management features for music project websites.

It is designed to pair with the [Music Project Base](https://github.com/trentofflarrabee/music-project-base) theme.

## Requirements

- WordPress 6.8 or newer
- PHP 7.4 or newer
- Administrator access for plugin configuration

## Architecture

Music Project Core owns site configuration and reusable content.

The companion Music Project Base theme owns frontend templates, markup, responsive layout, and visual presentation.

This separation allows important content and settings to remain available when the active theme changes.

## Features

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