Music Project Core
==================

Music Project Core is a WordPress plugin that provides reusable settings, content tools, integrations, and site-management features for music project websites.

It is designed to pair with the [Music Project Base](https://github.com/trentofflarrabee/music-project-base) theme.

Requirements
------------

*   WordPress 6.8 or newer
    
*   PHP 7.4 or newer
    
*   Administrator access for plugin configuration
    

Architecture
------------

Music Project Core owns site configuration and reusable content.

The companion Music Project Base theme owns frontend templates, markup, responsive layout, and visual presentation.

This separation allows important content and settings to remain available when the active theme changes.

Features
--------

### Homepage Section Manager

Control the visibility and order of these homepage sections:

*   Hero
    
*   Featured Content
    
*   Services
    
*   Quotes / Testimonials
    
*   Shows
    
*   Blog / News
    
*   Newsletter
    

Sections can be reordered through drag-and-drop or keyboard-accessible move controls.

### Hero

Configure:

*   Split or full-bleed layouts
    
*   Compact, standard, or full-screen height
    
*   Desktop video
    
*   Mobile image
    
*   Overlay style and opacity
    
*   Content placement and alignment
    
*   Heading, supporting text, and call to action
    
*   Social-link display
    

### Featured Content

Create a promotional section for:

*   Releases
    
*   Videos
    
*   Announcements
    
*   Featured messages
    
*   Images or supported video URLs
    
*   Optional embedded featured quotes
    

### Services

Create, remove, and reorder up to eight service items.

Each service supports:

*   Heading
    
*   Description
    
*   Link text
    
*   Link URL
    

Curated layouts and column choices are included.

### Quotes / Testimonials

Quotes and testimonials are stored as reusable WordPress content.

Available controls include:

*   Featured status
    
*   Source or client
    
*   Source URL
    
*   Manual ordering
    
*   Homepage count
    
*   Layout
    
*   Attribution visibility
    
*   Background tone
    

### Blog / News

Configure homepage blog presentation, including:

*   Grid, Featured First, or Compact layouts
    
*   Latest or manually selected featured post
    
*   Post counts
    
*   Featured images
    
*   Dates
    
*   Excerpts
    
*   Read More and View All labels
    

### Shows and Newsletter

The Integrations screen provides provider-neutral fields for:

*   Shows or event embeds
    
*   Newsletter signup embeds
    
*   Section headings and supporting text
    

An optional Bandsintown adapter is included through the following shortcode:

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   [mpc_bandsintown_shows artist="Artist Name" app_id="your-app-id" signup_target="#signup"]   `

The application ID may also be supplied through the BIT\_APP\_ID environment variable.

### Social Links

Manage reusable social and contact links for supported placements such as:

*   Homepage hero
    
*   Mobile navigation
    
*   Footer
    
*   Site Status page
    

Display modes include labels, icons, or icons with labels.

### Theme Style

Theme Style provides curated controls for:

*   Core colors
    
*   Header, mobile-navigation, and footer colors
    
*   Header behavior
    
*   Brand display
    
*   Corner styles
    
*   Card shadows
    
*   Border strength
    
*   Font stacks
    
*   Semantic typography assignments
    
*   Blog and editorial typography
    
*   Hero typography
    
*   Environmental background texture
    

Texture placements include:

*   Desktop header
    
*   Mobile navigation
    
*   Footer
    
*   Homepage section backgrounds
    
*   Pages and posts
    

### Footer

Configure:

*   Tagline
    
*   Copyright text
    
*   {year} and {site\_name} tokens
    
*   Brand visibility
    
*   Footer menu visibility
    
*   Social-link visibility
    
*   Simple, Stacked, or Split layouts
    

### Site Status

Temporarily place the public site into:

*   Coming Soon mode
    
*   Maintenance mode
    

Administrators retain access and can preview the status page before enabling it publicly.

Installation
------------

1.  Download or clone this repository.
    
2.  wp-content/plugins/music-project-core
    
3.  Activate **Music Project Core** under **Plugins** in WordPress.
    
4.  Install and activate the companion Music Project Base theme.
    
5.  Open the **Music Project** menu in WordPress administration.
    

Suggested Setup Order
---------------------

1.  Configure Homepage section visibility and order.
    
2.  Add Hero and Featured Content.
    
3.  Add Services and Quotes / Testimonials.
    
4.  Configure Shows and Newsletter under Integrations.
    
5.  Add Social Links.
    
6.  Configure Theme Style.
    
7.  Configure the Footer.
    
8.  Review Site Status settings.
    

Stored Data
-----------

Music Project Core stores configuration in these WordPress options:

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   mpc_homepage_settings  mpc_theme_style_settings  mpc_integrations_settings  mpc_footer_settings  mpc_social_links_settings  mpc_site_status_settings  mpc_schema_versions   `

Quotes and testimonials use the following custom post type:

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   mpc_press_quote   `

Activation and Updates
----------------------

Outstanding schema migrations run when the plugin is activated.

A lightweight migration check also runs in WordPress administration so normal plugin updates can apply new schema changes without requiring deactivation and reactivation.

Deactivation and Uninstall Policy
---------------------------------

Deactivating Music Project Core does not delete settings or content.

Deleting the plugin also preserves:

*   Plugin settings
    
*   Schema-version information
    
*   Quotes / Testimonials
    
*   Quote metadata
    

This non-destructive policy prevents accidental data loss and allows the plugin to be reinstalled later.

A future explicitly opt-in cleanup tool may provide permanent data removal.

Translation
-----------

The plugin uses the text domain:

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   music-project-core   `

Local translation files may be placed in:

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   languages/   `

Development
-----------

The plugin uses WordPress-native APIs, including:

*   Settings API
    
*   Custom post types
    
*   Media Library
    
*   Shortcodes
    
*   Nonces and capabilities
    
*   Translation functions
    
*   Activation hooks
    

Source assets are loaded directly from the repository. Asset versions use file modification times when available for cache busting.

Companion Theme
---------------

Music Project Base:

`https://github.com/trentofflarrabee/music-project-base   `

Support and Issues
------------------

Report reproducible problems through the repository issue tracker:

`https://github.com/trentofflarrabee/music-project-core/issues   `

Include:

*   WordPress version
    
*   PHP version
    
*   Active theme
    
*   Steps to reproduce
    
*   Relevant PHP or browser-console errors
    

Version
-------

Current development version:

` 0.1.0   `

License
-------

A project license has not yet been selected.

Add a LICENSE file and update this section before a public release.
