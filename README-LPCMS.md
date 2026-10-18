**v0.11.38 - app-only selected work & cleaner public source**

- The DigitalPulse Team Lead **Selected real work** panel now references the three mobile-first web applications only.
- Dynamic theme variables and custom CSS now share the cache-busted `/assets/theme.css` endpoint instead of producing an inline style block.
- Public application JavaScript uses the direct static bundle path and executable scripts are deferred; crawlable inline JSON-LD is preserved.

**Previous: v0.11.37 - contact CTA alignment & portfolio ordering**

- Bottom-aligned the Business Card button with the Enquire button in the Contact card on tablet/desktop widths.
- Moved Web application projects to the front of the Portfolio / All projects view.
- Moved O&J Cars to the final portfolio position.
- Reordered Portfolio filters so Web applications appears before WordPress websites.
- Version bumped to `0.11.37` to invalidate cached site assets.

**v0.11.36 - mobile contact map frame height fix**

- Replaced the OpenStreetMap iframe + hand-positioned HTML overlays with a native Leaflet map using standard OpenStreetMap tiles (no API key).
- Northampton is now rendered by the map engine at `52.2405, -0.9027`; Riga at `56.9496, 24.1052`.
- The map automatically fits both locations with controlled padding and a sensible maximum zoom, so marker coordinates remain correct at every viewport size.
- Branded markers and permanent city labels are attached to the real geographic markers rather than approximated pixel positions.

**v0.11.34 - accurate Northampton & Riga map pins**

### v0.11.34 changes
- Corrected the map-pin anchor geometry: coordinates now align to the centre/tip of the marker icon instead of the centre of the icon-plus-label group.
- Northampton is pinned at 52.2405, -0.9027 and Riga at 56.9496, 24.1052 within the existing no-API-key OpenStreetMap Europe view.
- Map labels now show the city names `Northampton` and `Riga` for clearer location identification.
- Mobile marker anchoring is adjusted separately so the smaller pins remain accurate.
- Version bumped to `0.11.34` to invalidate cached map assets.

**v0.11.33 - FAQ control alignment & visual polish**

### v0.11.33 changes
- Rebuilt the FAQ plus/minus control using CSS-drawn strokes instead of font glyphs, so the symbol is optically and mathematically centred.
- FAQ rows now use a fixed control column and centred question content for consistent vertical alignment.
- Added cleaner hover/open states, subtle control shadow, and a divider above expanded answers.
- Mobile FAQ controls use the same centred geometry at a slightly smaller size.
- Version bumped to `0.11.33` to invalidate cached CSS.

# LPCMS

**Landing Page CMS** - a flat-file, one-entry PHP CMS with a React-powered frontend, JSON content storage, inline editing, optional blog, optional shop/catalogue, product modals, cart, quote basket, PWA install pages, cache tools, media tools, and SEO-friendly URLs.

LPCMS is built for fast landing pages, company websites, product catalogues, small shops, portfolios, blogs, galleries, service websites, and campaign pages where you want a CMS without a database.

The main application is still **one PHP file: `index.php`**. Content and settings are stored in `data.json`. The included `.htaccess` files only handle SEO URLs, performance headers, security rules, and asset protection.

---

## Current version

**v0.11.38 - app-only selected work & cleaner public source**

### v0.11.38 changes
- Restricts the Team Lead selected-work cards to the three mobile-first web applications.
- Serves theme variables and custom CSS together from `/assets/theme.css`.
- Uses the static public JavaScript path and defers executable browser scripts while retaining inline SEO JSON-LD.
- Version `0.11.38` invalidates cached CSS/JavaScript.

**v0.11.37 - contact CTA alignment & portfolio ordering**

**v0.11.34 - accurate Northampton & Riga map pins**

### v0.11.34 changes
- Corrects the visual marker anchor so the map point is not shifted left by the city label.
- Uses exact Northampton and Riga coordinates with Web Mercator positioning inside the no-key OpenStreetMap embed.
- Keeps distinct desktop/mobile marker offsets so the marker tip remains aligned at both sizes.
- Version `0.11.34` invalidates cached CSS and JavaScript.


**v0.11.31 - public bundle AdminPanel guard**

### v0.11.31 changes
- Fixed `ReferenceError: AdminPanel is not defined` on anonymous/public pages.
- The public JavaScript bundle no longer evaluates the admin-only component when the visitor is not authenticated.
- The authenticated admin bundle still includes the complete AdminPanel and editor tooling.
- Version bumped to `0.11.31` to force browsers/CDNs to request the corrected app bundle.
- Public and authenticated admin bundles now use separate cache URLs (`scope=public` / `scope=admin`), so a cached public bundle cannot be reused after login.
- The service worker skips admin-scoped assets and respects `private` / `no-store` responses.

**v0.11.30 - lean SEO source, public/admin bundle split, no-key map and portfolio update**

### v0.11.30 changes
- Reduced anonymous rendered HTML from multi-megabyte inline output to a lean SEO-focused document: metadata, hreflang/canonical links, JSON-LD and a compact noscript crawlable summary remain in HTML while application CSS/JavaScript are external assets.
- Removed public bootstrap/seed JSON from page source. The frontend now starts from minimal language/base-path data attributes and loads sanitized site data through `?api=site`.
- Split the browser application into a public bundle and an administrator bundle; anonymous visitors do not receive admin panels, demo fixtures or the admin translation dictionary.
- Added O&J Cars (`https://ojcars.uk/`) to the real-project portfolio and preserved multilingual portfolio copy.
- Changed the WordPress sector-theme technology badges to a dedicated row below the section heading on desktop, with responsive wrapping on smaller screens.
- Replaced the legacy tiled contact-map implementation with a no-key OpenStreetMap embed so the map does not depend on a third-party API key.
- Added/kept Twitter card metadata and current-language API/i18n pruning to reduce anonymous payloads.

### v0.11.29 changes
- Added the multilingual WordPress sector-theme showcase covering Bootstrap/Gutenberg editing, ready-made patterns, professional business/WooCommerce themes, newsletter/subscription forms and multisite/database options.
- Added translated WordPress sector-theme marketing copy for all configured languages.
- Kept imported theme JavaScript disabled by default and strengthened page-cache invalidation after application/data/translation changes.

**Previous: v0.11.28 - smoother interactive hero science graph and contact spacing**

### v0.11.28 changes
- Increased the Contact section **Open contact form** button top spacing by another 20px (36px → 56px).
- Upgraded the hero canvas into a smoother science/network graph: adaptive node density, subtle graph grid, hub markers, animated data pulses and cursor-proximity connection lines inspired by the referenced Particles.js effect.
- Kept the hero animation dependency-free, responsive, theme-colour aware, paused while the tab is hidden, and static when reduced motion is requested.

**v0.11.27 - contact, flag switcher, hero network and admin showcase cleanup**

### v0.11.27 changes
- Contact defaults now use London, UK and +44 00 000 000, with translated detail labels and grey icon-only social links sourced from the configured footer social profiles.
- Contact form button receives additional vertical separation for a cleaner call-to-action layout.
- Header language control now uses local SVG flag images; the dropdown keeps the full language name plus a small lowercase language code.
- Added a lightweight animated science/network particle canvas behind the hero, with reduced-motion support and no external animation dependency.
- Replaced the three outdated Admin showcase screenshots (Site & content, Content & media, Shop & integrations) with clean icon cards while preserving their translated titles and descriptions.
- Added local flag assets for all 14 supported languages and removed the obsolete generated Admin preview screenshot set.

### v0.11.26 changes
- Re-rendered the real LPCMS hero walkthrough at 1440x810 using a raw-frame pipeline, avoiding the previous JPEG intermediate compression.
- Higher-quality H.264 encode (CRF 17) for sharper UI text while retaining fast-start streaming.
- Smoother eased camera moves and longer crossfades between the inline editor, free-image picker, icon library and applied-icon state.
- Reduced aggressive zooming so the real interface stays crisp instead of blurred.
- Dark transparent cursor with subtle click pulses; no white cursor background.
- Added cache-busted default hero video/poster filenames while keeping the legacy video files updated for existing installations.

**v0.11.25 - faster real-product hero walkthrough**


### v0.11.25 changes

- Rebuilt the hero demo video from real LPCMS screens with five quicker views: inline text editing, free-image search, image import, icon selection/apply, and Save JSON.
- Replaced the bright/white pointer treatment with a compact dark transparent cursor and faster click pulses.
- Shortened the loop to about 12.2 seconds while showing more product actions.
- Kept the demo at 1280x720 H.264 with fast-start delivery and an AVIF poster.

### v0.11.20 changes

- Icon-enabled inline blocks show the selected icon immediately instead of covering it with the image-upload overlay.
- Clicking an icon tile opens the icon library directly, giving small feature/process icons a large reliable click target.
- Iconify library icons render as SVG images with a visible fallback instead of fragile CSS masks.
- The icon picker has larger targets, a current-icon preview, and selected-state feedback.
- The public Super Admin capability cards use a consistent Bootstrap icon set and a polished high-contrast icon treatment.

### v0.11.19 changes

- Fixed v0.11.18 lightbox CSS accidentally rendering as visible page text after the React scripts.
- Moved lightbox/close-button styles into the main head stylesheet.
- Rebuilt the global image viewer using explicit `data-lightbox` groups for Gallery, Admin showcase, editable content images and the main product image.
- Added full-size/fallback URL handling for broken image previews.
- Added post-render direct hash scrolling for URLs such as `/#contact`, including sticky-header offset and hashchange support.
- Version bump invalidates old v0.11.18 page-cache/service-worker cache keys.

### v0.11.18 changes

- Rebuilt the site-wide image modal gallery with normalized absolute media URLs, duplicate filtering, keyboard navigation, captions and graceful image-load fallback.
- Added an Admin-side **Preview image** control so image viewing works while inline image-edit controls are active.
- Fixed `/assets/...` URL handling for root and subdirectory/base-path installations.
- Product detail thumbnails keep their selection behavior while the main product image remains zoomable.
- Added the missing default SEO `/assets/img/og.jpg`, eliminating the remaining known packaged local-asset 404.
- Consolidated PWA install-prompt capture. The Chromium `Banner not shown...preventDefault()` console line is informational when LPCMS intentionally defers the native prompt to the footer Android install button.

### v0.11.17 changes

- Polished the Admin & editing showcase: explanation on the left, real Super Admin screenshot on the right, fixed 4:3 crop and top-aligned screenshot focus.
- Added a reusable site-wide image lightbox for public section images with backdrop close, Escape, keyboard arrows, previous/next controls and captions.
- Admin showcase thumbnails now use consistent 16:10 top-aligned crops and open in the same lightbox instead of new browser tabs.
- Normalized modal and Admin close-button padding, centering and touch target size.

- Features, Services, How it works, Image cards and Content list items can use icons instead of images. Curated open-source choices are built in and the full Iconify-backed picker supports Bootstrap Icons, Lucide, Tabler, Material Symbols, Material Design Icons and Fluent UI.
- Choosing an icon clears the item image automatically so the selected icon is visible immediately.
- All CMS image uploads, free-image imports and demo logo uploads are AVIF-only. Images are proportionally fitted inside the selected scale box without stretching and metadata is removed.
- Media includes selectable scale presets (Large, Hero, Content, Card, Square, Logo and Custom) plus width/height and AVIF quality controls.
- If the server cannot create a valid AVIF file, the upload stops with an explicit error instead of silently storing a heavier JPG/PNG/WebP original.
- Added a richer public Super Admin showcase using a real admin screenshot plus translated explanations of design/themes, commerce, booking/chat, media/icons, SEO/languages, plans/client workflow, integrations and technical tools.
- The Super Admin showcase copy is editable per language.
- Theme descriptions, theme actions/buttons and the new media/icon/admin-showcase controls have exact translations in Latvian, Russian, French, Spanish, German, Polish, Lithuanian, Estonian, Danish, Norwegian, Swedish, Finnish and Icelandic. Technical names, provider brands and protocol identifiers remain unchanged intentionally.
- Retains the v0.11.15 optical language-button alignment, v0.11.14 lazy-loading/reveal animation improvements, 8+8 product loading and section ordering.

**v0.11.10 - footer and app-install UI cleanup**

### v0.11.10 changes

- Removed the floating Install button; Android and Apple install actions now live only in the footer.
- Footer copyright uses the © symbol.
- Social links render as aligned icon-only circular buttons with accessible labels/tooltips.
- Footer layout was rebalanced for desktop, tablet and mobile alignment.
- Fixed a missing closing parenthesis in the React `InstallModal` render tree that caused `Uncaught SyntaxError: missing ) after argument list`.
- Restores normal application rendering and Android / Apple install controls.
- Release validation includes parsing the fully rendered inline browser script with Node.js.

**v0.11.8 - visible Android / Apple app install controls**

### v0.11.8 changes
- Android and Apple install badges are visible in the public footer whenever the mobile app option is enabled, including while a Super Admin is logged in and previewing the website.
- The floating Install button also remains available during admin preview.
- Footer badges open platform-specific install actions: Android uses the native PWA install prompt when available; Apple opens Add to Home Screen instructions.
- Install actions now preserve the active language URL and use translated UI labels.

**v0.11.7 - language-aware editing, reliable English return route, and localised admin UI**


### v0.11.7 changes

- Inline editing is now language-aware. When a non-English language is active, editable public text is saved under that item/section’s `i18n.<language>` override while English remains the canonical source value.
- Expanded language-aware editing to hero eyebrow/logo text, gallery alt text, pricing display text, contact address/hours, app/cookie public copy, rich text, blog SEO, products, booking services, chatbot questions and other public content fields.
- Fixed the header language picker so selecting **English** from `/lv/`, `/de/`, `/fr/`, etc. reliably returns to the unprefixed English URL `/` (and preserves deep routes such as `/de/blog/post` → `/blog/post`).
- Fixed internal root URL generation so English links do not produce a double slash.
- The admin interface now follows the active site language. Main tabs, actions, field labels, accessibility labels, prompts and common technical controls use the bundled admin translation dictionaries for Latvian, Russian, French, Spanish, German, Polish, Lithuanian, Estonian, Danish, Norwegian, Swedish, Finnish and Icelandic. Provider/brand names, protocol names, API identifiers, URLs and JSON keys intentionally remain unchanged.
- Admin shows the current **Editing language** beside the logged-in editor so it is clear which language version will be changed.
- Rich-text link/image prompts and Super Admin confirmation dialogs now use the active admin language.
- English remains the default/canonical site at `/`; translated content continues to use shareable `/xx/` language URLs.


### v0.11.6 changes

- English remains the canonical/default site content at `/`. The shipped public demo content is now translated for **Latvian, Russian, French, Spanish, German, Polish, Lithuanian, Estonian, Danish, Norwegian, Swedish, Finnish and Icelandic** when their language URL is active.
- Translation coverage now includes the demo hero, section headings/copy, services, gallery labels, testimonials, product catalogue copy/attributes, pricing text, booking/demo-intake copy, blog content, FAQs, contact/CTA text, chat questions, checkout labels and other shipped public content in addition to the existing UI controls.
- Language URLs and manual selection continue to use the bundled `assets/i18n.json` pack, while English remains the editable source language in `data.json`.
- Fixed **Choose a free image** returning no usable results when the legacy Openverse search parameter is rejected.
- Added **Free images · automatic** as the default source: Openverse first, Wikimedia Commons as an automatic no-key fallback.
- Added direct **Wikimedia Commons · no key** source selection.
- Fixed remote image imports always failing with **“The original image could not be saved.”** Remote provider downloads are temporary server files and are now copied into media storage correctly; `move_uploaded_file()` is reserved for actual browser uploads.
- Free-image dialogs show provider/search errors inside the modal instead of silently leaving an empty grid, and show source/licence links before import.
- Existing Pexels, Pixabay and Unsplash API-key sources remain available.
- Removed the old `.pre-i18n` migration snapshots (`data.json.pre-i18n`, `.htaccess.pre-i18n`, and `index.php.pre-i18n`). They were backup copies only and are not runtime dependencies.

### v0.11.4 changes
- English remains the default language at `/`.
- Shareable language routes: `/lv/`, `/ru/`, `/fr/`, `/es/`, `/de/`, `/pl/`, `/lt/`, `/et/`, `/da/`, `/no/`, `/sv/`, `/fi/`, and `/is/`.
- Language prefixes are preserved on blog, product, and page routes.
- Browser-language detection only selects supported languages and a manual choice is persisted.
- Added `hreflang` language alternates and `x-default`.
- Added French, Spanish, German, Polish, Lithuanian, Estonian, Danish, Norwegian, Swedish, Finnish, and Icelandic alongside English, Latvian, and Russian.
- Header language selector is now a circular active-flag button matching the Search control, with a compact flag/name dropdown.
- Added the bundled `assets/i18n.json` translation pack for public controls and shipped content strings.

**v0.11.3 - expanded Gallery/Testimonials demos, aligned product actions, and footer app-install badges**

### v0.11.3 changes

- Gallery ships with 9 demo images: 6 visible initially, then 3 on **Load more photos**.
- Testimonials ships with 9 demo reviews: 6 visible initially, then 3 on **Load more testimonials**.
- Product-card actions are bottom-aligned, compact and full-width for a cleaner catalogue grid.
- Footer can show clickable Android and Apple install badges linked to the PWA install flow.
- The footer install badges can be enabled/disabled in Super Admin under the App settings.

This release turns LPCMS into a clearer two-plan website/e-commerce product while keeping technical configuration under super-admin control. It also repairs the public component bundle and adds the client demo onboarding workflow.

Highlights:

- **Catalogue render repair:** restores the complete public Products component and its price, variation, badge, filter, card, cart/quote and share helpers so the app no longer fails with `Products is not defined`.
- **Public render repair:** restores Services, Gallery, Testimonials, FAQ and load-more components so the React app no longer fails with `Services is not defined`.
- **Blog demo:** ships with 9 demo posts, shows 6 initially, and loads 3 more per click.
- **Visitor chat:** choose **AI chatbot** or **WhatsApp**, place the launcher bottom-left or bottom-right, and configure suggested chatbot questions.
- **Client demo intake:** a four-step public form collects business details, copy, products, logo, branding, chat choice and booking needs, then creates a private draft for Super Admin review.
- **Approval and billing:** Super Admin can approve/build the submitted draft and then send the client a plan billing email with an invoice reference and configurable payment instructions.
- **PWA repair:** valid 192px, 512px and Apple touch PNG icons are included and the modern mobile web-app meta tag is present.

- **Premium** is the business-website plan at **£3.50/month or €4/month**.
- **Premium Pro** is the e-commerce plan at **£6.50/month or €7/month**.
- Pricing is a simple **two-column comparison** and includes install support for **Android & Apple via PWA**.
- Super admin can select the client plan, load the matching business/e-commerce demo, and configure the fallback address `chosenname.dev4.click` when no custom domain is used.
- Editors keep day-to-day content, media, products, booking and chatbot presentation controls. SMTP, provider keys, raw commerce configuration, direct checkout URLs, plan/demo controls, server tools and other infrastructure settings are **super-admin-only**.
- **AI writing tools** can write, improve, shorten, simplify, change tone, translate and prepare SEO-friendly copy. AI actions are available from the AI Writer panel, inline editable text, and the rich-text editor.
- **Visual content blocks** now include image cards, content lists and logo strips, with visual section enable/layout controls.
- **E-commerce** adds server-validated cart pricing, shipping methods, discount codes, cart/quote flows, payment-adapter options and a broader integration catalogue.
- **Abandoned-cart recovery** can capture a visitor cart by email, restore it from a secure recovery token, and send configurable first/second reminder messages.
- **Booking** includes service selection, live available time slots, instant confirmation, stored bookings, notification email and optional HTTPS webhook.
- **AI chatbot** can answer from the site's own content, products, pricing, FAQs and booking services. It has a local knowledge fallback and can optionally use the configured AI provider.
- Existing blog, SEO, themes, headless APIs, media tools, PWA, cache and navigation features remain included.

### Abandoned-cart scheduler

Cart capture and recovery work immediately. Automatic reminder delivery needs a server scheduler/cron job to call:

```text
GET /?api=abandoned-cart-cron&key=YOUR_SCHEDULER_KEY
```

Generate and manage the scheduler key under **Admin > Cart recovery**. The endpoint rejects an empty or incorrect key.

### Checkout integration note

Payment/shipping cards in the integration catalogue are provider profiles, not embedded provider SDKs. Live checkout can use the order form, an HTTPS hosted payment URL, a super-admin direct product checkout URL, or the server-side HTTPS checkout adapter. The adapter receives a server-priced cart and must return an HTTPS payment/redirect URL. LPCMS does not collect card details itself.

### AI access note

Consumer browser sessions are not reused as API credentials. Connected AI uses a client-provided API key stored server-side and masked from public/editor payloads. Without connected AI, the site chatbot can still answer using its local website-knowledge fallback.

### Important theme-import limitation

Arbitrary HTML themes cannot be perfectly converted into a dynamic CMS layout automatically. LPCMS v0.8.4 performs a **safe automated adaptation**: it imports the theme assets, analyses Bootstrap compatibility, discovers its styles/scripts, derives design colours, creates an LPCMS adapter stylesheet, and applies those assets to LPCMS's existing React/Bootstrap component structure. Theme-specific static HTML content is retained in the imported theme folder as a reference, but LPCMS continues rendering CMS content from `data.json` through its own components. Complex templates may still need manual CSS/component tuning.

---

## Project idea

LPCMS works like a compact headless CMS:

- PHP serves the HTML shell, SEO metadata, APIs, admin login, file uploads, JSON saving, redirects, form delivery, cache, and server config sync.
- React renders the one-page public website, inline editing UI, admin panel, catalogue filters, modals, cart, and quote basket.
- JSON stores the website content and configuration.
- Apache/LiteSpeed `.htaccess` adds pretty URLs, compression, caching, security headers, and JSON protection.

No MySQL database is required.

---

## Package structure

```text
lpcms/
├── index.php              # Main CMS app, API, admin, frontend shell, SEO, uploads, forms
├── data.json              # Site content, sections, users, settings, products, posts, SEO
├── .htaccess              # Clean URLs, cache, compression, protection, security headers
├── mail-test.php          # Local mail diagnostic helper
├── README.md              # Documentation
└── assets/
    ├── .htaccess          # Blocks script execution in assets/uploads
    ├── cache/             # Generated anonymous page cache files
    ├── css/               # Optional custom/generated CSS
    ├── docs/              # Documents and downloads
    ├── fonts/             # Local fonts
    ├── gallery/           # Gallery images
    ├── img/               # Logo, hero, section, product, and general images
    ├── js/                # Optional custom JavaScript
    ├── themes/            # Imported/adapted HTML and Bootstrap themes
    └── uploads/           # General uploaded media
```

When installed on a writable server, LPCMS can create missing runtime directories and files automatically.

---

## Requirements

Recommended:

- PHP 8.0 or newer.
- Apache or LiteSpeed hosting for `.htaccess` rewrite support.
- HTTPS enabled.
- Writable project directory for JSON saving, upload folders, cache, and `.htaccess` sync.
- PHP `mail()` or custom SMTP inside `cms_send_mail()`.
- Theme ZIP extraction uses **ZipArchive** when `php-zip` is installed, otherwise LPCMS automatically falls back to PHP **PharData + zlib**. Most standard PHP installations already include Phar and zlib. At least one extraction engine must be available.
- Outbound HTTPS from PHP (cURL or `allow_url_fopen`) for one-click free online theme imports. Local ZIP uploads do not need outbound access.

Optional but useful:

- GD or Imagick for image handling.
- GD/Imagick/ImageMagick/ffmpeg with AVIF support for AVIF conversion.
- If AVIF is unavailable, JPG, PNG, GIF, and WebP uploads still work and are saved safely as originals.

Nginx can run the PHP app, but `.htaccess` rules must be translated into an Nginx server block.

### Windows 11 + WSL2 + Docker

This package includes `Dockerfile` and `docker-compose.yml` for a local Apache/PHP environment with PHP ZipArchive enabled. ZipArchive is now optional for LPCMS theme imports when PharData + zlib are available.

Recommended workflow:

```bash
# Run these commands inside Ubuntu/WSL2, preferably from the Linux filesystem.
cd ~/projects/lpcms
docker compose up --build -d
docker compose exec lpcms php -r 'var_dump(class_exists("ZipArchive"));'
```

Open `http://localhost:8080`. The verification command should print `bool(true)`.

Keep the project under the WSL Linux filesystem (for example `~/projects/lpcms`) rather than `/mnt/c/...` when possible. File permissions and bind-mount performance are usually better there. The included Docker image installs `libzip-dev`, builds the PHP `zip` extension with `docker-php-ext-install zip`, and enables Apache rewrite/headers/expires modules.

If you already have your own official PHP Apache image, the essential Dockerfile step is:

```dockerfile
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
```

---

## Headless / GraphQL API

Headless APIs are controlled under **Admin > Headless**.

### REST-style public content API

```text
GET /?api=headless&resource=site
GET /?api=headless&resource=settings
GET /?api=headless&resource=settings.seo
GET /?api=headless&resource=products
GET /?api=headless&resource=products&slug=starter-website-package
GET /?api=headless&resource=blog&slug=how-flat-cms-works
```

The response format is:

```json
{
  "ok": true,
  "resource": "products",
  "slug": null,
  "data": {},
  "version": "0.11.6"
}
```

### GraphQL

Endpoint:

```text
POST /?api=graphql
```

Example request body:

```json
{
  "query": "query($slug:String!){ product(slug:$slug) { title slug price } }",
  "variables": {
    "slug": "starter-website-package"
  }
}
```

Another example:

```graphql
{
  settings {
    siteName
    defaultLang
  }
  products {
    title
    items
  }
}
```

Schema helper:

```text
GET /?api=graphql-schema
```

Supported GraphQL root fields are intentionally read-only and lightweight: `site`, `settings`, `header`, `footer`, `sections`, `products`, `product(slug:)`, `blog`, `post(slug:)`, `pages`, `page(slug:)`, `search(query:)`, and `path(value:)`.

This is a compact built-in GraphQL reader designed to preserve LPCMS's single-file deployment model. It is not intended to replace a full GraphQL server with mutations, subscriptions, directives, federation, or a Composer dependency stack.

### CORS

`settings.headless.corsOrigins` is an allowlist. Add exact origins such as:

```text
https://app.example.com
https://www.example.net
```

Use `*` only when the content is intentionally public to every origin.

---

## Theme Manager

Open **Admin > Themes** or use the floating **Themes** button while logged in.

### Built-in themes

LPCMS includes multiple built-in presets. Clicking **Switch theme** saves the selected design immediately and reloads the page.

### Import a ThemeForest or other Bootstrap HTML theme

1. Obtain/download the HTML theme ZIP under a licence that allows you to use it.
2. Open **Admin > Themes**.
3. Open **Themes → Import themes → Import Bootstrap / ThemeForest ZIP**.
4. Choose the ZIP in the modal window.
5. Add a theme name and licence/reference note.
6. Click **Import & rebuild for LPCMS**.
7. LPCMS extracts only safe static web files, stores imported HTML as non-executable `*.lpcms-source.txt`, finds the source `index.html`, detects Bootstrap, discovers local assets, scores compatibility, creates an adapter CSS file, registers the theme, and activates it.
8. Review the compatibility warnings in the theme card.
9. Imported JavaScript stays disabled unless you explicitly enable **Load imported theme JavaScript** and save.

Theme ZIP imports have conservative security limits: 40MB archive/total extraction limit, 8MB per extracted file, 3000 extracted files, path-traversal protection, and no PHP/server executable extraction.

### Free online Bootstrap themes

Open **Themes → Import themes → Browse free Bootstrap themes**. LPCMS now opens a dedicated modal catalogue; choose a theme and click **Import theme**. Free online themes do not ask you to choose a local ZIP: LPCMS downloads the allowlisted archive on the server, analyses it, adapts it, installs it, and activates it.

LPCMS does not accept an arbitrary remote URL for this feature. The download URL must match the built-in allowlist, which prevents the theme downloader from becoming an SSRF/general-purpose remote fetch endpoint.

### Theme files

Imported themes are stored under:

```text
assets/themes/<generated-theme-id>/
```

Each installed theme includes an LPCMS manifest:

```text
lpcms-theme.json
```

and an automatically generated adapter:

```text
lpcms-adapter.css
```

The original imported CSS/assets are retained inside the theme directory. Imported HTML is retained as `*.lpcms-source.txt` so LPCMS can analyse and compare it without exposing a directly executable same-origin HTML page. A developer can still open the source file as text and manually tune the adaptation when needed.

---

## Installation

1. Upload the complete `lpcms/` package to your hosting root or subdirectory.
2. Open `index.php` and change the secret:

```php
define('CMS_SECRET', 'your-unique-random-64-character-or-longer-secret');
```

3. Confirm the super admin settings:

```php
define('BREAKGLASS_PASSWORD', 'LPCMS@admin');
define('BREAKGLASS_ADMIN_EMAIL', 'raivis@63.lv');

const ADMIN_EMAILS = [
  'raivis@63.lv',
];
```

4. Open the website in the browser.
5. Open super admin with:

```text
/?admin
```

6. Enter the super admin password:

```text
LPCMS@admin
```

7. LPCMS sends a 5-character confirmation code to the super admin email.
8. Enter the code and log in.
9. Add editor/client users from the Users tab if needed.
10. Start editing content inline or from the admin panel.

---

## Login and user roles

LPCMS supports two admin login flows.

### Super admin

Super admin is opened through:

```text
/?admin
```

Flow:

1. Super admin enters the password `LPCMS@admin`.
2. LPCMS sends a 5-character code to `raivis@63.lv`.
3. Super admin enters the code.
4. Super admin gets full access.

Super admin can:

- Edit all content.
- Edit site settings.
- Edit SEO settings.
- Manage forms.
- Manage shop/catalogue settings.
- Manage app/PWA settings.
- Manage cache and media settings.
- Manage libraries.
- Manage redirects.
- Manage `.htaccess` sync.
- Add, remove, enable, and disable editor users.
- Access JSON editor.

### Editor/client users

Editors do not use the fixed super admin password. They log in through secure email login links.

Flow:

1. Editor clicks admin icon or opens login.
2. Editor enters email.
3. Email must exist in the editor/user list added by super admin.
4. LPCMS sends a secure login link.
5. Editor opens the link and can edit allowed content.

Editors can change basic website content, posts, products, media, forms, and SEO content, but protected system settings are preserved server-side.

---

## Public website sections

LPCMS is a one-page landing CMS with optional detail routes for posts, pages, and products.

Default editable sections:

- Header.
- Hero slider or static hero.
- About.
- Stats.
- Features.
- Services.
- Process/timeline.
- Gallery.
- Testimonials.
- Products / catalogue / shop.
- Pricing.
- Blog.
- FAQ accordion.
- Contact.
- CTA.
- Footer.

All sections can be shown or hidden from the admin panel.

---

## Header and navigation

Header functionality:

- Logo text or logo image.
- Inline logo image replacement.
- JSON-powered menu.
- Menu items and submenus.
- Smooth scroll links for one-page sections.
- Language selector from JSON.
- Ajax-style site search.
- Hamburger menu below XL screen size.

---

## Hero section

Hero functionality:

- One slide = static hero.
- Multiple slides = animated slider.
- First slide uses the main `h1`.
- Later slides can visually use `.h1` style without creating multiple page-level `h1` elements.
- Background/side image can be replaced inline.
- Button text and URL are editable.
- Hero text is editable inline.

---

## Visual inline editing

When logged in, admins can edit directly on the live page.

Editable content includes:

- Headings.
- Paragraphs.
- Section text.
- Card titles.
- Card descriptions.
- CTA text.
- Blog content.
- Product content.
- Pricing features.
- FAQ answers.
- Gallery metadata.
- Contact details.
- Footer text.

Click the editable area, type, and save JSON from the admin panel.

---

## Rich text editor

LPCMS includes a lightweight built-in Tiptap-style visual editor without requiring Node, npm, or a build step.

Editor tools include:

- Paragraph.
- Heading 2.
- Heading 3.
- Bold.
- Italic.
- Underline.
- Strikethrough.
- Bullet list.
- Numbered list.
- Quote.
- Align left.
- Align center.
- Align right.
- Link.
- Unlink.
- Insert image by URL.
- Upload and insert image.
- Clear formatting.
- HTML/source editing.

The rich editor is used for longer HTML fields such as About body, CTA body, blog body, and product body.

---

## Inline image replacement

Admins can replace images directly on the live website or inside admin tabs.

Supported image areas include:

- Logo image.
- Hero images.
- About image.
- Feature image.
- Service/card images where configured.
- Gallery images.
- Testimonial photos.
- Product images.
- Product gallery images.
- Modal/detail images.

Upload behaviour:

- Drag an image onto the image area or click the image edit overlay.
- LPCMS attempts AVIF conversion when supported.
- If AVIF is unavailable, LPCMS saves the original safe JPG, PNG, GIF, or WebP file.
- The JSON path is updated with the uploaded file URL.

---

## Blog functionality

Blog works as an optional section and as SEO-friendly detail pages.

Blog features:

- Enable/disable blog section.
- Blog cards on homepage.
- Load more button.
- Blog modal/detail view.
- Slug field.
- SEO title and meta description.
- Open Graph image.
- Rich body editor.
- Share icons.
- Clean route support.

Example blog URLs:

```text
/blog/post-slug
/post/post-slug
```

---

# Products, catalogue, and shop

The Products feature can run in two modes:

1. **Simple product section** - product cards, details modal, quote/buy buttons.
2. **Shop/catalogue mode** - product archive with search, filters, variations, cart, quote basket, and order form.

Admin can enable or disable Shop mode from the Products tab.

---

## Product section layout

Catalogue defaults:

- Section title: **Your Products**.
- Initial products: 12.
- Load more: 12 more.
- Desktop: 4 products per row.
- Tablet: 2 products per row.
- Mobile: 1 product per row.
- React/Ajax filtering without page reload.

Product card can show:

- Image.
- Title.
- Short description.
- Category.
- Brand.
- SKU.
- Price.
- Old price.
- Discount badge.
- Details button.
- Buy/add-to-cart button.
- Add-to-quote button.

---

## Product search

Product search is instant and React-based.

Search can match:

- Product title.
- Product ID.
- SKU.
- Category.
- Brand.
- Tags.
- Short text.
- Description/body.
- Attribute names.
- Attribute values.
- Variation SKU.
- Variation attributes.

Example searches:

```text
SKU-1001
red
large
starter kit
category name
brand name
```

---

## Product filters

Admin can define available categories and attributes in JSON/admin settings.

Catalogue filters can include:

- Category.
- Brand.
- Colour.
- Size.
- Material.
- Package.
- Capacity.
- Any custom product attribute added to JSON.

Filters work with product-level attributes and variation-level attributes.

---

## Product sorting

Available sort options:

- Default order.
- Title A-Z.
- Price low-high.
- Price high-low.

---

## Product modal view

Every product can open a modal without leaving the landing page.

Product modal can show:

- Main product image.
- Gallery thumbnails.
- Product title.
- Product ID.
- SKU.
- Category.
- Brand.
- Tags.
- Full description.
- Feature list.
- Price.
- Old price.
- Discount label.
- Stock label.
- Variation selector.
- Quantity field.
- Buy/add-to-cart button.
- Add-to-quote button.
- Social share links.

---

## Simple products

A simple product has one main SKU and one price.

Example:

```json
{
  "title": "Service Package",
  "slug": "service-package",
  "id": "P1001",
  "sku": "SP-1001",
  "type": "simple",
  "category": "Services",
  "brand": "LPCMS",
  "price": 120,
  "oldPrice": 150,
  "discountLabel": "-20%",
  "currency": "EUR",
  "image": "/assets/img/product.jpg",
  "gallery": [],
  "attributes": {
    "colour": "blue",
    "size": "standard"
  },
  "stock": 10,
  "inStock": true,
  "quoteButton": true,
  "buyButton": true
}
```

---

## Variable products

Variable products support multiple product variations.

Each variation can have:

- Variation ID.
- Variation SKU.
- Attributes.
- Price.
- Old price.
- Stock.
- Image.

Example:

```json
{
  "title": "T-Shirt",
  "slug": "t-shirt",
  "id": "P2001",
  "sku": "TSHIRT",
  "type": "variable",
  "category": "Clothing",
  "price": 0,
  "attributes": {
    "colour": ["Black", "White"],
    "size": ["S", "M", "L"]
  },
  "variations": [
    {
      "id": "P2001-BLK-M",
      "sku": "TS-BLK-M",
      "attributes": {
        "colour": "Black",
        "size": "M"
      },
      "price": 29,
      "oldPrice": 39,
      "stock": 5,
      "image": "/assets/img/tshirt-black.jpg"
    },
    {
      "id": "P2001-WHT-L",
      "sku": "TS-WHT-L",
      "attributes": {
        "colour": "White",
        "size": "L"
      },
      "price": 29,
      "oldPrice": 0,
      "stock": 3,
      "image": "/assets/img/tshirt-white.jpg"
    }
  ]
}
```

---

## Pricing and discounts

Product price logic:

- `price > 0` = priced product.
- `oldPrice > price` = old price is shown as crossed out.
- `discountLabel` = badge shown on card/modal.
- `price = 0` = quote-only product.

Button logic:

- If price is greater than `0`, Buy/Add to cart can be shown.
- If price is `0`, Buy/Add to cart is hidden and Add to quote is shown.
- Admin can enable both buttons where appropriate.
- Admin can make products quote-only by setting price to `0`.

---

## Cart functionality

Cart is used for priced products.

Cart features:

- Add product to cart.
- Select variation before adding.
- Choose quantity.
- Local browser storage for cart state.
- Floating cart button.
- Cart modal.
- Remove product line.
- Clear cart.
- Total calculation.
- Checkout/order request form.

LPCMS does not process card payments. The order is submitted through the form and can include direct bank transfer/direct debit instructions.

---

## Quote basket functionality

Quote basket is used for products that need a custom offer.

Quote features:

- Add to quote button.
- Price `0` products are quote-only.
- Floating quote basket button.
- Quote basket modal.
- Remove product line.
- Clear quote basket.
- Send quote request.
- All selected products are inserted into the form textarea automatically.

Quote line details can include:

- Product title.
- Product ID.
- SKU.
- Variation.
- Quantity.
- Price if available.

---

## Direct bank transfer / direct debit

LPCMS includes admin settings for direct bank transfer/direct debit style payment instructions.

Admin fields:

- Enabled/disabled.
- Payment title.
- Payment instructions.
- Account name.
- IBAN.
- SWIFT/BIC.
- Bank name.
- Reference prefix.

Typical flow:

1. Customer adds priced products to cart.
2. Customer opens cart and clicks checkout.
3. LPCMS opens the order form.
4. Cart lines are inserted into the form textarea.
5. Form submission is emailed and/or saved to JSON depending on settings.
6. Admin confirms the order and payment details manually.

This is a flat-file order request system, not a full payment processor.

---

## Product SEO URLs

Product detail routes are handled by `index.php` with `.htaccess` rewriting.

Examples:

```text
/product/product-slug
/products/product-slug
```

Server-side metadata can be generated for product routes:

- SEO title.
- Meta description.
- Canonical URL.
- Open Graph image.
- Product structured data where available.

---

## Pages and SEO URLs

LPCMS supports pretty routes for pages, posts, and products.

Examples:

```text
/page/page-slug
/pages/page-slug
/blog/post-slug
/post/post-slug
/product/product-slug
/products/product-slug
/privacy-policy
```

The `.htaccess` routes everything through `index.php`, and `index.php` detects the correct route and metadata.

---

## SEO functionality

SEO settings can be managed globally and per content item.

SEO features:

- Site title.
- Meta description.
- Robots meta.
- Canonical URL.
- Open Graph title.
- Open Graph description.
- Open Graph image.
- Blog metadata.
- Product metadata.
- Page metadata.
- Clean URLs.
- Redirects.
- JSON-LD structured data.
- Server-side metadata before React renders.

Recommended:

- Use one clear `h1` per page/route.
- Add unique titles/descriptions to products and blog posts.
- Use compressed product images.
- Keep slugs short and readable.

---

## Forms

LPCMS includes contact, CTA, quote, and order form flows.

Form features:

- Modal form support.
- Contact section form.
- CTA form.
- Quote request form.
- Cart/order request form.
- hCaptcha optional.
- Email delivery to configured recipients.
- Fallback to super admin email if recipient is empty.
- Multiple recipients separated by comma or semicolon.
- Reply-To header.
- Form submissions can be stored in JSON.
- Quote/cart products automatically go into the textarea.

If admin login emails work but forms do not, check the form recipient settings and hosting mail restrictions.

---

## hCaptcha

hCaptcha can be enabled from admin settings.

Recommended production setup:

- Add hCaptcha site key.
- Add hCaptcha secret key.
- Enable hCaptcha for public forms.
- Test after deployment.

---

## Gallery

Gallery features:

- Grid layout.
- Load more.
- Inline image replacement.
- Lightbox support if library is enabled.
- Images stored in `assets/gallery/` or selected upload directory.

---

## Testimonials

Testimonials section supports:

- Cards.
- Names.
- Role/company text.
- Photo/avatar.
- Load more.
- Inline text editing.
- Inline photo replacement.

---

## Services

Services section supports:

- Cards.
- Icons.
- Optional images.
- Text editing.
- Button text and links.
- Load more.

---

## Pricing section

Pricing cards support:

- Plan title.
- Price.
- Period.
- Description.
- Feature list.
- Button text.
- Button URL.
- Highlighted/recommended plans.

---

## FAQ accordion

FAQ section supports:

- Question/answer items.
- Bootstrap accordion behaviour.
- Editable text.
- SEO-friendly content on page.

---

## Contact section

Contact section supports:

- Google Maps embed.
- Contact details box.
- Phone.
- Email.
- Address.
- Social icons.
- Contact form.
- Modal form from buttons.

---

## CTA section

CTA section supports:

- Title.
- Rich HTML text.
- Button text.
- Modal form trigger.
- hCaptcha support when enabled.

---

## Footer

Footer supports:

- Copyright text.
- Footer menu.
- Social follow links.
- Admin icon login trigger.
- Scroll-to-top button.

---

## Mobile app install / PWA

LPCMS includes install helper routes inspired by mobile app install flows.

Routes:

```text
/?install=android
/?install=apple
/manifest.webmanifest
/service-worker.js
```

Admin App tab controls:

- App name.
- Short name.
- App icon URL.
- Theme color.
- Background color.
- Offline message.
- Android install page text.
- Apple install page text.

PWA notes:

- Installability depends on HTTPS, manifest, service worker, and browser rules.
- iOS installation is manual through Safari Share -> Add to Home Screen.
- Android can show install prompts when the browser allows it.

---

## Cache system

LPCMS includes a simple anonymous HTML cache.

Cache features:

- Enable/disable page cache.
- Cache TTL.
- Auto clear cache after saving JSON.
- Clear cache button.
- Clean old cache files button.
- Cache statistics.
- Cache files stored in `assets/cache/`.

Cache is intended for public anonymous pages. Admin sessions should bypass cache.

---

## Media settings

Media settings include:

- Upload directory management.
- Browser resize max width.
- Browser AVIF quality.
- Browser-side AVIF conversion toggle.
- Server AVIF fallback attempts.
- Safe original fallback when AVIF is unavailable.
- Media directory size scan.
- Cache/media stats.

Supported uploads:

- JPG.
- PNG.
- GIF.
- WebP.
- AVIF.
- HEIC/HEIF where server support exists.

If AVIF conversion is unavailable, normal images still upload.

---

## Optional libraries

Libraries can be connected or disconnected from admin settings.

Typical library toggles:

- Bootstrap.
- Bootstrap Icons.
- AOS animations.
- GLightbox.
- hCaptcha.
- Google Analytics after cookie consent.
- SCSS/custom CSS settings.

Disable unused libraries for better speed.

---

## Cookies and Google Analytics

LPCMS supports cookie consent and delayed analytics loading.

Recommended setup:

1. Add Google Analytics measurement ID.
2. Enable cookie banner.
3. Load analytics only after consent.
4. Link privacy/cookie policy in footer.

---

## Redirects

Redirects are stored in JSON and managed from the admin Redirects tab.

Use redirects for:

- Old URLs.
- Changed blog slugs.
- Changed product slugs.
- Landing campaign URLs.
- Removing `/index.php` from public links.

Redirect fields:

- From path.
- To path/URL.
- HTTP status, usually `301`.
- Enabled/disabled.

---

## `.htaccess` sync

LPCMS can create and synchronize `.htaccess` from admin/server settings.

Root `.htaccess` handles:

- Pretty URLs.
- Routing to `index.php`.
- Blocking direct `data.json` access.
- Directory listing prevention.
- Static asset cache headers.
- Compression rules.
- Security headers.
- Optional force HTTPS.
- Optional www to non-www redirect.

Custom rules should be placed outside the managed block:

```apache
# BEGIN FlatCMS
# LPCMS generated rules
# END FlatCMS
```

`assets/.htaccess` helps block script execution inside uploaded media folders.

---

## Theme settings

Theme settings are stored in JSON and applied dynamically.

Theme controls include:

- Brand color.
- Secondary/accent color.
- Background color.
- Text color.
- Border radius.
- Container/grid class.
- Button style.
- Section spacing.
- Custom CSS.
- Optional SCSS source/settings.

The default visual direction is clean Bootstrap landing page styling with softer fonts, cards, gradients, animations, and a Tabler-inspired admin interface.

---

## Data storage

Main runtime data is stored in:

```text
data.json
```

Data can include:

- Site settings.
- Admin/editor users.
- Header/footer.
- Menus.
- Languages.
- Sections.
- Blog posts.
- Products.
- Product categories.
- Product attributes.
- Product variations.
- Cart/quote settings.
- Forms.
- Submissions.
- SEO.
- Redirects.
- Theme settings.
- Library settings.
- App/PWA settings.
- Cache/media settings.

Back up `data.json` and `assets/` often.

---

## Backup

To back up a site, copy:

```text
data.json
assets/
index.php
.htaccess
```

Most important files:

- `data.json` - all content and settings.
- `assets/` - uploaded images, documents, cache, media.

---

## Security checklist

Before production launch:

- Change `CMS_SECRET`.
- Use HTTPS.
- Confirm `data.json` is blocked from public direct access.
- Keep only trusted users in the admin list.
- Replace default super admin settings if needed.
- Confirm login email delivery.
- Confirm form email delivery.
- Keep file permissions as strict as your host allows.
- Disable directory listing.
- Keep backups.
- Do not allow untrusted users to add arbitrary embeds or HTML.
- Use hCaptcha for public forms.
- Use server/CDN rate limiting when possible.

---

## Performance checklist

For best score:

- Use WebP/AVIF images where possible.
- Resize large images before upload or use LPCMS resize settings.
- Enable static asset caching.
- Enable compression.
- Disable unused libraries.
- Keep homepage product count reasonable.
- Use load more for catalogue and gallery.
- Avoid too many external embeds.
- Use CDN if traffic is high.
- Clear cache after major changes.

---

## Development notes

LPCMS intentionally avoids a build step. React and optional frontend libraries are loaded in the page, and the CMS application logic stays inside `index.php`.

This project is not WooCommerce and does not use WordPress custom post types. The v0.7.0 shop system recreates the main small-shop catalogue behaviours in flat JSON form:

- Products.
- Categories.
- Attributes.
- Variations.
- Discounts.
- Gallery.
- Cart.
- Quote basket.
- Bank transfer/direct debit instructions.
- Order/quote form submission.

For real payment gateways, stock reservation, tax/VAT rules, invoicing, shipping labels, coupons, and multi-user order management, additional development is required.

---

## Release history

### v0.8.4

- Removed the hard `php-zip` requirement from the Bootstrap/ThemeForest and free online theme import workflows.
- Added a native PHP **PharData + zlib** ZIP extraction fallback for servers where `ZipArchive` is not enabled.
- Import capability is now reported by the API as `archiveSupport`, including the preferred/available extraction engines.
- Theme import modals stay enabled when PharData is available and show which fallback engine LPCMS will use.
- Imported theme manifests record the archive extraction engine used for the installation.
- The PharData path keeps the same allowlisted extensions, path-traversal protection, non-executable HTML storage, 8MB-per-file, 40MB-total, and 3,000-file limits.
- `ZipArchive` remains the preferred engine when available; WSL2/Docker users can still use the included Docker setup to enable it.

### v0.8.3

- Added an inline-edit **Save changes?** modal. Text, alignment, colour, font weight, and clear-format changes can be previewed together; when the editor is exited LPCMS asks whether to save them to `data.json` or keep editing.
- The save confirmation is independent from product/search/theme modals, so inline editing inside an existing content modal does not destroy the previous modal state.
- Added silent save handling for the confirmation dialog, with inline error feedback instead of opening a second acknowledgement modal.
- Added `Dockerfile` and `docker-compose.yml` for Windows 11 + WSL2 + Docker, including PHP ZipArchive support.
- Added a WSL2/Docker setup and ZipArchive verification guide to the README.

### v0.8.2

- Fixed inline title/heading formatting controls disappearing after the first alignment, colour, or font-weight action.
- Moved the inline editor to a stable React component backed by context, so parent data updates no longer unmount the active editor toolbar.
- Alignment, text colour, font weight, and clear-format controls now update without closing the toolbar.
- Improved toolbar pointer handling so clicking alignment/clear icons does not steal the editable heading focus.
- Added accessible labels/tooltips to inline formatting controls.

### v0.8.1

- Fixed the product catalogue search toolbar height regression: the search wrapper no longer uses a flex basis that expands vertically, so the search icon stays inside the 48px search input and the large blank gap is removed.
- Reworked theme importing into explicit modal workflows.
- **Browse free Bootstrap themes** now opens a dedicated modal catalogue with an **Import theme** button on every allowlisted theme.
- Free online imports never ask for a local ZIP file; LPCMS sends `sourceType=free` and downloads the allowlisted archive server-side.
- **Import Bootstrap / ThemeForest ZIP** opens a separate ZIP upload modal with theme name and licence/reference fields.
- Added clear PHP ZipArchive availability messages to both import dialogs.

### v0.8.0

- Added read-only REST-style headless API and CORS controls.
- Added compact GraphQL read API with variables, nested selection sets, resource lookup, search, and schema helper.
- Added Themes admin UI plus a floating Themes shortcut for logged-in administrators.
- Added five built-in design presets and immediate theme activation.
- Added safe Bootstrap/HTML ZIP import with compatibility analysis, asset discovery, palette extraction, adapter CSS generation, registry, activation, and deletion.
- Added one-click allowlisted Start Bootstrap free-theme imports.
- Added explicit imported-JavaScript opt-in; imported scripts stay disabled by default.
- Added `assets/themes/` runtime storage and theme manifests.

### v0.7.4

- Improved product colour attributes.
- Colour/Color filters now render as visual swatch chips.
- Product colour badges now show a swatch instead of plain `colour: Blue` text.
- Product modal variation choices now show swatches when a variation has colour/color attributes.
- Kept SKU, category, size, material, and other non-colour attributes as readable text.

### v0.7.2

- Fixed `shopCartItems is not defined` React crash in the main `App` component.
- Added the missing `shopCartItems` / `setShopCartItems` state initializer from localStorage.
- Kept v0.7.x shop/catalogue, product modal, cart, quote, filters, and load-more functionality.

### v0.7.0

- Upgraded Products into optional Shop/Catalogue mode.
- Added category and variation filters.
- Added fast React/Ajax search by ID/SKU/title/category/attributes/variations.
- Added 12 item catalogue load more flow.
- Added responsive 4/2/1 product grid.
- Added product modal with gallery, variations, stock, discount, cart, quote.
- Added cart for priced products.
- Added quote basket for quote-only products.
- Added direct bank transfer/direct debit settings.
- Updated README with full shop documentation.

### v0.6.2

- Fixed image upload fallback when AVIF conversion is unavailable.
- JPG/PNG/GIF/WebP uploads work even without AVIF support.

### v0.6.1

- Fixed React hook order crash in admin panel after login.

### v0.6.0

- Added PWA/mobile install routes.
- Added app settings.
- Added anonymous page cache.
- Added cache/media maintenance tools.
- Added super admin `/?admin` flow with password plus 5-character email code.
- Added editor user management.

### v0.5.0

- Added rich text editor tools.
- Added inline image replacement.
- Improved form email handling.

### v0.4.0

- Improved Bootstrap/iLanding-style frontend design.
- Improved Tabler-style admin UI.
- Added stats, features, process, pricing, and FAQ sections.

---

## Naming convention

Repository/package naming:

```text
lpcms
lpcms.zip
lpcms-v0.8.4.zip
```

---

## License

Add your preferred license before public release.


## v0.7.4 catalogue filter upgrade

This release improves the product catalogue UI and demo content.

### Compact product filter toolbar

The product section now uses compact dropdown filters instead of wide colour chips on the page. It is inspired by WooCommerce-style filter interfaces that support AJAX filtering, sorting, colour swatches, numeric ranges, categories, and product attributes.

Included catalogue filters:

- fast Ajax-style product search by title, ID, SKU, brand, category, tag, attribute, and variation data
- compact category dropdown
- compact colour dropdown with colour swatches
- compact dropdowns for size, material, license, duration, and any custom product attribute
- price range dropdown with minimum/maximum fields and range controls
- sorting by default order, title, price low-high, and price high-low
- active filter chips with one-click removal
- reset button for all active filters

### Demo products

The package now includes 20 demo products covering simple products, variable products, quote-only products, SKUs, product IDs, categories, brands, tags, colour swatches, sizes, materials, licenses, durations, price ranges, discount badges, stock values, modal descriptions, cart, and quote-basket flows.

Super admin can also use **Products → Import 20 demo products** to restore the complete demo product set.


### v0.11.17 Admin showcase and image lightbox
- Feature, Service and How-it-works items can use open-source icons from Bootstrap Icons, Lucide, Tabler, Material Symbols, Material Design Icons and Fluent UI through the existing Iconify-backed picker.
- All image uploads/imports are AVIF-only and scale proportionally inside a configurable width × height box.
- The public Admin showcase includes a real Super Admin control-centre screenshot and a clearer explanation of protected functionality.
- Theme/admin copy received an additional multilingual exact-translation pass.

### v0.11.36
- Fixed the mobile Contact map card leaving a large empty area below the Leaflet map.
- The outer map shell, Leaflet wrapper and map canvas now share one responsive mobile height (`clamp(300px, 72vw, 360px)`).
- Desktop/tablet map sizing and the exact Northampton/Riga coordinates are unchanged.
