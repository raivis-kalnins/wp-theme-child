**v0.11.44 — custom WordPress plugins / apps / smart e-commerce SEO expansion**

- New **33 UK, Baltic and Nordic market pages** in **14 languages**, server rendered, with distinct original optimized WebP illustrations, canonical and hreflang tags, schema.org structured data, internal links, FAQ sections, and a sitemap.
- Main homepage and SEO descriptions promote **custom WordPress plugins / Gutenberg / ACF**, **bespoke PHP/React apps**, and **smart WooCommerce development** in all 14 languages.
- Social 63 portfolio entry uses a lightweight **concept preview** with an honest **In development** label.
- Route directory: `/locations/` · SEO feeds: `/sitemap.xml`, `/robots.txt`, optional `/llms.txt`.
- **IMPORTANT:** Preserve/merge newer live `data.json` edits and never replace production `.secrets.php` without checking. See **README-SEO-LOCATIONS.md** for deployment and SEO notes.

**v0.11.42 - complete DigitalPulse apps portfolio, multilingual launcher and Clutch footer**

- Updated **Portfolio** with all **17 requested DigitalPulse apps** and their correct HTTPS URLs, richer descriptions, relevant technology tags and direct external links (new tabs). Older client projects are preserved: **32 projects total**.
- Added a responsive, compact **DigitalPulse Apps** icon-and-description launcher immediately after Portfolio. All 17 app links open safely in new tabs (`noopener noreferrer`). The Apps block is editable in the LPCMS Content admin panel.
- App portfolio thumbnails use an existing live third-party screenshot service (thum.io). Each of the 17 apps has a bundled **local SVG preview fallback** at `assets/img/apps/` if the remote screenshot cannot be fetched (for example, login-only apps or blocked screenshot traffic). No images sent inline with a chat message were extractable as files in the project.
- Included localized app descriptions and section titles for **EN, LV, RU, FR, ES, DE, PL, LT, ET, DA, NO, SV, FI, IS**; retained the original multilingual site and its existing translations.
- Included a **Clutch profile icon** in the footer linked to `https://clutch.co/profile/digital-pulse-1`, and a GitHub source link on the WPBB Commerce Platform project.
- Updated both public and admin JavaScript, existing PHP SEO/no-JavaScript HTML and CSS, preserving the existing project content and CMS administration.
- **Deploy:** back up your current `data.json` (it holds CMS edits), site files, and `.secrets.php`, then replace the extracted `dp-master` folder's contents in your webroot. Do not overwrite live CMS data if it has changed since the supplied archive without reconciling it. Clear the site/CDN cache. Production deployment has not been performed.

**v0.11.38 - app-only selected work & cleaner public source**

- The Team Lead **Selected real work** panel now contains only the three mobile-first web applications; small WordPress websites are no longer included there.
- Theme variables and custom theme CSS are combined behind the clean `/assets/theme.css` URL, removing the inline theme `<style>` block.
- Anonymous pages load the public application from its direct static asset URL, and executable scripts are deferred.
- SEO JSON-LD remains inline because search engines expect structured data in the document.

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

**v0.11.32 - clean boot, FAQ readability & no-key map pins**

### v0.11.32 changes
- Prevents the temporary fallback LP/blue branding from rendering while the public/admin site data is loading.
- Makes FAQ question text and +/- controls stronger, cleaner and more readable, with accessible expanded-state markup.
- Keeps the no-API-key OpenStreetMap embed and overlays clear location pins for Northampton (UK) and Riga (Latvia).
- Admin email login flow is unchanged because it is confirmed working.
- Version bumped to `0.11.32` to invalidate old browser/CDN/service-worker assets.

# DigitalPulse - Creative Web Solutions

Multilingual DigitalPulse agency website built on the supplied LPCMS system.

## Included

- DigitalPulse branding, logo, favicon and responsive agency UI.
- Swiper hero with LPCMS / WordPress / WooCommerce positioning and anchor offers.
- Autoplay technology Swiper directly below the hero.
- Technology list includes the supplied professional stack: WordPress, WooCommerce, custom themes/plugins, API integrations, Figma, Adobe XD, ChatGPT Codex, GitHub Copilot, Kimi and Grok, plus PHP, MySQL, REST API, JSON, ReactJS, JavaScript, jQuery, SCSS, Bootstrap and Visual Studio Code.
- Services for LPCMS, WordPress, WooCommerce, web/mobile applications, AI solutions, hosting and maintenance.
- Marketing, SEO and AI-content options covering technical/on-page SEO, local visibility, campaign landing pages, analytics/conversion tracking, AI-assisted text generation and marketing integrations.
- Industries/use-cases grid covering construction, property, automotive, travel, hotels, hospitality, e-commerce, manufacturing, professional services, wellness, education, non-profits, events/booking, trades, creative businesses and SaaS/portals.
- Pricing, highlights, six-step process, 32-item filterable real-project portfolio (including 17 DigitalPulse apps and O&J Cars at ojcars.uk), project brief, photo-led blog, WordPress sector-theme showcase, team lead profile, FAQ, contact, CTA and footer.
- Contact coverage for the whole UK and Europe, with Northampton shown first and Riga second alongside a no-key OpenStreetMap Europe embed.
- 14-language LPCMS language switcher retained: EN, LV, RU, FR, ES, DE, PL, LT, ET, DA, NO, SV, FI and IS.
- New DigitalPulse copy is translated through the existing LPCMS translation pack; technical product/tool names stay unchanged where appropriate.
- Existing LPCMS users/authentication/password behaviour preserved from the supplied project.
- Anonymous page source is SEO-focused and lightweight: CSS/JavaScript are external, public data is loaded through the sanitized site API, and administrator UI code is not included in the anonymous bundle.

## Contact configured on the website

- hello@digitalpulse.click
- +44 7952 641616
- Northampton, England
- Riga, Latvia

## Run locally

PHP 8+ is recommended.

```bash
php -S 127.0.0.1:8080
```

Open `http://127.0.0.1:8080/`.

## Main files

- `index.php` - LPCMS application, public website and administration UI.
- `data.json` - DigitalPulse content and site configuration.
- `assets/i18n.json` - multilingual translations.
- `assets/img/digitalpulse-logo.svg` - main brand logo.
- Contact map uses an OpenStreetMap embed that requires no API key; UK and Latvia delivery locations remain listed beside it.
- `assets/img/portfolio/` - cached screenshots used by the real-project portfolio.
- `README-LPCMS.md` - original LPCMS documentation.

## Notes

Portfolio entries use project URLs and live screenshot thumbnails (with local fallbacks for inaccessible pages), with in-development projects labelled accordingly. UK agency experience references are kept in a collapsed, click-only section rather than rendered as crawlable project links. Before public deployment, review production mail/API settings, CAPTCHA, analytics consent, write permissions and administrator access policy.

## Brand assets and business card

Print-ready vector logo variants are in `assets/brand/`, including primary colour plus black/white monochrome files suitable for garments, signage, vehicle graphics, A4 documents and business stationery. The downloadable business-card PDF is stored at `assets/docs/digitalpulse-business-card-print.pdf` and is previewed from the Contact section.

## hCaptcha

Public enquiry, project-intake, booking and administrator login forms use hCaptcha. The public site key is stored in `data.json`. The verification secret is loaded server-side from `.secrets.php` (or the `HCAPTCHA_SECRET` environment variable) and `.secrets.php` is excluded by `.gitignore`; do not commit that file to a public repository.

### v0.11.36
- Fixed the mobile Contact map card leaving a large empty area below the Leaflet map.
- The outer map shell, Leaflet wrapper and map canvas now share one responsive mobile height (`clamp(300px, 72vw, 360px)`).
- Desktop/tablet map sizing and the exact Northampton/Riga coordinates are unchanged.
