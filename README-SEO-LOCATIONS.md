# DigitalPulse v0.11.44 — SEO location pages and portfolio update

## What changed

- **Social 63** portfolio item now shows an optimized, locally hosted WebP concept screenshot and an honest **In development** status.
- **33 destination/market pages**, with unique English market context, 33 distinct original city/country illustrations, and translated text on **14 language paths** (EN, LV, RU, FR, ES, DE, PL, LT, ET, DA, NO, SV, FI, IS).
- The UK group includes London, Milton Keynes, Northampton, Coventry, Birmingham, Peterborough, Leicester, Cambridge, Northamptonshire and Warwickshire; Baltic and Nordic pages cover each named country and its capital, plus additional cities.
- The website homepage prioritizes **custom WordPress plugins / Gutenberg / ACF**, **bespoke PHP/React apps** and **smart WooCommerce / e-commerce** development. Core homepage SEO titles and descriptions have been rewritten in all 14 languages.
- New location pages are **server-rendered** for crawler access, and use one minimal CSS file, locally hosted WebP images, native HTML disclosures and zero runtime JS bundles.
- Metadata: per-language canonical and hreflang URLs, Open Graph, Twitter cards, descriptive image alternatives, breadcrumb links and relevant schema.org Organization, Service, WebPage and BreadcrumbList nodes.
- Added discoverability endpoints `/sitemap.xml`, `/robots.txt`, and an *optional informational* `/llms.txt` (not a search-ranking signal). Homepage gets Organization, WebSite and Service JSON-LD.
- Added localizable Locations links to the main Services dropdown and footer, and regional related-page links.
- No fabricated branch locations, reviews, case studies or clients. Proposed project briefs are explicitly **examples**, not assertions of past work.

## Routes

- `/locations/` — market directory
- `/locations/northampton/`, `/locations/london/`, `/locations/coventry/`, `/locations/warwickshire/` etc.
- `/lv/locations/riga/`, `/lt/locations/vilnius/`, `/et/locations/tallinn/`, `/sv/locations/stockholm/`, etc.
- All 14 supported language prefixes work; English is canonical without `/en/`.
- `/sitemap.xml` and `/robots.txt`

## Deployment precautions

1. Take a **full backup** of live files and the current `data.json`, `.secrets.php` and any uploads before replacing anything.
2. Upload the ZIP contents into the site's **document root**, where the existing `index.php` lives. Do not upload a nested extra folder level.
3. The new `data.json` contains important homepage, portfolio and footer edits. If the live CMS was changed after the earlier supplied archive, **compare/merge** these specific entries before overwriting `data.json`. Otherwise newer live CMS edits would be lost.
4. Keep the **production `.secrets.php`** and production user-specific configuration; don't overwrite secrets blindly. If the site generated runtime content in assets/uploads, preserve it.
5. Ensure PHP 8+ and Apache `.htaccess`/`mod_rewrite` are available for clean URLs. The location routes are implemented in `location-pages.php`, included by `index.php` (no extra CMS plugin install).
6. Clear any server/CDN cache and PWA/service-worker cache if needed. Inspect the homepage, `/locations/`, a language page, an app portfolio card and contact form in the live environment.
7. Add `https://digitalpulse.click/sitemap.xml` to Google Search Console and Bing Webmaster Tools, then inspect a sample of pages and watch crawl, indexing and Core Web Vitals reports over time.
8. If your production site uses a different canonical hostname, update **`settings.baseUrl` in `data.json`** and ensure redirects consistently prefer one hostname. This release uses `https://digitalpulse.click`.

## SEO and AI search expectations

These improvements make the location pages accessible to crawlers and help search engines understand the site. They **do not guarantee** ranking, rich results, indexing or AI answer inclusion. Before promoting a market as a key target, enrich its page with verifiable local experience, relevant examples, industry-specific information and useful internal links. Do not mass-produce thin near-duplicates or misrepresent office locations.

Structured data is based on actual service capabilities, not invented customer reviews, star ratings, addresses or local Google Business Profiles. The `/llms.txt` file is an optional reading aid, not an official Google/Bing ranking standard.

## Implementation / editing

- `location-pages.php` — server-side routing, rendering, metadata, sitemap, robots and informational AI-readable index.
- `locations-data.json` — human-editable list of regions, city names, industries, workflows, example briefs and translations.
- `assets/css/location-pages.css` — all location landing page styling, mobile responsiveness and accessible focus states.
- `assets/img/locations/*.webp` — individual original lightweight illustrations. The images are illustrations **not photographs of the named cities**.
- `assets/img/portfolio/social-63-concept.webp` — Social 63 development concept preview.
- `data.json` — homepage/portfolio/apps/translations/menu/footer and SEO configuration.
- `index.php` and `.htaccess` — routing integration, version bump, homepage structured data and JSON file protection.

## Local smoke tests performed

- `php -l` on `index.php` and `location-pages.php`
- Access to homepage, location index, multiple language pages, assets, SEO feeds and expected 404/redirect routes via a temporary PHP server
- XML parsing/unique URL checks of sitemap and JSON validation of project data

Production hosting, browser visual testing and actual Google/Bing indexing **have not been tested or deployed**.
