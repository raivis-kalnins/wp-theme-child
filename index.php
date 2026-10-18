<?php
// Production safety: PHP diagnostics must never be injected into HTML/JSON responses.
// Keep diagnostics in the server error log instead, especially for API requests.
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
$GLOBALS['CMS_API_BUFFER_LEVEL'] = null;
if (isset($_GET['api'])) {
  $GLOBALS['CMS_API_BUFFER_LEVEL'] = ob_get_level();
  ob_start();
}
/**
 * Flat Headless React + JSON CMS in one index.php
 * ------------------------------------------------
 * Upload this file as index.php. It auto-creates:
 *   data.json
 *   .htaccess
 *   assets/.htaccess
 *   assets/uploads/
 *   assets/gallery/
 *   assets/img/
 *   assets/css/
 *   assets/js/
 *   assets/fonts/
 *   assets/docs/
 *   assets/cache/
 *
 * IMPORTANT BEFORE PRODUCTION:
 * 1) Replace CMS_SECRET with a unique secret for this deployment.
 * 2) Confirm BREAKGLASS_ADMIN_EMAIL and add editor users from the Users tab.
 * 3) Configure Admin > Mailer for PHP mail() or SMTP delivery.
 * 4) Ensure GD/Imagick/ffmpeg can encode AVIF when browser conversion is unavailable.
 * 5) Visit /?admin for super admin login; editor users use email security links.
 * 6) Enable Products > Shop mode for catalogue, cart, quote flow, payment instructions, and commerce integration profiles.
 */

$cmsSecureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'secure' => $cmsSecureCookie,
  'httponly' => true,
  'samesite' => 'Lax',
]);
session_start();

define('CMS_VERSION', '0.11.50');
define('DATA_FILE', __DIR__ . '/data.json');
define('MAIL_LOG_FILE', __DIR__ . '/.lpcms-mail.log');
define('HTACCESS_FILE', __DIR__ . '/.htaccess');
define('ASSETS_DIR', __DIR__ . '/assets');
define('I18N_FILE', ASSETS_DIR . '/i18n.json');
define('ASSETS_HTACCESS_FILE', ASSETS_DIR . '/.htaccess');
define('UPLOAD_DIR', ASSETS_DIR . '/uploads');
define('GALLERY_DIR', ASSETS_DIR . '/gallery');
define('IMG_DIR', ASSETS_DIR . '/img');
define('CSS_DIR', ASSETS_DIR . '/css');
define('JS_DIR', ASSETS_DIR . '/js');
define('FONTS_DIR', ASSETS_DIR . '/fonts');
define('DOCS_DIR', ASSETS_DIR . '/docs');
define('VIDEO_DIR', ASSETS_DIR . '/video');
define('CACHE_DIR', ASSETS_DIR . '/cache');
define('THEMES_DIR', ASSETS_DIR . '/themes');
define('THEME_MAX_UPLOAD_BYTES', 40 * 1024 * 1024);
define('THEME_MAX_FILES', 3000);
define('THEME_MAX_FILE_BYTES', 8 * 1024 * 1024);

define('CMS_SECRET', '3562811c9a621f22e0868716c33ac1ced123412f5ac7429ff8278672900692a7');
define('DEV_SHOW_LOGIN_LINK', false);
define('CMS_TIMEZONE', 'Europe/London');
define('BREAKGLASS_PREFIX', 'admin');
define('BREAKGLASS_PASSWORD', 'LPCMS@admin');
define('BREAKGLASS_ADMIN_EMAIL', 'raivis@63.lv');
define('BREAKGLASS_OTP_TTL', 10 * 60);
define('BREAKGLASS_MAX_CODE_ATTEMPTS', 5);
define('BREAKGLASS_CODE_LENGTH', 5);
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_AVIF_QUALITY', 70);
define('IMAGE_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
define('VIDEO_MAX_UPLOAD_BYTES', 50 * 1024 * 1024);

const ADMIN_EMAILS = [
  'raivis@63.lv',
];

function cms_demo_products(): array {
  return json_decode('[{"title":"Starter Website Package","slug":"starter-website-package","id":"1001","sku":"STARTER-WEBSITE-PA","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","digital","blue","xs"],"price":550,"oldPrice":690,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Blue","size":"XS","material":"Digital","license":"Standard"},"variations":[{"id":"starter-website-package-a","sku":"STARTER-WEBSITE-PA-A","attributes":{"size":"XS","colour":"Blue"},"price":550,"oldPrice":690,"stock":5,"image":""},{"id":"starter-website-package-b","sku":"STARTER-WEBSITE-PA-B","attributes":{"size":"S","colour":"Navy"},"price":600,"oldPrice":760,"stock":3,"image":""}],"stock":10,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Starter Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Starter Website Package","description":"Starter Website Package demo product.","image":""}},{"title":"Business Website Package","slug":"business-website-package","id":"1002","sku":"BUSINESS-WEBSITE-P","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","black","s"],"price":990,"oldPrice":1290,"discountLabel":"-23%","currency":"EUR","image":"https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Black","size":"S","material":"Service","license":"Business"},"variations":[{"id":"business-website-package-a","sku":"BUSINESS-WEBSITE-P-A","attributes":{"size":"S","colour":"Black"},"price":990,"oldPrice":1290,"stock":6,"image":""},{"id":"business-website-package-b","sku":"BUSINESS-WEBSITE-P-B","attributes":{"size":"M","colour":"Purple"},"price":1040,"oldPrice":1360,"stock":4,"image":""}],"stock":12,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Business Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Business Website Package","description":"Business Website Package demo product.","image":""}},{"title":"Custom Quote Project","slug":"custom-quote-project","id":"1003","sku":"CUSTOM-QUOTE-PROJE","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","cotton","white","m"],"price":0,"oldPrice":0,"discountLabel":"Quote only","currency":"EUR","image":"https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"White","size":"M","material":"Cotton","duration":"Project"},"variations":[],"stock":0,"inStock":true,"text":"Quote-only custom product.","body":"<p>Custom Quote Project is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":false,"quoteButton":true,"related":[],"seo":{"title":"Custom Quote Project","description":"Custom Quote Project demo product.","image":""}},{"title":"SEO Audit Service","slug":"seo-audit-service","id":"1004","sku":"SEO-AUDIT-SERVICE","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","leather","navy","l"],"price":180,"oldPrice":240,"discountLabel":"-25%","currency":"EUR","image":"https://images.unsplash.com/photo-1432888622747-4eb9a8f2c287?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Navy","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":16,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>SEO Audit Service is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"SEO Audit Service","description":"SEO Audit Service demo product.","image":""}},{"title":"Speed Optimization","slug":"speed-optimization","id":"1005","sku":"SPEED-OPTIMIZATION","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","aluminum","purple","xl"],"price":320,"oldPrice":420,"discountLabel":"-24%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Purple","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":18,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Speed Optimization is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Speed Optimization","description":"Speed Optimization demo product.","image":""}},{"title":"Monthly Care Plan","slug":"monthly-care-plan","id":"1006","sku":"MONTHLY-CARE-PLAN","type":"variable","category":"subscriptions","brand":"LPCMS","tags":["subscriptions","glass","green","custom"],"price":79,"oldPrice":99,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Green","size":"Custom","material":"Glass","duration":"Project"},"variations":[{"id":"monthly-care-plan-a","sku":"MONTHLY-CARE-PLAN-A","attributes":{"size":"Custom","colour":"Green"},"price":79,"oldPrice":99,"stock":10,"image":""},{"id":"monthly-care-plan-b","sku":"MONTHLY-CARE-PLAN-B","attributes":{"size":"XS","colour":"Orange"},"price":84,"oldPrice":107,"stock":6,"image":""}],"stock":20,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Monthly Care Plan is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Monthly Care Plan","description":"Monthly Care Plan demo product.","image":""}},{"title":"Logo Design Add-on","slug":"logo-design-add-on","id":"1007","sku":"LOGO-DESIGN-ADD-ON","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","digital","red","xs"],"price":250,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Red","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":22,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Logo Design Add-on is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Logo Design Add-on","description":"Logo Design Add-on demo product.","image":""}},{"title":"Brochure PDF Design","slug":"brochure-pdf-design","id":"1008","sku":"BROCHURE-PDF-DESIG","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","service","gold","s"],"price":390,"oldPrice":490,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Gold","size":"S","material":"Service","duration":"Monthly"},"variations":[],"stock":24,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Brochure PDF Design is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Brochure PDF Design","description":"Brochure PDF Design demo product.","image":""}},{"title":"Cotton Promo T-Shirt","slug":"cotton-promo-t-shirt","id":"1009","sku":"COTTON-PROMO-T-SHI","type":"variable","category":"merch","brand":"Garilla","tags":["merch","cotton","orange","m"],"price":24,"oldPrice":34,"discountLabel":"-29%","currency":"EUR","image":"https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Orange","size":"M","material":"Cotton"},"variations":[{"id":"cotton-promo-t-shirt-a","sku":"COTTON-PROMO-T-SHI-A","attributes":{"size":"M","colour":"Orange"},"price":24,"oldPrice":34,"stock":13,"image":""},{"id":"cotton-promo-t-shirt-b","sku":"COTTON-PROMO-T-SHI-B","attributes":{"size":"L","colour":"Silver"},"price":29,"oldPrice":42,"stock":8,"image":""}],"stock":26,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Cotton Promo T-Shirt is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Cotton Promo T-Shirt","description":"Cotton Promo T-Shirt demo product.","image":""}},{"title":"Premium Hoodie","slug":"premium-hoodie","id":"1010","sku":"PREMIUM-HOODIE","type":"variable","category":"merch","brand":"Garilla","tags":["merch","leather","grey","l"],"price":54,"oldPrice":69,"discountLabel":"-22%","currency":"EUR","image":"https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Grey","size":"L","material":"Leather"},"variations":[{"id":"premium-hoodie-a","sku":"PREMIUM-HOODIE-A","attributes":{"size":"L","colour":"Grey"},"price":54,"oldPrice":69,"stock":14,"image":""},{"id":"premium-hoodie-b","sku":"PREMIUM-HOODIE-B","attributes":{"size":"XL","colour":"Pink"},"price":59,"oldPrice":77,"stock":9,"image":""}],"stock":28,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Hoodie is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Hoodie","description":"Premium Hoodie demo product.","image":""}},{"title":"Leather Notebook","slug":"leather-notebook","id":"1011","sku":"LEATHER-NOTEBOOK","type":"simple","category":"merch","brand":"Garilla","tags":["merch","aluminum","brown","xl"],"price":18,"oldPrice":25,"discountLabel":"-28%","currency":"EUR","image":"https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Brown","size":"XL","material":"Aluminum"},"variations":[],"stock":30,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Leather Notebook is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Leather Notebook","description":"Leather Notebook demo product.","image":""}},{"title":"Aluminium Water Bottle","slug":"aluminium-water-bottle","id":"1012","sku":"ALUMINIUM-WATER-BO","type":"variable","category":"merch","brand":"Garilla","tags":["merch","glass","silver","custom"],"price":15,"oldPrice":22,"discountLabel":"-32%","currency":"EUR","image":"https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Silver","size":"Custom","material":"Glass"},"variations":[{"id":"aluminium-water-bottle-a","sku":"ALUMINIUM-WATER-BO-A","attributes":{"size":"Custom","colour":"Silver"},"price":15,"oldPrice":22,"stock":16,"image":""},{"id":"aluminium-water-bottle-b","sku":"ALUMINIUM-WATER-BO-B","attributes":{"size":"XS","colour":"Beige"},"price":20,"oldPrice":30,"stock":10,"image":""}],"stock":32,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Aluminium Water Bottle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Aluminium Water Bottle","description":"Aluminium Water Bottle demo product.","image":""}},{"title":"Glass Desk Sign","slug":"glass-desk-sign","id":"1013","sku":"GLASS-DESK-SIGN","type":"simple","category":"merch","brand":"Studio","tags":["merch","digital","pink","xs"],"price":48,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Pink","size":"XS","material":"Digital"},"variations":[],"stock":34,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Glass Desk Sign is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Glass Desk Sign","description":"Glass Desk Sign demo product.","image":""}},{"title":"Digital Download Pack","slug":"digital-download-pack","id":"1014","sku":"DIGITAL-DOWNLOAD-P","type":"simple","category":"digital-products","brand":"LPCMS","tags":["digital-products","service","sky","s"],"price":39,"oldPrice":59,"discountLabel":"-34%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Sky","size":"S","material":"Service","license":"Business"},"variations":[],"stock":36,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Digital Download Pack is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Digital Download Pack","description":"Digital Download Pack demo product.","image":""}},{"title":"Premium Template Bundle","slug":"premium-template-bundle","id":"1015","sku":"PREMIUM-TEMPLATE-B","type":"variable","category":"digital-products","brand":"LPCMS","tags":["digital-products","cotton","beige","m"],"price":89,"oldPrice":129,"discountLabel":"-31%","currency":"EUR","image":"https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Beige","size":"M","material":"Cotton","license":"Enterprise"},"variations":[{"id":"premium-template-bundle-a","sku":"PREMIUM-TEMPLATE-B-A","attributes":{"size":"M","colour":"Beige"},"price":89,"oldPrice":129,"stock":19,"image":""},{"id":"premium-template-bundle-b","sku":"PREMIUM-TEMPLATE-B-B","attributes":{"size":"L","colour":"Lime"},"price":94,"oldPrice":199,"stock":12,"image":""}],"stock":38,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Template Bundle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Template Bundle","description":"Premium Template Bundle demo product.","image":""}},{"title":"Photo Gallery Setup","slug":"photo-gallery-setup","id":"1016","sku":"PHOTO-GALLERY-SETU","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","leather","rose","l"],"price":160,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Rose","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":40,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Photo Gallery Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Photo Gallery Setup","description":"Photo Gallery Setup demo product.","image":""}},{"title":"Contact Form Setup","slug":"contact-form-setup","id":"1017","sku":"CONTACT-FORM-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","aluminum","yellow","xl"],"price":120,"oldPrice":150,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Yellow","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":42,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Contact Form Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Contact Form Setup","description":"Contact Form Setup demo product.","image":""}},{"title":"Multilingual Setup","slug":"multilingual-setup","id":"1018","sku":"MULTILINGUAL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","glass","lime","custom"],"price":280,"oldPrice":350,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Lime","size":"Custom","material":"Glass","duration":"Project"},"variations":[],"stock":44,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Multilingual Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Multilingual Setup","description":"Multilingual Setup demo product.","image":""}},{"title":"PWA Install Setup","slug":"pwa-install-setup","id":"1019","sku":"PWA-INSTALL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","digital","clear","xs"],"price":210,"oldPrice":260,"discountLabel":"-19%","currency":"EUR","image":"https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Clear","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":46,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>PWA Install Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"PWA Install Setup","description":"PWA Install Setup demo product.","image":""}},{"title":"Enterprise Custom Build","slug":"enterprise-custom-build","id":"1020","sku":"ENTERPRISE-CUSTOM-","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","cream","s"],"price":2500,"oldPrice":0,"discountLabel":"Popular","currency":"EUR","image":"https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Cream","size":"S","material":"Service","license":"Custom"},"variations":[{"id":"enterprise-custom-build-a","sku":"ENTERPRISE-CUSTOM--A","attributes":{"size":"S","colour":"Cream"},"price":2500,"oldPrice":0,"stock":24,"image":""},{"id":"enterprise-custom-build-b","sku":"ENTERPRISE-CUSTOM--B","attributes":{"size":"M","colour":"White"},"price":2550,"oldPrice":0,"stock":16,"image":""}],"stock":48,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Enterprise Custom Build is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Enterprise Custom Build","description":"Enterprise Custom Build demo product.","image":""}}]', true) ?: [];
}


function cms_demo_blog_posts(): array {
  $posts = [
    ['How this flat CMS works','how-flat-cms-works','PHP handles JSON storage and admin APIs while React renders the public website.','This CMS keeps content in JSON and renders it with React. The same index.php serves the frontend, APIs, admin login, uploads, forms, redirects, SEO metadata and app features.','https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80'],
    ['Planning a business website that converts','planning-business-website-that-converts','Start with one clear goal, a simple path to contact, and content that answers real customer questions.','A useful business website makes the next step obvious. Lead with the customer problem, explain the service in plain language, show trust signals, and finish each important section with a clear action such as booking, calling or requesting a quote.','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80'],
    ['Business website or online shop?','business-website-or-online-shop','Choose Premium for a service-led site and Premium Pro when visitors need products, cart and checkout.','A business website is ideal for presenting services, enquiries, booking and company information. An e-commerce website adds catalogue, product variations, cart, shipping, discounts and checkout. Start with the flow your customers actually need.','https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80'],
    ['Writing clearer pages with AI assistance','writing-clearer-pages-with-ai','Use AI to draft, shorten, rewrite and translate content while keeping final approval with the website owner.','AI works best as an editing assistant. Give it accurate business facts, ask for a specific tone and audience, then review the result before publishing. This keeps pages consistent without inventing services, prices or promises.','https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80'],
    ['Why abandoned-cart reminders matter','why-abandoned-cart-reminders-matter','A polite reminder can bring shoppers back when they were interrupted before checkout.','Cart recovery should be helpful rather than aggressive. Save the customer cart only after an email is supplied, send a first reminder after a reasonable delay, and use a second reminder later if the order is still incomplete.','https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80'],
    ['Getting an online shop ready for launch','getting-online-shop-ready-for-launch','Check products, prices, delivery, discounts, payment instructions and order emails before opening the shop.','Before launch, test a complete purchase on mobile and desktop. Confirm variation pricing, stock behaviour, shipping costs, discount rules, checkout totals and the messages received by both the customer and administrator.','https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80'],
    ['Install your website like an app','install-your-website-like-an-app','A progressive web app lets visitors add the website to Android or Apple home screens.','An installable website gives customers a convenient home-screen icon without requiring a traditional app-store release. Keep the manifest icons valid, serve the site securely over HTTPS and provide simple Android and Apple install guidance.','https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=900&q=80'],
    ['Real-time booking reduces admin','real-time-booking-reduces-admin','Let visitors choose an available service, date and time and receive confirmation immediately.','A clear booking flow removes back-and-forth messages. Publish only genuine availability, capture the details needed to deliver the appointment, and send confirmation to the customer and the business as soon as the slot is reserved.','https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=900&q=80'],
    ['WhatsApp chat or AI chatbot?','whatsapp-chat-or-ai-chatbot','Use WhatsApp for direct human conversation or an AI chatbot for instant answers from approved website content.','WhatsApp is a strong choice when a person should take over the conversation quickly. An AI chatbot is useful for repeated questions about services, products, pricing and booking. The best option depends on how the business wants to support visitors.','https://images.unsplash.com/photo-1525182008055-f88b95ff7980?auto=format&fit=crop&w=900&q=80'],
  ];
  $out = [];
  foreach ($posts as $i => $p) {
    $out[] = [
      'title' => $p[0],
      'slug' => $p[1],
      'date' => date('Y-m-d', strtotime('-' . ($i * 7) . ' days')),
      'image' => $p[4],
      'excerpt' => $p[2],
      'body' => '<p>' . htmlspecialchars($p[3], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>',
      'seo' => ['title' => $p[0], 'description' => $p[2], 'image' => $p[4]],
    ];
  }
  return $out;
}

function cms_i18n_pack(): array {
  static $pack = null;
  if ($pack !== null) return $pack;
  $fallback = [
    'languages' => [
      ['code'=>'en','label'=>'EN','name'=>'English'],
      ['code'=>'lv','label'=>'LV','name'=>'Latviešu'],
      ['code'=>'ru','label'=>'RU','name'=>'Русский'],
      ['code'=>'fr','label'=>'FR','name'=>'Français'],
      ['code'=>'es','label'=>'ES','name'=>'Español'],
      ['code'=>'de','label'=>'DE','name'=>'Deutsch'],
      ['code'=>'pl','label'=>'PL','name'=>'Polski'],
      ['code'=>'lt','label'=>'LT','name'=>'Lietuvių'],
      ['code'=>'et','label'=>'ET','name'=>'Eesti'],
      ['code'=>'da','label'=>'DA','name'=>'Dansk'],
      ['code'=>'no','label'=>'NO','name'=>'Norsk'],
      ['code'=>'sv','label'=>'SV','name'=>'Svenska'],
      ['code'=>'fi','label'=>'FI','name'=>'Suomi'],
      ['code'=>'is','label'=>'IS','name'=>'Íslenska'],
    ],
    'strings' => [],
    'templates' => [],
  ];
  if (!is_file(I18N_FILE)) return $pack = $fallback;
  $raw = json_decode((string)@file_get_contents(I18N_FILE), true);
  if (!is_array($raw)) return $pack = $fallback;
  return $pack = array_replace_recursive($fallback, $raw);
}

function cms_language_options(): array {
  return cms_i18n_pack()['languages'] ?? [];
}

function cms_language_codes(array $data = []): array {
  $langs = $data['settings']['languages'] ?? cms_language_options();
  $codes = [];
  foreach ($langs as $l) {
    $c = strtolower(trim((string)($l['code'] ?? '')));
    if (preg_match('/^[a-z]{2}$/', $c)) $codes[] = $c;
  }
  return array_values(array_unique($codes ?: ['en']));
}

function cms_current_language(array $data): string {
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  $codes = cms_language_codes($data);
  $query = strtolower(trim((string)($_GET['cms_lang'] ?? '')));
  if ($query !== '' && in_array($query, $codes, true)) return $query;
  $path = trim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''), '/');
  $base = trim(cms_route_prefix(), '/');
  if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = trim(substr($path, strlen($base)), '/');
  $first = strtolower((string)(explode('/', $path)[0] ?? ''));
  if ($first !== '' && in_array($first, $codes, true)) return $first;
  return in_array($default, $codes, true) ? $default : ($codes[0] ?? 'en');
}

function cms_i18n_text(array $data, string $text, ?string $lang = null): string {
  if ($text === '') return $text;
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  if ($lang === $default || $lang === 'en') return $text;
  $pack = cms_i18n_pack();
  $map = $pack['strings'][$lang] ?? [];
  if (isset($map[$text]) && is_string($map[$text])) return $map[$text];
  return $text;
}

function cms_localized_value(array $data, array $object, string $key, ?string $lang = null, $fallback = '') {
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  if ($lang !== $default && isset($object['i18n'][$lang]) && is_array($object['i18n'][$lang]) && array_key_exists($key, $object['i18n'][$lang])) {
    return $object['i18n'][$lang][$key];
  }
  $value = array_key_exists($key, $object) ? $object[$key] : $fallback;
  if (is_string($value)) return cms_i18n_text($data, $value, $lang);
  if (is_array($value) && $lang !== $default) {
    return array_map(static fn($v) => is_string($v) ? cms_i18n_text($data, $v, $lang) : $v, $value);
  }
  return $value;
}

function cms_language_prefix(array $data, ?string $lang = null): string {
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  return ($lang !== '' && $lang !== $default) ? '/' . $lang : '';
}

function cms_language_home_path(array $data, ?string $lang = null): string {
  $prefix = cms_language_prefix($data, $lang);
  $path = $prefix . '/';
  return $path === '' ? '/' : $path;
}

function cms_default_data(): array {
  return [
    'version' => CMS_VERSION,
    'settings' => [
      'siteName' => 'Flat React JSON CMS',
      'baseUrl' => '',
      'defaultLang' => 'en',
      'languages' => cms_language_options(),
      'localization' => [
        'enabled' => true,
        'autoDetect' => true,
        'persistChoice' => true,
        'urlPrefixes' => true,
        'defaultAtRoot' => true,
      ],
      'branding' => [
        'favicon' => '',
        'placeholderMark' => 'LP',
      ],
      'mailer' => [
        'mode' => 'mail',
        'fromName' => 'LPCMS',
        'fromEmail' => '',
        'replyTo' => '',
        'smtp' => [
          'host' => '',
          'port' => 587,
          'encryption' => 'tls',
          'username' => '',
          'password' => '',
          'timeout' => 12,
          'verifyPeer' => true,
        ],
      ],
      'commerceIntegrations' => [
        'checkout' => [
          'mode' => 'order-form',
          'paymentLink' => '',
          'adapterUrl' => '',
          'bearerToken' => '',
        ],
        'webhook' => [
          'enabled' => false,
          'url' => '',
          'bearerToken' => '',
        ],
        'providers' => [],
      ],
      'seo' => [
        'title' => 'Flat React JSON CMS - Fast one page CMS',
        'description' => 'A fast single-file PHP and JSON headless CMS rendered by React.',
        'keywords' => 'flat file cms, react cms, json cms, bootstrap cms',
        'image' => '/assets/img/og.jpg',
        'canonical' => '',
        'robots' => 'index,follow'
      ],
      'htaccess' => [
        'autoSync' => true,
        'prettyUrls' => true,
        'forceHttps' => false,
        'removeWww' => false,
        'assetCacheDays' => 365,
        'protectJson' => true
      ],
      'theme' => [
        'brand' => '#0d6efd',
        'brand2' => '#6610f2',
        'accent' => '#20c997',
        'text' => '#1f2937',
        'muted' => '#6b7280',
        'bg' => '#ffffff',
        'softBg' => '#f6f8fb',
        'radius' => '22px',
        'grid' => 'container',
        'font' => 'Inter, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
        'customScss' => '',
        'customCss' => ''
      ],
      'themeManager' => [
        'active' => 'lpcms-default',
        'loadImportedCss' => true,
        'loadImportedJs' => false,
        'installed' => [],
      ],
      'headless' => [
        'enabled' => true,
        'restEnabled' => true,
        'graphqlEnabled' => true,
        'corsOrigins' => [],
        'cacheSeconds' => 60,
      ],
      'libraries' => [
        'bootstrap' => true,
        'bootstrapIcons' => true,
        'swiper' => false,
        'aos' => true,
        'glightbox' => false,
        'sassBuilder' => false,
      ],
      'analytics' => [
        'ga4Id' => '',
        'loadAfterCookieConsent' => true
      ],
      'cookies' => [
        'enabled' => true,
        'message' => 'We use cookies for analytics and site functionality.',
        'acceptText' => 'Accept',
        'declineText' => 'Decline'
      ],
      'hcaptcha' => [
        'enabled' => false,
        'siteKey' => '',
        'secret' => ''
      ],
      'mediaSources' => [
        'provider' => 'free',
        'free' => ['enabled' => true],
        'openverse' => ['enabled' => true],
        'commons' => ['enabled' => true],
        // Pexels, Pixabay and Unsplash use a configurable no-key proxy by default.
        // A provider API key is optional and, when present, switches that provider
        // to its official API directly.
        'proxy' => [
          'enabled' => true,
          'baseUrl' => 'https://proxy.getinstantimages.com/api/',
          'schemaVersion' => '7.2.0',
        ],
        'pexels' => ['enabled' => true, 'apiKey' => ''],
        'pixabay' => ['enabled' => true, 'apiKey' => ''],
        'unsplash' => ['enabled' => true, 'apiKey' => ''],
      ],
      'ai' => [
        'enabled' => false,
        'accessTier' => 'free',
        'clientName' => '',
        'clientEmail' => '',
        'provider' => 'openai',
        'model' => '',
        'apiKey' => '',
        'endpoint' => '',
        'systemPrompt' => 'You are a concise website content assistant. Return polished website copy only unless the user asks for explanations.',
      ],
      'sitePlan' => [
        'selected' => 'premium',
        'platformDomain' => 'dev4.click',
        'subdomain' => 'yourbusiness',
        'customDomain' => '',
        'premiumGbp' => '3.50',
        'premiumEur' => '4.00',
        'premiumProGbp' => '6.50',
        'premiumProEur' => '7.00',
      ],
      'abandonedCart' => [
        'enabled' => false,
        'firstDelayMinutes' => 60,
        'secondDelayMinutes' => 1440,
        'firstSubject' => 'You left something in your cart',
        'firstMessage' => "You still have items waiting in your cart. Use the link below to return and finish checkout.",
        'secondSubject' => 'Your cart is still waiting',
        'secondMessage' => "A quick reminder that your saved cart is still available. Return to the shop whenever you are ready.",
        'cronKey' => '',
        'carts' => [],
      ],
      'demoIntakeAdmin' => [
        'notifyEmail' => '',
        'billingSubject' => 'Your Digital Pulse website plan is ready',
        'billingMessage' => 'Your website demo has been approved and prepared. Please use the billing details below to activate the selected monthly plan.',
        'paymentInstructions' => 'Add your payment link, bank transfer details, or other billing instructions here.',
        'invoicePrefix' => 'DP',
        'dueDays' => 7,
        'requests' => [],
      ],
      'sectionOrder' => ['hero','about','adminShowcase','pricing','stats','features','services','wpThemes','process','gallery','testimonials','products','demoIntake','blog','faq','contact','cta','cards','contentList','team','growth','industries','logos','booking'],
      'sectionPatterns' => [
        'hero' => '1', 'about' => '1', 'stats' => '1', 'features' => '1', 'services' => '1', 'wpThemes' => '1', 'cards' => '1', 'contentList' => '1', 'logos' => '1', 'process' => '1', 'gallery' => '1',
        'testimonials' => '1', 'products' => '1', 'booking' => '1', 'adminShowcase' => '1', 'pricing' => '1', 'demoIntake' => '1', 'blog' => '1', 'faq' => '1', 'contact' => '1', 'cta' => '1'
      ],
      'app' => [
        'enabled' => true,
        'name' => 'LPCMS',
        'shortName' => 'LPCMS',
        'description' => 'Install this website as a mobile app.',
        'themeColor' => '#0d6efd',
        'backgroundColor' => '#ffffff',
        'icon' => '/assets/img/app-icon-512.png',
        'icon192' => '/assets/img/app-icon-192.png',
        'icon512' => '/assets/img/app-icon-512.png',
        'appleIcon' => '/assets/img/apple-touch-icon.png',
        'androidInstallUrl' => '/?install=android',
        'appleInstallUrl' => '/?install=apple',
        'footerBadgesEnabled' => true,
        'offlineMessage' => 'You are offline. Please reconnect to continue editing.'
      ],
      'cache' => [
        'enabled' => true,
        'htmlTtl' => 300,
        'autoClearOnSave' => true,
        'maxAgeDays' => 14
      ],
      'media' => [
        'maxImageWidth' => 1920,
        'boxWidth' => 1920,
        'boxHeight' => 1920,
        'boxPreset' => 'large',
        'avifQuality' => 72,
        'browserAvif' => true,
        'strictAvif' => true,
        'keepOriginals' => false
      ],
      'users' => [
        'items' => [
          ['email' => 'raivis@63.lv', 'name' => 'Super administrator', 'role' => 'super', 'active' => true],
        ]
      ],
      'forms' => [
        'emailTo' => '',
        'storeSubmissions' => true,
        'submissions' => []
      ]
    ],
    'header' => [
      'logoText' => 'LPCMS',
      'logoImage' => '',
      'sticky' => true,
      'menu' => [
        ['label' => 'Home', 'href' => '#home', 'target' => '_self', 'children' => []],
        ['label' => 'About', 'href' => '#about', 'target' => '_self', 'children' => []],
        ['label' => 'Services', 'href' => '#services', 'target' => '_self', 'children' => []],
        ['label' => 'Process', 'href' => '#process', 'target' => '_self', 'children' => []],
        ['label' => 'Pricing', 'href' => '#pricing', 'target' => '_self', 'children' => []],
        ['label' => 'Gallery', 'href' => '#gallery', 'target' => '_self', 'children' => []],
        ['label' => 'Blog', 'href' => '#blog', 'target' => '_self', 'children' => []],
        ['label' => 'Contact', 'href' => '#contact', 'target' => '_self', 'children' => []],
      ]
    ],
    'hero' => [
      'enabled' => true,
      'slides' => [
        [
          'eyebrow' => 'One file CMS',
          'title' => 'Flat Headless React CMS powered by JSON',
          'text' => 'Inline edit content, manage SEO, build sections, galleries, blogs, products, and forms without a database.',
          'buttonText' => 'Start editing',
          'buttonHref' => '#about',
          'mediaType' => 'video',
          'video' => '/assets/video/hero-cms-demo-v4.mp4',
          'videoPoster' => '/assets/video/hero-cms-demo-poster-v4.avif',
          'videoAutoplay' => true,
          'videoLoop' => true,
          'image' => '',
          'bg' => '',
          'seoH1' => true
        ]
      ]
    ],
    'about' => [
      'enabled' => true,
      'preheader' => 'About',
      'title' => 'About us',
      'subtitle' => 'A clean Bootstrap-style landing page with inline admin UX.',
      'image' => '',
      'body' => '<p>Login securely, then click text or image blocks directly on the page. Images can be clicked or dragged into place. LPCMS converts to AVIF when available, otherwise it safely saves the original JPG/PNG/WebP file.</p>'
    ],
    'stats' => [
      'enabled' => true,
      'preheader' => 'Highlights',
      'title' => 'Numbers that matter',
      'subtitle' => 'Small animated counters for trust, speed, and proof points.',
      'items' => [
        ['number' => '100%', 'label' => 'Flat-file JSON content'],
        ['number' => '1', 'label' => 'PHP application file'],
        ['number' => '0', 'label' => 'Database tables'],
        ['number' => 'SEO', 'label' => 'Clean URLs and meta controls'],
      ]
    ],
    'features' => [
      'enabled' => true,
      'preheader' => 'Features',
      'title' => 'Built for fast landing pages',
      'subtitle' => 'Reusable editable feature rows with icons, text, and actions.',
      'image' => '',
      'items' => [
        ['title' => 'Visual inline editing', 'text' => 'Click headings, paragraphs, cards, and modal content directly on the page.', 'icon' => 'bi bi-magic', 'image' => ''],
        ['title' => 'Product catalogue mode', 'text' => 'Run products like blog posts with modals, prices, discounts, galleries, buy links, and quote baskets.', 'icon' => 'bi bi-bag-check', 'image' => ''],
        ['title' => 'Performance first', 'text' => 'Optional libraries, asset cache headers, compression rules, and JSON-only content keep the page lightweight.', 'icon' => 'bi bi-speedometer2', 'image' => ''],
      ]
    ],
    'cards' => [
      'enabled' => false,
      'preheader' => 'Highlights',
      'title' => 'Image cards',
      'subtitle' => 'Flexible visual cards for services, categories, offers, or featured content.',
      'columns' => 3,
      'items' => [
        ['title' => 'Card one', 'text' => 'Add a short description and optional image.', 'image' => '', 'icon' => 'lucide:layout-template', 'buttonText' => 'Learn more', 'buttonHref' => '#demo-request'],
        ['title' => 'Card two', 'text' => 'Use cards to guide visitors to the next step.', 'image' => '', 'icon' => 'tabler:route', 'buttonText' => 'Learn more', 'buttonHref' => '#demo-request'],
        ['title' => 'Card three', 'text' => 'The layout stays responsive automatically.', 'image' => '', 'icon' => 'material-symbols:responsive-layout', 'buttonText' => 'Learn more', 'buttonHref' => '#contact'],
      ]
    ],
    'contentList' => [
      'enabled' => false,
      'preheader' => 'Details',
      'title' => 'Content list',
      'subtitle' => 'A clean structured list for services, team members, products, or benefits.',
      'items' => [
        ['title' => 'Structured content item', 'text' => 'Keep information aligned and easy to scan on desktop and mobile.', 'image' => '', 'icon' => 'lucide:list-checks', 'buttonText' => 'Read more', 'buttonHref' => '#contact']
      ]
    ],
    'logos' => [
      'enabled' => false,
      'preheader' => 'Trusted by',
      'title' => 'Trusted by',
      'subtitle' => 'Show client, partner, supplier, or certification logos in a balanced responsive row.',
      'items' => [
        ['label' => 'Partner logo', 'image' => '', 'href' => '#']
      ]
    ],
    'process' => [
      'enabled' => true,
      'preheader' => 'How it works',
      'title' => 'How it works',
      'subtitle' => 'A simple editable process timeline.',
      'items' => [
        ['title' => 'Upload', 'text' => 'Upload index.php and open the site once to create JSON and assets.', 'icon' => 'bi bi-cloud-arrow-up', 'image' => ''],
        ['title' => 'Edit', 'text' => 'Login securely and edit content inline or from the admin tabs.', 'icon' => 'bi bi-pencil-square', 'image' => ''],
        ['title' => 'Publish', 'text' => 'Save JSON, sync .htaccess, and publish fast SEO-ready pages.', 'icon' => 'bi bi-rocket-takeoff', 'image' => ''],
      ]
    ],
    'adminShowcase' => [
      'enabled' => true,
      'preheader' => 'Admin & editing',
      'title' => 'Manage your website without touching code',
      'subtitle' => 'Edit content on the page or in Admin. Daily changes are simple and technical settings stay protected.',
      'superAdminTitle' => 'Super Admin control centre',
      'superAdminText' => 'Super Admin keeps advanced website controls in one protected panel: themes, products, booking, chat, media, SEO, plans, demos, mail, APIs, cache, redirects and server tools.',
      'superAdminImage' => '/assets/img/admin/super-admin-controls.avif',
      'superAdminItems' => [
        ['title' => 'Design & themes', 'text' => 'Switch themes, adjust design tokens and control imported theme assets.', 'icon' => 'bi bi-palette2'],
        ['title' => 'Commerce', 'text' => 'Manage products, checkout, shipping, discounts and recovery tools.', 'icon' => 'bi bi-bag-check'],
        ['title' => 'Booking & chat', 'text' => 'Configure booking services, AI chatbot or WhatsApp visitor support.', 'icon' => 'bi bi-calendar2-check'],
        ['title' => 'Media & icons', 'text' => 'Upload AVIF media, import free images and choose open-source icons.', 'icon' => 'bi bi-images'],
        ['title' => 'SEO & languages', 'text' => 'Control metadata, multilingual content and shareable language URLs.', 'icon' => 'bi bi-translate'],
        ['title' => 'Plans & client workflow', 'text' => 'Load demos, approve client requests and send billing emails.', 'icon' => 'bi bi-receipt-cutoff'],
        ['title' => 'Mail & integrations', 'text' => 'Protect SMTP, API keys, webhooks and provider-specific settings.', 'icon' => 'bi bi-plug'],
        ['title' => 'Technical tools', 'text' => 'Manage cache, redirects, headless APIs, app settings and server checks.', 'icon' => 'bi bi-sliders2-vertical'],
      ],
      'domainTitle' => 'Free subdomain or your own domain',
      'domainText' => 'Start free with {custom_name}.dev4.click, or connect a domain from Name.com, NIC.LV or any other domain provider.',
      'domainOptions' => [
        ['title' => 'Free subdomain', 'text' => 'Choose your name and publish without buying a domain.', 'example' => '{custom_name}.dev4.click', 'icon' => 'bi bi-globe2'],
        ['title' => 'Own domain', 'text' => 'Connect any domain you already own.', 'example' => 'yourcompany.com · yourcompany.lv · any TLD', 'icon' => 'bi bi-link-45deg'],
      ],
      'items' => [
        ['title' => 'Site & content', 'text' => 'Manage site identity, languages, sections and app settings.', 'icon' => 'bi bi-window-stack'],
        ['title' => 'Content & media', 'text' => 'Edit text, sections and images with inline editing or Admin fields.', 'icon' => 'bi bi-images'],
        ['title' => 'Shop & integrations', 'text' => 'Manage products, booking, chat, checkout and integrations.', 'icon' => 'bi bi-bag-check'],
      ],
      'tableTitle' => 'What can you manage?',
      'tableSubtitle' => 'Editors get simple controls. Super Admin keeps technical settings protected.',
      'rows' => [
        ['title' => 'Text, images and sections', 'editor' => true, 'super' => true, 'ease' => 'Click or use simple fields'],
        ['title' => 'Blog, SEO and languages', 'editor' => true, 'super' => true, 'ease' => 'Edit each language with its own URL'],
        ['title' => 'Products and e-commerce', 'editor' => true, 'super' => true, 'ease' => 'Manage cards, prices, stock and variations'],
        ['title' => 'Booking and AI chat / WhatsApp', 'editor' => true, 'super' => true, 'ease' => 'Switch on and edit content'],
        ['title' => 'Domains and deployment', 'editor' => false, 'super' => true, 'ease' => 'Free subdomain or connect your domain'],
        ['title' => 'SMTP, API keys and integrations', 'editor' => false, 'super' => true, 'ease' => 'Protected technical settings'],
        ['title' => 'Plans, demos and client billing', 'editor' => false, 'super' => true, 'ease' => 'Super Admin approval workflow'],
      ]
    ],
    'pricing' => [
      'enabled' => true,
      'preheader' => 'Pricing',
      'title' => 'Simple plans for your website',
      'subtitle' => 'Choose a business website or an e-commerce website. Both plans include the core editor and app install option.',
      'items' => [
        ['title' => 'Premium', 'badge' => 'Business website', 'price' => '£3.50', 'secondaryPrice' => '€4', 'period' => '/ month', 'text' => 'For a professional business website without shop checkout.', 'features' => ['Editable website sections', 'Blog and SEO controls', 'Contact forms', 'Theme and media tools', 'Install on Android & Apple (PWA)', 'Free {custom_name}.dev4.click subdomain or your own domain'], 'buttonText' => 'Choose Premium', 'buttonHref' => '#contact'],
        ['title' => 'Premium Pro', 'badge' => 'E-commerce website', 'price' => '£6.50', 'secondaryPrice' => '€7', 'period' => '/ month', 'text' => 'For selling products online with the full commerce toolkit.', 'features' => ['Everything in Premium', 'Product catalogue and variations', 'Shopping cart and quote basket', 'Checkout, shipping and discount options', 'Abandoned-cart reminders', 'Booking and AI chatbot modules'], 'buttonText' => 'Choose Premium Pro', 'buttonHref' => '#contact'],
      ]
    ],
    'faq' => [
      'enabled' => true,
      'preheader' => 'FAQ',
      'title' => 'Frequently asked questions',
      'subtitle' => 'Bootstrap-style accordion content managed from JSON.',
      'items' => [
        ['question' => 'Can LPCMS run without a database?', 'answer' => 'Yes. Content, settings, SEO, products, redirects, and forms are stored in JSON.'],
        ['question' => 'Can products be quote-only?', 'answer' => 'Yes. Set product price to 0 and the buy button is hidden while Add to quote stays available.'],
        ['question' => 'Can I disable libraries?', 'answer' => 'Yes. Admin library settings let you connect or disconnect optional frontend libraries.'],
      ]
    ],
    'services' => [
      'enabled' => true,
      'preheader' => 'Services',
      'title' => 'Services',
      'subtitle' => 'Cards with AJAX-style load more.',
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more services',
      'items' => [
        ['title' => 'Headless JSON content', 'text' => 'All editable content is stored in data.json.', 'icon' => 'bi bi-braces', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
        ['title' => 'Inline editing', 'text' => 'Edit headings, text, cards, and HTML blocks directly on the page.', 'icon' => 'bi bi-pencil-square', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
        ['title' => 'SEO controls', 'text' => 'Set global and post-level meta title, descriptions, OG images, canonical URLs, and JSON-LD.', 'icon' => 'bi bi-search-heart', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
      ]
    ],
    'wpThemes' => [
      'enabled' => true,
      'preheader' => 'WordPress sector themes',
      'title' => 'Professional WordPress websites, ready for almost any sector.',
      'subtitle' => 'Start from a proven sector-ready foundation and tailor the design, content and integrations to your business — from lead-generation sites to full WooCommerce stores.',
      'image' => '/assets/img/wp-themes.png',
      'alt' => 'Preview grid of sector-ready WordPress themes for business, automotive, education, hospitality, insurance, logistics, healthcare, property, restaurants, travel and WooCommerce.',
      'badge' => 'WordPress + Bootstrap + Gutenberg',
      'noteTitle' => 'One stable core. Multiple sectors. Faster delivery.',
      'noteText' => 'We reuse a tested architecture and adapt the visual system, content structure and integrations for each industry, so projects are easier to launch, maintain and grow.',
      'buttonText' => 'Discuss your WordPress project',
      'buttonHref' => '#contact',
      'items' => [
        ['title' => 'Bootstrap + Gutenberg blocks', 'text' => 'A clean Bootstrap front end with Gutenberg blocks that are easy for your team to edit without touching code.', 'icon' => 'bi bi-grid-3x3-gap'],
        ['title' => 'Ready-made patterns', 'text' => 'Pre-built hero, services, CTA, FAQ, testimonials and content patterns keep pages consistent and quick to assemble.', 'icon' => 'bi bi-layout-wtf'],
        ['title' => 'Forms + newsletter included', 'text' => 'Every WordPress project includes professional enquiry forms plus a newsletter or subscribe workflow.', 'icon' => 'bi bi-envelope-check'],
        ['title' => 'Business or WooCommerce', 'text' => 'Choose a professional business website or a WooCommerce store with polished product, cart and checkout styling.', 'icon' => 'bi bi-cart-check'],
        ['title' => 'Multisite & database options', 'text' => 'Use WordPress Multisite for a shared network, or separate sites and databases when stronger isolation or scaling is the better fit.', 'icon' => 'bi bi-diagram-3'],
        ['title' => 'Sector-ready foundations', 'text' => 'Automotive, building services, education, hotels, insurance, logistics, healthcare, property, restaurants, travel, retail and more.', 'icon' => 'bi bi-buildings'],
      ],
      'webPrices' => [
        'preheader' => 'Web development prices',
        'title' => 'Digital solutions with clear starting prices',
        'subtitle' => 'Prices are based on the current 63.lv web-development service list — from a business website to e-commerce, maintenance and hosting.',
        'ukNote' => 'UK prices are converted from EUR and rounded up.',
        'items' => [
          ['title' => 'Startup', 'text' => 'A good start for a company presence online.', 'eur' => 550, 'gbp' => 473, 'icon' => 'bi bi-rocket-takeoff'],
          ['title' => 'Pro', 'text' => 'More sections, broader structure and tailored content.', 'eur' => 850, 'gbp' => 731, 'icon' => 'bi bi-window-stack'],
          ['title' => 'Sector website', 'text' => 'More individual company or sector website development.', 'eur' => 1050, 'gbp' => 903, 'icon' => 'bi bi-buildings'],
          ['title' => 'Online store', 'text' => 'WooCommerce solution for online sales.', 'eur' => 1500, 'gbp' => 1290, 'icon' => 'bi bi-cart-check'],
        ],
        'extras' => [
          ['title' => 'Old to new website', 'eur' => 1050, 'gbp' => 903, 'unit' => 'project'],
          ['title' => 'Maintenance / coding', 'eur' => 25, 'gbp' => 22, 'unit' => 'hour'],
          ['title' => 'Hosting for clients', 'eur' => 3, 'gbp' => 3, 'unit' => 'month'],
          ['title' => 'SEO, optimisation & social media connection', 'price' => 'Quoted to scope', 'unit' => 'quote'],
        ],
      ]
    ],
    'gallery' => [
      'enabled' => true,
      'preheader' => 'Gallery',
      'title' => 'Gallery',
      'subtitle' => 'Directory-based photos from assets/gallery or uploaded files.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more photos',
      'items' => [
        ['src' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80', 'alt' => 'Modern office workspace'],
        ['src' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=900&q=80', 'alt' => 'Team planning meeting'],
        ['src' => 'https://images.unsplash.com/photo-1521737852567-6949f3f9f2b5?auto=format&fit=crop&w=900&q=80', 'alt' => 'Creative team collaboration'],
        ['src' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=900&q=80', 'alt' => 'Bright studio office'],
        ['src' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80', 'alt' => 'Contemporary business interior'],
        ['src' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=900&q=80', 'alt' => 'Open workspace and team'],
        ['src' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80', 'alt' => 'Team working together'],
        ['src' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80', 'alt' => 'Business consultation'],
        ['src' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=900&q=80', 'alt' => 'Project presentation meeting'],
      ]
    ],
    'testimonials' => [
      'enabled' => true,
      'preheader' => 'Testimonials',
      'title' => 'Testimonials',
      'subtitle' => 'Customer quote cards with load more.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more testimonials',
      'items' => [
        ['name' => 'Olivia Carter', 'role' => 'Founder, North & Co.', 'text' => 'The website was simple to review and update, and the whole launch process felt organised from the first draft.', 'photo' => ''],
        ['name' => 'Daniel Wright', 'role' => 'Operations Manager', 'text' => 'We can manage everyday content ourselves while the technical settings stay safely out of the way.', 'photo' => ''],
        ['name' => 'Sophie Bennett', 'role' => 'Studio Director', 'text' => 'The mobile layout is clean, fast and much easier for our customers to use than our previous site.', 'photo' => ''],
        ['name' => 'James Patel', 'role' => 'Retail Owner', 'text' => 'Products, enquiries and quote requests are all in one place now, which has made daily administration much simpler.', 'photo' => ''],
        ['name' => 'Amelia Green', 'role' => 'Marketing Lead', 'text' => 'Inline editing means our team can keep promotions and landing page content current without waiting for a developer.', 'photo' => ''],
        ['name' => 'Noah Evans', 'role' => 'Managing Director', 'text' => 'The demo gave us a clear picture of the finished website before approval, which made the decision process straightforward.', 'photo' => ''],
        ['name' => 'Emily Roberts', 'role' => 'Consultant', 'text' => 'Booking confirmations and the website chat options give clients a much smoother way to get in touch.', 'photo' => ''],
        ['name' => 'Lucas Martin', 'role' => 'E-commerce Manager', 'text' => 'The catalogue controls are practical and the checkout experience works well across desktop and mobile.', 'photo' => ''],
        ['name' => 'Grace Thompson', 'role' => 'Business Owner', 'text' => 'It feels like a professional website builder without making the client-facing admin unnecessarily complicated.', 'photo' => ''],
      ]
    ],
    'products' => [
      'enabled' => true,
      'preheader' => 'Products',
      'title' => 'Your Products',
      'subtitle' => 'Ajax catalogue with product search, categories, variation filters, modal details, cart, quote basket, and direct debit/bank transfer order flow.',
      'mode' => 'catalog',
      'shopEnabled' => true,
      'defaultPurchaseMode' => 'quote',
      'catalogEnabled' => true,
      'searchEnabled' => true,
      'filterEnabled' => true,
      'homeLimit' => 8,
      'batch' => 8,
      'desktopColumns' => 4,
      'tabletColumns' => 2,
      'mobileColumns' => 1,
      'showButton' => true,
      'buttonText' => 'Load more products',
      'showBuyButton' => true,
      'showQuoteButton' => true,
      'buyButtonText' => 'Buy',
      'quoteButtonText' => 'Add to quote',
      'basketEnabled' => true,
      'basketTitle' => 'Quote basket',
      'basketButtonText' => 'Send quote request',
      'cartEnabled' => true,
      'cartTitle' => 'Shopping cart',
      'cartButtonText' => 'Checkout',
      'checkoutButtonText' => 'Send order',
      'currency' => 'EUR',
      'shippingMethods' => [
        ['id' => 'pickup', 'label' => 'Local pickup', 'price' => 0, 'enabled' => true],
        ['id' => 'standard', 'label' => 'Standard delivery', 'price' => 4.99, 'enabled' => true],
      ],
      'discountCodes' => [
        ['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'enabled' => false],
      ],
      'categories' => [
        ['slug' => 'website-packages', 'title' => 'Website Packages'],
        ['slug' => 'custom-services', 'title' => 'Custom Services'],
        ['slug' => 'seo-tools', 'title' => 'SEO & Performance'],
        ['slug' => 'subscriptions', 'title' => 'Subscriptions'],
        ['slug' => 'merch', 'title' => 'Merchandise'],
        ['slug' => 'digital-products', 'title' => 'Digital Products'],
      ],
      'attributeFilters' => [
        ['key' => 'colour', 'label' => 'Colour', 'options' => ['Blue', 'Black', 'White', 'Navy', 'Purple', 'Green', 'Red', 'Gold', 'Orange', 'Grey', 'Brown', 'Silver', 'Pink', 'Sky', 'Beige', 'Rose', 'Yellow', 'Lime', 'Clear', 'Cream']],
        ['key' => 'size', 'label' => 'Size', 'options' => ['XS', 'S', 'M', 'L', 'XL', 'Custom']],
        ['key' => 'material', 'label' => 'Material', 'options' => ['Digital', 'Service', 'Cotton', 'Leather', 'Aluminum', 'Glass']],
        ['key' => 'license', 'label' => 'License', 'options' => ['Standard', 'Business', 'Enterprise', 'Custom']],
        ['key' => 'duration', 'label' => 'Duration', 'options' => ['One-off', 'Monthly', 'Project']],
      ],
      'payment' => [
        'directDebit' => [
          'enabled' => true,
          'title' => 'Direct bank transfer / direct debit',
          'instructions' => 'Submit the order form and we will confirm payment details. Add your order reference to the payment message.',
          'accountName' => 'Your Company Ltd',
          'iban' => '',
          'swift' => '',
          'bankName' => '',
          'referencePrefix' => 'LPCMS'
        ]
      ],
      'items' => cms_demo_products(),
    ],
    'booking' => [
      'enabled' => false,
      'preheader' => 'Booking',
      'title' => 'Book an appointment',
      'subtitle' => 'Choose a service, date and available time. Confirmation appears immediately and is sent by email.',
      'services' => [
        ['id' => 'consultation', 'title' => 'Consultation', 'durationMinutes' => 60, 'price' => 0, 'enabled' => true],
        ['id' => 'demo', 'title' => 'Product demo', 'durationMinutes' => 30, 'price' => 0, 'enabled' => true],
      ],
      'weeklySlots' => [
        'monday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'tuesday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'wednesday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'thursday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'friday' => ['09:00','10:30','12:00','14:00','15:30'],
        'saturday' => [],
        'sunday' => [],
      ],
      'confirmationText' => 'Booking confirmed. Check your email for the details.',
      'technical' => [
        'notifyEmail' => '',
        'webhookUrl' => '',
      ],
      'bookings' => [],
    ],
    'chatbot' => [
      'enabled' => true,
      'mode' => 'chatbot',
      'position' => 'left',
      'buttonText' => 'Ask AI',
      'title' => 'How can we help?',
      'welcome' => 'Hi! Ask me about our services, products, prices, booking, or contact details.',
      'placeholder' => 'Type your question…',
      'fallback' => 'I could not find a confident answer in the website content. Please use the contact form and the team will help you.',
      'suggestedQuestions' => ['What services do you offer?', 'How much does it cost?', 'Can I book an appointment?', 'How do I contact you?'],
      'whatsapp' => [
        'number' => '',
        'buttonText' => 'WhatsApp',
        'message' => 'Hi! I would like to ask about your services.',
      ],
      'technical' => [
        'aiEnabled' => true,
        'maxContextChars' => 9000,
        'systemPrompt' => 'You are the website assistant. Answer only from the supplied website knowledge, keep answers concise, do not invent facts, reply in the visitor language, and suggest booking or contact when useful.',
      ],
    ],
    'blog' => [
      'enabled' => true,
      'preheader' => 'Blog',
      'title' => 'Blog',
      'subtitle' => 'Nine demo posts with six shown first and three more per click.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more posts',
      'items' => cms_demo_blog_posts(),
    ],
    'demoIntake' => [
      'enabled' => true,
      'preheader' => 'Free demo',
      'title' => 'Get a ready website demo',
      'subtitle' => 'Complete four short steps with your business details, content and logo. A private draft is generated for Super Admin review before anything is published.',
      'buttonText' => 'Create my demo request',
      'thankYou' => 'Thank you. Your website draft was created and sent for administrator review.',
      'privacyText' => 'I confirm that I am allowed to provide this business information and logo for preparing my website demo.',
      'plans' => [
        ['id' => 'premium', 'title' => 'Premium', 'description' => 'Business website', 'price' => '£3.50 / €4 per month'],
        ['id' => 'premium-pro', 'title' => 'Premium Pro', 'description' => 'E-commerce website', 'price' => '£6.50 / €7 per month'],
      ],
      'steps' => ['Your business', 'Website content', 'Branding & tools', 'Review'],
    ],
    'pages' => [
      'enabled' => true,
      'items' => [
        [
          'title' => 'Privacy Policy',
          'slug' => 'privacy-policy',
          'excerpt' => 'Privacy policy page managed from JSON.',
          'body' => '<p>Update this privacy policy from the JSON editor or through future page block controls.</p>',
          'seo' => ['title' => 'Privacy Policy', 'description' => 'Privacy policy for this website.', 'image' => '']
        ]
      ]
    ],
    'contact' => [
      'enabled' => true,
      'preheader' => 'Contact',
      'title' => 'Contact us',
      'subtitle' => 'Map, details, modal form, and social share/follow.',
      'mapEmbed' => 'https://www.google.com/maps?q=London%2C%20UK&output=embed',
      'details' => [
        'address' => 'London, UK',
        'phone' => '+44 00 000 000',
        'email' => 'hello@example.com',
        'hours' => 'Mon-Fri 09:00-18:00'
      ],
      'formButtonText' => 'Open contact form'
    ],
    'cta' => [
      'enabled' => true,
      'title' => 'Ready to launch a fast flat-file site?',
      'text' => 'Open the admin panel, change the content, upload images, and publish.',
      'buttonText' => 'Open contact form'
    ],
    'footer' => [
      'copy' => 'Copyright {year} FlatCMS. All rights reserved.',
      'menu' => [
        ['label' => 'Privacy', 'href' => '/page/privacy-policy'],
        ['label' => 'Contact', 'href' => '#contact']
      ],
      'social' => [
        ['label' => 'Facebook', 'url' => '#', 'icon' => 'bi bi-facebook'],
        ['label' => 'Instagram', 'url' => '#', 'icon' => 'bi bi-instagram'],
        ['label' => 'LinkedIn', 'url' => '#', 'icon' => 'bi bi-linkedin'],
      ]
    ],
    'redirects' => [
      ['from' => '/old-page', 'to' => '/', 'status' => 301, 'enabled' => false]
    ],
    'embeds' => [],
  ];
}

function cms_asset_dirs(): array {
  return [ASSETS_DIR, UPLOAD_DIR, GALLERY_DIR, IMG_DIR, CSS_DIR, JS_DIR, FONTS_DIR, DOCS_DIR, VIDEO_DIR, CACHE_DIR, THEMES_DIR];
}

function cms_route_prefix(): string {
  $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $dir === '' ? '/' : $dir . '/';
}


function cms_asset_public_url(string $targetDir, string $name): string {
  $base = rtrim(cms_route_prefix(), '/');
  return ($base !== '' ? $base : '') . '/assets/' . rawurlencode(basename($targetDir)) . '/' . rawurlencode($name);
}

function cms_resolve_remote_url(string $baseUrl, string $location): string {
  $location = trim($location);
  if ($location === '') return '';
  if (preg_match('#^https?://#i', $location)) return $location;
  $base = parse_url($baseUrl);
  $scheme = (string)($base['scheme'] ?? 'https');
  $host = (string)($base['host'] ?? '');
  if ($host === '') return '';
  $port = isset($base['port']) ? ':' . (int)$base['port'] : '';
  if (str_starts_with($location, '//')) return $scheme . ':' . $location;
  if (str_starts_with($location, '/')) return $scheme . '://' . $host . $port . $location;
  $path = (string)($base['path'] ?? '/');
  $dir = preg_replace('#/[^/]*$#', '/', $path) ?: '/';
  $combined = $dir . $location;
  $parts = [];
  foreach (explode('/', $combined) as $part) {
    if ($part === '' || $part === '.') continue;
    if ($part === '..') { array_pop($parts); continue; }
    $parts[] = $part;
  }
  return $scheme . '://' . $host . $port . '/' . implode('/', $parts);
}

function cms_htaccess_settings(array $data): array {
  return array_replace([
    'autoSync' => true,
    'prettyUrls' => true,
    'forceHttps' => false,
    'removeWww' => false,
    'assetCacheDays' => 365,
    'protectJson' => true,
  ], $data['settings']['htaccess'] ?? []);
}

function cms_root_htaccess(array $data): string {
  $s = cms_htaccess_settings($data);
  $cacheDays = max(1, min(365, (int)($s['assetCacheDays'] ?? 365)));
  $cacheSeconds = $cacheDays * 86400;
  $forceHttps = !empty($s['forceHttps']) ? "RewriteCond %{HTTPS} !=on\nRewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n" : "# To force HTTPS from the CMS admin, enable settings.htaccess.forceHttps.\n";
  $removeWww = !empty($s['removeWww']) ? "RewriteCond %{HTTP_HOST} ^www\\.(.+)$ [NC]\nRewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]\n" : "# To redirect www to non-www, enable settings.htaccess.removeWww.\n";
  $protectJson = !empty($s['protectJson']) ? <<<'TXT'
<FilesMatch "(^\.|(?:data|locations-data)\.json$|.*\.tmp$|.*\.bak$|.*\.old$|composer\.(json|lock)$|package(-lock)?\.json$|yarn\.lock$|vite\.config\.|webpack\.config\.|phpunit\.)">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>
TXT : '';

  return rtrim("# BEGIN FlatCMS\n" . <<<'HTA'
# Managed by index.php. Custom rules can be placed before BEGIN or after END.
Options -Indexes
DirectoryIndex index.php
FileETag None

<IfModule mod_negotiation.c>
  Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
  RewriteEngine On
HTA
  . "\n" . $forceHttps . $removeWww . <<<'HTA'

  # PWA app endpoints served by index.php.
  RewriteRule ^manifest\.webmanifest$ index.php?pwa=manifest [L,QSA]
  RewriteRule ^service-worker\.js$ index.php?pwa=sw [L,QSA]

  # Clean dynamic theme stylesheet URL.
  RewriteRule ^assets/theme\.css$ index.php?asset=theme-css [L,QSA]

  # Repair legacy relative asset URLs saved before v0.11.12. New uploads are
  # root-relative, but these rules keep older data working on /lv/, /de/blog/..., etc.
  RewriteRule ^([A-Za-z]{2})/assets/(.*)$ assets/$2 [L,NC]
  RewriteRule ^([A-Za-z]{2})/(blog|posts|product|products|page|pages)/assets/(.*)$ assets/$3 [L,NC]
  RewriteRule ^([A-Za-z]{2})/(blog|posts|product|products|page|pages)/[A-Za-z0-9-]+/assets/(.*)$ assets/$3 [L,NC]
  RewriteRule ^(blog|posts|product|products|page|pages)/assets/(.*)$ assets/$2 [L,NC]
  RewriteRule ^(blog|posts|product|products|page|pages)/[A-Za-z0-9-]+/assets/(.*)$ assets/$2 [L,NC]

  # Let real files and directories load directly.
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # Language-prefixed public URLs. English remains canonical at /; /en/ is accepted as an alias.
  RewriteRule ^([A-Za-z]{2})/?$ index.php?cms_lang=$1 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/blog/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=blog&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/posts/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=blog&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/product/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=products&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/products/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=products&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/page/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/pages/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]

  # Pretty SEO URLs for JSON content.
  RewriteRule ^blog/([A-Za-z0-9-]+)/?$ index.php?cms_route=blog&slug=$1 [L,QSA,B]
  RewriteRule ^posts/([A-Za-z0-9-]+)/?$ index.php?cms_route=blog&slug=$1 [L,QSA,B]
  RewriteRule ^product/([A-Za-z0-9-]+)/?$ index.php?cms_route=products&slug=$1 [L,QSA,B]
  RewriteRule ^products/([A-Za-z0-9-]+)/?$ index.php?cms_route=products&slug=$1 [L,QSA,B]
  RewriteRule ^page/([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]
  RewriteRule ^pages/([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]

  # Optional short page URL: /about-us -> JSON page slug about-us.
  RewriteRule ^([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]

  # Fallback to the React shell.
  RewriteRule . index.php [L,QSA]
</IfModule>

HTA
  . $protectJson . "\n" . <<<'HTA'
<FilesMatch "^\.lpcms-mail\.log$">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>

<IfModule mod_headers.c>
  Header unset ETag
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), payment=()"
</IfModule>

<IfModule mod_mime.c>
  AddType image/avif avif
  AddType image/webp webp
  AddType image/svg+xml svg svgz
  AddType font/woff2 woff2
  AddType application/manifest+json webmanifest
  AddType video/mp4 mp4
  AddType video/webm webm
</IfModule>

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain text/html text/xml text/css text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml font/ttf font/otf font/woff font/woff2
</IfModule>

<IfModule mod_brotli.c>
  AddOutputFilterByType BROTLI_COMPRESS text/plain text/html text/xml text/css text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml font/ttf font/otf font/woff font/woff2
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault "access plus 1 month"
  ExpiresByType text/html "access plus 0 seconds"
  ExpiresByType application/json "access plus 0 seconds"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/avif "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType font/woff "access plus 1 year"
  ExpiresByType application/pdf "access plus 1 month"
  ExpiresByType video/mp4 "access plus 1 year"
  ExpiresByType video/webm "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(?:css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|mp4|webm)$">
HTA
  . "\n" . '    Header set Cache-Control "public, max-age=' . $cacheSeconds . ', immutable"' . "\n" . <<<'HTA'
  </FilesMatch>
  <FilesMatch "\.(?:html|php|json)$">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
  </FilesMatch>
</IfModule>
HTA
  . "\n# END FlatCMS\n");
}

function cms_assets_htaccess(array $data): string {
  $s = cms_htaccess_settings($data);
  $cacheDays = max(1, min(365, (int)($s['assetCacheDays'] ?? 365)));
  $cacheSeconds = $cacheDays * 86400;
  return "# BEGIN FlatCMS\n" . <<<'HTA'
# Managed by index.php. Protects uploads while allowing static assets.
Options -Indexes

<FilesMatch "(^\.|\.(?:php|phtml|php[0-9]?|phar|cgi|pl|py|sh|asp|aspx|jsp)$)">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>

<IfModule mod_php.c>
  php_flag engine off
</IfModule>

<IfModule mod_mime.c>
  AddType image/avif avif
  AddType image/webp webp
  AddType image/svg+xml svg svgz
  AddType font/woff2 woff2
  AddType video/mp4 mp4
  AddType video/webm webm
</IfModule>

<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  <FilesMatch "\.(?:css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|mp4|webm)$">
HTA
  . "\n" . '    Header set Cache-Control "public, max-age=' . $cacheSeconds . ', immutable"' . "\n" . <<<'HTA'
  </FilesMatch>
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault "access plus 1 month"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/avif "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType font/woff "access plus 1 year"
  ExpiresByType video/mp4 "access plus 1 year"
  ExpiresByType video/webm "access plus 1 year"
</IfModule>
HTA
  . "\n# END FlatCMS\n";
}

function cms_upsert_managed_block(string $file, string $block): array {
  $start = '# BEGIN FlatCMS';
  $end = '# END FlatCMS';
  $existing = file_exists($file) ? (string)file_get_contents($file) : '';
  $new = $existing;
  if (str_contains($existing, $start) && str_contains($existing, $end)) {
    $new = preg_replace_callback('/# BEGIN FlatCMS.*?# END FlatCMS\s*/s', fn() => rtrim($block) . "\n", $existing, 1);
  } else {
    $new = rtrim($existing) . ($existing !== '' ? "\n\n" : '') . rtrim($block) . "\n";
  }
  if ($new === $existing) return ['file' => basename($file), 'changed' => false, 'ok' => true, 'message' => 'Already synced'];
  $ok = @file_put_contents($file, $new, LOCK_EX) !== false;
  return ['file' => basename($file), 'changed' => $ok, 'ok' => $ok, 'message' => $ok ? 'Synced' : 'Could not write file'];
}

function cms_sync_htaccess_files(array $data): array {
  $settings = cms_htaccess_settings($data);
  if (empty($settings['autoSync'])) {
    return ['ok' => true, 'skipped' => true, 'message' => 'Auto sync disabled'];
  }
  if (!is_dir(ASSETS_DIR)) @mkdir(ASSETS_DIR, 0755, true);
  $root = cms_upsert_managed_block(HTACCESS_FILE, cms_root_htaccess($data));
  $assets = cms_upsert_managed_block(ASSETS_HTACCESS_FILE, cms_assets_htaccess($data));
  return ['ok' => !empty($root['ok']) && !empty($assets['ok']), 'root' => $root, 'assets' => $assets];
}

function cms_htaccess_status(array $data): array {
  return [
    'rootExists' => file_exists(HTACCESS_FILE),
    'assetsExists' => file_exists(ASSETS_HTACCESS_FILE),
    'rootWritable' => is_writable(__DIR__) || is_writable(HTACCESS_FILE),
    'assetsWritable' => is_writable(ASSETS_DIR) || is_writable(ASSETS_HTACCESS_FILE),
    'settings' => cms_htaccess_settings($data),
  ];
}

function cms_ensure_files(): void {
  foreach (cms_asset_dirs() as $dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
      error_log('LPCMS: could not create required directory: ' . $dir);
    }
  }
  if (!file_exists(DATA_FILE)) {
    $defaultJson = json_encode(cms_default_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($defaultJson === false || @file_put_contents(DATA_FILE, $defaultJson, LOCK_EX) === false) {
      error_log('LPCMS: could not create data.json. Check web-root ownership and write permissions.');
    }
  }
}

function cms_load_data(): array {
  cms_ensure_files();
  $raw = @file_get_contents(DATA_FILE);
  $data = json_decode($raw ?: '', true);
  if (!is_array($data)) {
    $data = cms_default_data();
  }
  return array_replace_recursive(cms_default_data(), $data);
}

function cms_user_from_email(array $data, string $email): ?array {
  $email = strtolower(trim($email));
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return null;

  $users = $data['settings']['users']['items'] ?? [];
  if (!is_array($users)) return null;

  foreach ($users as $user) {
    if (!is_array($user)) continue;

    $userEmail = strtolower(trim((string)($user['email'] ?? '')));
    if ($userEmail === '' || !hash_equals($userEmail, $email)) continue;
    if (array_key_exists('active', $user) && !$user['active']) return null;

    $role = strtolower(trim((string)($user['role'] ?? 'editor')));
    if (!in_array($role, ['super', 'editor'], true)) return null;

    $user['email'] = $userEmail;
    $user['role'] = $role;
    $user['active'] = true;
    return $user;
  }

  return null;
}

function cms_save_data(array $data): bool {
  $data['version'] = CMS_VERSION;
  $tmp = DATA_FILE . '.tmp';
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    return false;
  }
  if (file_put_contents($tmp, $json, LOCK_EX) === false) {
    return false;
  }
  return rename($tmp, DATA_FILE);
}

function cms_json($payload, int $status = 200): void {
  $baseLevel = $GLOBALS['CMS_API_BUFFER_LEVEL'] ?? null;
  if (is_int($baseLevel)) {
    while (ob_get_level() > $baseLevel) @ob_end_clean();
  }
  $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    $status = 500;
    $json = '{"ok":false,"error":"JSON encoding failed on the server."}';
  }
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  echo $json;
  exit;
}

function cms_b64u(string $s): string {
  return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function cms_b64u_dec(string $s): string|false {
  $pad = strlen($s) % 4;
  if ($pad) $s .= str_repeat('=', 4 - $pad);
  return base64_decode(strtr($s, '-_', '+/'));
}

function cms_make_token(string $email): string {
  $payload = cms_b64u(json_encode([
    'email' => strtolower(trim($email)),
    'exp' => time() + 15 * 60,
    'nonce' => bin2hex(random_bytes(12))
  ]));
  $sig = cms_b64u(hash_hmac('sha256', $payload, CMS_SECRET, true));
  return $payload . '.' . $sig;
}

function cms_verify_token(string $token): ?string {
  $parts = explode('.', $token, 2);
  if (count($parts) !== 2) return null;
  [$payload, $sig] = $parts;
  $expected = cms_b64u(hash_hmac('sha256', $payload, CMS_SECRET, true));
  if (!hash_equals($expected, $sig)) return null;
  $json = cms_b64u_dec($payload);
  if (!$json) return null;
  $data = json_decode($json, true);
  if (!is_array($data) || empty($data['email']) || empty($data['exp'])) return null;
  if ((int)$data['exp'] < time()) return null;
  $email = strtolower(trim($data['email']));
  if (!in_array($email, array_map('strtolower', ADMIN_EMAILS), true)) return null;
  return $email;
}

function cms_base_url(array $data): string {
  $configured = trim((string)($data['settings']['baseUrl'] ?? ''));
  if ($configured !== '') return rtrim($configured, '/');
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
  $scheme = $https ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $scheme . '://' . $host . ($path === '' ? '' : $path);
}

function cms_mailer_settings(array $data): array {
  $defaults = [
    'mode' => 'mail',
    'fromName' => 'LPCMS',
    'fromEmail' => '',
    'replyTo' => '',
    'smtp' => [
      'host' => '', 'port' => 587, 'encryption' => 'tls', 'username' => '', 'password' => '',
      'timeout' => 12, 'verifyPeer' => true,
    ],
  ];
  return array_replace_recursive($defaults, $data['settings']['mailer'] ?? []);
}

function cms_mail_default_from(array $data): array {
  $settings = cms_mailer_settings($data);
  $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
  $host = preg_replace('/:\d+$/', '', $host);
  $host = preg_replace('/^www\./', '', $host);
  $host = trim((string)preg_replace('/[^a-z0-9.-]/', '', $host), '.-');
  if ($host === '' || !str_contains($host, '.')) $host = 'localhost.localdomain';
  $email = trim((string)($settings['fromEmail'] ?? ''));
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $email = 'no-reply@' . $host;
  $name = trim((string)($settings['fromName'] ?? 'LPCMS')) ?: 'LPCMS';
  return [$email, $name];
}

function cms_mail_log(string $event, array $context = []): void {
  $clean = [];
  foreach ($context as $key => $value) {
    if (preg_match('/pass|secret|token/i', (string)$key)) continue;
    if (is_scalar($value) || $value === null) $clean[$key] = $value;
  }
  $line = json_encode(['time' => date('c'), 'event' => $event] + $clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($line !== false) @file_put_contents(MAIL_LOG_FILE, $line . "\n", FILE_APPEND | LOCK_EX);
}

function cms_mail_logs(int $limit = 100): array {
  if (!is_file(MAIL_LOG_FILE)) return [];
  $lines = @file(MAIL_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
  $lines = array_slice($lines, -max(1, min(300, $limit)));
  $out = [];
  foreach ($lines as $line) {
    $row = json_decode($line, true);
    $out[] = is_array($row) ? $row : ['time' => '', 'event' => 'log', 'message' => $line];
  }
  return array_reverse($out);
}

function cms_smtp_read($socket): array {
  $text = '';
  $code = 0;
  while (!feof($socket)) {
    $line = fgets($socket, 4096);
    if ($line === false) break;
    $text .= $line;
    if (preg_match('/^(\d{3})([ -])/', $line, $m)) {
      $code = (int)$m[1];
      if ($m[2] === ' ') break;
    }
  }
  return [$code, trim($text)];
}

function cms_smtp_command($socket, string $command, array $expected, string $label = ''): array {
  if ($command !== '') fwrite($socket, $command . "\r\n");
  [$code, $text] = cms_smtp_read($socket);
  if (!in_array($code, $expected, true)) throw new RuntimeException('SMTP ' . ($label !== '' ? $label : ($command !== '' ? strtok($command, ' ') : 'response')) . ' failed: ' . $text);
  return [$code, $text];
}

function cms_smtp_connect(array $smtp): array {
  $host = trim((string)($smtp['host'] ?? ''));
  $port = max(1, min(65535, (int)($smtp['port'] ?? 587)));
  $encryption = strtolower((string)($smtp['encryption'] ?? 'tls'));
  if ($host === '') throw new RuntimeException('SMTP host is not configured.');
  if (!in_array($encryption, ['none','tls','ssl'], true)) $encryption = 'tls';
  $timeout = max(3, min(60, (int)($smtp['timeout'] ?? 12)));
  $verifyPeer = !array_key_exists('verifyPeer', $smtp) || !empty($smtp['verifyPeer']);
  $context = stream_context_create(['ssl' => [
    'verify_peer' => $verifyPeer,
    'verify_peer_name' => $verifyPeer,
    'allow_self_signed' => !$verifyPeer,
    'SNI_enabled' => true,
    'peer_name' => $host,
  ]]);
  $scheme = $encryption === 'ssl' ? 'ssl' : 'tcp';
  $errno = 0; $errstr = '';
  $socket = @stream_socket_client($scheme . '://' . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
  if (!$socket) throw new RuntimeException('SMTP connection failed: ' . ($errstr ?: ('error ' . $errno)));
  stream_set_timeout($socket, $timeout);
  cms_smtp_command($socket, '', [220]);
  $helo = preg_replace('/[^A-Za-z0-9.-]/', '', (string)($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
  cms_smtp_command($socket, 'EHLO ' . $helo, [250]);
  if ($encryption === 'tls') {
    cms_smtp_command($socket, 'STARTTLS', [220]);
    $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    if ($crypto !== true) throw new RuntimeException('SMTP STARTTLS negotiation failed.');
    cms_smtp_command($socket, 'EHLO ' . $helo, [250]);
  }
  $username = (string)($smtp['username'] ?? '');
  $password = (string)($smtp['password'] ?? '');
  if ($username !== '') {
    cms_smtp_command($socket, 'AUTH LOGIN', [334]);
    cms_smtp_command($socket, base64_encode($username), [334], 'AUTH');
    cms_smtp_command($socket, base64_encode($password), [235], 'AUTH');
  }
  return [$socket, ['host' => $host, 'port' => $port, 'encryption' => $encryption]];
}

function cms_smtp_send(array $data, string $to, string $subject, string $body): bool {
  $settings = cms_mailer_settings($data);
  $smtp = $settings['smtp'] ?? [];
  [$fromEmail, $fromName] = cms_mail_default_from($data);
  [$socket] = cms_smtp_connect($smtp);
  try {
    cms_smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
    cms_smtp_command($socket, 'RCPT TO:<' . $to . '>', [250,251]);
    cms_smtp_command($socket, 'DATA', [354]);
    $safeSubject = trim(preg_replace('/[\r\n]+/', ' ', $subject));
    $safeName = trim(preg_replace('/[\r\n]+/', ' ', $fromName));
    $replyTo = trim((string)($settings['replyTo'] ?? ''));
    $headers = [
      'MIME-Version: 1.0',
      'Content-Type: text/plain; charset=UTF-8',
      'Content-Transfer-Encoding: 8bit',
      'From: ' . $safeName . ' <' . $fromEmail . '>',
      'To: <' . $to . '>',
      'Subject: ' . $safeSubject,
      'Date: ' . date(DATE_RFC2822),
      'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . (parse_url(cms_base_url($data), PHP_URL_HOST) ?: 'localhost') . '>',
    ];
    if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: <' . $replyTo . '>';
    $normalized = preg_replace("/\r\n|\r|\n/", "\r\n", $body);
    $normalized = preg_replace('/^\./m', '..', (string)$normalized);
    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $normalized . "\r\n.\r\n");
    cms_smtp_command($socket, '', [250], 'message body');
    cms_smtp_command($socket, 'QUIT', [221]);
    return true;
  } finally {
    if (is_resource($socket)) fclose($socket);
  }
}

function cms_send_mail(string $to, string $subject, string $body, ?array $data = null): bool {
  $data = $data ?: cms_load_data();
  $settings = cms_mailer_settings($data);
  $mode = strtolower((string)($settings['mode'] ?? 'mail'));
  try {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Recipient email is invalid.');
    if ($mode === 'smtp') {
      $ok = cms_smtp_send($data, $to, $subject, $body);
    } else {
      [$fromEmail, $fromName] = cms_mail_default_from($data);
      $headers = "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
      $headers .= 'From: ' . preg_replace('/[\r\n]+/', ' ', $fromName) . ' <' . $fromEmail . ">\r\n";
      $replyTo = trim((string)($settings['replyTo'] ?? ''));
      if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers .= 'Reply-To: <' . $replyTo . ">\r\n";
      $ok = @mail($to, trim(preg_replace('/[\r\n]+/', ' ', $subject)), $body, $headers);
    }
    cms_mail_log($ok ? 'sent' : 'failed', ['mode' => $mode, 'to' => $to, 'subject' => substr($subject,0,160)]);
    return $ok;
  } catch (Throwable $e) {
    cms_mail_log('error', ['mode' => $mode, 'to' => $to, 'subject' => substr($subject,0,160), 'message' => substr($e->getMessage(),0,500)]);
    return false;
  }
}

function cms_test_mailer_connection(array $data): array {
  $settings = cms_mailer_settings($data);
  $mode = strtolower((string)($settings['mode'] ?? 'mail'));
  if ($mode !== 'smtp') return ['ok' => function_exists('mail'), 'message' => function_exists('mail') ? 'PHP mail() is available.' : 'PHP mail() is not available.', 'mode' => 'mail'];
  try {
    [$socket, $meta] = cms_smtp_connect($settings['smtp'] ?? []);
    cms_smtp_command($socket, 'QUIT', [221]);
    if (is_resource($socket)) fclose($socket);
    cms_mail_log('connection-ok', $meta);
    return ['ok' => true, 'message' => 'SMTP connection and authentication succeeded.', 'mode' => 'smtp'] + $meta;
  } catch (Throwable $e) {
    cms_mail_log('connection-error', ['mode' => 'smtp', 'message' => substr($e->getMessage(),0,500)]);
    return ['ok' => false, 'message' => $e->getMessage(), 'mode' => 'smtp'];
  }
}

function cms_favicon_href(array $data): string {
  $custom = trim((string)($data['settings']['branding']['favicon'] ?? ''));
  if ($custom !== '') return $custom;
  $brand = (string)($data['settings']['theme']['brand'] ?? '#0d6efd');
  if (!preg_match('/^#[0-9a-f]{6}$/i', $brand)) $brand = '#0d6efd';
  $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="16" fill="' . $brand . '"/><text x="32" y="41" text-anchor="middle" font-family="Arial,sans-serif" font-size="25" font-weight="700" fill="white">LP</text></svg>';
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function cms_post_commerce_webhook(array $data, array $payload): array {
  $cfg = $data['settings']['commerceIntegrations']['webhook'] ?? [];
  if (empty($cfg['enabled'])) return ['ok' => false, 'skipped' => true, 'message' => 'Commerce webhook disabled.'];
  $url = trim((string)($cfg['url'] ?? ''));
  if (!preg_match('#^https://#i', $url)) return ['ok' => false, 'message' => 'Commerce webhook must use HTTPS.'];
  $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) return ['ok' => false, 'message' => 'Could not encode webhook payload.'];
  $token = trim((string)($cfg['bearerToken'] ?? ''));
  $headers = ['Content-Type: application/json', 'User-Agent: LPCMS/' . CMS_VERSION];
  if ($token !== '') $headers[] = 'Authorization: Bearer ' . $token;
  try {
    if (function_exists('curl_init')) {
      $ch = curl_init($url);
      curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5]);
      $body = curl_exec($ch); $err = curl_error($ch); $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
      if ($body === false || $status < 200 || $status >= 300) throw new RuntimeException($err ?: ('HTTP ' . $status));
      return ['ok' => true, 'status' => $status];
    }
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $json, 'timeout' => 10, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) throw new RuntimeException('Webhook request failed.');
    return ['ok' => true, 'status' => 200];
  } catch (Throwable $e) {
    return ['ok' => false, 'message' => $e->getMessage()];
  }
}


function cms_http_json_request(string $url, string $method = 'GET', array $headers = [], ?array $payload = null, int $timeout = 20): array {
  if (!preg_match('#^https://#i', $url)) throw new RuntimeException('External API endpoint must use HTTPS.');
  $headers[] = 'Accept: application/json';
  $body = $payload === null ? null : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($payload !== null) $headers[] = 'Content-Type: application/json';
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    $opts = [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>$timeout,CURLOPT_HTTPHEADER=>$headers,CURLOPT_USERAGENT=>'LPCMS/'.CMS_VERSION,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2];
    if ($method !== 'GET') { $opts[CURLOPT_CUSTOMREQUEST]=$method; if ($body !== null) $opts[CURLOPT_POSTFIELDS]=$body; }
    if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) $opts[CURLOPT_PROTOCOLS]=CURLPROTO_HTTPS;
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch); $err=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); curl_close($ch);
    if ($raw===false) throw new RuntimeException($err ?: 'External API request failed.');
  } else {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>implode("\r\n",$headers),'content'=>$body??'','timeout'=>$timeout,'ignore_errors'=>true],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
    $raw=@file_get_contents($url,false,$ctx);
    if ($raw===false) throw new RuntimeException('External API request failed.');
    $status=200;
    foreach (($http_response_header??[]) as $line) if (preg_match('#^HTTP/\S+\s+(\d{3})#',$line,$m)) $status=(int)$m[1];
  }
  $json=json_decode((string)$raw,true);
  if ($status<200 || $status>=300) {
    $msg=is_array($json)?(string)($json['error']['message']??$json['message']??$json['error']??''):'';
    throw new RuntimeException($msg!==''?$msg:('External API returned HTTP '.$status.'.'));
  }
  if (!is_array($json)) throw new RuntimeException('External API returned invalid JSON.');
  return $json;
}

function cms_public_remote_url(string $url): bool {
  if (!preg_match('#^https?://#i',$url)) return false;
  $host=(string)parse_url($url,PHP_URL_HOST);
  if ($host==='') return false;
  $ips=[];
  if (filter_var($host,FILTER_VALIDATE_IP)) $ips=[$host]; else $ips=gethostbynamel($host)?:[];
  if (!$ips) return false;
  foreach ($ips as $ip) if (filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)===false) return false;
  return true;
}

function cms_remote_image_bytes(string $url, int $maxBytes = 15728640): array {
  $current=trim($url);
  if ($current==='') throw new RuntimeException('No remote image URL was supplied.');
  $accept='Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8';
  for($hop=0;$hop<5;$hop++){
    if (!cms_public_remote_url($current)) throw new RuntimeException('Remote image URL is not allowed.');
    if (function_exists('curl_init')) {
      $ch=curl_init($current);
      $opts=[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_CONNECTTIMEOUT=>8,
        CURLOPT_TIMEOUT=>30,
        CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; LPCMS/'.CMS_VERSION.'; +https://lpcms.dev4.click)',
        CURLOPT_HTTPHEADER=>[$accept,'Accept-Language: en-US,en;q=0.8'],
        CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_HEADER=>false,
      ];
      if (defined('CURLOPT_PROTOCOLS')) $opts[CURLOPT_PROTOCOLS]=(defined('CURLPROTO_HTTPS')?CURLPROTO_HTTPS:0)|(defined('CURLPROTO_HTTP')?CURLPROTO_HTTP:0);
      curl_setopt_array($ch,$opts);
      $raw=curl_exec($ch); $err=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $type=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE); $redir=(string)curl_getinfo($ch,CURLINFO_REDIRECT_URL); curl_close($ch);
      if ($raw===false) throw new RuntimeException($err?:'Remote image download failed.');
      if ($status>=300 && $status<400 && $redir!=='') {
        $next=cms_resolve_remote_url($current,$redir);
        if($next==='') throw new RuntimeException('Remote image returned an invalid redirect.');
        $current=$next; continue;
      }
      if ($status<200 || $status>=300) throw new RuntimeException('Remote image returned HTTP '.$status.'.');
    } else {
      $ctx=stream_context_create(['http'=>[
        'timeout'=>30,'follow_location'=>0,'ignore_errors'=>true,
        'user_agent'=>'Mozilla/5.0 (compatible; LPCMS/'.CMS_VERSION.'; +https://lpcms.dev4.click)',
        'header'=>$accept."\r\nAccept-Language: en-US,en;q=0.8\r\n"
      ],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
      $raw=@file_get_contents($current,false,$ctx,0,$maxBytes+1); if($raw===false) throw new RuntimeException('Remote image download failed.');
      $status=200;$type='';$redir='';
      foreach(($http_response_header??[]) as $line){
        if(preg_match('#^HTTP/\S+\s+(\d{3})#',$line,$m))$status=(int)$m[1];
        if(stripos($line,'Content-Type:')===0)$type=trim(substr($line,13));
        if(stripos($line,'Location:')===0)$redir=trim(substr($line,9));
      }
      if($status>=300&&$status<400&&$redir!==''){
        $next=cms_resolve_remote_url($current,$redir);
        if($next==='') throw new RuntimeException('Remote image returned an invalid redirect.');
        $current=$next; continue;
      }
      if($status<200||$status>=300) throw new RuntimeException('Remote image returned HTTP '.$status.'.');
    }
    $raw=(string)$raw;
    if(strlen($raw)>$maxBytes) throw new RuntimeException('Remote image exceeds 15MB.');
    if(strlen($raw)<16) throw new RuntimeException('Remote image download was empty.');
    $tmp=tempnam(sys_get_temp_dir(),'lpcms-media-'); if($tmp===false) throw new RuntimeException('Could not create temporary image file.');
    $written=@file_put_contents($tmp,$raw,LOCK_EX);
    if($written===false || $written<1){ @unlink($tmp); throw new RuntimeException('Could not save the downloaded image temporarily.'); }
    $mime=cms_detect_mime($tmp);
    if(!str_starts_with($mime,'image/')){ @unlink($tmp); throw new RuntimeException('Selected URL did not return an image.'); }
    return ['tmp'=>$tmp,'mime'=>$mime,'url'=>$current,'bytes'=>(int)$written,'contentType'=>$type];
  }
  throw new RuntimeException('Too many image redirects.');
}

function cms_media_proxy_search(array $sources, string $name, string $q, int $page, callable $clean): array {
  $proxy=is_array($sources['proxy']??null)?$sources['proxy']:[];
  if(empty($proxy['enabled'])) throw new RuntimeException('No-key stock image proxy is disabled.');
  $base=trim((string)($proxy['baseUrl']??'https://proxy.getinstantimages.com/api/'));
  if(!preg_match('#^https://#i',$base)) throw new RuntimeException('Stock image proxy must use HTTPS.');
  $version=trim((string)($proxy['schemaVersion']??'7.2.0'))?:'7.2.0';
  $params=['type'=>'search','page'=>$page,'term'=>$q,'version'=>$version];
  if($name==='unsplash') $params['content_filter']='high';
  if($name==='pixabay') $params['safesearch']='true';
  $url=rtrim($base,'/').'/'.rawurlencode($name).'?'.http_build_query($params,'','&',PHP_QUERY_RFC3986);
  $j=cms_http_json_request($url);
  $licences=[
    'pexels'=>['Pexels License','https://www.pexels.com/license/'],
    'pixabay'=>['Pixabay Content License','https://pixabay.com/service/license-summary/'],
    'unsplash'=>['Unsplash License','https://unsplash.com/license'],
  ];
  [$licence,$licenceUrl]=$licences[$name]??['',''];
  $items=[];
  foreach(($j['results']??[]) as $r){
    if(!is_array($r)) continue;
    $urls=is_array($r['urls']??null)?$r['urls']:[];
    $user=is_array($r['user']??null)?$r['user']:[];
    $thumb=(string)($urls['thumb']??$urls['small']??$r['thumb']??'');
    $full=(string)($urls['full']??$urls['regular']??$r['url']??$thumb);
    if($full==='' || $thumb==='') continue;
    $title=$clean($r['alt']??$r['title']??$r['caption']??(ucfirst($name).' image')) ?: (ucfirst($name).' image');
    $items[]=[
      'id'=>(string)($r['id']??md5($full)),
      'title'=>$title,
      'thumb'=>$thumb,
      'url'=>$full,
      'creator'=>$clean($user['name']??$r['creator']??''),
      'license'=>$licence,
      'licenseUrl'=>$licenceUrl,
      'landing'=>(string)($r['permalink']??($r['links']['html']??$user['url']??'')),
      'source'=>ucfirst($name),
      'downloadTrack'=>(string)($urls['download_url']??''),
    ];
  }
  if(!$items && !empty($j['error'])){
    $msg=is_array($j['error'])?(string)($j['error']['message']??''):(string)$j['error'];
    if($msg!=='') throw new RuntimeException($msg);
  }
  return $items;
}

function cms_media_search(array $data, string $provider, string $q, int $page = 1): array {
  $q=trim($q); if($q==='') return [];
  $page=max(1,min(30,$page)); $provider=strtolower(trim($provider)); if($provider==='') $provider='free';
  $sources=$data['settings']['mediaSources']??[];
  $clean=function($value): string {
    $value=is_array($value)?($value['value']??''):$value;
    return trim(html_entity_decode(strip_tags((string)$value),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  };
  $searchOne=function(string $name) use ($data,$sources,$q,$page,$clean): array {
    $cfg=$sources[$name]??[];
    $proxyEnabled=!empty($sources['proxy']['enabled']);
    $hasDirectKey=trim((string)($cfg['apiKey']??''))!=='';
    if(!in_array($name,['openverse','commons'],true) && empty($cfg['enabled']) && !$proxyEnabled && !$hasDirectKey) throw new RuntimeException(ucfirst($name).' media source is not enabled.');
    $items=[];
    if($name==='openverse'){
      // Openverse deprecated the old `mature` search parameter. Omitting it keeps
      // anonymous searches compatible with the current public API defaults.
      $url='https://api.openverse.org/v1/images/?q='.rawurlencode($q).'&page_size=24&page='.$page;
      $j=cms_http_json_request($url);
      foreach(($j['results']??[]) as $r) $items[]=[
        'id'=>(string)($r['id']??''),
        'title'=>$clean($r['title']??'Openverse image')?:'Openverse image',
        'thumb'=>(string)($r['thumbnail']??$r['url']??''),
        'url'=>(string)($r['url']??''),
        'creator'=>$clean($r['creator']??''),
        'license'=>$clean($r['license']??''),
        'licenseUrl'=>(string)($r['license_url']??''),
        'landing'=>(string)($r['foreign_landing_url']??''),
        'source'=>'Openverse'
      ];
    } elseif($name==='commons'){
      $params=[
        'action'=>'query','format'=>'json','formatversion'=>'2','generator'=>'search',
        'gsrsearch'=>$q,'gsrnamespace'=>'6','gsrlimit'=>'24','gsroffset'=>(string)(($page-1)*24),
        'prop'=>'imageinfo','iiprop'=>'url|mime|extmetadata','iiurlwidth'=>'900'
      ];
      $j=cms_http_json_request('https://commons.wikimedia.org/w/api.php?'.http_build_query($params,'','&',PHP_QUERY_RFC3986));
      foreach(($j['query']['pages']??[]) as $r){
        $ii=$r['imageinfo'][0]??[]; $mime=strtolower((string)($ii['mime']??''));
        $allowedCommons=['image/jpeg','image/png','image/gif','image/webp','image/avif'];
        if($mime!=='' && !in_array($mime,$allowedCommons,true)) continue;
        $meta=$ii['extmetadata']??[];
        $title=$clean($meta['ObjectName']??'');
        if($title==='') $title=preg_replace('/^File:/i','',(string)($r['title']??'Wikimedia Commons image'));
        $creator=$clean($meta['Artist']??($meta['Credit']??''));
        $license=$clean($meta['LicenseShortName']??($meta['UsageTerms']??''));
        $licenseUrl=(string)(($meta['LicenseUrl']['value']??'')?:'');
        $landing=(string)($ii['descriptionurl']??'');
        if($landing==='') $landing='https://commons.wikimedia.org/wiki/'.rawurlencode(str_replace(' ','_', (string)($r['title']??'')));
        $items[]=[
          'id'=>'commons-'.(string)($r['pageid']??md5((string)($r['title']??''))),
          'title'=>$title?:'Wikimedia Commons image',
          'thumb'=>(string)($ii['thumburl']??$ii['url']??''),
          'url'=>(string)($ii['url']??''),
          'creator'=>$creator,
          'license'=>$license?:'See source licence',
          'licenseUrl'=>$licenseUrl,
          'landing'=>$landing,
          'source'=>'Wikimedia Commons'
        ];
      }
    } elseif($name==='pexels'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://api.pexels.com/v1/search?query='.rawurlencode($q).'&per_page=24&page='.$page,'GET',['Authorization: '.$key]);
      foreach(($j['photos']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['alt']??'Pexels image')?:'Pexels image','thumb'=>(string)($r['src']['medium']??''),'url'=>(string)($r['src']['large2x']??$r['src']['original']??''),'creator'=>$clean($r['photographer']??''),'license'=>'Pexels License','licenseUrl'=>'https://www.pexels.com/license/','landing'=>(string)($r['url']??''),'source'=>'Pexels'];
    } elseif($name==='pixabay'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://pixabay.com/api/?key='.rawurlencode($key).'&q='.rawurlencode($q).'&image_type=photo&safesearch=true&per_page=24&page='.$page);
      foreach(($j['hits']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['tags']??'Pixabay image')?:'Pixabay image','thumb'=>(string)($r['webformatURL']??''),'url'=>(string)($r['largeImageURL']??$r['webformatURL']??''),'creator'=>$clean($r['user']??''),'license'=>'Pixabay Content License','licenseUrl'=>'https://pixabay.com/service/license-summary/','landing'=>(string)($r['pageURL']??''),'source'=>'Pixabay'];
    } elseif($name==='unsplash'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://api.unsplash.com/search/photos?query='.rawurlencode($q).'&per_page=24&page='.$page,'GET',['Authorization: Client-ID '.$key,'Accept-Version: v1']);
      foreach(($j['results']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['alt_description']??$r['description']??'Unsplash image')?:'Unsplash image','thumb'=>(string)($r['urls']['small']??''),'url'=>(string)($r['urls']['regular']??''),'creator'=>$clean($r['user']['name']??''),'license'=>'Unsplash License','licenseUrl'=>'https://unsplash.com/license','landing'=>(string)($r['links']['html']??''),'source'=>'Unsplash'];
    } else throw new RuntimeException('Unknown media provider.');
    return array_values(array_filter($items,fn($x)=>!empty($x['url'])&&!empty($x['thumb'])));
  };

  if($provider==='free'){
    $errors=[];
    // Openverse is tried first for broad CC/open-media coverage. Wikimedia Commons
    // is the automatic no-key fallback when Openverse is unavailable or returns no results.
    foreach(['openverse','commons'] as $name){
      try { $items=$searchOne($name); if($items) return $items; }
      catch(Throwable $e){ $errors[]=$name.': '.$e->getMessage(); }
    }
    if($errors) throw new RuntimeException('Free image search could not reach its public sources. '.implode(' | ',$errors));
    return [];
  }
  return $searchOne($provider);
}

function cms_ai_generate(array $data, string $prompt, string $context = '', string $tone = '', string $systemOverride = ''): array {
  $cfg=$data['settings']['ai']??[]; if(($cfg['accessTier']??'free')!=='pro') throw new RuntimeException('Connected AI generation is available only in Pro mode. Free mode uses manual AI shortcuts.'); if(empty($cfg['enabled'])) throw new RuntimeException('AI Assistant is disabled.');
  $provider=strtolower((string)($cfg['provider']??'openai')); $key=trim((string)($cfg['apiKey']??'')); if($key==='') throw new RuntimeException('Add an API key for the selected AI provider.');
  $system=trim($systemOverride!==''?$systemOverride:(string)($cfg['systemPrompt']??''));
  $input=trim($prompt).($tone!==''?"\n\nTone: ".$tone:'').($context!==''?"\n\nExisting content/context:\n".$context:'');
  if($provider==='gemini'){
    $model=trim((string)($cfg['model']??''))?:'gemini-3.7-flash';
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';
    $j=cms_http_json_request($url,'POST',['x-goog-api-key: '.$key],['system_instruction'=>['parts'=>[['text'=>$system]]],'contents'=>[['role'=>'user','parts'=>[['text'=>$input]]]]]);
    $text=(string)($j['candidates'][0]['content']['parts'][0]['text']??'');
  } else {
    $defaults=['openai'=>['https://api.openai.com/v1/chat/completions','gpt-5'],'kimi'=>['https://api.moonshot.cn/v1/chat/completions','kimi-k3'],'mistral'=>['https://api.mistral.ai/v1/chat/completions','mistral-small-latest'],'custom'=>['','']];
    if(!isset($defaults[$provider])) throw new RuntimeException('This AI provider needs a custom OpenAI-compatible endpoint.');
    [$defaultUrl,$defaultModel]=$defaults[$provider]; $url=trim((string)($cfg['endpoint']??''))?:$defaultUrl; $model=trim((string)($cfg['model']??''))?:$defaultModel;
    if($url===''||$model==='') throw new RuntimeException('Configure the AI endpoint and model.');
    $j=cms_http_json_request($url,'POST',['Authorization: Bearer '.$key],['model'=>$model,'messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>$input]],'temperature'=>0.7]);
    $text=(string)($j['choices'][0]['message']['content']??'');
  }
  if(trim($text)==='') throw new RuntimeException('The AI provider returned no text.');
  return ['ok'=>true,'text'=>trim($text),'provider'=>$provider,'model'=>$model??''];
}

function cms_checkout_cart(array $data, array $submitted, array $options = []): array {
  $products=$data['products']['items']??[]; $lines=[]; $subtotal=0.0; $currency=(string)($data['products']['currency']??'EUR');
  foreach(array_slice($submitted,0,50) as $row){
    $qty=max(1,min(99,(int)($row['qty']??1))); $found=null;
    foreach($products as $p){ if((string)($p['id']??'')===(string)($row['id']??'') || ((string)($p['slug']??'')!=='' && (string)($p['slug']??'')===(string)($row['slug']??''))){$found=$p;break;} }
    if(!$found) continue;
    $mode=(string)($found['purchaseMode']??$data['products']['defaultPurchaseMode']??'quote'); if($mode!=='checkout') continue;
    $price=(float)($found['price']??0); $variationId=(string)($row['variationId']??''); $variation=null;
    if($variationId!=='' && is_array($found['variations']??null)) foreach($found['variations'] as $v){ if((string)($v['id']??$v['sku']??'')===$variationId){$variation=$v;break;} }
    if($variation && isset($variation['price'])) $price=(float)$variation['price']; if($price<=0) continue;
    $line=['id'=>(string)($found['id']??''),'slug'=>(string)($found['slug']??''),'title'=>(string)($found['title']??'Product'),'sku'=>(string)(($variation['sku']??null)?:($found['sku']??'')),'variationId'=>$variationId,'qty'=>$qty,'price'=>$price,'currency'=>(string)(($variation['currency']??null)?:($found['currency']??$currency)),'image'=>(string)(($variation['image']??null)?:($found['image']??''))];
    $lines[]=$line; $subtotal+=$price*$qty;
  }
  if(!$lines) throw new RuntimeException('No checkout-enabled products were found in the cart.');

  $discountCode=strtoupper(trim((string)($options['discountCode']??''))); $discount=0.0;
  if($discountCode!==''){
    $matched=null;
    foreach(($data['products']['discountCodes']??[]) as $code){ if(!empty($code['enabled']) && strtoupper(trim((string)($code['code']??'')))===$discountCode){$matched=$code;break;} }
    if(!$matched) throw new RuntimeException('Discount code is not valid.');
    $value=max(0,(float)($matched['value']??0)); $type=(string)($matched['type']??'percent');
    $discount=$type==='fixed' ? min($subtotal,$value) : min($subtotal,$subtotal*min(100,$value)/100);
  }

  $shippingId=trim((string)($options['shippingId']??'')); $shipping=0.0; $shippingLabel=''; $enabledShipping=[];
  foreach(($data['products']['shippingMethods']??[]) as $method) if(!empty($method['enabled'])) $enabledShipping[]=$method;
  if($enabledShipping){
    $selected=null;
    foreach($enabledShipping as $method) if((string)($method['id']??'')===$shippingId){$selected=$method;break;}
    if(!$selected) $selected=$enabledShipping[0];
    $shippingId=(string)($selected['id']??''); $shippingLabel=(string)($selected['label']??'Delivery'); $shipping=max(0,(float)($selected['price']??0));
  }
  $total=max(0,$subtotal-$discount+$shipping);
  return ['lines'=>$lines,'subtotal'=>round($subtotal,2),'discount'=>round($discount,2),'discountCode'=>$discountCode,'shipping'=>round($shipping,2),'shippingId'=>$shippingId,'shippingLabel'=>$shippingLabel,'total'=>round($total,2),'currency'=>$currency];
}

function cms_start_checkout(array $data, array $payload): array {
  $cfg=$data['settings']['commerceIntegrations']['checkout']??[]; $mode=(string)($cfg['mode']??'order-form');
  $cart=cms_checkout_cart($data,is_array($payload['items']??null)?$payload['items']:[],$payload);
  if($mode==='payment-link'){
    $url=trim((string)($cfg['paymentLink']??'')); if(!preg_match('#^https://#i',$url)) throw new RuntimeException('Configure an HTTPS hosted payment link.');
    return ['ok'=>true,'mode'=>$mode,'url'=>$url,'cart'=>$cart];
  }
  if($mode==='adapter'){
    $url=trim((string)($cfg['adapterUrl']??'')); if(!preg_match('#^https://#i',$url)) throw new RuntimeException('Configure an HTTPS checkout adapter URL.');
    $headers=[]; $token=trim((string)($cfg['bearerToken']??'')); if($token!=='')$headers[]='Authorization: Bearer '.$token;
    $j=cms_http_json_request($url,'POST',$headers,['event'=>'checkout.start','cart'=>$cart,'customer'=>is_array($payload['customer']??null)?$payload['customer']:[],'returnUrl'=>cms_base_url($data)]);
    $redirect=(string)($j['url']??$j['redirect_url']??$j['checkout_url']??''); if(!preg_match('#^https://#i',$redirect)) throw new RuntimeException('Checkout adapter did not return an HTTPS redirect URL.');
    return ['ok'=>true,'mode'=>$mode,'url'=>$redirect,'cart'=>$cart];
  }
  return ['ok'=>true,'mode'=>'order-form','cart'=>$cart];
}

function cms_abandoned_cart_capture(array &$data, string $email, array $items, array $options = []): array {
  if(empty($data['settings']['abandonedCart']['enabled'])) return ['ok'=>true,'skipped'=>true];
  $email=strtolower(trim($email)); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('A valid email is required to save the cart.');
  $cart=cms_checkout_cart($data,$items,$options); $now=date('c'); $carts=is_array($data['settings']['abandonedCart']['carts']??null)?$data['settings']['abandonedCart']['carts']:[]; $index=-1;
  foreach($carts as $i=>$row){ if(strtolower((string)($row['email']??''))===$email && empty($row['completedAt']) && (strtotime((string)($row['updatedAt']??$row['createdAt']??''))?:0)>time()-172800){$index=$i;break;} }
  $entry=['id'=>'cart-'.bin2hex(random_bytes(6)),'token'=>bin2hex(random_bytes(18)),'email'=>$email,'createdAt'=>$now,'updatedAt'=>$now,'completedAt'=>'','firstSentAt'=>'','secondSentAt'=>'','cart'=>$cart];
  if($index>=0){$entry=array_replace($carts[$index],$entry,['id'=>$carts[$index]['id']??$entry['id'],'token'=>$carts[$index]['token']??$entry['token'],'createdAt'=>$carts[$index]['createdAt']??$now]);$carts[$index]=$entry;} else $carts[]=$entry;
  $data['settings']['abandonedCart']['carts']=array_slice($carts,-500); if(!cms_save_data($data)) throw new RuntimeException('Could not save the cart reminder.');
  return ['ok'=>true,'saved'=>true,'token'=>$entry['token'],'cart'=>$cart];
}

function cms_abandoned_cart_complete(array &$data, string $email): array {
  $email=strtolower(trim($email)); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return ['ok'=>true,'skipped'=>true]; $changed=0;
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $i=>$row){ if(strtolower((string)($row['email']??''))===$email && empty($row['completedAt'])){$data['settings']['abandonedCart']['carts'][$i]['completedAt']=date('c');$changed++;} }
  if($changed) cms_save_data($data); return ['ok'=>true,'completed'=>$changed];
}

function cms_abandoned_cart_recover(array $data, string $token): ?array {
  $token=trim($token); if($token==='') return null;
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $row){ if(!empty($row['token']) && hash_equals((string)$row['token'],$token) && empty($row['completedAt'])) return ['items'=>$row['cart']['lines']??[],'email'=>$row['email']??'','cart'=>$row['cart']??[]]; }
  return null;
}

function cms_process_abandoned_carts(array &$data): array {
  $cfg=$data['settings']['abandonedCart']??[]; if(empty($cfg['enabled'])) return ['ok'=>true,'processed'=>0,'sent'=>0,'message'=>'Abandoned-cart reminders are disabled.'];
  $first=max(5,(int)($cfg['firstDelayMinutes']??60))*60; $second=max((int)($cfg['secondDelayMinutes']??1440), (int)($cfg['firstDelayMinutes']??60)+5)*60; $sent=0; $processed=0; $now=time();
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $i=>$row){
    if(!empty($row['completedAt'])) continue; $created=strtotime((string)($row['createdAt']??''))?:0; if(!$created) continue; $processed++;
    $email=(string)($row['email']??''); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) continue;
    $items=[]; foreach(($row['cart']['lines']??[]) as $line) $items[]=(int)($line['qty']??1).' × '.(string)($line['title']??'Product');
    $resume=cms_base_url($data).'/?recover-cart='.rawurlencode((string)($row['token']??'')); $list=$items?"\n\n".implode("\n",$items):'';
    if(empty($row['firstSentAt']) && $now-$created >= $first){
      $body=trim((string)($cfg['firstMessage']??'Your cart is waiting.')).$list."\n\nReturn to your cart:\n".$resume;
      if(cms_send_mail($email,(string)($cfg['firstSubject']??'You left something in your cart'),$body,$data)){$data['settings']['abandonedCart']['carts'][$i]['firstSentAt']=date('c');$sent++;}
    }
    if(empty($row['secondSentAt']) && $now-$created >= $second){
      $body=trim((string)($cfg['secondMessage']??'Your saved cart is still available.')).$list."\n\nReturn to your cart:\n".$resume;
      if(cms_send_mail($email,(string)($cfg['secondSubject']??'Your cart is still waiting'),$body,$data)){$data['settings']['abandonedCart']['carts'][$i]['secondSentAt']=date('c');$sent++;}
    }
  }
  if($sent) cms_save_data($data); return ['ok'=>true,'processed'=>$processed,'sent'=>$sent,'message'=>'Processed '.$processed.' pending cart(s); sent '.$sent.' reminder(s).'];
}

function cms_booking_slots(array $data, string $date, string $serviceId = ''): array {
  if(empty($data['booking']['enabled'])) return [];
  $tz=new DateTimeZone(CMS_TIMEZONE); $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$date,$tz); if(!$dt || $dt->format('Y-m-d')!==$date) throw new RuntimeException('Choose a valid booking date.');
  $today=(new DateTimeImmutable('today',$tz)); if($dt<$today) return [];
  $day=(string)$dt->format('N'); $dayName=strtolower($dt->format('l')); $weekly=$data['booking']['weeklySlots']??[]; $slots=array_values(array_filter($weekly[$dayName]??$weekly[$day]??[],fn($x)=>preg_match('/^\d{2}:\d{2}$/',(string)$x)));
  $taken=[]; foreach(($data['booking']['bookings']??[]) as $booking){ if(($booking['date']??'')===$date && empty($booking['cancelledAt'])) $taken[]=(string)($booking['time']??''); }
  if($date===$today->format('Y-m-d')){ $current=(new DateTimeImmutable('now',$tz))->format('H:i'); $slots=array_values(array_filter($slots,fn($slot)=>$slot>$current)); }
  return array_values(array_filter($slots,fn($slot)=>!in_array((string)$slot,$taken,true)));
}

function cms_booking_create(array &$data, array $payload): array {
  if(empty($data['booking']['enabled'])) throw new RuntimeException('Online booking is not enabled.');
  $name=trim(strip_tags((string)($payload['name']??''))); $email=strtolower(trim((string)($payload['email']??''))); $phone=trim(strip_tags((string)($payload['phone']??''))); $serviceId=trim((string)($payload['serviceId']??'')); $date=trim((string)($payload['date']??'')); $time=trim((string)($payload['time']??''));
  if($name===''||strlen($name)>120) throw new RuntimeException('Name is required.'); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Valid email is required.');
  $service=null; foreach(($data['booking']['services']??[]) as $item) if((string)($item['id']??'')===$serviceId){$service=$item;break;} if(!$service || ($service['enabled']??true)===false) throw new RuntimeException('Choose a valid service.');
  $slots=cms_booking_slots($data,$date,$serviceId); if(!in_array($time,$slots,true)) throw new RuntimeException('That time is no longer available. Please choose another slot.');
  $code='BK-'.strtoupper(bin2hex(random_bytes(3))); $entry=['code'=>$code,'createdAt'=>date('c'),'serviceId'=>$serviceId,'serviceName'=>(string)($service['title']??$service['name']??'Service'),'date'=>$date,'time'=>$time,'name'=>$name,'email'=>$email,'phone'=>substr($phone,0,80),'cancelledAt'=>''];
  if(!isset($data['booking']['bookings'])||!is_array($data['booking']['bookings'])) $data['booking']['bookings']=[]; $data['booking']['bookings'][]=$entry; $data['booking']['bookings']=array_slice($data['booking']['bookings'],-1000);
  if(!cms_save_data($data)) throw new RuntimeException('Could not save the booking.');
  $subject='Booking confirmed - '.$entry['serviceName'].' - '.$date.' '.$time; $body="Booking confirmed.\n\nReference: {$code}\nService: {$entry['serviceName']}\nDate: {$date}\nTime: {$time}\nName: {$name}"; cms_send_mail($email,$subject,$body,$data);
  $notify=trim((string)($data['booking']['technical']['notifyEmail']??'')); if($notify==='') $notify=trim((string)($data['settings']['forms']['emailTo']??'')); if($notify==='') $notify=BREAKGLASS_ADMIN_EMAIL;
  foreach(preg_split('/[;,]+/',$notify)?:[] as $to){$to=trim($to);if(filter_var($to,FILTER_VALIDATE_EMAIL)) cms_send_mail($to,'New website booking - '.$entry['serviceName'],$body."\nEmail: {$email}\nPhone: {$phone}",$data);}
  $webhook=trim((string)($data['booking']['technical']['webhookUrl']??'')); if($webhook!=='' && preg_match('#^https://#i',$webhook) && cms_public_remote_url($webhook)){try{cms_http_json_request($webhook,'POST',[],['event'=>'booking.created','booking'=>$entry]);}catch(Throwable $e){}}
  return ['ok'=>true,'booking'=>['code'=>$code,'serviceName'=>$entry['serviceName'],'date'=>$date,'time'=>$time],'message'=>(string)cms_localized_value($data, $data['booking'] ?? [], 'confirmationText', cms_current_language($data), 'Booking confirmed.')];
}


function cms_demo_substr(string $text, int $max): string {
  return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

function cms_demo_clean_text($value, int $max = 3000): string {
  $text = trim(strip_tags((string)$value));
  $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
  return cms_demo_substr($text, $max);
}

function cms_demo_lines(string $text, int $limit = 12): array {
  $rows = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
  if (count($rows) <= 1 && str_contains($text, ';')) $rows = explode(';', $text);
  $out = [];
  foreach ($rows as $row) {
    $row = trim($row);
    if ($row === '') continue;
    $parts = preg_split('/\s+[—–-]\s+/', $row, 2) ?: [$row];
    $title = trim((string)($parts[0] ?? ''));
    $desc = trim((string)($parts[1] ?? ''));
    if ($title === '') continue;
    $out[] = ['title' => cms_demo_substr($title,120), 'text' => cms_demo_substr($desc ?: 'Professional service tailored to your requirements.',500), 'icon' => 'bi bi-check2-circle', 'image' => '', 'buttonText' => 'Contact us', 'buttonHref' => '#contact'];
    if (count($out) >= $limit) break;
  }
  return $out;
}

function cms_demo_products_from_text(string $text, string $currency = 'GBP', int $limit = 18): array {
  $rows = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
  $out = [];
  foreach ($rows as $i => $row) {
    $row = trim($row); if ($row === '') continue;
    $parts = array_map('trim', explode('|', $row));
    $title = cms_demo_clean_text($parts[0] ?? '', 120); if ($title === '') continue;
    $priceRaw = str_replace(',', '.', (string)($parts[1] ?? '0'));
    $price = preg_match('/-?[0-9]+(?:\.[0-9]+)?/', $priceRaw, $m) ? max(0, (float)$m[0]) : 0;
    $desc = cms_demo_clean_text($parts[2] ?? 'Demo product prepared from the client website brief.', 500);
    $slug = cms_slug($title) ?: ('product-' . ($i+1));
    $out[] = [
      'title'=>$title,'slug'=>$slug,'id'=>'demo-'.($i+1), 'sku'=>strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$slug),0,18)),
      'type'=>'simple','category'=>'demo-products','brand'=>'','tags'=>['demo-products'],'price'=>$price,'oldPrice'=>0,'discountLabel'=>'','currency'=>$currency,
      'image'=>'','gallery'=>[],'attributes'=>[],'variations'=>[],'stock'=>0,'inStock'=>true,'text'=>$desc,'body'=>'<p>'.htmlspecialchars($desc,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</p>','features'=>[],
      'purchaseMode'=>$price>0?'checkout':'quote','buyButton'=>$price>0,'quoteButton'=>$price<=0,'related'=>[],
      'seo'=>['title'=>$title,'description'=>$desc,'image'=>'']
    ];
    if (count($out) >= $limit) break;
  }
  return $out;
}

function cms_demo_build_draft(array $entry, array $data): array {
  $company = cms_demo_clean_text($entry['company'] ?? 'Your Business', 120) ?: 'Your Business';
  $industry = cms_demo_clean_text($entry['industry'] ?? '', 120);
  $about = cms_demo_clean_text($entry['about'] ?? '', 2500);
  $goals = cms_demo_clean_text($entry['goals'] ?? '', 1000);
  $tagline = cms_demo_clean_text($entry['tagline'] ?? '', 180);
  if ($tagline === '') $tagline = $industry !== '' ? $company . ' · ' . $industry : 'Welcome to ' . $company;
  $services = cms_demo_lines((string)($entry['services'] ?? ''), 12);
  if (!$services) $services = [['title'=>'Our service','text'=>'Tell visitors clearly what you provide and how to get started.','icon'=>'bi bi-check2-circle','image'=>'','buttonText'=>'Contact us','buttonHref'=>'#contact']];
  $plan = ($entry['plan'] ?? 'premium') === 'premium-pro' ? 'premium-pro' : 'premium';
  $currency = in_array(($entry['currency'] ?? 'GBP'), ['GBP','EUR'], true) ? (string)$entry['currency'] : 'GBP';
  $products = $plan === 'premium-pro' ? cms_demo_products_from_text((string)($entry['products'] ?? ''), $currency) : [];
  $preferred = cms_demo_clean_text($entry['preferredDomain'] ?? '', 180);
  $customDomain = '';
  $subdomain = cms_slug($company) ?: 'yourbusiness';
  if ($preferred !== '') {
    $clean = preg_replace('#^https?://#i','',$preferred);
    $clean = trim((string)preg_replace('#/.*$#','',$clean),'. ');
    if (str_contains($clean,'.')) $customDomain = strtolower($clean); else $subdomain = cms_slug($clean) ?: $subdomain;
  }
  $brand = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($entry['primaryColor'] ?? '')) ? (string)$entry['primaryColor'] : (string)($data['settings']['theme']['brand'] ?? '#0d6efd');
  $chatChoice = in_array(($entry['chatChoice'] ?? 'chatbot'),['chatbot','whatsapp','none'],true)?(string)$entry['chatChoice']:'chatbot';
  $logo = (string)($entry['logo'] ?? '');
  return [
    'siteName'=>$company,
    'header'=>['logoText'=>$company,'logoImage'=>$logo],
    'hero'=>['eyebrow'=>$industry ?: 'Welcome','title'=>$tagline,'text'=>$goals ?: ($about !== '' ? cms_demo_substr($about,420) : 'Professional solutions from '.$company.'.'),'buttonText'=>'Contact us','buttonHref'=>'#contact'],
    'about'=>['title'=>'About '.$company,'subtitle'=>$industry,'body'=>'<p>'.htmlspecialchars($about ?: ('Tell visitors about '.$company.' here.'),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</p>'],
    'services'=>$services,
    'products'=>$products,
    'contact'=>['address'=>cms_demo_clean_text($entry['address'] ?? '',300),'phone'=>cms_demo_clean_text($entry['phone'] ?? '',80),'email'=>cms_demo_clean_text($entry['email'] ?? '',160)],
    'brand'=>$brand,
    'style'=>cms_demo_clean_text($entry['style'] ?? 'clean',40),
    'plan'=>$plan,
    'subdomain'=>$subdomain,
    'customDomain'=>$customDomain,
    'chatChoice'=>$chatChoice,
    'whatsapp'=>cms_demo_clean_text($entry['whatsapp'] ?? '',80),
    'bookingNeeded'=>!empty($entry['bookingNeeded']),
  ];
}

function cms_demo_apply_request(array &$data, int $index): array {
  $requests = $data['settings']['demoIntakeAdmin']['requests'] ?? [];
  if (!isset($requests[$index]) || !is_array($requests[$index])) throw new RuntimeException('Demo request not found.');
  $r = $requests[$index];
  $draft = is_array($r['draft'] ?? null) ? $r['draft'] : cms_demo_build_draft($r,$data);
  $data['settings']['siteName'] = (string)($draft['siteName'] ?? $data['settings']['siteName'] ?? 'Website');
  $data['header']['logoText'] = (string)($draft['header']['logoText'] ?? $data['header']['logoText'] ?? '');
  if (!empty($draft['header']['logoImage'])) $data['header']['logoImage'] = (string)$draft['header']['logoImage'];
  if (!isset($data['hero']['slides'][0]) || !is_array($data['hero']['slides'][0])) $data['hero']['slides'][0] = [];
  $data['hero']['slides'][0] = array_replace($data['hero']['slides'][0], [
    'eyebrow'=>(string)($draft['hero']['eyebrow']??''),'title'=>(string)($draft['hero']['title']??''),'text'=>(string)($draft['hero']['text']??''),
    'buttonText'=>(string)($draft['hero']['buttonText']??'Contact us'),'buttonHref'=>(string)($draft['hero']['buttonHref']??'#contact')
  ]);
  $data['about']['enabled'] = true;
  $data['about']['title'] = (string)($draft['about']['title'] ?? 'About us');
  $data['about']['subtitle'] = (string)($draft['about']['subtitle'] ?? '');
  $data['about']['body'] = (string)($draft['about']['body'] ?? '');
  $data['services']['enabled'] = true;
  $data['services']['items'] = is_array($draft['services'] ?? null) ? $draft['services'] : [];
  $data['contact']['enabled'] = true;
  $data['contact']['details']['address'] = (string)($draft['contact']['address'] ?? '');
  $data['contact']['details']['phone'] = (string)($draft['contact']['phone'] ?? '');
  $data['contact']['details']['email'] = (string)($draft['contact']['email'] ?? '');
  $data['settings']['theme']['brand'] = (string)($draft['brand'] ?? $data['settings']['theme']['brand'] ?? '#0d6efd');
  $data['settings']['sitePlan']['selected'] = (string)($draft['plan'] ?? 'premium');
  $data['settings']['sitePlan']['subdomain'] = (string)($draft['subdomain'] ?? 'yourbusiness');
  $data['settings']['sitePlan']['customDomain'] = (string)($draft['customDomain'] ?? '');
  $isPro = ($draft['plan'] ?? '') === 'premium-pro';
  $data['products']['enabled'] = $isPro;
  $data['products']['shopEnabled'] = $isPro;
  $data['products']['catalogEnabled'] = $isPro;
  $data['products']['cartEnabled'] = $isPro;
  if ($isPro && !empty($draft['products'])) $data['products']['items'] = $draft['products'];
  $chatChoice = (string)($draft['chatChoice'] ?? 'none');
  $data['chatbot']['enabled'] = $chatChoice !== 'none';
  if ($chatChoice !== 'none') $data['chatbot']['mode'] = $chatChoice;
  if ($chatChoice === 'whatsapp') $data['chatbot']['whatsapp']['number'] = (string)($draft['whatsapp'] ?? '');
  $data['booking']['enabled'] = !empty($draft['bookingNeeded']);
  $platform = trim((string)($data['settings']['sitePlan']['platformDomain'] ?? 'dev4.click')) ?: 'dev4.click';
  $custom = trim((string)($draft['customDomain'] ?? ''));
  $host = $custom !== '' ? $custom : ((string)($draft['subdomain'] ?? 'yourbusiness') . '.' . $platform);
  $requests[$index]['status'] = 'approved';
  $requests[$index]['approvedAt'] = date('c');
  $requests[$index]['websiteUrl'] = 'https://' . $host;
  $requests[$index]['draft'] = $draft;
  $data['settings']['demoIntakeAdmin']['requests'] = $requests;
  return $requests[$index];
}

function cms_demo_billing_amount(array $data, array $request): array {
  $currency = in_array(($request['currency'] ?? 'GBP'),['GBP','EUR'],true)?(string)$request['currency']:'GBP';
  $pro = ($request['plan'] ?? '') === 'premium-pro';
  if ($currency === 'EUR') $amount = (string)($data['settings']['sitePlan'][$pro?'premiumProEur':'premiumEur'] ?? ($pro?'7.00':'4.00'));
  else $amount = (string)($data['settings']['sitePlan'][$pro?'premiumProGbp':'premiumGbp'] ?? ($pro?'6.50':'3.50'));
  return [$currency,$amount,$currency==='EUR'?'€':'£'];
}

function cms_demo_send_bill(array &$data, int $index): array {
  $requests = $data['settings']['demoIntakeAdmin']['requests'] ?? [];
  if (!isset($requests[$index]) || !is_array($requests[$index])) throw new RuntimeException('Demo request not found.');
  $r = $requests[$index];
  if (($r['status'] ?? '') !== 'approved') throw new RuntimeException('Approve and build the website before sending billing.');
  $email = strtolower(trim((string)($r['email'] ?? '')));
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Client email is invalid.');
  [$currency,$amount,$symbol] = cms_demo_billing_amount($data,$r);
  $cfg = $data['settings']['demoIntakeAdmin'] ?? [];
  $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i','',(string)($cfg['invoicePrefix']??'DP'))) ?: 'DP';
  $invoice = (string)($r['invoiceNumber'] ?? '');
  if ($invoice === '') $invoice = $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));
  $dueDays = max(0,min(90,(int)($cfg['dueDays']??7)));
  $due = date('Y-m-d', strtotime('+' . $dueDays . ' days'));
  $subject = trim((string)($cfg['billingSubject'] ?? 'Your website plan is ready')) ?: 'Your website plan is ready';
  $intro = trim((string)($cfg['billingMessage'] ?? 'Your website has been approved and prepared.'));
  $payment = trim((string)($cfg['paymentInstructions'] ?? ''));
  $planTitle = ($r['plan'] ?? '') === 'premium-pro' ? 'Premium Pro — E-commerce website' : 'Premium — Business website';
  $body = "Hello " . ((string)($r['name']??'') ?: 'there') . ",\n\n" . $intro . "\n\nInvoice reference: {$invoice}\nPlan: {$planTitle}\nAmount: {$symbol}{$amount} {$currency} / month\nPayment due: {$due}\nWebsite: " . ((string)($r['websiteUrl']??'') ?: cms_base_url($data)) . ($payment!==''?"\n\nPayment instructions:\n{$payment}":'') . "\n\nThank you.";
  if (!cms_send_mail($email,$subject,$body,$data)) throw new RuntimeException('Billing email could not be sent. Check Super Admin > Mailer settings and logs.');
  $requests[$index]['invoiceNumber'] = $invoice;
  $requests[$index]['billSentAt'] = date('c');
  $requests[$index]['billAmount'] = $amount;
  $requests[$index]['billCurrency'] = $currency;
  $data['settings']['demoIntakeAdmin']['requests'] = $requests;
  return $requests[$index];
}

function cms_chatbot_context(array $data, int $maxChars = 9000): string {
  $chunks=[]; $clean=fn($v)=>trim(preg_replace('/\s+/u',' ',strip_tags((string)$v))??'');
  $chunks[]='Site: '.$clean($data['settings']['siteName']??'');
  foreach(['about','services','wpThemes','features','pricing','faq','products','contact'] as $key){
    $section=$data[$key]??[]; if(!is_array($section)) continue; if(isset($section['title'])) $chunks[]=ucfirst($key).': '.$clean($section['title']).' '.$clean($section['subtitle']??$section['text']??'');
    foreach(array_slice(is_array($section['items']??null)?$section['items']:[],0,60) as $item){ if(!is_array($item)) continue; $bits=[]; foreach(['title','name','question','answer','text','excerpt','body','price','secondaryPrice','period'] as $field) if(!empty($item[$field])) $bits[]=$clean($item[$field]); if(!empty($item['features'])&&is_array($item['features'])) $bits[]=implode(', ',array_map($clean,$item['features'])); if($bits)$chunks[]=implode(' | ',$bits); }
  }
  if(!empty($data['contact']['details'])&&is_array($data['contact']['details'])) $chunks[]='Contact: '.implode(' | ',array_map($clean,$data['contact']['details']));
  foreach(($data['booking']['services']??[]) as $service) $chunks[]='Booking service: '.$clean($service['title']??$service['name']??'').' '.$clean($service['price']??'').' '.((int)($service['durationMinutes']??$service['duration']??0)).' minutes';
  return substr(implode("\n",array_filter($chunks)),0,max(1000,min(20000,$maxChars)));
}

function cms_chatbot_fallback(array $data, string $question): string {
  $lower=function($v){$v=(string)$v;return function_exists('mb_strtolower')?mb_strtolower($v,'UTF-8'):strtolower($v);}; $q=$lower(strip_tags($question)); $tokens=array_values(array_unique(array_filter(preg_split('/[^\pL\pN]+/u',$q)?:[],fn($x)=>strlen($x)>=3))); $candidates=[];
  foreach(['faq','services','wpThemes','products','pricing'] as $group) foreach(($data[$group]['items']??[]) as $item){ if(!is_array($item))continue; $text=implode(' ',array_filter([(string)($item['title']??''),(string)($item['question']??''),(string)($item['answer']??''),(string)($item['text']??''),(string)($item['body']??''),implode(' ',is_array($item['features']??null)?$item['features']:[])])); $low=$lower(strip_tags($text)); $score=0; foreach($tokens as $token) if(str_contains($low,$token))$score++; if($score>0)$candidates[]=[$score,$text]; }
  usort($candidates,fn($a,$b)=>$b[0]<=>$a[0]); if($candidates){$answer=trim(preg_replace('/\s+/u',' ',strip_tags((string)$candidates[0][1]))??'');return substr($answer,0,700);}
  return (string)($data['chatbot']['fallback']??$data['chatbot']['fallbackText']??'Please use the contact form and the team will help you.');
}

function cms_chatbot_reply(array $data, array $payload): array {
  if(empty($data['chatbot']['enabled']) || (($data['chatbot']['mode']??'chatbot')!=='chatbot')) throw new RuntimeException('AI chatbot is not enabled.'); $question=trim(strip_tags((string)($payload['message']??''))); if($question==='') throw new RuntimeException('Write a question first.'); if(strlen($question)>1200) throw new RuntimeException('Question is too long.');
  $tech=$data['chatbot']['technical']??[]; $knowledge=cms_chatbot_context($data,(int)($tech['maxContextChars']??9000)); $history=[]; foreach(array_slice(is_array($payload['history']??null)?$payload['history']:[],-8) as $row){$role=(string)($row['role']??'');$text=trim(strip_tags((string)($row['text']??'')));if(in_array($role,['user','assistant'],true)&&$text!=='')$history[]=ucfirst($role).': '.substr($text,0,500);} $prompt="Visitor question: {$question}".($history?"\n\nRecent conversation:\n".implode("\n",$history):'');
  $ai=$data['settings']['ai']??[]; if(!empty($tech['aiEnabled'])&&!empty($ai['enabled'])&&($ai['accessTier']??'free')==='pro'&&!empty($ai['apiKey'])){ try{$res=cms_ai_generate($data,$prompt,$knowledge,'',(string)($tech['systemPrompt']??''));return ['ok'=>true,'reply'=>$res['text'],'mode'=>'ai'];}catch(Throwable $e){} }
  return ['ok'=>true,'reply'=>cms_chatbot_fallback($data,$question),'mode'=>'local'];
}

function cms_now(): DateTimeImmutable {
  static $timezone = null;
  if (!$timezone) $timezone = new DateTimeZone(CMS_TIMEZONE);
  return new DateTimeImmutable('now', $timezone);
}

function cms_breakglass_slug(?DateTimeInterface $date = null): string {
  $date = $date ?: cms_now();
  return BREAKGLASS_PREFIX . $date->format('dmY');
}

function cms_request_relative_path(): string {
  $path = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
  $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
  if ($scriptDir !== '' && $scriptDir !== '/' && ($path === $scriptDir || str_starts_with($path, $scriptDir . '/'))) {
    $path = substr($path, strlen($scriptDir));
  }
  return trim($path, '/');
}

function cms_is_breakglass_request(): bool {
  if (array_key_exists('admin', $_GET)) return true;
  $path = cms_request_relative_path();
  if ($path !== '' && hash_equals(cms_breakglass_slug(), $path)) return true;
  $storedPath = (string)($_SESSION['cms_breakglass_path'] ?? '');
  $expires = (int)($_SESSION['cms_breakglass_exp'] ?? 0);
  return $storedPath !== '' && $expires >= time() && hash_equals($storedPath, $path);
}

function cms_client_ip(): string {
  return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 100);
}

function cms_rate_limit_consume(string $bucket, int $limit, int $windowSeconds): bool {
  $key = hash('sha256', CMS_SECRET . '|' . $bucket . '|' . cms_client_ip());
  $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'lpcms-rate-' . $key . '.json';
  $now = time();
  $fp = @fopen($file, 'c+');
  if (!$fp) return true;
  if (!@flock($fp, LOCK_EX)) {
    fclose($fp);
    return true;
  }
  rewind($fp);
  $timestamps = json_decode((string)stream_get_contents($fp), true);
  if (!is_array($timestamps)) $timestamps = [];
  $timestamps = array_values(array_filter($timestamps, fn($ts) => is_numeric($ts) && (int)$ts > $now - $windowSeconds));
  $allowed = count($timestamps) < $limit;
  if ($allowed) $timestamps[] = $now;
  rewind($fp);
  ftruncate($fp, 0);
  fwrite($fp, json_encode($timestamps));
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
  return $allowed;
}

function cms_mask_email(string $email): string {
  [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
  if ($domain === '') return 'configured administrator email';
  $first = $local !== '' ? substr($local, 0, 1) : '*';
  return $first . str_repeat('*', max(4, strlen($local) - 1)) . '@' . $domain;
}

function cms_breakglass_csrf(): string {
  if (empty($_SESSION['cms_breakglass_csrf'])) $_SESSION['cms_breakglass_csrf'] = bin2hex(random_bytes(24));
  return (string)$_SESSION['cms_breakglass_csrf'];
}

function cms_breakglass_clear_challenge(): void {
  unset(
    $_SESSION['cms_breakglass_code_hash'],
    $_SESSION['cms_breakglass_exp'],
    $_SESSION['cms_breakglass_attempts'],
    $_SESSION['cms_breakglass_path']
  );
}

function cms_render_breakglass(string $stage = 'password', string $message = '', bool $error = false): void {
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('X-Robots-Tag: noindex, nofollow, noarchive', true);
  $csrf = cms_breakglass_csrf();
  $maskedEmail = cms_mask_email(BREAKGLASS_ADMIN_EMAIL);
  $messageHtml = $message !== '' ? '<div class="notice ' . ($error ? 'error' : 'success') . '">' . cms_e($message) . '</div>' : '';
  $form = $stage === 'code'
    ? '<form method="post" autocomplete="off">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="code">'
      . '<label for="code">5-character email code</label>'
      . '<input id="code" name="code" type="text" inputmode="numeric" pattern="[A-Za-z0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" required autofocus>'
      . '<button type="submit">Verify and open admin</button>'
      . '</form>'
      . '<form method="post" class="secondary">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="reset">'
      . '<button type="submit" class="link-button">Start again</button>'
      . '</form>'
    : '<form method="post" autocomplete="off">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="password">'
      . '<label for="password">Super admin password</label>'
      . '<input id="password" name="password" type="password" autocomplete="current-password" required autofocus>'
      . '<button type="submit">Send verification code</button>'
      . '</form>';
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
    . '<meta name="robots" content="noindex,nofollow"><title>Administrator verification</title><style>'
    . ':root{color-scheme:light}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at top left,#dbeafe,transparent 40%),radial-gradient(circle at bottom right,#ede9fe,transparent 42%),#f8fafc;color:#172033;font:16px/1.55 Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.card{width:min(100%,440px);background:rgba(255,255,255,.94);border:1px solid rgba(15,23,42,.09);border-radius:26px;padding:30px;box-shadow:0 28px 90px rgba(15,23,42,.16)}.mark{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;margin-bottom:20px;background:linear-gradient(135deg,#0d6efd,#6610f2);color:white;font-weight:800;box-shadow:0 14px 30px rgba(13,110,253,.24)}h1{margin:0 0 8px;font-size:1.7rem;letter-spacing:-.035em}p{margin:0 0 20px;color:#64748b}label{display:block;margin:0 0 7px;font-weight:700;color:#334155}input{width:100%;border:1px solid #cbd5e1;border-radius:14px;padding:13px 14px;font:inherit;outline:none;background:#fff}input:focus{border-color:#0d6efd;box-shadow:0 0 0 4px rgba(13,110,253,.1)}button{width:100%;border:0;border-radius:999px;margin-top:14px;padding:13px 18px;font:inherit;font-weight:750;cursor:pointer;background:linear-gradient(135deg,#0d6efd,#6610f2);color:#fff;box-shadow:0 14px 34px rgba(13,110,253,.22)}.notice{border-radius:14px;padding:11px 13px;margin:0 0 18px;font-weight:650}.notice.error{background:#fff1f2;color:#9f1239;border:1px solid #fecdd3}.notice.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}.secondary{margin-top:6px}.link-button{background:transparent;color:#475569;box-shadow:none;margin-top:5px;padding:9px}.small{font-size:.86rem;color:#64748b;margin-top:18px}

</style></head><body><main class="card">'
    . '<div class="mark">LP</div><h1>Administrator verification</h1><p>Super admin access is protected by the LPCMS password and a one-time code sent to ' . cms_e($maskedEmail) . '.</p>'
    . $messageHtml . $form
    . '<div class="small">The 5-character code expires after 10 minutes. This page is not indexed and responses are not cached.</div>'
    . '</main></body></html>';
  exit;
}

function cms_handle_breakglass(array $data): void {
  $hasChallenge = !empty($_SESSION['cms_breakglass_code_hash']) && (int)($_SESSION['cms_breakglass_exp'] ?? 0) >= time();
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') cms_render_breakglass($hasChallenge ? 'code' : 'password');

  $postedCsrf = (string)($_POST['csrf'] ?? '');
  if ($postedCsrf === '' || !hash_equals(cms_breakglass_csrf(), $postedCsrf)) {
    cms_render_breakglass($hasChallenge ? 'code' : 'password', 'The form expired. Reload the page and try again.', true);
  }

  $action = (string)($_POST['action'] ?? '');
  if ($action === 'reset') {
    cms_breakglass_clear_challenge();
    cms_render_breakglass('password');
  }

  if ($action === 'password') {
    if (!cms_rate_limit_consume('breakglass-password', 20, 3600)) {
      cms_render_breakglass('password', 'Too many attempts. Try again later.', true);
    }
    $expected = BREAKGLASS_PASSWORD;
    $password = (string)($_POST['password'] ?? '');
    if (!hash_equals($expected, $password)) {
      usleep(350000);
      cms_render_breakglass('password', 'Invalid credentials.', true);
    }
    $lastSend = (int)($_SESSION['cms_breakglass_last_send'] ?? 0);
    if ($lastSend > time() - 60) {
      cms_render_breakglass('password', 'A code was sent recently. Wait one minute before requesting another.', true);
    }
    if (!cms_rate_limit_consume('breakglass-send', 6, 3600)) {
      cms_render_breakglass('password', 'The hourly code limit has been reached. Try again later.', true);
    }

    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < BREAKGLASS_CODE_LENGTH; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    $_SESSION['cms_breakglass_code_hash'] = hash_hmac('sha256', $code, CMS_SECRET);
    $_SESSION['cms_breakglass_exp'] = time() + BREAKGLASS_OTP_TTL;
    $_SESSION['cms_breakglass_attempts'] = 0;
    $_SESSION['cms_breakglass_path'] = cms_request_relative_path();
    $timeText = cms_now()->format('Y-m-d H:i:s T');
    $body = "Your LPCMS super administrator verification code is: {$code}\n\n"
      . "It expires in 10 minutes and can be used once.\n\n"
      . "Requested: {$timeText}\nIP: " . cms_client_ip() . "\n\n"
      . "If you did not request this code, ignore this email.";
    $sent = cms_send_mail(BREAKGLASS_ADMIN_EMAIL, 'LPCMS administrator verification code', $body);
    if (!$sent) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'The verification email could not be sent. Configure PHP mail or SMTP and try again.', true);
    }
    $_SESSION['cms_breakglass_last_send'] = time();
    cms_render_breakglass('code', 'A 5-character verification code was sent to ' . cms_mask_email(BREAKGLASS_ADMIN_EMAIL) . '.');
  }

  if ($action === 'code') {
    $expires = (int)($_SESSION['cms_breakglass_exp'] ?? 0);
    $expectedHash = (string)($_SESSION['cms_breakglass_code_hash'] ?? '');
    if ($expectedHash === '' || $expires < time()) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'The verification code expired. Start again.', true);
    }
    if (!cms_rate_limit_consume('breakglass-code', 25, 3600)) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'Too many verification attempts. Start again later.', true);
    }
    $attempts = (int)($_SESSION['cms_breakglass_attempts'] ?? 0) + 1;
    $_SESSION['cms_breakglass_attempts'] = $attempts;
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string)($_POST['code'] ?? '')));
    $valid = strlen($code) === BREAKGLASS_CODE_LENGTH && hash_equals($expectedHash, hash_hmac('sha256', $code, CMS_SECRET));
    if (!$valid) {
      if ($attempts >= BREAKGLASS_MAX_CODE_ATTEMPTS) {
        cms_breakglass_clear_challenge();
        cms_render_breakglass('password', 'Too many incorrect codes. Start again.', true);
      }
      $remaining = BREAKGLASS_MAX_CODE_ATTEMPTS - $attempts;
      cms_render_breakglass('code', 'Incorrect code. ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.', true);
    }

    session_regenerate_id(true);
    $_SESSION['cms_admin_email'] = BREAKGLASS_ADMIN_EMAIL;
    $_SESSION['cms_admin_role'] = 'super';
    $_SESSION['cms_csrf'] = bin2hex(random_bytes(20));
    cms_breakglass_clear_challenge();
    unset($_SESSION['cms_breakglass_csrf'], $_SESSION['cms_breakglass_last_send']);
    header('Location: ' . cms_route_prefix(), true, 303);
    exit;
  }

  cms_render_breakglass($hasChallenge ? 'code' : 'password', 'Invalid request.', true);
}

function cms_detect_mime(string $file): string {
  $detected = '';
  if (function_exists('finfo_open')) {
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
      $detected = strtolower((string)@finfo_file($finfo, $file));
      finfo_close($finfo);
      if ($detected !== '' && !in_array($detected, ['application/octet-stream', 'binary/octet-stream'], true)) return $detected;
    }
  }
  $size = @getimagesize($file);
  $imageMime = strtolower((string)($size['mime'] ?? ''));
  if ($imageMime !== '') return $imageMime;

  // Some shared hosts do not identify AVIF/HEIC correctly. Inspect the ISO-BMFF brand as a fallback.
  $head = (string)@file_get_contents($file, false, null, 0, 64);
  if (strlen($head) >= 12 && substr($head, 4, 4) === 'ftyp') {
    $brands = substr($head, 8);
    if (str_contains($brands, 'avif') || str_contains($brands, 'avis')) return 'image/avif';
    foreach (['heic', 'heix', 'hevc', 'hevx', 'heis', 'heim'] as $brand) {
      if (str_contains($brands, $brand)) return 'image/heic';
    }
    if (str_contains($brands, 'mif1') || str_contains($brands, 'msf1')) return 'image/heif';
  }
  return $detected !== '' ? $detected : 'application/octet-stream';
}

function cms_is_avif_file(string $file): bool {
  if (cms_detect_mime($file) === 'image/avif') return true;
  $head = (string)@file_get_contents($file, false, null, 0, 64);
  return str_contains($head, 'ftypavif') || str_contains($head, 'ftypavis');
}

function cms_image_dimensions(string $file): array {
  $size = @getimagesize($file);
  if (!is_array($size)) return ['width' => 0, 'height' => 0];
  return ['width' => (int)($size[0] ?? 0), 'height' => (int)($size[1] ?? 0)];
}

function cms_validate_image_dimensions(array $dimensions): void {
  $width = (int)($dimensions['width'] ?? 0);
  $height = (int)($dimensions['height'] ?? 0);
  if ($width > 20000 || $height > 20000 || ($width > 0 && $height > 0 && $width * $height > 100000000)) {
    throw new RuntimeException('Image dimensions are too large.');
  }
}

function cms_media_resize_options(?array $data = null, array $input = []): array {
  $media = is_array($data) ? ($data['settings']['media'] ?? []) : [];
  $preset = strtolower(trim((string)($input['boxPreset'] ?? $media['boxPreset'] ?? 'large')));
  $presets = [
    'large' => [1920, 1920], 'hero' => [1600, 900], 'content' => [1200, 900],
    'card' => [900, 675], 'square' => [800, 800], 'logo' => [800, 400], 'custom' => [0, 0]
  ];
  [$presetW,$presetH] = $presets[$preset] ?? $presets['large'];
  $legacy = (int)($media['maxImageWidth'] ?? IMAGE_MAX_WIDTH);
  $w = (int)($input['boxWidth'] ?? ($presetW ?: ($media['boxWidth'] ?? $legacy)));
  $h = (int)($input['boxHeight'] ?? ($presetH ?: ($media['boxHeight'] ?? $legacy)));
  $w = max(64, min(4096, $w ?: $legacy ?: IMAGE_MAX_WIDTH));
  $h = max(64, min(4096, $h ?: $w));
  $quality = (int)($input['quality'] ?? $media['avifQuality'] ?? IMAGE_AVIF_QUALITY);
  $quality = max(35, min(95, $quality));
  return ['boxWidth'=>$w,'boxHeight'=>$h,'boxPreset'=>$preset,'quality'=>$quality];
}

function cms_fit_image_dimensions(array $dimensions, int $maxWidth, int $maxHeight): array {
  $width=max(1,(int)($dimensions['width']??1)); $height=max(1,(int)($dimensions['height']??1));
  $ratio=min(1, $maxWidth/$width, $maxHeight/$height);
  return ['width'=>max(1,(int)round($width*$ratio)),'height'=>max(1,(int)round($height*$ratio))];
}

function cms_convert_image_imagick(string $source, string $destination, int $targetWidth, int $targetHeight, int $quality): bool {
  if (!class_exists('Imagick')) return false;
  try {
    if (method_exists('Imagick', 'queryFormats') && count(Imagick::queryFormats('AVIF')) === 0) return false;
    $image = new Imagick(); $image->readImage($source);
    if ($image->getNumberImages() > 1) $image->setIteratorIndex(0);
    if (method_exists($image, 'autoOrientImage')) $image->autoOrientImage();
    if ($image->getImageWidth() !== $targetWidth || $image->getImageHeight() !== $targetHeight) $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1, false);
    $image->setImagePage(0,0,0,0); $image->stripImage(); $image->setImageFormat('avif'); $image->setImageCompressionQuality($quality);
    $ok=$image->writeImage($destination); $image->clear(); $image->destroy();
    return $ok && is_file($destination) && filesize($destination)>0;
  } catch(Throwable $e){ @unlink($destination); return false; }
}

function cms_gd_open_image(string $source, string $mime) {
  return match ($mime) {
    'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : false,
    'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : false,
    'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($source) : false,
    'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
    'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($source) : false,
    default => false,
  };
}

function cms_gd_auto_orient($image, string $source, string $mime) {
  if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) return $image;
  $exif=@exif_read_data($source); $orientation=(int)($exif['Orientation']??1);
  $rotate=static function($current,int $degrees){$rotated=@imagerotate($current,$degrees,0);if(!$rotated)return $current;imagedestroy($current);return $rotated;};
  if($orientation===2&&function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL); if($orientation===3)$image=$rotate($image,180); if($orientation===4&&function_exists('imageflip'))@imageflip($image,IMG_FLIP_VERTICAL);
  if($orientation===5){if(function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL);$image=$rotate($image,90);} if($orientation===6)$image=$rotate($image,-90);
  if($orientation===7){if(function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL);$image=$rotate($image,-90);} if($orientation===8)$image=$rotate($image,90); return $image;
}

function cms_convert_image_gd(string $source, string $destination, string $mime, int $targetWidth, int $targetHeight, int $quality): bool {
  if (!function_exists('imageavif')) return false; $image=null; $output=null;
  try {
    $image=cms_gd_open_image($source,$mime); if(!$image)return false; $image=cms_gd_auto_orient($image,$source,$mime); if(!$image)return false;
    $width=imagesx($image);$height=imagesy($image);if($width<1||$height<1)return false;
    $output=@imagecreatetruecolor($targetWidth,$targetHeight);if(!$output)return false;@imagealphablending($output,false);@imagesavealpha($output,true);
    $transparent=@imagecolorallocatealpha($output,0,0,0,127);if($transparent!==false)@imagefilledrectangle($output,0,0,$targetWidth,$targetHeight,$transparent);
    if(!@imagecopyresampled($output,$image,0,0,0,0,$targetWidth,$targetHeight,$width,$height))return false;
    $ok=@imageavif($output,$destination,$quality); return $ok&&is_file($destination)&&filesize($destination)>0;
  } catch(Throwable $e){@unlink($destination);return false;} finally{if($output)@imagedestroy($output);if($image)@imagedestroy($image);}
}

function cms_run_process(array $command, ?string &$error = null): bool {
  if (!function_exists('proc_open')) return false;
  try { $pipes=[];$process=@proc_open($command,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);if(!is_resource($process))return false;
    if(isset($pipes[0])&&is_resource($pipes[0]))fclose($pipes[0]); if(isset($pipes[1])&&is_resource($pipes[1])){stream_get_contents($pipes[1]);fclose($pipes[1]);}
    if(isset($pipes[2])&&is_resource($pipes[2])){$error=trim((string)stream_get_contents($pipes[2]));fclose($pipes[2]);} return proc_close($process)===0;
  } catch(Throwable $e){$error=$e->getMessage();return false;}
}
function cms_find_binary(string $name, array $commonPaths = []): ?string { foreach($commonPaths as $path)if(is_file($path)&&is_executable($path))return $path;return $name; }
function cms_convert_image_cli(string $source, string $destination, int $targetWidth, int $targetHeight, int $quality): bool {
  $magick=cms_find_binary('magick',['/usr/bin/magick','/usr/local/bin/magick','/opt/imagemagick/bin/magick']);$error='';
  if(cms_run_process([$magick,$source.'[0]','-auto-orient','-resize',$targetWidth.'x'.$targetHeight.'>','-strip','-quality',(string)$quality,$destination],$error)&&is_file($destination)&&filesize($destination)>0&&cms_is_avif_file($destination))return true;
  @unlink($destination); $ffmpeg=cms_find_binary('ffmpeg',['/usr/bin/ffmpeg','/usr/local/bin/ffmpeg','/opt/homebrew/bin/ffmpeg']);
  $ok=cms_run_process([$ffmpeg,'-hide_banner','-loglevel','error','-y','-i',$source,'-map_metadata','-1','-frames:v','1','-vf','scale='.$targetWidth.':'.$targetHeight,'-c:v','libaom-av1','-still-picture','1','-crf','30','-b:v','0','-cpu-used','6',$destination],$error);
  return $ok&&is_file($destination)&&filesize($destination)>0&&cms_is_avif_file($destination);
}

function cms_store_image_as_avif(string $source, string $originalName, string $targetDir, array $options = []): array {
  $mime=cms_detect_mime($source); $allowed=['image/jpeg','image/png','image/gif','image/webp','image/avif','image/heic','image/heif','image/heic-sequence','image/heif-sequence','image/x-heic','image/x-heif'];
  if(!in_array($mime,$allowed,true))throw new RuntimeException('Please upload a JPG, PNG, GIF, WebP, HEIC, or AVIF image.');
  $dimensions=cms_image_dimensions($source);cms_validate_image_dimensions($dimensions);if(!is_dir($targetDir)&&!@mkdir($targetDir,0755,true)&&!is_dir($targetDir))throw new RuntimeException('Upload directory could not be created.');
  $opts=cms_media_resize_options(null,$options);$target=cms_fit_image_dimensions($dimensions,$opts['boxWidth'],$opts['boxHeight']);
  $base=cms_slug(pathinfo($originalName,PATHINFO_FILENAME));$name=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.avif';$destination=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;$temporary=$destination.'.tmp-'.bin2hex(random_bytes(3)).'.avif';$method='';
  $alreadySuitable=$mime==='image/avif'&&(int)($dimensions['width']??0)===$target['width']&&(int)($dimensions['height']??0)===$target['height'];
  if($alreadySuitable&&@copy($source,$temporary))$method='existing-avif';
  elseif(cms_convert_image_imagick($source,$temporary,$target['width'],$target['height'],$opts['quality']))$method='imagick';
  elseif(cms_convert_image_gd($source,$temporary,$mime,$target['width'],$target['height'],$opts['quality']))$method='gd';
  elseif(cms_convert_image_cli($source,$temporary,$target['width'],$target['height'],$opts['quality']))$method='cli';
  else{@unlink($temporary);throw new RuntimeException('AVIF conversion is required but unavailable on this server. Enable GD/Imagick AVIF support or ImageMagick/ffmpeg.');}
  if(!cms_is_avif_file($temporary)){@unlink($temporary);throw new RuntimeException('The uploaded image could not be converted to AVIF.');}
  if(!@rename($temporary,$destination)){@unlink($temporary);throw new RuntimeException('The converted AVIF image could not be saved.');}@chmod($destination,0644);
  $outputDimensions=cms_image_dimensions($destination);if(($outputDimensions['width']??0)===0)$outputDimensions=$target;
  return ['name'=>$name,'path'=>$destination,'width'=>(int)$outputDimensions['width'],'height'=>(int)$outputDimensions['height'],'bytes'=>(int)filesize($destination),'method'=>$method,'format'=>'avif','boxPreset'=>$opts['boxPreset']];
}

function cms_store_hero_video(string $source, string $originalName, string $targetDir): array {
  if (!is_dir($targetDir) && !@mkdir($targetDir,0755,true) && !is_dir($targetDir)) throw new RuntimeException('Video upload directory could not be created.');
  $mime=cms_detect_mime($source); $ext=strtolower(pathinfo($originalName,PATHINFO_EXTENSION));
  $allowedMime=['video/mp4','video/webm','application/octet-stream'];
  if(!in_array($mime,$allowedMime,true) && !in_array($ext,['mp4','webm'],true)) throw new RuntimeException('Please upload an MP4 or WebM video.');
  $base=cms_slug(pathinfo($originalName,PATHINFO_FILENAME)) ?: 'hero-video';
  $name=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.mp4';
  $destination=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
  $temporary=$destination.'.tmp-'.bin2hex(random_bytes(3)).'.mp4';
  $ffmpeg=cms_find_binary('ffmpeg',['/usr/bin/ffmpeg','/usr/local/bin/ffmpeg','/opt/homebrew/bin/ffmpeg']); $error='';
  $ok=cms_run_process([$ffmpeg,'-hide_banner','-loglevel','error','-y','-i',$source,'-map_metadata','-1','-an','-vf','scale=1280:-2:force_original_aspect_ratio=decrease','-c:v','libx264','-preset','medium','-crf','26','-pix_fmt','yuv420p','-movflags','+faststart',$temporary],$error);
  if($ok && is_file($temporary) && filesize($temporary)>0){
    if(!@rename($temporary,$destination)){@unlink($temporary);throw new RuntimeException('The optimized video could not be saved.');}
    @chmod($destination,0644);
    return ['name'=>$name,'path'=>$destination,'bytes'=>(int)filesize($destination),'format'=>'mp4','optimized'=>true];
  }
  @unlink($temporary);
  $fallbackExt=$ext==='webm'?'webm':'mp4';
  $fallbackName=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.'.$fallbackExt;
  $fallback=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fallbackName;
  if(!@move_uploaded_file($source,$fallback)) throw new RuntimeException('Video upload failed. Install ffmpeg for automatic HD optimization.');
  @chmod($fallback,0644);
  return ['name'=>$fallbackName,'path'=>$fallback,'bytes'=>(int)filesize($fallback),'format'=>$fallbackExt,'optimized'=>false];
}

function cms_admin_role(): string {
  if (empty($_SESSION['cms_admin_email'])) return '';
  $sessionRole = strtolower((string)($_SESSION['cms_admin_role'] ?? ''));
  if (in_array($sessionRole, ['super', 'editor'], true)) return $sessionRole;
  $user = cms_user_from_email(cms_load_data(), (string)$_SESSION['cms_admin_email']);
  $role = $user['role'] ?? '';
  if (in_array($role, ['super', 'editor'], true)) {
    $_SESSION['cms_admin_role'] = $role;
    return $role;
  }
  return '';
}

function cms_is_admin(): bool {
  return cms_admin_role() !== '';
}

function cms_is_super_admin(): bool {
  return cms_admin_role() === 'super';
}

function cms_require_admin(): void {
  if (!cms_is_admin()) cms_json(['ok' => false, 'error' => 'Unauthorized'], 401);
  $csrf = $_SERVER['HTTP_X_CMS_CSRF'] ?? '';
  if ($_SERVER['REQUEST_METHOD'] !== 'GET' && (!isset($_SESSION['cms_csrf']) || !hash_equals($_SESSION['cms_csrf'], $csrf))) {
    cms_json(['ok' => false, 'error' => 'Bad CSRF token'], 419);
  }
}

function cms_require_super_admin(): void {
  cms_require_admin();
  if (!cms_is_super_admin()) cms_json(['ok' => false, 'error' => 'Super administrator access required.'], 403);
}

function cms_filter_editor_payload(array $payload, array $current): array {
  $locks = [
    ['settings','users'], ['settings','htaccess'], ['settings','libraries'], ['settings','analytics'], ['settings','hcaptcha'], ['settings','forms'],
    ['settings','cache'], ['settings','media'], ['settings','mediaSources'], ['settings','ai'], ['settings','app'], ['settings','sitePlan'], ['settings','abandonedCart'], ['settings','demoIntakeAdmin'], ['demoIntake'],
    ['settings','themeManager'], ['settings','headless'], ['settings','mailer'], ['settings','commerceIntegrations'], ['booking','technical'], ['chatbot','technical'], ['redirects'], ['embeds']
  ];
  foreach ($locks as $path) {
    $src = $current;
    foreach ($path as $part) {
      if (!is_array($src) || !array_key_exists($part, $src)) { $src = null; break; }
      $src = $src[$part];
    }
    $ref =& $payload;
    foreach ($path as $i => $part) {
      if ($i === count($path)-1) { $ref[$part] = $src; break; }
      if (!isset($ref[$part]) || !is_array($ref[$part])) $ref[$part] = [];
      $ref =& $ref[$part];
    }
    unset($ref);
  }
  if (!empty($current['settings']['forms']['submissions'])) {
    $payload['settings']['forms']['submissions'] = $current['settings']['forms']['submissions'];
  }
  return $payload;
}

function cms_strip_private_settings(array $data): array {
  if (isset($data['settings']['mailer']['smtp'])) {
    $has = !empty($data['settings']['mailer']['smtp']['password']);
    $data['settings']['mailer']['smtp']['password'] = '';
    $data['settings']['mailer']['smtp']['passwordSet'] = $has;
  }
  if (isset($data['settings']['commerceIntegrations']['webhook'])) {
    $has = !empty($data['settings']['commerceIntegrations']['webhook']['bearerToken']);
    $data['settings']['commerceIntegrations']['webhook']['bearerToken'] = '';
    $data['settings']['commerceIntegrations']['webhook']['bearerTokenSet'] = $has;
  }
  if (isset($data['settings']['commerceIntegrations']['checkout'])) {
    $has = !empty($data['settings']['commerceIntegrations']['checkout']['bearerToken']);
    $data['settings']['commerceIntegrations']['checkout']['bearerToken'] = '';
    $data['settings']['commerceIntegrations']['checkout']['bearerTokenSet'] = $has;
  }
  if (isset($data['settings']['ai'])) {
    $has = !empty($data['settings']['ai']['apiKey']);
    $data['settings']['ai']['apiKey'] = '';
    $data['settings']['ai']['apiKeySet'] = $has;
  }
  if (isset($data['settings']['abandonedCart'])) {
    $has = !empty($data['settings']['abandonedCart']['cronKey']);
    $data['settings']['abandonedCart']['cronKey'] = '';
    $data['settings']['abandonedCart']['cronKeySet'] = $has;
  }
  foreach (['pexels','pixabay','unsplash'] as $provider) {
    if (isset($data['settings']['mediaSources'][$provider])) {
      $has = !empty($data['settings']['mediaSources'][$provider]['apiKey']);
      $data['settings']['mediaSources'][$provider]['apiKey'] = '';
      $data['settings']['mediaSources'][$provider]['apiKeySet'] = $has;
    }
  }
  return $data;
}

function cms_prune_public_i18n($value, string $lang, string $default) {
  if (!is_array($value)) return $value;
  if (isset($value['i18n']) && is_array($value['i18n'])) {
    if ($lang === $default || !isset($value['i18n'][$lang])) unset($value['i18n']);
    else $value['i18n'] = [$lang => $value['i18n'][$lang]];
  }
  foreach ($value as $key => $child) {
    if ($key === 'i18n') continue;
    if (is_array($child)) $value[$key] = cms_prune_public_i18n($child, $lang, $default);
  }
  return $value;
}

function cms_public_data(array $data): array {
  $data = cms_strip_private_settings($data);
  $admin = cms_is_admin();
  $super = cms_is_super_admin();
  if ($super) return $data;

  unset($data['settings']['hcaptcha']['secret']);
  unset($data['settings']['forms']);
  unset($data['settings']['users']);
  unset($data['settings']['mailer']);
  unset($data['settings']['demoIntakeAdmin']);
  unset($data['settings']['commerceIntegrations']);
  unset($data['settings']['mediaSources']);

  $ai = $data['settings']['ai'] ?? [];
  $data['settings']['ai'] = $admin ? [
    'enabled' => !empty($ai['enabled']),
    'accessTier' => (string)($ai['accessTier'] ?? 'free'),
    'providerConnected' => !empty($ai['apiKeySet']),
  ] : [];

  $abandoned = $data['settings']['abandonedCart'] ?? [];
  $data['settings']['abandonedCart'] = ['enabled' => !empty($abandoned['enabled'])];
  if (!$admin) unset($data['settings']['sitePlan']);

  if (isset($data['booking']) && is_array($data['booking'])) {
    unset($data['booking']['technical']);
    if (!$admin) unset($data['booking']['bookings']);
  }
  if (isset($data['chatbot']) && is_array($data['chatbot'])) unset($data['chatbot']['technical']);
  if (!$admin) {
    $lang = cms_current_language($data);
    $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
    $data = cms_prune_public_i18n($data, $lang, $default);
  }
  return $data;
}

function cms_client_i18n_pack(array $data): array {
  $pack = cms_i18n_pack();
  $lang = cms_current_language($data);
  $out = [
    'languages' => $pack['languages'] ?? [],
    'strings' => isset($pack['strings'][$lang]) ? [$lang => $pack['strings'][$lang]] : [],
  ];
  if (cms_is_admin()) {
    $out['adminStrings'] = isset($pack['adminStrings'][$lang]) ? [$lang => $pack['adminStrings'][$lang]] : [];
    $out['adminTerms'] = isset($pack['adminTerms'][$lang]) ? [$lang => $pack['adminTerms'][$lang]] : [];
  }
  return $out;
}

function cms_client_bootstrap_data(array $data): array {
  $public = cms_public_data($data);
  $settings = $public['settings'] ?? [];
  $theme = $settings['theme'] ?? [];
  return [
    'version' => CMS_VERSION,
    'settings' => [
      'siteName' => (string)($settings['siteName'] ?? ''),
      'defaultLang' => (string)($settings['defaultLang'] ?? 'en'),
      'languages' => $settings['languages'] ?? cms_language_options(),
      'localization' => $settings['localization'] ?? [],
      'theme' => [
        'brand' => $theme['brand'] ?? '#0d6efd',
        'brand2' => $theme['brand2'] ?? '#6610f2',
        'accent' => $theme['accent'] ?? '#20c997',
        'text' => $theme['text'] ?? '#1f2937',
        'muted' => $theme['muted'] ?? '#6b7280',
        'bg' => $theme['bg'] ?? '#ffffff',
        'softBg' => $theme['softBg'] ?? '#f6f8fb',
        'radius' => $theme['radius'] ?? '22px',
        'grid' => $theme['grid'] ?? 'container',
        'font' => $theme['font'] ?? 'system-ui',
        'customCss' => '',
      ],
      'analytics' => [],
      'cookies' => ['enabled' => false],
      'app' => ['enabled' => false],
      'hcaptcha' => ['enabled' => false],
      'media' => [],
    ],
    'header' => [
      'logoText' => (string)($public['header']['logoText'] ?? $settings['siteName'] ?? ''),
      'logoImage' => (string)($public['header']['logoImage'] ?? ''),
      'menu' => [],
    ],
    'footer' => ['menu' => [], 'social' => []],
    'products' => ['basketEnabled' => false, 'shopEnabled' => false, 'cartEnabled' => false],
    'chatbot' => ['enabled' => false],
  ];
}

function cms_headless_public_data(array $data): array {
  $data = cms_strip_private_settings($data);
  unset($data['settings']['hcaptcha']['secret']);
  unset($data['settings']['forms']);
  unset($data['settings']['users']);
  unset($data['settings']['mailer']);
  unset($data['settings']['demoIntakeAdmin']);
  unset($data['settings']['commerceIntegrations']);
  unset($data['settings']['ai']);
  unset($data['settings']['mediaSources']);
  unset($data['settings']['sitePlan']);
  $data['settings']['abandonedCart'] = ['enabled' => !empty($data['settings']['abandonedCart']['enabled'])];
  if (isset($data['booking']) && is_array($data['booking'])) { unset($data['booking']['bookings'], $data['booking']['technical']); }
  if (isset($data['chatbot']) && is_array($data['chatbot'])) unset($data['chatbot']['technical']);
  return $data;
}

function cms_theme_presets(): array {
  return [
    ['id'=>'lpcms-default','title'=>'LPCMS Default','description'=>'Original LPCMS blue/violet design.','palette'=>['brand'=>'#0d6efd','brand2'=>'#6610f2','accent'=>'#20c997','text'=>'#1f2937','muted'=>'#6b7280','bg'=>'#ffffff','softBg'=>'#f6f8fb','radius'=>'22px']],
    ['id'=>'lpcms-cobalt','title'=>'Cobalt Studio','description'=>'Deep blue corporate theme with compact corners.','palette'=>['brand'=>'#0f4c81','brand2'=>'#2563eb','accent'=>'#14b8a6','text'=>'#172033','muted'=>'#64748b','bg'=>'#ffffff','softBg'=>'#f1f5f9','radius'=>'16px']],
    ['id'=>'lpcms-forest','title'=>'Forest Business','description'=>'Green and charcoal business theme.','palette'=>['brand'=>'#166534','brand2'=>'#15803d','accent'=>'#d97706','text'=>'#1f2937','muted'=>'#6b7280','bg'=>'#ffffff','softBg'=>'#f0fdf4','radius'=>'18px']],
    ['id'=>'lpcms-slate','title'=>'Slate Minimal','description'=>'Neutral monochrome theme for professional sites.','palette'=>['brand'=>'#334155','brand2'=>'#0f172a','accent'=>'#64748b','text'=>'#0f172a','muted'=>'#64748b','bg'=>'#ffffff','softBg'=>'#f8fafc','radius'=>'12px']],
    ['id'=>'lpcms-sunset','title'=>'Sunset Creative','description'=>'Warm orange/pink palette for marketing sites.','palette'=>['brand'=>'#ea580c','brand2'=>'#db2777','accent'=>'#f59e0b','text'=>'#292524','muted'=>'#78716c','bg'=>'#fffdfa','softBg'=>'#fff7ed','radius'=>'24px']],
  ];
}

function cms_free_theme_catalog(): array {
  $items = [
    ['agency','Agency','startbootstrap-agency','One-page agency and services design.'],
    ['creative','Creative','startbootstrap-creative','Bold one-page marketing and portfolio design.'],
    ['landing-page','Landing Page','startbootstrap-landing-page','Lead-generation and product landing page.'],
    ['business-frontpage','Business Frontpage','startbootstrap-business-frontpage','General company and SME front page.'],
    ['small-business','Small Business','startbootstrap-small-business','Simple small-business starter layout.'],
    ['shop-homepage','Shop Homepage','startbootstrap-shop-homepage','Bootstrap shop/catalogue card layout.'],
    ['modern-business','Modern Business','startbootstrap-modern-business','Multipage corporate/business design.'],
    ['grayscale','Grayscale','startbootstrap-grayscale','Dark/monochrome one-page design.'],
    ['new-age','New Age','startbootstrap-new-age','App/PWA landing page design.'],
    ['blog-home','Blog Home','startbootstrap-blog-home','Blog archive/homepage layout.'],
  ];
  return array_map(function($x) {
    [$id,$title,$repo,$description] = $x;
    $downloads = [
      'https://codeload.github.com/StartBootstrap/' . $repo . '/zip/refs/heads/master',
      'https://codeload.github.com/StartBootstrap/' . $repo . '/zip/refs/heads/main',
      'https://github.com/StartBootstrap/' . $repo . '/archive/refs/heads/master.zip',
      'https://github.com/StartBootstrap/' . $repo . '/archive/refs/heads/main.zip',
    ];
    return [
      'id' => 'free-' . $id,
      'title' => $title,
      'description' => $description,
      'provider' => 'Start Bootstrap',
      'license' => 'MIT',
      'source' => 'https://github.com/StartBootstrap/' . $repo,
      'download' => $downloads[0],
      'downloads' => $downloads,
      'manualDownload' => $downloads[0],
    ];
  }, $items);
}

function cms_headless_settings(array $data): array {
  return array_replace([
    'enabled' => true,
    'restEnabled' => true,
    'graphqlEnabled' => true,
    'corsOrigins' => [],
    'cacheSeconds' => 60,
  ], $data['settings']['headless'] ?? []);
}

function cms_apply_headless_cors(array $data): void {
  $settings = cms_headless_settings($data);
  $origins = is_array($settings['corsOrigins'] ?? null) ? $settings['corsOrigins'] : [];
  $origin = trim((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
  if ($origin !== '' && in_array('*', $origins, true)) {
    header('Access-Control-Allow-Origin: *');
  } elseif ($origin !== '' && in_array($origin, $origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
  }
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, X-CMS-CSRF');
  header('X-LPCMS-Headless: 1');
  $seconds = max(0, min(3600, (int)($settings['cacheSeconds'] ?? 60)));
  if (!cms_is_admin()) header('Cache-Control: public, max-age=' . $seconds);
}

function cms_data_path($value, string $path) {
  if ($path === '' || $path === '.') return $value;
  foreach (explode('.', $path) as $part) {
    if (!is_array($value) || !array_key_exists($part, $value)) return null;
    $value = $value[$part];
  }
  return $value;
}

function cms_headless_resource(array $data, string $resource, string $slug = '') {
  $public = cms_headless_public_data($data);
  if ($resource === '' || $resource === 'site') return $public;
  if ($resource === 'settings') return $public['settings'] ?? [];
  if ($resource === 'navigation' || $resource === 'header') return $public['header'] ?? [];
  if ($resource === 'sections') {
    $out = $public;
    unset($out['settings'], $out['header'], $out['footer'], $out['redirects'], $out['embeds']);
    return $out;
  }
  $groups = ['products','blog','pages','services','gallery','testimonials'];
  if (in_array($resource, $groups, true)) {
    $group = $public[$resource] ?? [];
    if ($slug === '') return $group;
    foreach (($group['items'] ?? []) as $item) {
      if (cms_slug((string)($item['slug'] ?? $item['title'] ?? '')) === cms_slug($slug)) return $item;
    }
    return null;
  }
  return cms_data_path($public, $resource);
}

function cms_graphql_schema(): string {
  return <<<'GQL'
type Query {
  site: JSON
  settings: JSON
  header: JSON
  footer: JSON
  sections: JSON
  products: JSON
  product(slug: String!): JSON
  blog: JSON
  post(slug: String!): JSON
  pages: JSON
  page(slug: String!): JSON
  search(query: String!): JSON
  path(value: String!): JSON
}
scalar JSON
GQL;
}

function cms_graphql_tokens(string $query): array {
  $query = preg_replace('/#[^\r\n]*/', '', $query) ?? $query;
  $pattern = <<<'REGEX'
~\.\.\.|\$[A-Za-z_][A-Za-z0-9_]*|[A-Za-z_][A-Za-z0-9_]*|-?\d+(?:\.\d+)?|"(?:\\.|[^"\\])*"|[!$():=@\[\]{|},]~
REGEX;
  preg_match_all($pattern, $query, $m);
  return $m[0] ?? [];
}

function cms_graphql_parse_value(array $tokens, int &$i, array $variables) {
  $tok = $tokens[$i] ?? null;
  if ($tok === null) return null;
  if (str_starts_with($tok, '$')) { $i++; return $variables[substr($tok, 1)] ?? null; }
  if (($tok[0] ?? '') === '"') { $i++; $v = json_decode($tok, true); return is_string($v) ? $v : trim($tok, '"'); }
  if ($tok === 'true' || $tok === 'false') { $i++; return $tok === 'true'; }
  if ($tok === 'null') { $i++; return null; }
  if (is_numeric($tok)) { $i++; return str_contains($tok, '.') ? (float)$tok : (int)$tok; }
  if ($tok === '[') {
    $i++; $out = [];
    while (($tokens[$i] ?? null) !== ']' && $i < count($tokens)) {
      $out[] = cms_graphql_parse_value($tokens, $i, $variables);
      if (($tokens[$i] ?? null) === ',') $i++;
    }
    if (($tokens[$i] ?? null) === ']') $i++;
    return $out;
  }
  $i++; return $tok;
}

function cms_graphql_skip_definition(array $tokens, int &$i): void {
  if (($tokens[$i] ?? '') === 'query') {
    $i++;
    if (($tokens[$i] ?? '') !== '{' && ($tokens[$i] ?? '') !== '(') $i++;
    if (($tokens[$i] ?? '') === '(') {
      $depth = 0;
      do { $t = $tokens[$i++] ?? ''; if ($t === '(') $depth++; elseif ($t === ')') $depth--; } while ($i < count($tokens) && $depth > 0);
    }
  }
  while (($tokens[$i] ?? null) !== '{' && $i < count($tokens)) $i++;
}

function cms_graphql_parse_selection(array $tokens, int &$i, array $variables): array {
  if (($tokens[$i] ?? null) !== '{') return [];
  $i++; $fields = [];
  while ($i < count($tokens) && ($tokens[$i] ?? null) !== '}') {
    if (($tokens[$i] ?? null) === ',') { $i++; continue; }
    $first = $tokens[$i++] ?? '';
    if ($first === '' || $first === '...') { while ($i < count($tokens) && !in_array($tokens[$i], ['{','}'], true)) $i++; continue; }
    $alias = null; $name = $first;
    if (($tokens[$i] ?? null) === ':') { $i++; $alias = $first; $name = $tokens[$i++] ?? $first; }
    $args = [];
    if (($tokens[$i] ?? null) === '(') {
      $i++;
      while ($i < count($tokens) && ($tokens[$i] ?? null) !== ')') {
        $argName = $tokens[$i++] ?? '';
        if (($tokens[$i] ?? null) === ':') $i++;
        $args[$argName] = cms_graphql_parse_value($tokens, $i, $variables);
        if (($tokens[$i] ?? null) === ',') $i++;
      }
      if (($tokens[$i] ?? null) === ')') $i++;
    }
    $children = (($tokens[$i] ?? null) === '{') ? cms_graphql_parse_selection($tokens, $i, $variables) : [];
    $fields[] = ['name'=>$name,'alias'=>$alias ?: $name,'args'=>$args,'children'=>$children];
  }
  if (($tokens[$i] ?? null) === '}') $i++;
  return $fields;
}

function cms_graphql_project($value, array $children) {
  if (!$children) return $value;
  if (!is_array($value)) return null;
  if ($value === []) return [];
  $isList = array_keys($value) === range(0, count($value)-1);
  if ($isList) return array_map(fn($row) => cms_graphql_project($row, $children), $value);
  $out = [];
  foreach ($children as $field) {
    $name = $field['name'];
    $alias = $field['alias'];
    $childValue = is_array($value) && array_key_exists($name, $value) ? $value[$name] : null;
    $out[$alias] = cms_graphql_project($childValue, $field['children']);
  }
  return $out;
}

function cms_graphql_search(array $data, string $query): array {
  $q = strtolower(trim($query));
  if ($q === '') return [];
  $out = [];
  foreach (['services','products','blog','pages','gallery'] as $group) {
    foreach (($data[$group]['items'] ?? []) as $i => $item) {
      $text = strtolower((string)json_encode($item, JSON_UNESCAPED_UNICODE));
      if (str_contains($text, $q)) $out[] = ['group'=>$group,'index'=>$i,'item'=>$item];
    }
  }
  return $out;
}

function cms_graphql_root_value(array $data, string $name, array $args) {
  $public = cms_headless_public_data($data);
  if ($name === 'site') return $public;
  if ($name === 'settings') return $public['settings'] ?? [];
  if ($name === 'header') return $public['header'] ?? [];
  if ($name === 'footer') return $public['footer'] ?? [];
  if ($name === 'sections') return cms_headless_resource($data, 'sections');
  if ($name === 'products') return $public['products'] ?? [];
  if ($name === 'blog') return $public['blog'] ?? [];
  if ($name === 'pages') return $public['pages'] ?? [];
  if ($name === 'product') return cms_headless_resource($data, 'products', (string)($args['slug'] ?? ''));
  if ($name === 'post') return cms_headless_resource($data, 'blog', (string)($args['slug'] ?? ''));
  if ($name === 'page') return cms_headless_resource($data, 'pages', (string)($args['slug'] ?? ''));
  if ($name === 'search') return cms_graphql_search($public, (string)($args['query'] ?? ''));
  if ($name === 'path') return cms_data_path($public, (string)($args['value'] ?? ''));
  return null;
}

function cms_graphql_execute(array $data, string $query, array $variables = []): array {
  $tokens = cms_graphql_tokens($query);
  if (!$tokens) return ['data'=>null,'errors'=>[['message'=>'Empty GraphQL query.']]];
  $i = 0;
  cms_graphql_skip_definition($tokens, $i);
  $selection = cms_graphql_parse_selection($tokens, $i, $variables);
  if (!$selection) return ['data'=>null,'errors'=>[['message'=>'GraphQL selection set is required.']]];
  $out = [];
  foreach ($selection as $field) {
    $value = cms_graphql_root_value($data, $field['name'], $field['args']);
    $out[$field['alias']] = cms_graphql_project($value, $field['children']);
  }
  return ['data'=>$out];
}

function cms_function_enabled(string $name): bool {
  if (!function_exists($name)) return false;
  $disabled = array_filter(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
  return !in_array($name, $disabled, true);
}

function cms_theme_command_path(string $name): string {
  foreach (['/usr/local/bin/' . $name, '/usr/bin/' . $name, '/bin/' . $name] as $path) if (is_file($path) && is_executable($path)) return $path;
  if (!cms_function_enabled('shell_exec')) return '';
  $out = trim((string)@shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
  return ($out !== '' && is_file($out) && is_executable($out)) ? $out : '';
}

function cms_theme_download_support(): array {
  $methods = [];
  if (function_exists('curl_init')) $methods[] = 'PHP cURL';
  if ((bool)filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) $methods[] = 'PHP HTTPS streams';
  if (cms_function_enabled('proc_open') && cms_theme_command_path('curl') !== '') $methods[] = 'system curl';
  if (cms_function_enabled('proc_open') && cms_theme_command_path('wget') !== '') $methods[] = 'system wget';
  return ['supported'=>count($methods)>0,'methods'=>$methods,'preferred'=>$methods[0] ?? null];
}

function cms_theme_zip_signature_ok(string $file): bool {
  if (!is_file($file) || filesize($file) < 4) return false;
  $fh = @fopen($file, 'rb');
  if (!$fh) return false;
  $head = (string)fread($fh, 4); fclose($fh);
  return str_starts_with($head, "PK\x03\x04") || str_starts_with($head, "PK\x05\x06") || str_starts_with($head, "PK\x07\x08");
}

function cms_theme_download_candidates(string $url): array {
  foreach (cms_free_theme_catalog() as $item) {
    $downloads = array_values(array_unique(array_filter((array)($item['downloads'] ?? [$item['download'] ?? '']))));
    if (in_array($url, $downloads, true) || $url === ($item['download'] ?? '')) return $downloads;
  }
  throw new RuntimeException('Remote theme URL is not in the LPCMS allowlist.');
}

function cms_theme_proc_download(array $command, string $tmp, string $label, array &$errors): bool {
  if (!cms_function_enabled('proc_open')) { $errors[] = $label . ': proc_open disabled'; return false; }
  @file_put_contents($tmp, '');
  $pipes = [];
  $proc = @proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
  if (!is_resource($proc)) { $errors[] = $label . ': could not start command'; return false; }
  if (isset($pipes[0])) fclose($pipes[0]);
  $stdout = isset($pipes[1]) ? stream_get_contents($pipes[1]) : ''; if (isset($pipes[1])) fclose($pipes[1]);
  $stderr = isset($pipes[2]) ? stream_get_contents($pipes[2]) : ''; if (isset($pipes[2])) fclose($pipes[2]);
  $code = proc_close($proc);
  if ($code === 0 && cms_theme_zip_signature_ok($tmp)) return true;
  $detail = trim((string)$stderr) ?: trim((string)$stdout);
  $errors[] = $label . ': exit ' . $code . ($detail !== '' ? ' - ' . substr(preg_replace('/\\s+/', ' ', $detail), 0, 240) : '');
  return false;
}

function cms_theme_download_remote(string $url): string {
  $candidates = cms_theme_download_candidates($url);
  $tmp = tempnam(sys_get_temp_dir(), 'lpcms-theme-');
  if ($tmp === false) throw new RuntimeException('Could not create a temporary theme file.');
  $errors = [];

  foreach ($candidates as $candidate) {
    if (function_exists('curl_init')) {
      @file_put_contents($tmp, '');
      $fh = @fopen($tmp, 'wb');
      if ($fh) {
        $ch = curl_init($candidate);
        $opts = [
          CURLOPT_FILE=>$fh,
          CURLOPT_FOLLOWLOCATION=>true,
          CURLOPT_MAXREDIRS=>5,
          CURLOPT_CONNECTTIMEOUT=>12,
          CURLOPT_TIMEOUT=>60,
          CURLOPT_USERAGENT=>'LPCMS/' . CMS_VERSION,
          CURLOPT_FAILONERROR=>false,
          CURLOPT_SSL_VERIFYPEER=>true,
          CURLOPT_SSL_VERIFYHOST=>2,
        ];
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) $opts[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        curl_setopt_array($ch, $opts);
        $executed = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch); fclose($fh);
        if ($executed && $code >= 200 && $code < 400 && cms_theme_zip_signature_ok($tmp)) {
          if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
          return $tmp;
        }
        $errors[] = 'PHP cURL ' . parse_url($candidate, PHP_URL_HOST) . ': HTTP ' . ($code ?: 'n/a') . ($err ? ' - ' . $err : '') . (is_file($tmp) && filesize($tmp) > 0 && !cms_theme_zip_signature_ok($tmp) ? ' - response was not a ZIP' : '');
      } else $errors[] = 'PHP cURL: temporary file is not writable';
    }

    if ((bool)filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
      @file_put_contents($tmp, '');
      $ssl = ['verify_peer'=>true,'verify_peer_name'=>true];
      if (is_file('/etc/ssl/certs/ca-certificates.crt')) $ssl['cafile'] = '/etc/ssl/certs/ca-certificates.crt';
      $ctx = stream_context_create([
        'http'=>['timeout'=>60,'follow_location'=>1,'max_redirects'=>5,'user_agent'=>'LPCMS/' . CMS_VERSION,'ignore_errors'=>true],
        'ssl'=>$ssl,
      ]);
      $in = @fopen($candidate, 'rb', false, $ctx);
      if ($in) {
        $out = @fopen($tmp, 'wb');
        $copied = $out ? stream_copy_to_stream($in, $out, THEME_MAX_UPLOAD_BYTES + 1) : false;
        fclose($in); if ($out) fclose($out);
        if ($copied !== false && $copied > 0 && $copied <= THEME_MAX_UPLOAD_BYTES && cms_theme_zip_signature_ok($tmp)) return $tmp;
        $errors[] = 'PHP HTTPS streams ' . parse_url($candidate, PHP_URL_HOST) . ': ' . ($copied === false ? 'copy failed' : ($copied > THEME_MAX_UPLOAD_BYTES ? 'archive exceeded 40MB' : 'response was not a ZIP'));
      } else {
        $last = error_get_last();
        $errors[] = 'PHP HTTPS streams ' . parse_url($candidate, PHP_URL_HOST) . ': ' . substr((string)($last['message'] ?? 'connection failed'), 0, 240);
      }
    }

    $curlBin = cms_theme_command_path('curl');
    if ($curlBin !== '') {
      if (cms_theme_proc_download([$curlBin,'--fail','--location','--silent','--show-error','--retry','2','--connect-timeout','12','--max-time','60','--user-agent','LPCMS/' . CMS_VERSION,'--output',$tmp,$candidate], $tmp, 'system curl', $errors)) {
        if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
        return $tmp;
      }
    }

    $wgetBin = cms_theme_command_path('wget');
    if ($wgetBin !== '') {
      if (cms_theme_proc_download([$wgetBin,'--quiet','--tries=2','--timeout=60','--max-redirect=5','--user-agent=LPCMS/' . CMS_VERSION,'-O',$tmp,$candidate], $tmp, 'system wget', $errors)) {
        if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
        return $tmp;
      }
    }
  }

  @unlink($tmp);
  $detail = implode(' | ', array_slice(array_unique($errors), 0, 8));
  throw new RuntimeException('Could not download the free theme over HTTPS. ' . ($detail !== '' ? $detail . '. ' : '') . 'If outbound HTTPS/DNS is blocked, use “Download ZIP manually” in the theme catalogue and then import it with “Import Bootstrap / ThemeForest ZIP”.');
}

function cms_theme_safe_rel(string $name): string {
  $name = str_replace('\\', '/', $name);
  $name = preg_replace('#/+#', '/', $name) ?? $name;
  $name = ltrim($name, '/');
  if ($name === '' || str_contains($name, "\0")) return '';
  foreach (explode('/', $name) as $part) if ($part === '..') return '';
  return $name;
}

function cms_theme_allowed_file(string $name): bool {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allowed = ['html','htm','css','scss','js','mjs','json','txt','md','svg','png','jpg','jpeg','gif','webp','avif','ico','woff','woff2','ttf','eot','map','xml','webmanifest'];
  if (in_array($ext, $allowed, true)) return true;
  $base = strtoupper(basename($name));
  return in_array($base, ['LICENSE','LICENSE.MD','LICENSE.TXT','README','README.MD'], true);
}

function cms_theme_remove_dir(string $dir): void {
  if (!is_dir($dir)) return;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($it as $item) { if ($item->isDir()) @rmdir($item->getPathname()); else @unlink($item->getPathname()); }
  @rmdir($dir);
}

function cms_theme_archive_support(): array {
  $engines = [];
  if (class_exists('ZipArchive')) $engines[] = 'ZipArchive';
  // PharData can read ZIP archives without php-zip on normal PHP builds. LPCMS
  // copies extensionless upload temp files to a temporary .zip path before opening.
  if (class_exists('PharData') && extension_loaded('zlib')) $engines[] = 'PharData';
  return ['supported'=>!empty($engines),'preferred'=>$engines[0] ?? null,'engines'=>$engines];
}

function cms_theme_store_stream(string $name, int $declaredSize, $in, string $target, array &$state): ?string {
  $name = cms_theme_safe_rel($name);
  if ($name === '' || str_ends_with($name, '/') || !cms_theme_allowed_file($name)) return null;
  if ($declaredSize > THEME_MAX_FILE_BYTES) throw new RuntimeException('Theme contains a file larger than the 8MB per-file limit: ' . basename($name));
  $state['count']++;
  if ($state['count'] > THEME_MAX_FILES) throw new RuntimeException('Theme exceeds the 3,000-file extraction limit.');

  // Keep imported HTML as non-executable source material. LPCMS only uses it to
  // discover Bootstrap/assets and never serves it as a live same-origin page.
  $storedName = $name;
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (in_array($ext, ['html','htm'], true)) $storedName .= '.lpcms-source.txt';
  $dest = $target . '/' . $storedName;
  $dir = dirname($dest); if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) throw new RuntimeException('Could not create a theme asset directory.');
  $out = @fopen($dest, 'wb');
  if (!$out) throw new RuntimeException('Could not write imported theme asset: ' . basename($storedName));
  $copied = stream_copy_to_stream($in, $out, THEME_MAX_FILE_BYTES + 1);
  fclose($out);
  if ($copied === false) throw new RuntimeException('Could not extract imported theme asset: ' . basename($storedName));
  if ($copied > THEME_MAX_FILE_BYTES) throw new RuntimeException('Theme contains a file larger than the 8MB per-file limit: ' . basename($name));
  $state['bytes'] += (int)$copied;
  if ($state['bytes'] > THEME_MAX_UPLOAD_BYTES) throw new RuntimeException('Theme exceeds the 40MB extraction limit.');
  @chmod($dest, 0644);
  $state['files'][] = $storedName;
  return $storedName;
}

function cms_theme_extract_zip_ziparchive(string $zipFile, string $target): array {
  $zip = new ZipArchive();
  if ($zip->open($zipFile) !== true) throw new RuntimeException('The uploaded file is not a readable ZIP archive.');
  cms_theme_remove_dir($target); @mkdir($target, 0755, true);
  $state = ['count'=>0,'bytes'=>0,'files'=>[]];
  try {
    for ($i=0; $i<$zip->numFiles; $i++) {
      $stat = $zip->statIndex($i); if (!$stat) continue;
      $rawName = (string)($stat['name'] ?? '');
      $safeName = cms_theme_safe_rel($rawName);
      if ($safeName === '' || str_ends_with($safeName, '/') || !cms_theme_allowed_file($safeName)) continue;
      $in = $zip->getStream($rawName); if (!$in) continue;
      try { cms_theme_store_stream($safeName, (int)($stat['size'] ?? 0), $in, $target, $state); }
      finally { fclose($in); }
    }
  } catch (Throwable $e) { $zip->close(); cms_theme_remove_dir($target); throw $e; }
  $zip->close();
  if (!$state['files']) { cms_theme_remove_dir($target); throw new RuntimeException('No safe HTML/CSS/theme assets were found in the ZIP.'); }
  return $state + ['engine'=>'ZipArchive'];
}

function cms_theme_extract_zip_phar(string $zipFile, string $target): array {
  $tmpDir = rtrim(sys_get_temp_dir(), '/\\') . '/lpcms-phar-' . bin2hex(random_bytes(8));
  if (!@mkdir($tmpDir, 0700, true) && !is_dir($tmpDir)) throw new RuntimeException('Could not create a temporary archive directory.');
  $pharZip = $tmpDir . '/theme.zip';
  if (!@copy($zipFile, $pharZip)) { @rmdir($tmpDir); throw new RuntimeException('Could not prepare the ZIP archive for the PharData fallback.'); }
  cms_theme_remove_dir($target); @mkdir($target, 0755, true);
  $state = ['count'=>0,'bytes'=>0,'files'=>[]];
  try {
    $archive = new PharData($pharZip);
    $prefix = 'phar://' . str_replace('\\','/',$pharZip) . '/';
    $iterator = new RecursiveIteratorIterator($archive, RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($iterator as $key => $file) {
      if (!$file instanceof SplFileInfo || !$file->isFile()) continue;
      $uri = str_replace('\\','/', (string)$key);
      if (!str_starts_with($uri, $prefix)) continue;
      $rawName = substr($uri, strlen($prefix));
      $safeName = cms_theme_safe_rel($rawName);
      if ($safeName === '' || !cms_theme_allowed_file($safeName)) continue;
      $in = @fopen($uri, 'rb'); if (!$in) continue;
      try { cms_theme_store_stream($safeName, (int)$file->getSize(), $in, $target, $state); }
      finally { fclose($in); }
    }
  } catch (Throwable $e) {
    cms_theme_remove_dir($target); @unlink($pharZip); @rmdir($tmpDir);
    throw new RuntimeException('PharData could not read this ZIP archive: ' . $e->getMessage(), 0, $e);
  }
  @unlink($pharZip); @rmdir($tmpDir);
  if (!$state['files']) { cms_theme_remove_dir($target); throw new RuntimeException('No safe HTML/CSS/theme assets were found in the ZIP.'); }
  return $state + ['engine'=>'PharData'];
}

function cms_theme_extract_zip(string $zipFile, string $target): array {
  $support = cms_theme_archive_support();
  if (!$support['supported']) throw new RuntimeException('No ZIP extraction engine is available. Enable PHP ZipArchive or the standard Phar/PharData extension.');
  if (class_exists('ZipArchive')) return cms_theme_extract_zip_ziparchive($zipFile, $target);
  if (class_exists('PharData') && extension_loaded('zlib')) return cms_theme_extract_zip_phar($zipFile, $target);
  throw new RuntimeException('No supported ZIP extraction engine is available.');
}

function cms_theme_find_index(string $target): ?string {
  $candidates = [];
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $file) if ($file->isFile() && in_array(strtolower($file->getFilename()), ['index.html.lpcms-source.txt','index.htm.lpcms-source.txt'], true)) {
    $rel = ltrim(str_replace('\\','/',substr($file->getPathname(), strlen($target))), '/');
    $score = str_contains($rel, '/dist/index.html.lpcms-source.txt') || str_starts_with($rel, 'dist/index.html.lpcms-source.txt') ? 0 : (substr_count($rel, '/') + 1);
    $candidates[] = [$score, strlen($rel), $rel];
  }
  if (!$candidates) return null;
  usort($candidates, fn($a,$b)=>$a[0]<=>$b[0] ?: $a[1]<=>$b[1]);
  return $candidates[0][2];
}

function cms_theme_resolve_asset(string $target, string $indexRel, string $asset): ?string {
  $asset = html_entity_decode(trim($asset), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  $asset = preg_replace('/[?#].*$/', '', $asset) ?? $asset;
  if ($asset === '' || preg_match('#^(?:https?:)?//#i', $asset) || str_starts_with($asset, 'data:') || str_starts_with($asset, '#')) return null;
  $base = dirname($target . '/' . $indexRel);
  $candidate = $asset[0] === '/' ? $target . '/' . ltrim($asset, '/') : $base . '/' . $asset;
  $real = realpath($candidate); $root = realpath($target);
  if (!$real || !$root || !str_starts_with(str_replace('\\','/',$real), rtrim(str_replace('\\','/',$root), '/') . '/')) return null;
  return ltrim(str_replace('\\','/',substr($real, strlen($root))), '/');
}

function cms_theme_analyse(string $target, string $indexRel): array {
  $html = @file_get_contents($target . '/' . $indexRel) ?: '';
  $version = 'unknown';
  if (preg_match('/bootstrap(?:@|[-_.])?5(?:\.|\b)/i', $html) || str_contains($html, 'data-bs-toggle')) $version = '5';
  elseif (preg_match('/bootstrap(?:@|[-_.])?4(?:\.|\b)/i', $html) || str_contains($html, 'data-toggle=')) $version = '4';
  elseif (preg_match('/bootstrap(?:@|[-_.])?3(?:\.|\b)/i', $html)) $version = '3';
  elseif (stripos($html, 'bootstrap') !== false) $version = 'present/unknown';
  $css = []; $js = [];
  if (preg_match_all('/<link\b[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) foreach ($m[1] as $asset) { $r = cms_theme_resolve_asset($target,$indexRel,$asset); if ($r && strtolower(pathinfo($r,PATHINFO_EXTENSION))==='css') $css[]=$r; }
  if (preg_match_all('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) foreach ($m[1] as $asset) { $r = cms_theme_resolve_asset($target,$indexRel,$asset); if ($r && in_array(strtolower(pathinfo($r,PATHINFO_EXTENSION)),['js','mjs'],true)) $js[]=$r; }
  $css = array_values(array_unique($css)); $js = array_values(array_unique($js));
  if ($version === 'unknown' || $version === 'present/unknown') {
    foreach ($css as $rel) {
      if (stripos(basename($rel), 'bootstrap') === false) continue;
      $probe = @file_get_contents($target . '/' . $rel);
      if (!is_string($probe)) continue;
      $probe = substr($probe, 0, 12000);
      if (preg_match('/Bootstrap\s+v?([345])(?:\.|\b)/i', $probe, $vm)) { $version = $vm[1]; break; }
    }
  }
  $warnings = [];
  $score = 100;
  if ($version === '4') { $score -= 15; $warnings[] = 'Bootstrap 4 detected. LPCMS uses Bootstrap 5; common visual CSS is imported, but Bootstrap 4 JavaScript/data attributes are not auto-loaded.'; }
  elseif ($version === '3') { $score -= 35; $warnings[] = 'Bootstrap 3 detected. Expect manual CSS adjustments for grid/components.'; }
  elseif ($version === 'unknown') { $score -= 20; $warnings[] = 'Bootstrap version could not be detected.'; }
  if (!$css) { $score -= 30; $warnings[] = 'No local CSS files were discovered from index.html.'; }
  if ($js) $warnings[] = 'Theme JavaScript was found but is disabled by default for safety. Enable imported JS only after reviewing it.';
  // Prefer the theme's own stylesheet colors over Bootstrap's vendor defaults.
  $paletteCss = array_values(array_filter($css, fn($rel)=>stripos(basename((string)$rel), 'bootstrap') === false));
  if (!$paletteCss) $paletteCss = $css;
  $allCss = '';
  foreach (array_slice($paletteCss,0,8) as $rel) { $chunk=@file_get_contents($target.'/'.$rel); if (is_string($chunk)) $allCss .= substr($chunk,0,500000); }
  preg_match_all('/#[0-9a-fA-F]{6}\b/', $allCss, $cm); $freq=[];
  foreach ($cm[0] ?? [] as $c) {
    $c=strtolower($c);
    if (in_array($c,['#ffffff','#000000','#f8f9fa','#212529'],true)) continue;
    $r=hexdec(substr($c,1,2)); $g=hexdec(substr($c,3,2)); $b=hexdec(substr($c,5,2));
    if (($r>238 && $g>238 && $b>238) || ($r<20 && $g<20 && $b<20)) continue;
    $freq[$c]=($freq[$c]??0)+1;
  }
  arsort($freq); $colors=array_slice(array_keys($freq),0,4);
  $palette = ['brand'=>$colors[0]??'#0d6efd','brand2'=>$colors[1]??($colors[0]??'#6610f2'),'accent'=>$colors[2]??'#20c997'];
  return ['bootstrap'=>$version,'css'=>$css,'js'=>$js,'score'=>max(0,$score),'warnings'=>$warnings,'palette'=>$palette];
}

function cms_theme_adapter_css(array $analysis, string $themeId): string {
  $p=$analysis['palette'] ?? [];
  $brand=$p['brand']??'#0d6efd'; $brand2=$p['brand2']??$brand; $accent=$p['accent']??'#20c997';
  return "/* LPCMS generated adapter for {$themeId}. Original theme assets remain unchanged. */\n"
    . ":root{--cms-brand:{$brand};--cms-brand2:{$brand2};--cms-accent:{$accent};}\n"
    . ".lpcms-imported-theme .btn-cms{background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));}\n"
    . ".lpcms-imported-theme .text-primary{color:var(--cms-brand)!important}.lpcms-imported-theme .bg-primary{background-color:var(--cms-brand)!important}.lpcms-imported-theme .border-primary{border-color:var(--cms-brand)!important}\n";
}

function cms_theme_install_archive(string $zipFile, string $title, string $sourceType, string $sourceLabel = '', string $license = ''): array {
  $baseId = cms_slug($title ?: 'imported-theme');
  $id = 'theme-' . $baseId . '-' . substr(bin2hex(random_bytes(4)),0,6);
  $target = THEMES_DIR . '/' . $id;
  $extract = cms_theme_extract_zip($zipFile, $target);
  $indexRel = cms_theme_find_index($target);
  if (!$indexRel) { cms_theme_remove_dir($target); throw new RuntimeException('No index.html was found in the theme archive.'); }
  $analysis = cms_theme_analyse($target, $indexRel);
  $adapter = 'lpcms-adapter.css';
  file_put_contents($target . '/' . $adapter, cms_theme_adapter_css($analysis, $id), LOCK_EX);
  $manifest = [
    'id'=>$id,'title'=>$title ?: ucfirst(str_replace('-',' ',$baseId)),'sourceType'=>$sourceType,'sourceLabel'=>$sourceLabel,'license'=>$license,
    'installedAt'=>date('c'),'index'=>$indexRel,'bootstrap'=>$analysis['bootstrap'],'compatibilityScore'=>$analysis['score'],'warnings'=>$analysis['warnings'],
    'css'=>array_values(array_unique(array_merge($analysis['css'],[$adapter]))),'js'=>$analysis['js'],'palette'=>$analysis['palette'],'files'=>$extract['count'],'bytes'=>$extract['bytes'],'archiveEngine'=>$extract['engine']??'unknown'
  ];
  file_put_contents($target . '/lpcms-theme.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), LOCK_EX);
  return $manifest;
}

function cms_theme_manager_settings(array $data): array {
  return array_replace(['active'=>'lpcms-default','loadImportedCss'=>true,'loadImportedJs'=>false,'installed'=>[]], $data['settings']['themeManager'] ?? []);
}

function cms_theme_register(array $data, array $manifest, bool $activate = true): array {
  $mgr = cms_theme_manager_settings($data);
  $installed = is_array($mgr['installed'] ?? null) ? $mgr['installed'] : [];
  $installed = array_values(array_filter($installed, fn($x)=>is_array($x) && ($x['id']??'') !== ($manifest['id']??'')));
  $installed[] = $manifest;
  $data['settings']['themeManager'] = array_replace($mgr, ['installed'=>$installed]);
  if ($activate) {
    $data['settings']['themeManager']['active'] = $manifest['id'];
    foreach (($manifest['palette'] ?? []) as $k=>$v) if (isset($data['settings']['theme'][$k])) $data['settings']['theme'][$k]=$v;
  }
  return $data;
}

function cms_theme_activate(array $data, string $id): array {
  foreach (cms_theme_presets() as $preset) if (($preset['id']??'') === $id) {
    $data['settings']['themeManager']['active'] = $id;
    foreach (($preset['palette'] ?? []) as $k=>$v) $data['settings']['theme'][$k]=$v;
    return $data;
  }
  foreach ((cms_theme_manager_settings($data)['installed'] ?? []) as $theme) if (($theme['id']??'') === $id) {
    if (!is_dir(THEMES_DIR . '/' . $id)) throw new RuntimeException('Theme files are missing from assets/themes.');
    $data['settings']['themeManager']['active'] = $id;
    foreach (($theme['palette'] ?? []) as $k=>$v) if (isset($data['settings']['theme'][$k])) $data['settings']['theme'][$k]=$v;
    return $data;
  }
  throw new RuntimeException('Theme not found.');
}

function cms_active_theme_assets(array $data): array {
  $mgr = cms_theme_manager_settings($data); $active=(string)($mgr['active']??'lpcms-default');
  foreach (($mgr['installed'] ?? []) as $theme) if (($theme['id']??'') === $active) {
    $prefix = 'assets/themes/' . rawurlencode($active) . '/';
    $css = [];$js=[];
    if (!empty($mgr['loadImportedCss'])) foreach (($theme['css']??[]) as $rel) if (cms_theme_safe_rel((string)$rel)!=='') $css[]=$prefix.str_replace('%2F','/',rawurlencode(cms_theme_safe_rel((string)$rel)));
    if (!empty($mgr['loadImportedJs'])) foreach (($theme['js']??[]) as $rel) if (cms_theme_safe_rel((string)$rel)!=='') $js[]=$prefix.str_replace('%2F','/',rawurlencode(cms_theme_safe_rel((string)$rel)));
    return ['id'=>$active,'imported'=>true,'css'=>$css,'js'=>$js,'meta'=>$theme];
  }
  return ['id'=>$active,'imported'=>false,'css'=>[],'js'=>[],'meta'=>null];
}

function cms_theme_delete(array $data, string $id): array {
  if (!str_starts_with($id, 'theme-')) throw new RuntimeException('Built-in themes cannot be deleted.');
  $mgr = cms_theme_manager_settings($data);
  $mgr['installed'] = array_values(array_filter($mgr['installed'] ?? [], fn($x)=>($x['id']??'')!==$id));
  if (($mgr['active']??'') === $id) $mgr['active']='lpcms-default';
  $data['settings']['themeManager']=$mgr;
  cms_theme_remove_dir(THEMES_DIR . '/' . $id);
  return $data;
}

function cms_slug(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  return trim($s ?: 'item', '-');
}

function cms_e(?string $s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cms_pretty_path(string $group, string $slug, array $data = [], ?string $lang = null): string {
  $slug = cms_slug($slug);
  $prefix = $data ? cms_language_prefix($data, $lang) : '';
  $base = $prefix;
  if ($group === 'blog') return $base . '/blog/' . $slug;
  if ($group === 'products') return $base . '/product/' . $slug;
  if ($group === 'pages') return $base . '/page/' . $slug;
  return $base . '/?slug=' . rawurlencode($slug);
}

function cms_request_route(array $data): array {
  $map = ['blog' => 'blog', 'posts' => 'blog', 'post' => 'blog', 'products' => 'products', 'product' => 'products', 'pages' => 'pages', 'page' => 'pages'];
  $route = strtolower(trim((string)($_GET['cms_route'] ?? ''), '/'));
  $slug = trim((string)($_GET['slug'] ?? ''), '/');
  $path = trim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''), '/');
  $base = trim(cms_route_prefix(), '/');
  if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = trim(substr($path, strlen($base)), '/');
  $parts = $path === '' ? [] : array_values(array_filter(explode('/', $path), 'strlen'));
  $codes = cms_language_codes($data);
  $lang = cms_current_language($data);
  if (!empty($parts) && in_array(strtolower((string)$parts[0]), $codes, true)) array_shift($parts);
  if ($route !== '' && isset($map[$route])) $route = $map[$route];
  if ($slug === '' && count($parts) >= 2 && isset($map[strtolower($parts[0])])) {
    $route = $map[strtolower($parts[0])];
    $slug = $parts[1];
  } elseif ($slug === '' && count($parts) === 1 && !str_contains($parts[0], '.')) {
    $route = 'pages';
    $slug = $parts[0];
  }
  return ['group' => $route, 'slug' => $slug === '' ? '' : cms_slug($slug), 'path' => implode('/', $parts), 'lang' => $lang];
}

function cms_find_route_item(array $data): array {
  $r = cms_request_route($data);
  $groups = $r['group'] ? [$r['group']] : ['pages', 'blog', 'products'];
  $candidates = array_filter([$r['slug'], basename($r['path'] ?? '')]);
  foreach ($groups as $group) {
    foreach (($data[$group]['items'] ?? []) as $i => $item) {
      $itemSlug = cms_slug((string)($item['slug'] ?? ($item['title'] ?? 'item')));
      if (in_array($itemSlug, $candidates, true)) {
        return ['group' => $group, 'index' => $i, 'slug' => $itemSlug, 'item' => $item, 'lang'=>$r['lang'], 'path' => cms_pretty_path($group, $itemSlug, $data, $r['lang'])];
      }
    }
  }
  return ['group' => $r['group'], 'slug' => $r['slug'], 'item' => null, 'lang'=>$r['lang'], 'path' => $r['slug'] ? cms_pretty_path($r['group'] ?: 'pages', $r['slug'], $data, $r['lang']) : cms_language_home_path($data, $r['lang'])];
}

function cms_find_meta(array $data): array {
  $default = $data['settings']['seo'] ?? [];
  $route = cms_find_route_item($data);
  $lang = $route['lang'] ?? cms_current_language($data);
  if (!empty($route['item']) && is_array($route['item'])) {
    $item = $route['item'];
    $group = $route['group'];
    $seo = $item['seo'] ?? [];
    $type = $group === 'products' ? 'product' : ($group === 'blog' ? 'article' : 'website');
    $seoTitle = cms_localized_value($data, is_array($seo) ? $seo : [], 'title', $lang, '');
    $seoDescription = cms_localized_value($data, is_array($seo) ? $seo : [], 'description', $lang, '');
    $title = (string)($seoTitle !== '' ? $seoTitle : cms_localized_value($data, $item, 'title', $lang, ($default['title'] ?? '')));
    $description = (string)($seoDescription !== '' ? $seoDescription : (cms_localized_value($data, $item, 'excerpt', $lang, '') ?: cms_localized_value($data, $item, 'text', $lang, ($default['description'] ?? ''))));
    return array_replace($default, [
      'title' => $title,
      'description' => $description,
      'image' => $seo['image'] ?? $item['image'] ?? ($default['image'] ?? ''),
      'type' => $type,
      'item' => $item,
      'group' => $group,
      'slug' => $route['slug'],
      'lang' => $lang,
      'path' => $route['path'],
    ]);
  }
  $out = array_replace(['type' => 'website'], $default);
  foreach (['title','description','keywords'] as $k) {
    if (isset($default['i18n'][$lang]) && is_array($default['i18n'][$lang]) && array_key_exists($k, $default['i18n'][$lang])) $out[$k] = $default['i18n'][$lang][$k];
    elseif (!empty($out[$k])) $out[$k] = cms_i18n_text($data, (string)$out[$k], $lang);
  }
  $out['lang'] = $lang;
  $out['path'] = cms_language_home_path($data, $lang);
  return $out;
}

function cms_apply_redirects(array $data): void {
  if (isset($_GET['api']) || isset($_GET['login'])) return;
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  foreach (($data['redirects'] ?? []) as $r) {
    if (empty($r['enabled'])) continue;
    $from = $r['from'] ?? '';
    $to = $r['to'] ?? '';
    if ($from && $to && rtrim($from, '/') === rtrim($path, '/')) {
      $status = in_array((int)($r['status'] ?? 301), [301,302,307,308], true) ? (int)$r['status'] : 301;
      header('Location: ' . $to, true, $status);
      exit;
    }
  }
}

function cms_verify_hcaptcha(array $data, string $token): bool {
  $enabled = !empty($data['settings']['hcaptcha']['enabled']);
  $secret = trim((string)(getenv('HCAPTCHA_SECRET') ?: ($data['settings']['hcaptcha']['secret'] ?? '')));
  if ($secret === '') {
    $secretFile = __DIR__ . '/.secrets.php';
    if (is_file($secretFile)) {
      $private = include $secretFile;
      if (is_array($private)) $secret = trim((string)($private['hcaptchaSecret'] ?? ''));
    }
  }
  if (!$enabled) return true;
  if ($secret === '' || $token === '') return false;
  $post = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '']);
  $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $post, 'timeout' => 8]];
  $res = @file_get_contents('https://hcaptcha.com/siteverify', false, stream_context_create($opts));
  $json = json_decode($res ?: '', true);
  return !empty($json['success']);
}

function cms_app_settings(array $data): array {
  return array_replace([
    'enabled' => true,
    'name' => 'LPCMS',
    'shortName' => 'LPCMS',
    'description' => 'Install this website as a mobile app.',
    'themeColor' => '#0d6efd',
    'backgroundColor' => '#ffffff',
    'icon' => '/assets/img/app-icon-512.png',
    'icon192' => '/assets/img/app-icon-192.png',
    'icon512' => '/assets/img/app-icon-512.png',
    'appleIcon' => '/assets/img/apple-touch-icon.png',
    'androidInstallUrl' => '/?install=android',
    'appleInstallUrl' => '/?install=apple',
    'footerBadgesEnabled' => true,
    'offlineMessage' => 'You are offline. Please reconnect to continue editing.'
  ], $data['settings']['app'] ?? []);
}

function cms_render_manifest(array $data): void {
  $app = cms_app_settings($data);
  $base = cms_base_url($data);
  $icon192 = trim((string)($app['icon192'] ?? $app['icon'] ?? '')) ?: '/assets/img/app-icon-192.png';
  $icon512 = trim((string)($app['icon512'] ?? $app['icon'] ?? '')) ?: '/assets/img/app-icon-512.png';
  if (!preg_match('/^https?:\/\//', $icon192)) $icon192 = rtrim($base, '/') . '/' . ltrim($icon192, '/');
  if (!preg_match('/^https?:\/\//', $icon512)) $icon512 = rtrim($base, '/') . '/' . ltrim($icon512, '/');
  $manifest = [
    'name' => (string)$app['name'],
    'short_name' => (string)$app['shortName'],
    'description' => (string)$app['description'],
    'start_url' => cms_route_prefix(),
    'scope' => cms_route_prefix(),
    'display' => 'standalone',
    'background_color' => (string)$app['backgroundColor'],
    'theme_color' => (string)$app['themeColor'],
    'icons' => [
      ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
      ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable']
    ]
  ];
  header('Content-Type: application/manifest+json; charset=utf-8');
  header('Cache-Control: no-cache, must-revalidate');
  echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

function cms_render_service_worker(array $data): void {
  $app = cms_app_settings($data);
  $version = 'lpcms-' . CMS_VERSION . '-' . (string)@filemtime(__FILE__) . '-' . (file_exists(DATA_FILE) ? (string)filemtime(DATA_FILE) : time());
  $offline = json_encode((string)($app['offlineMessage'] ?? 'Offline'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  header('Content-Type: application/javascript; charset=utf-8');
  header('Service-Worker-Allowed: ' . cms_route_prefix());
  header('Cache-Control: no-cache, must-revalidate');
  echo "const LPCMS_CACHE = " . json_encode($version) . ";
";
  echo "self.addEventListener('install', e => { self.skipWaiting(); e.waitUntil(caches.open(LPCMS_CACHE)); });
";
  echo "self.addEventListener('activate', e => { e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k.startsWith('lpcms-') && k !== LPCMS_CACHE).map(k => caches.delete(k))))); self.clients.claim(); });
";
  echo "self.addEventListener('fetch', e => { const req = e.request; if (req.method !== 'GET') return; const url = new URL(req.url); if (url.protocol !== 'http:' && url.protocol !== 'https:') return; if (url.searchParams.has('api') || url.searchParams.has('admin') || url.searchParams.has('login') || url.searchParams.get('scope') === 'admin') return; e.respondWith(fetch(req).then(res => { const cc=String(res.headers.get('Cache-Control')||'').toLowerCase(); const cacheable=res.ok&&!cc.includes('no-store')&&!cc.includes('private')&&(req.destination === 'document' || req.destination === 'image' || req.destination === 'style' || req.destination === 'script'); if (cacheable) { const copy = res.clone(); caches.open(LPCMS_CACHE).then(c => c.put(req, copy)).catch(() => {}); } return res; }).catch(() => caches.match(req).then(cached => cached || new Response('<!doctype html><title>Offline</title><meta name=viewport content=width=device-width,initial-scale=1><body style=font-family:system-ui;padding:2rem><h1>Offline</h1><p>'+" . $offline . "+'</p></body>', {headers:{'Content-Type':'text/html; charset=utf-8'}})))); });
";
  exit;
}

function cms_render_install_page(array $data, string $platform): void {
  $app = cms_app_settings($data);
  $isApple = $platform === 'apple';
  $title = ($isApple ? 'Install on iPhone / iPad' : 'Install on Android') . ' - ' . (string)$app['name'];
  $manifest = rtrim(cms_route_prefix(), '/') . '/manifest.webmanifest';
  $sw = rtrim(cms_route_prefix(), '/') . '/service-worker.js';
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="' . cms_e((string)$app['themeColor']) . '"><link rel="manifest" href="' . cms_e($manifest) . '"><title>' . cms_e($title) . '</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#eef5ff,#f7f3ff);font:16px/1.6 system-ui,-apple-system,Segoe UI,sans-serif;color:#172033;padding:24px}.card{width:min(100%,720px);background:#fff;border-radius:28px;padding:32px;box-shadow:0 30px 90px rgba(15,23,42,.16);border:1px solid rgba(15,23,42,.08)}.badge{display:inline-flex;border-radius:999px;padding:.35rem .7rem;background:#eef4ff;color:#0d6efd;font-weight:700}h1{font-size:clamp(2rem,5vw,3.4rem);line-height:1.02;margin:.8rem 0 1rem;letter-spacing:-.045em}li{margin:.6rem 0}.actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.4rem}button,a{border:0;border-radius:999px;padding:.85rem 1.15rem;text-decoration:none;font-weight:750;cursor:pointer}.primary{background:linear-gradient(135deg,#0d6efd,#6610f2);color:#fff}.ghost{background:#fff;color:#243449;border:1px solid rgba(15,23,42,.12)}</style></head><body><main class="card"><span class="badge">LPCMS mobile app</span><h1>' . cms_e($title) . '</h1>';
  if ($isApple) {
    echo '<p>Open this website in Safari, tap <strong>Share</strong>, choose <strong>Add to Home Screen</strong>, then tap <strong>Add</strong>.</p><ol><li>Use Safari on iPhone or iPad.</li><li>Open the website URL.</li><li>Tap the Share icon.</li><li>Select Add to Home Screen.</li><li>Launch the app from the home screen.</li></ol><div class="actions">';
  } else {
    echo '<p>Open this website in Chrome/Edge on Android, then use <strong>Install app</strong> or <strong>Add to Home screen</strong>.</p><ol><li>Open the website URL on Android.</li><li>Tap the browser menu.</li><li>Choose Install app or Add to Home screen.</li><li>Launch the app from your home screen.</li></ol><div class="actions"><button class="primary" id="installBtn">Install app</button>';
  }
  echo '<a class="ghost" href="' . cms_e(cms_route_prefix()) . '">Back to website</a></div><script>let p=null;window.addEventListener("beforeinstallprompt",e=>{e.preventDefault();p=e;const b=document.getElementById("installBtn");if(b)b.style.display="inline-flex"});const b=document.getElementById("installBtn");if(b)b.onclick=async()=>{if(!p){alert("Use your browser menu and choose Install app / Add to Home screen.");return}p.prompt();await p.userChoice;p=null};if("serviceWorker"in navigator&&(location.protocol==="https:"||location.hostname==="localhost")){navigator.serviceWorker.register("' . cms_e($sw) . '").catch(()=>{})}</script></main></body></html>';
  exit;
}

function cms_cache_settings(array $data): array {
  return array_replace(['enabled' => true, 'htmlTtl' => 300, 'autoClearOnSave' => true, 'maxAgeDays' => 14], $data['settings']['cache'] ?? []);
}

function cms_cache_key(): string {
  $uri = (string)($_SERVER['HTTP_HOST'] ?? 'localhost') . '|' . (string)($_SERVER['REQUEST_URI'] ?? '/');
  return hash('sha256', CMS_VERSION . '|' . (file_exists(__FILE__) ? filemtime(__FILE__) : 0) . '|' . (file_exists(DATA_FILE) ? filemtime(DATA_FILE) : 0) . '|' . (file_exists(I18N_FILE) ? filemtime(I18N_FILE) : 0) . '|' . $uri);
}

function cms_cache_enabled_for_request(array $data): bool {
  $s = cms_cache_settings($data);
  if (empty($s['enabled'])) return false;
  if (cms_is_admin()) return false;
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
  foreach (['api','login','admin','install','pwa'] as $key) if (array_key_exists($key, $_GET)) return false;
  return true;
}

function cms_try_serve_page_cache(array $data): void {
  if (!cms_cache_enabled_for_request($data)) return;
  $ttl = max(0, min(86400, (int)(cms_cache_settings($data)['htmlTtl'] ?? 300)));
  if ($ttl <= 0) return;
  $file = CACHE_DIR . '/page-' . cms_cache_key() . '.html';
  if (is_file($file) && filemtime($file) >= time() - $ttl) {
    header('Content-Type: text/html; charset=utf-8');
    header('X-LPCMS-Cache: HIT');
    readfile($file);
    exit;
  }
}

function cms_start_page_cache(array $data): void {
  if (!cms_cache_enabled_for_request($data)) return;
  $file = CACHE_DIR . '/page-' . cms_cache_key() . '.html';
  @ob_start(function ($html) use ($file) {
    if (http_response_code() < 400 && is_string($html) && strlen($html) > 200) {
      if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
      @file_put_contents($file, $html, LOCK_EX);
    }
    return $html;
  });
  header('X-LPCMS-Cache: MISS');
}

function cms_dir_size(string $dir, int &$files = 0): int {
  $bytes = 0;
  if (!is_dir($dir)) return 0;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $file) if ($file->isFile()) { $files++; $bytes += $file->getSize(); }
  return $bytes;
}

function cms_cache_stats(): array {
  $files = 0;
  $bytes = cms_dir_size(CACHE_DIR, $files);
  return ['files' => $files, 'bytes' => $bytes, 'mb' => round($bytes / 1048576, 2), 'dir' => 'assets/cache'];
}

function cms_clear_cache(): int {
  $deleted = 0;
  if (!is_dir(CACHE_DIR)) return 0;
  foreach (glob(CACHE_DIR . '/*') ?: [] as $file) {
    if (is_file($file) && @unlink($file)) $deleted++;
  }
  return $deleted;
}

function cms_media_stats(array $data): array {
  $dirs = ['img' => IMG_DIR, 'gallery' => GALLERY_DIR, 'uploads' => UPLOAD_DIR, 'docs' => DOCS_DIR, 'cache' => CACHE_DIR];
  $out = [];
  foreach ($dirs as $key => $dir) {
    $files = 0; $bytes = cms_dir_size($dir, $files);
    $out[$key] = ['files' => $files, 'bytes' => $bytes, 'mb' => round($bytes / 1048576, 2)];
  }
  $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
  $orphanCache = 0;
  foreach (glob(CACHE_DIR . '/*') ?: [] as $file) if (is_file($file)) $orphanCache++;
  return ['directories' => $out, 'cache' => cms_cache_stats(), 'referencesChecked' => strlen($json), 'orphanCacheFiles' => $orphanCache];
}

$data = cms_load_data();
if (isset($_GET['asset']) && $_GET['asset'] === 'theme-css') {
  header('Content-Type: text/css; charset=utf-8');
  header('Cache-Control: public, max-age=31536000, immutable');
  echo ':root{';
  echo '--cms-brand:' . (string)($data['settings']['theme']['brand'] ?? '#0d6efd') . ';';
  echo '--cms-brand2:' . (string)($data['settings']['theme']['brand2'] ?? '#6610f2') . ';';
  echo '--cms-accent:' . (string)($data['settings']['theme']['accent'] ?? '#20c997') . ';';
  echo '--cms-text:' . (string)($data['settings']['theme']['text'] ?? '#1f2937') . ';';
  echo '--cms-muted:' . (string)($data['settings']['theme']['muted'] ?? '#6b7280') . ';';
  echo '--cms-bg:' . (string)($data['settings']['theme']['bg'] ?? '#fff') . ';';
  echo '--cms-soft:' . (string)($data['settings']['theme']['softBg'] ?? '#f6f8fb') . ';';
  echo '--cms-radius:' . (string)($data['settings']['theme']['radius'] ?? '22px') . ';';
  echo '--cms-font:' . (string)($data['settings']['theme']['font'] ?? 'system-ui') . ';';
  echo "}\n";
  echo (string)($data['settings']['theme']['customCss'] ?? '');
  exit;
}
if (isset($_GET['asset']) && $_GET['asset'] === 'app-js') {
  $adminBundle = ASSETS_DIR . '/js/.lpcms-admin.js';
  $publicBundle = ASSETS_DIR . '/js/lpcms-public.js';
  $scope = strtolower(trim((string)($_GET['scope'] ?? 'public')));
  $isAdmin = cms_is_admin();
  if ($scope === 'admin') {
    if (!$isAdmin) {
      http_response_code(403);
      header('Content-Type: text/plain; charset=utf-8');
      header('Cache-Control: private, no-store');
      echo 'Admin bundle requires an authenticated CMS session.';
      exit;
    }
    $bundle = $adminBundle;
    header('Cache-Control: private, no-store');
  } else {
    $bundle = $publicBundle;
    header('Cache-Control: public, max-age=86400');
  }
  if (!is_file($bundle)) { http_response_code(404); exit; }
  header('Content-Type: application/javascript; charset=utf-8');
  header('X-Content-Type-Options: nosniff');
  readfile($bundle);
  exit;
}
cms_sync_htaccess_files($data);
$requestPath = cms_request_relative_path();
if ((isset($_GET['pwa']) && $_GET['pwa'] === 'manifest') || $requestPath === 'manifest.webmanifest') cms_render_manifest($data);
if ((isset($_GET['pwa']) && $_GET['pwa'] === 'sw') || $requestPath === 'service-worker.js') cms_render_service_worker($data);
if (isset($_GET['install']) && in_array(strtolower((string)$_GET['install']), ['android','apple'], true)) cms_render_install_page($data, strtolower((string)$_GET['install']));
if (cms_is_breakglass_request()) cms_handle_breakglass($data);
cms_apply_redirects($data);

// SSR market pages, sitemap.xml, robots.txt and optional llms.txt.
require_once __DIR__ . '/location-pages.php';
dp_locations_handle($data, $requestPath);

if (isset($_GET['login'])) {
  $email = cms_verify_token((string)$_GET['login']);
  if (!$email) {
    http_response_code(403);
    echo 'Invalid or expired login link.';
    exit;
  }
  session_regenerate_id(true);
  $_SESSION['cms_admin_email'] = $email;
  $loginUser = cms_user_from_email(cms_load_data(), $email);
  $_SESSION['cms_admin_role'] = $loginUser['role'] ?? 'editor';
  $_SESSION['cms_csrf'] = bin2hex(random_bytes(20));
  header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
  exit;
}

if (isset($_GET['api'])) {
  $api = (string)$_GET['api'];
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

  if ($api === 'headless' || $api === 'graphql' || $api === 'graphql-schema') {
    $headless = cms_headless_settings($data);
    if (empty($headless['enabled'])) cms_json(['ok'=>false,'error'=>'Headless API is disabled.'], 404);
    cms_apply_headless_cors($data);
    if ($method === 'OPTIONS') { http_response_code(204); exit; }
  }

  if ($api === 'headless') {
    if (empty(cms_headless_settings($data)['restEnabled'])) cms_json(['ok'=>false,'error'=>'REST headless API is disabled.'], 404);
    $resource = trim((string)($_GET['resource'] ?? 'site'));
    $slug = trim((string)($_GET['slug'] ?? ''));
    $value = cms_headless_resource(cms_load_data(), $resource, $slug);
    if ($value === null) cms_json(['ok'=>false,'error'=>'Resource not found.'], 404);
    cms_json(['ok'=>true,'resource'=>$resource,'slug'=>$slug?:null,'data'=>$value,'version'=>CMS_VERSION]);
  }

  if ($api === 'graphql-schema') {
    if (empty(cms_headless_settings($data)['graphqlEnabled'])) cms_json(['ok'=>false,'error'=>'GraphQL API is disabled.'], 404);
    header('Content-Type: text/plain; charset=utf-8'); echo cms_graphql_schema(); exit;
  }

  if ($api === 'graphql') {
    if (empty(cms_headless_settings($data)['graphqlEnabled'])) cms_json(['ok'=>false,'error'=>'GraphQL API is disabled.'], 404);
    $payload = $method === 'POST' ? (json_decode(file_get_contents('php://input') ?: '', true) ?: []) : $_GET;
    $query = (string)($payload['query'] ?? '');
    $variables = is_array($payload['variables'] ?? null) ? $payload['variables'] : [];
    $result = cms_graphql_execute(cms_load_data(), $query, $variables);
    cms_json($result, !empty($result['errors']) ? 400 : 200);
  }

  if ($api === 'theme-catalog') {
    cms_require_super_admin();
    $archiveSupport = cms_theme_archive_support();
    $downloadSupport = cms_theme_download_support();
    cms_json(['ok'=>true,'presets'=>cms_theme_presets(),'free'=>cms_free_theme_catalog(),'manager'=>cms_theme_manager_settings(cms_load_data()),'zipSupported'=>$archiveSupport['supported'],'archiveSupport'=>$archiveSupport,'downloadSupport'=>$downloadSupport]);
  }

  if ($api === 'theme-activate' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    try { $fresh = cms_theme_activate(cms_load_data(), (string)($payload['id'] ?? '')); }
    catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()], 422); }
    if (!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not save active theme.'],500);
    cms_clear_cache();
    cms_json(['ok'=>true,'message'=>'Theme activated.','data'=>cms_public_data(cms_load_data()),'manager'=>cms_theme_manager_settings(cms_load_data())]);
  }

  if ($api === 'theme-delete' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    try { $fresh = cms_theme_delete(cms_load_data(), (string)($payload['id'] ?? '')); }
    catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()], 422); }
    if (!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not update theme registry.'],500);
    cms_clear_cache();
    cms_json(['ok'=>true,'message'=>'Imported theme deleted.','manager'=>cms_theme_manager_settings(cms_load_data())]);
  }

  if ($api === 'theme-import' && $method === 'POST') {
    cms_require_super_admin();
    $archiveSupport = cms_theme_archive_support();
    if (!$archiveSupport['supported']) cms_json(['ok'=>false,'error'=>'No ZIP extraction engine is available. Enable PHP ZipArchive or Phar/PharData.'], 501);
    $sourceType = (string)($_POST['sourceType'] ?? 'upload'); $zipFile=''; $cleanup=false; $title=''; $label=''; $license='';
    try {
      if ($sourceType === 'free') {
        $themeId = (string)($_POST['themeId'] ?? ''); $found=null;
        foreach (cms_free_theme_catalog() as $item) if (($item['id']??'')===$themeId) { $found=$item; break; }
        if (!$found) throw new RuntimeException('Free theme was not found in the allowlisted catalogue.');
        $zipFile = cms_theme_download_remote($found['download']); $cleanup=true; $title=$found['title']; $label=$found['source']; $license=$found['license'];
      } else {
        if (empty($_FILES['themeZip']['tmp_name']) || !is_uploaded_file($_FILES['themeZip']['tmp_name'])) throw new RuntimeException('Choose a Bootstrap HTML theme ZIP file.');
        if ((int)($_FILES['themeZip']['size'] ?? 0) <= 0 || (int)($_FILES['themeZip']['size'] ?? 0) > THEME_MAX_UPLOAD_BYTES) throw new RuntimeException('Theme ZIP must be between 1 byte and 40MB.');
        $zipFile = $_FILES['themeZip']['tmp_name']; $title=trim((string)($_POST['title'] ?? pathinfo((string)($_FILES['themeZip']['name'] ?? 'Imported theme'), PATHINFO_FILENAME))); $label=(string)($_FILES['themeZip']['name'] ?? 'Uploaded ZIP'); $license=trim((string)($_POST['license'] ?? 'User-provided / verify licence'));
      }
      $manifest = cms_theme_install_archive($zipFile,$title,$sourceType,$label,$license);
      $fresh = cms_theme_register(cms_load_data(),$manifest,true);
      if (!cms_save_data($fresh)) throw new RuntimeException('Theme files imported, but data.json could not be updated.');
      cms_clear_cache();
      if ($cleanup) @unlink($zipFile);
      cms_json(['ok'=>true,'message'=>'Theme imported, analysed, adapted, registered and activated.','theme'=>$manifest,'data'=>cms_public_data(cms_load_data())]);
    } catch (Throwable $e) {
      if ($cleanup && $zipFile) @unlink($zipFile);
      cms_json(['ok'=>false,'error'=>$e->getMessage()],422);
    }
  }

  if ($api === 'site') {
    cms_json([
      'ok' => true,
      'admin' => cms_is_admin(),
      'email' => $_SESSION['cms_admin_email'] ?? '',
      'role' => cms_admin_role(),
      'isSuper' => cms_is_super_admin(),
      'csrf' => $_SESSION['cms_csrf'] ?? '',
      'route' => cms_find_route_item(cms_load_data()),
      'lang' => cms_current_language(cms_load_data()),
      'server' => cms_htaccess_status(cms_load_data()),
      'data' => cms_public_data(cms_load_data()),
      'i18n' => cms_client_i18n_pack(cms_load_data())
    ]);
  }

  if ($api === 'login' && $method === 'POST') {
    if (!cms_rate_limit_consume('magic-link-login', 6, 900)) {
      cms_json(['ok' => false, 'error' => 'Too many login emails requested. Try again later.'], 429);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    if (!cms_verify_hcaptcha($data, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok' => false, 'error' => 'Captcha verification failed.'], 422);
    $email = strtolower(trim((string)($payload['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'error' => 'Please enter a valid email.'], 422);
    $user = cms_user_from_email($data, $email);
    if (!$user || ($user['role'] ?? 'editor') !== 'editor') {
      cms_json(['ok' => false, 'error' => $email === strtolower(BREAKGLASS_ADMIN_EMAIL) ? 'Super administrator must login with /?admin.' : 'Please ask administrator access for your email.'], 403);
    }
    $token = cms_make_token($email);
    $link = cms_base_url($data) . '/index.php?login=' . rawurlencode($token);
    $sent = cms_send_mail($email, 'Your secure CMS login link', "Open this secure login link within 15 minutes:\n\n" . $link . "\n\nIf you did not request this link, ignore this email.");
    cms_json(['ok' => true, 'sent' => $sent, 'message' => $sent ? 'Secure login link sent. Please check your email.' : 'Mail was not sent. Configure PHP mail or SMTP.', 'devLink' => DEV_SHOW_LOGIN_LINK ? $link : null]);
  }

  if ($api === 'logout' && $method === 'POST') {
    cms_require_admin();
    $_SESSION = [];
    session_destroy();
    cms_json(['ok' => true]);
  }

  if ($api === 'save' && $method === 'POST') {
    cms_require_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($payload)) cms_json(['ok' => false, 'error' => 'Invalid JSON'], 422);
    if (strlen(json_encode($payload)) > 5 * 1024 * 1024) cms_json(['ok' => false, 'error' => 'JSON too large'], 413);
    $current = cms_load_data();
    if (!cms_is_super_admin()) {
      $payload = cms_filter_editor_payload($payload, $current);
    } else {
      $postedPassword = (string)($payload['settings']['mailer']['smtp']['password'] ?? '');
      if ($postedPassword === '' && !empty($current['settings']['mailer']['smtp']['password'])) $payload['settings']['mailer']['smtp']['password'] = $current['settings']['mailer']['smtp']['password'];
      $postedToken = (string)($payload['settings']['commerceIntegrations']['webhook']['bearerToken'] ?? '');
      if ($postedToken === '' && !empty($current['settings']['commerceIntegrations']['webhook']['bearerToken'])) $payload['settings']['commerceIntegrations']['webhook']['bearerToken'] = $current['settings']['commerceIntegrations']['webhook']['bearerToken'];
      $checkoutToken = (string)($payload['settings']['commerceIntegrations']['checkout']['bearerToken'] ?? '');
      if ($checkoutToken === '' && !empty($current['settings']['commerceIntegrations']['checkout']['bearerToken'])) $payload['settings']['commerceIntegrations']['checkout']['bearerToken'] = $current['settings']['commerceIntegrations']['checkout']['bearerToken'];
      $aiKey = (string)($payload['settings']['ai']['apiKey'] ?? '');
      if ($aiKey === '' && !empty($current['settings']['ai']['apiKey'])) $payload['settings']['ai']['apiKey'] = $current['settings']['ai']['apiKey'];
      foreach (['pexels','pixabay','unsplash'] as $provider) {
        $postedKey = (string)($payload['settings']['mediaSources'][$provider]['apiKey'] ?? '');
        if ($postedKey === '' && !empty($current['settings']['mediaSources'][$provider]['apiKey'])) $payload['settings']['mediaSources'][$provider]['apiKey'] = $current['settings']['mediaSources'][$provider]['apiKey'];
      }
      unset($payload['settings']['mailer']['smtp']['passwordSet'], $payload['settings']['commerceIntegrations']['webhook']['bearerTokenSet'], $payload['settings']['commerceIntegrations']['checkout']['bearerTokenSet'], $payload['settings']['ai']['apiKeySet']);
      foreach (['pexels','pixabay','unsplash'] as $provider) unset($payload['settings']['mediaSources'][$provider]['apiKeySet']);
      $cronKey = (string)($payload['settings']['abandonedCart']['cronKey'] ?? '');
      if ($cronKey === '' && !empty($current['settings']['abandonedCart']['cronKey'])) $payload['settings']['abandonedCart']['cronKey'] = $current['settings']['abandonedCart']['cronKey'];
      unset($payload['settings']['abandonedCart']['cronKeySet']);
    }
    $payload['settings']['abandonedCart']['carts'] = $current['settings']['abandonedCart']['carts'] ?? [];
    $payload['booking']['bookings'] = $current['booking']['bookings'] ?? [];
    if (!empty($current['settings']['forms']['submissions']) && empty($payload['settings']['forms']['submissions'])) {
      $payload['settings']['forms']['submissions'] = $current['settings']['forms']['submissions'];
    }
    if (!cms_save_data($payload)) cms_json(['ok' => false, 'error' => 'Could not save data.json'], 500);
    if (!empty(($payload['settings']['cache'] ?? [])['autoClearOnSave'])) cms_clear_cache();
    cms_sync_htaccess_files($payload);
    cms_json(['ok' => true, 'data' => cms_public_data(cms_load_data()), 'server' => cms_htaccess_status($payload), 'role' => cms_admin_role()]);
  }

  if ($api === 'mailer-tools') {
    cms_require_super_admin();
    $fresh = cms_load_data();
    if ($method === 'GET') {
      $ms = cms_mailer_settings($fresh);
      cms_json(['ok' => true, 'status' => [
        'mode' => $ms['mode'] ?? 'mail',
        'mailFunction' => function_exists('mail'),
        'openssl' => extension_loaded('openssl'),
        'sockets' => function_exists('stream_socket_client'),
        'smtpConfigured' => !empty($ms['smtp']['host']),
        'passwordSet' => !empty($ms['smtp']['password']),
        'logWritable' => is_writable(__DIR__) || (is_file(MAIL_LOG_FILE) && is_writable(MAIL_LOG_FILE)),
      ], 'logs' => cms_mail_logs(120)]);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    $action = (string)($payload['action'] ?? 'status');
    if ($action === 'connection') cms_json(cms_test_mailer_connection($fresh));
    if ($action === 'send-test') {
      $to = trim((string)($payload['to'] ?? ''));
      if (!filter_var($to, FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'message' => 'Enter a valid test email address.'], 422);
      $ok = cms_send_mail($to, 'LPCMS mailer test', "This is an LPCMS mailer test.\n\nTime: " . date('c') . "\nMode: " . (cms_mailer_settings($fresh)['mode'] ?? 'mail'), $fresh);
      cms_json(['ok' => $ok, 'message' => $ok ? 'Test email accepted by the configured mailer.' : 'Test email failed. Check the Mailer logs below.', 'logs' => cms_mail_logs(120)], $ok ? 200 : 502);
    }
    if ($action === 'clear-logs') {
      if (is_file(MAIL_LOG_FILE)) @file_put_contents(MAIL_LOG_FILE, '', LOCK_EX);
      cms_json(['ok' => true, 'message' => 'Mailer logs cleared.', 'logs' => []]);
    }
    cms_json(['ok' => false, 'message' => 'Unknown mailer tool action.'], 422);
  }


  if ($api === 'media-search') {
    cms_require_admin();
    $provider=(string)($_GET['provider']??'openverse'); $q=(string)($_GET['q']??''); $page=(int)($_GET['page']??1);
    try { cms_json(['ok'=>true,'provider'=>$provider,'results'=>cms_media_search(cms_load_data(),$provider,$q,$page)]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'media-import' && $method === 'POST') {
    cms_require_admin();
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $targetKey=(string)($payload['target']??'uploads');
    $targets=['uploads'=>UPLOAD_DIR,'gallery'=>GALLERY_DIR,'img'=>IMG_DIR]; if(!isset($targets[$targetKey])) cms_json(['ok'=>false,'error'=>'Invalid media target.'],422);
    $candidates=array_values(array_unique(array_filter([
      trim((string)($payload['url']??'')),
      trim((string)($payload['thumb']??'')),
    ],fn($v)=>$v!=='')));
    if(!$candidates) cms_json(['ok'=>false,'error'=>'No image URL was supplied.'],422);
    $remote=null; $downloadErrors=[];
    try {
      foreach($candidates as $candidate){
        try { $remote=cms_remote_image_bytes($candidate); break; }
        catch(Throwable $downloadError){ $downloadErrors[]=$downloadError->getMessage(); }
      }
      if(!$remote) throw new RuntimeException('Could not download the selected image. '.implode(' | ',array_values(array_unique($downloadErrors))));
      $name=trim((string)($payload['title']??''))?:basename((string)parse_url($remote['url'],PHP_URL_PATH))?:'imported-image';
      $mediaData=cms_load_data(); $resize=cms_media_resize_options($mediaData,$payload);
      $stored=cms_store_image_as_avif($remote['tmp'],$name,$targets[$targetKey],$resize);
      $message='Free media imported, scaled to the selected box, and converted to AVIF.';
      @unlink($remote['tmp']); $remote=null;
      $asset=cms_asset_public_url($targets[$targetKey],$stored['name']);
      cms_json(['ok'=>true,'url'=>$asset,'format'=>$stored['format'],'width'=>$stored['width'],'height'=>$stored['height'],'bytes'=>$stored['bytes'],'message'=>$message,'credit'=>['creator'=>(string)($payload['creator']??''),'source'=>(string)($payload['source']??''),'license'=>(string)($payload['license']??''),'licenseUrl'=>(string)($payload['licenseUrl']??''),'landing'=>(string)($payload['landing']??'')]]);
    } catch(Throwable $e){ if(is_array($remote)&&!empty($remote['tmp']))@unlink($remote['tmp']); cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'icon-search') {
    cms_require_admin(); $q=trim((string)($_GET['q']??'')); $prefix=trim((string)($_GET['prefix']??'')); if($q==='')cms_json(['ok'=>true,'icons'=>[]]);
    try { $url='https://api.iconify.design/search?query='.rawurlencode($q).'&limit=72'.($prefix!==''?'&prefix='.rawurlencode($prefix):''); $j=cms_http_json_request($url); cms_json(['ok'=>true,'icons'=>array_values(array_slice($j['icons']??[],0,72))]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'ai-generate' && $method === 'POST') {
    cms_require_admin(); $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_ai_generate(cms_load_data(),(string)($payload['prompt']??''),(string)($payload['context']??''),(string)($payload['tone']??''))); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }


  if ($api === 'demo-intake' && $method === 'POST') {
    if(!cms_rate_limit_consume('demo-intake',6,3600)) cms_json(['ok'=>false,'error'=>'Too many demo requests. Please try again later.'],429);
    $fresh = cms_load_data();
    if (empty($fresh['demoIntake']['enabled'])) cms_json(['ok'=>false,'error'=>'Demo requests are not enabled.'],422);
    if (!empty($_POST['_hp'])) cms_json(['ok'=>true,'message'=>(string)($fresh['demoIntake']['thankYou']??'Thank you.')]);
    if (!cms_verify_hcaptcha($fresh,(string)($_POST['hcaptcha']??''))) cms_json(['ok'=>false,'error'=>'Captcha verification failed.'],422);
    $plan = (string)($_POST['plan']??'premium'); if(!in_array($plan,['premium','premium-pro'],true))$plan='premium';
    $currency = strtoupper((string)($_POST['currency']??'GBP')); if(!in_array($currency,['GBP','EUR'],true))$currency='GBP';
    $entry = [
      'id'=>'DEMO-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,8)),
      'createdAt'=>date('c'),'status'=>'pending','plan'=>$plan,'planTitle'=>$plan==='premium-pro'?'Premium Pro':'Premium','currency'=>$currency,
      'name'=>cms_demo_clean_text($_POST['name']??'',120),'email'=>strtolower(cms_demo_clean_text($_POST['email']??'',160)),'phone'=>cms_demo_clean_text($_POST['phone']??'',80),
      'company'=>cms_demo_clean_text($_POST['company']??'',120),'industry'=>cms_demo_clean_text($_POST['industry']??'',120),'tagline'=>cms_demo_clean_text($_POST['tagline']??'',180),
      'about'=>cms_demo_clean_text($_POST['about']??'',2500),'services'=>cms_demo_clean_text($_POST['services']??'',3000),'products'=>cms_demo_clean_text($_POST['products']??'',3000),
      'address'=>cms_demo_clean_text($_POST['address']??'',300),'existingWebsite'=>cms_demo_clean_text($_POST['existingWebsite']??'',300),'preferredDomain'=>cms_demo_clean_text($_POST['preferredDomain']??'',180),
      'language'=>cms_demo_clean_text($_POST['language']??'English',80),'goals'=>cms_demo_clean_text($_POST['goals']??'',1500),'primaryColor'=>cms_demo_clean_text($_POST['primaryColor']??'',20),
      'style'=>cms_demo_clean_text($_POST['style']??'clean',40),'chatChoice'=>cms_demo_clean_text($_POST['chatChoice']??'chatbot',20),'whatsapp'=>cms_demo_clean_text($_POST['whatsapp']??'',80),
      'bookingNeeded'=>!empty($_POST['bookingNeeded']),'socials'=>cms_demo_clean_text($_POST['socials']??'',1200),'logo'=>'',
    ];
    if ($entry['company']==='' || $entry['name']==='') cms_json(['ok'=>false,'error'=>'Business name and contact name are required.'],422);
    if (!filter_var($entry['email'],FILTER_VALIDATE_EMAIL)) cms_json(['ok'=>false,'error'=>'Valid email required.'],422);
    if ($entry['about']==='' || $entry['services']==='') cms_json(['ok'=>false,'error'=>'Business description and services are required.'],422);
    if (empty($_POST['consent'])) cms_json(['ok'=>false,'error'=>'Demo preparation consent is required.'],422);
    if ($entry['chatChoice']==='whatsapp' && $entry['whatsapp']==='') cms_json(['ok'=>false,'error'=>'WhatsApp number is required for the WhatsApp chat option.'],422);
    if (!empty($_FILES['logo']) && (int)($_FILES['logo']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE) {
      $f=$_FILES['logo']; if((int)($f['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) cms_json(['ok'=>false,'error'=>'Logo upload did not complete.'],422);
      if((int)($f['size']??0)<=0 || (int)$f['size']>IMAGE_MAX_UPLOAD_BYTES) cms_json(['ok'=>false,'error'=>'Logo must be between 1 byte and 20MB.'],413);
      try { $stored=cms_store_image_as_avif($f['tmp_name'],(string)($f['name']??'logo'),IMG_DIR,cms_media_resize_options($fresh,['boxPreset'=>'logo'])); }
      catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
      $entry['logo']=cms_asset_public_url(IMG_DIR,$stored['name']);
    }
    $entry['draft']=cms_demo_build_draft($entry,$fresh);
    if(!isset($fresh['settings']['demoIntakeAdmin']['requests'])||!is_array($fresh['settings']['demoIntakeAdmin']['requests']))$fresh['settings']['demoIntakeAdmin']['requests']=[];
    $fresh['settings']['demoIntakeAdmin']['requests'][]=$entry;
    if(count($fresh['settings']['demoIntakeAdmin']['requests'])>250)$fresh['settings']['demoIntakeAdmin']['requests']=array_slice($fresh['settings']['demoIntakeAdmin']['requests'],-250);
    if(!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not save the demo request.'],500);
    $notify=trim((string)($fresh['settings']['demoIntakeAdmin']['notifyEmail']??'')); if($notify==='')$notify=trim((string)($fresh['settings']['forms']['emailTo']??'')); if($notify==='')$notify=BREAKGLASS_ADMIN_EMAIL;
    foreach(preg_split('/[;,]+/',$notify)?:[] as $to){$to=trim($to);if(filter_var($to,FILTER_VALIDATE_EMAIL))cms_send_mail($to,'New website demo request - '.$entry['company'],"Reference: {$entry['id']}\nClient: {$entry['name']}\nEmail: {$entry['email']}\nPlan: {$entry['planTitle']} ({$entry['currency']})\nBusiness: {$entry['company']}\n\nOpen Super Admin > Demo requests to review, approve and build the draft.",$fresh);}
    cms_json(['ok'=>true,'message'=>(string)($fresh['demoIntake']['thankYou']??'Thank you. Your website draft was created for review.'),'requestId'=>$entry['id']]);
  }

  if ($api === 'demo-requests') {
    cms_require_super_admin();
    $fresh=cms_load_data(); $requests=$fresh['settings']['demoIntakeAdmin']['requests']??[];
    if($method==='GET') cms_json(['ok'=>true,'requests'=>array_values($requests)]);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $action=(string)($payload['action']??''); $id=(string)($payload['id']??'');
    $index=null; foreach($requests as $i=>$r){if((string)($r['id']??'')===$id){$index=$i;break;}} if($index===null)cms_json(['ok'=>false,'error'=>'Demo request not found.'],404);
    try{
      if($action==='approve'){$request=cms_demo_apply_request($fresh,$index);if(!cms_save_data($fresh))throw new RuntimeException('Could not save approved website draft.');cms_clear_cache();cms_json(['ok'=>true,'message'=>'Demo approved and generated content loaded into the current website.','request'=>$request,'requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests']??[]),'data'=>cms_public_data($fresh)]);}
      if($action==='bill'){$request=cms_demo_send_bill($fresh,$index);if(!cms_save_data($fresh))throw new RuntimeException('Billing email was sent but the billing status could not be saved.');cms_json(['ok'=>true,'message'=>'Billing email sent to the client.','request'=>$request,'requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests']??[])]);}
      if($action==='reject'){$fresh['settings']['demoIntakeAdmin']['requests'][$index]['status']='rejected';$fresh['settings']['demoIntakeAdmin']['requests'][$index]['rejectedAt']=date('c');cms_save_data($fresh);cms_json(['ok'=>true,'message'=>'Demo request marked as rejected.','requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests'])]);}
      if($action==='delete'){array_splice($fresh['settings']['demoIntakeAdmin']['requests'],$index,1);cms_save_data($fresh);cms_json(['ok'=>true,'message'=>'Demo request deleted.','requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests'])]);}
      cms_json(['ok'=>false,'error'=>'Unknown demo request action.'],422);
    }catch(Throwable $e){cms_json(['ok'=>false,'error'=>$e->getMessage()],422);}
  }

  if ($api === 'abandoned-cart-recover' && $method === 'GET') {
    $token = trim((string)($_GET['token'] ?? ''));
    if ($token === '') cms_json(['ok'=>false,'error'=>'Recovery token required.'],422);
    $cart = cms_abandoned_cart_recover(cms_load_data(), $token);
    if (!$cart) cms_json(['ok'=>false,'error'=>'Saved cart was not found or is no longer available.'],404);
    cms_json(['ok'=>true,'cart'=>$cart['cart'] ?? [],'email'=>$cart['email'] ?? '']);
  }

  if ($api === 'abandoned-cart' && $method === 'POST') {
    if(!cms_rate_limit_consume('abandoned-cart',30,900)) cms_json(['ok'=>false,'error'=>'Too many cart updates. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    $fresh=cms_load_data();
    if(empty($fresh['settings']['abandonedCart']['enabled'])) cms_json(['ok'=>false,'error'=>'Cart recovery is not enabled.'],422);
    $action=(string)($payload['action']??'capture');
    $email=trim((string)($payload['email']??''));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) cms_json(['ok'=>false,'error'=>'Valid email required.'],422);
    if($action==='complete') cms_json(['ok'=>true,'result'=>cms_abandoned_cart_complete($fresh,$email)]);
    try {
      $result=cms_abandoned_cart_capture($fresh,$email,is_array($payload['items']??null)?$payload['items']:[],['shippingId'=>$payload['shippingId']??'','discountCode'=>$payload['discountCode']??'']);
      cms_json(['ok'=>true,'token'=>$result['token']??'','cart'=>$result['cart']??[]]);
    } catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'abandoned-cart-tools') {
    cms_require_super_admin();
    $fresh=cms_load_data();
    $carts=$fresh['settings']['abandonedCart']['carts']??[];
    if($method==='GET') cms_json(['ok'=>true,'enabled'=>!empty($fresh['settings']['abandonedCart']['enabled']),'count'=>count($carts),'carts'=>array_values(array_slice(array_reverse($carts),0,50))]);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    $action=(string)($payload['action']??'process');
    if($action==='process') cms_json(['ok'=>true,'result'=>cms_process_abandoned_carts($fresh),'carts'=>$fresh['settings']['abandonedCart']['carts']??[]]);
    if($action==='clear-completed'){
      $fresh['settings']['abandonedCart']['carts']=array_values(array_filter($carts,fn($c)=>empty($c['completedAt'])));
      cms_save_data($fresh); cms_json(['ok'=>true,'message'=>'Completed recovered carts cleared.','carts'=>$fresh['settings']['abandonedCart']['carts']]);
    }
    cms_json(['ok'=>false,'error'=>'Unknown cart recovery action.'],422);
  }

  if ($api === 'abandoned-cart-cron' && $method === 'GET') {
    $fresh=cms_load_data();
    $saved=(string)($fresh['settings']['abandonedCart']['cronKey']??'');
    $key=(string)($_GET['key']??'');
    if($saved==='' || $key==='' || !hash_equals($saved,$key)) cms_json(['ok'=>false,'error'=>'Invalid scheduler key.'],403);
    if(empty($fresh['settings']['abandonedCart']['enabled'])) cms_json(['ok'=>false,'error'=>'Cart recovery is disabled.'],422);
    cms_json(['ok'=>true,'result'=>cms_process_abandoned_carts($fresh)]);
  }

  if ($api === 'booking-slots' && $method === 'GET') {
    try { cms_json(['ok'=>true,'slots'=>cms_booking_slots(cms_load_data(),(string)($_GET['date']??''),(string)($_GET['serviceId']??''))]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'booking-create' && $method === 'POST') {
    if(!cms_rate_limit_consume('booking-create',12,900)) cms_json(['ok'=>false,'error'=>'Too many booking attempts. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $fresh=cms_load_data();
    if (!cms_verify_hcaptcha($fresh, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok'=>false,'error'=>'Captcha verification failed.'],422);
    try { cms_json(cms_booking_create($fresh,$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'chatbot' && $method === 'POST') {
    if(!cms_rate_limit_consume('chatbot',30,600)) cms_json(['ok'=>false,'error'=>'Too many chat messages. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_chatbot_reply(cms_load_data(),$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'checkout-start' && $method === 'POST') {
    if(!cms_rate_limit_consume('checkout-start',20,900)) cms_json(['ok'=>false,'error'=>'Too many checkout attempts. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_start_checkout(cms_load_data(),$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'sync-htaccess' && $method === 'POST') {
    cms_require_super_admin();
    $fresh = cms_load_data();
    $result = cms_sync_htaccess_files($fresh);
    cms_json(['ok' => !empty($result['ok']), 'message' => !empty($result['ok']) ? 'Root and assets .htaccess files are synced.' : 'Could not sync one or more .htaccess files.', 'result' => $result, 'server' => cms_htaccess_status($fresh)]);
  }

  if ($api === 'upload' && $method === 'POST') {
    cms_require_admin();
    $targets = ['uploads' => UPLOAD_DIR, 'gallery' => GALLERY_DIR, 'img' => IMG_DIR, 'docs' => DOCS_DIR, 'video' => VIDEO_DIR];
    $targetKey = (string)($_POST['target'] ?? 'uploads');
    if (!isset($targets[$targetKey])) cms_json(['ok' => false, 'error' => 'Invalid upload target'], 422);
    $target = $targets[$targetKey];
    if (empty($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) cms_json(['ok' => false, 'error' => 'No file uploaded'], 422);
    $f = $_FILES['file'];
    $uploadError = (int)($f['error'] ?? UPLOAD_ERR_OK);
    if ($uploadError !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) cms_json(['ok' => false, 'error' => 'The upload did not complete.'], 422);
    if (($f['size'] ?? 0) <= 0) cms_json(['ok' => false, 'error' => 'The uploaded file is empty.'], 422);
    $maxBytes = $targetKey === 'video' ? VIDEO_MAX_UPLOAD_BYTES : IMAGE_MAX_UPLOAD_BYTES;
    if (($f['size'] ?? 0) > $maxBytes) cms_json(['ok' => false, 'error' => $targetKey === 'video' ? 'Maximum video upload size is 50MB.' : 'Maximum upload size is 20MB.'], 413);

    if ($targetKey === 'video') {
      try { $stored = cms_store_hero_video($f['tmp_name'], (string)($f['name'] ?? 'hero-video.mp4'), $target); }
      catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
      $url=cms_asset_public_url($target,$stored['name']);
      cms_json(['ok'=>true,'url'=>$url,'name'=>$stored['name'],'format'=>$stored['format'],'bytes'=>$stored['bytes'],'optimized'=>$stored['optimized'],'message'=>$stored['optimized']?'Video optimized to HD H.264 MP4.':'Video uploaded. Install ffmpeg for automatic HD optimization.']);
    }

    if ($targetKey === 'docs') {
      if (cms_detect_mime($f['tmp_name']) !== 'application/pdf') cms_json(['ok' => false, 'error' => 'Only PDF files are allowed in documents.'], 422);
      $name = date('Ymd-His') . '-' . cms_slug(pathinfo((string)$f['name'], PATHINFO_FILENAME)) . '-' . bin2hex(random_bytes(3)) . '.pdf';
      $dest = $target . '/' . $name;
      if (!move_uploaded_file($f['tmp_name'], $dest)) cms_json(['ok' => false, 'error' => 'Upload failed'], 500);
      @chmod($dest, 0644);
      $url = cms_asset_public_url($target,$name);
      cms_json(['ok' => true, 'url' => $url, 'name' => $name, 'format' => 'pdf', 'bytes' => (int)filesize($dest)]);
    }

    $mediaData=cms_load_data();
    $resize=cms_media_resize_options($mediaData,$_POST);
    try {
      $stored = cms_store_image_as_avif($f['tmp_name'], (string)($f['name'] ?? 'image'), $target, $resize);
      $message = 'Image scaled to ' . $resize['boxWidth'] . '×' . $resize['boxHeight'] . ' box and converted to AVIF.';
    } catch (RuntimeException $e) {
      cms_json(['ok' => false, 'error' => $e->getMessage()], 422);
    }
    $url = cms_asset_public_url($target,$stored['name']);
    cms_json([
      'ok' => true,
      'url' => $url,
      'name' => $stored['name'],
      'format' => $stored['format'],
      'width' => $stored['width'],
      'height' => $stored['height'],
      'bytes' => $stored['bytes'],
      'convertedBy' => $stored['method'],
      'message' => $message,
    ]);
  }

  if ($api === 'form' && $method === 'POST') {
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    if (!cms_verify_hcaptcha($data, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok' => false, 'error' => 'Captcha verification failed.'], 422);
    $entry = [
      'time' => date('c'),
      'type' => substr((string)($payload['type'] ?? 'contact'), 0, 40),
      'name' => substr(strip_tags((string)($payload['name'] ?? '')), 0, 120),
      'email' => substr(strip_tags((string)($payload['email'] ?? '')), 0, 160),
      'phone' => substr(strip_tags((string)($payload['phone'] ?? '')), 0, 80),
      'message' => substr(strip_tags((string)($payload['message'] ?? '')), 0, 3000),
    ];
    if (!filter_var($entry['email'], FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'error' => 'Valid email required.'], 422);
    if (!empty($data['settings']['forms']['storeSubmissions'])) {
      $data['settings']['forms']['submissions'][] = $entry;
      cms_save_data($data);
    }
    $toRaw = trim((string)($data['settings']['forms']['emailTo'] ?? ''));
    if ($toRaw === '') $toRaw = BREAKGLASS_ADMIN_EMAIL;
    $sentCount = 0;
    foreach (preg_split('/[;,]+/', $toRaw) ?: [] as $recipient) {
      $recipient = trim((string)$recipient);
      if ($recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        if (cms_send_mail($recipient, 'New website form submission', "Type: {$entry['type']}\nName: {$entry['name']}\nEmail: {$entry['email']}\nPhone: {$entry['phone']}\n\n{$entry['message']}", $data)) $sentCount++;
      }
    }
    $isOrder = str_contains(strtolower($entry['type']), 'order');
    $webhook = $isOrder ? cms_post_commerce_webhook($data, ['event' => 'order.submitted', 'submission' => $entry]) : ['skipped' => true];
    if ($isOrder && !empty($data['settings']['abandonedCart']['enabled'])) cms_abandoned_cart_complete($data, $entry['email']);
    cms_json(['ok' => true, 'message' => $sentCount > 0 ? 'Thank you. Your request was sent.' : 'Thank you. Your request was saved, but email delivery needs mail/SMTP configuration.', 'commerceWebhook' => $webhook]);
  }

  if ($api === 'maintenance' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    $action = (string)($payload['action'] ?? 'stats');
    if ($action === 'clear-cache') {
      $deleted = cms_clear_cache();
      cms_json(['ok' => true, 'message' => 'Cache cleared. Deleted ' . $deleted . ' file(s).', 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    if ($action === 'stats') {
      cms_json(['ok' => true, 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    if ($action === 'cleanup-old-cache') {
      $settings = cms_cache_settings(cms_load_data());
      $maxAge = max(1, (int)($settings['maxAgeDays'] ?? 14)) * 86400;
      $deleted = 0;
      foreach (glob(CACHE_DIR . '/*') ?: [] as $file) {
        if (is_file($file) && filemtime($file) < time() - $maxAge && @unlink($file)) $deleted++;
      }
      cms_json(['ok' => true, 'message' => 'Old cache cleanup complete. Deleted ' . $deleted . ' file(s).', 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    cms_json(['ok' => false, 'error' => 'Unknown maintenance action'], 422);
  }

  if ($api === 'search') {
    $q = strtolower(trim((string)($_GET['q'] ?? '')));
    $out = [];
    if ($q !== '') {
      foreach (['services','products','blog','gallery'] as $group) {
        foreach (($data[$group]['items'] ?? []) as $i => $item) {
          $text = strtolower(json_encode($item, JSON_UNESCAPED_UNICODE));
          if (str_contains($text, $q)) {
            $out[] = [
              'group' => $group,
              'title' => $item['title'] ?? $item['alt'] ?? ucfirst($group) . ' item',
              'text' => $item['excerpt'] ?? $item['text'] ?? $item['alt'] ?? '',
              'href' => '#' . $group,
              'index' => $i
            ];
          }
        }
      }
    }
    cms_json(['ok' => true, 'results' => $out]);
  }

  cms_json(['ok' => false, 'error' => 'Unknown API'], 404);
}

cms_try_serve_page_cache($data);
cms_start_page_cache($data);
$meta = cms_find_meta($data);
$base = cms_base_url($data);
$metaImage = (string)($meta['image'] ?? '');
if ($metaImage && !preg_match('/^https?:\/\//', $metaImage)) $metaImage = rtrim($base, '/') . '/' . ltrim($metaImage, '/');
$canonical = (string)($meta['canonical'] ?? '') ?: (!empty($meta['path']) ? $base . $meta['path'] : $base . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
$libs = $data['settings']['libraries'] ?? [];
$activeThemeAssets = cms_active_theme_assets($data);
$activeThemeId = (string)($activeThemeAssets['id'] ?? 'lpcms-default');
$currentLang = cms_current_language($data);
$routeInfo = cms_find_route_item($data);
$i18nPack = cms_i18n_pack();
?>
<!doctype html>
<html lang="<?= cms_e($currentLang) ?>"
      data-lpcms-base="<?= cms_e(rtrim(cms_route_prefix(), '/')) ?>"
      data-lpcms-server-lang="<?= cms_e($currentLang) ?>"
      data-lpcms-default-lang="<?= cms_e(strtolower((string)($data['settings']['defaultLang'] ?? 'en'))) ?>"
      data-lpcms-langs="<?= cms_e(implode(',', array_values(array_filter(array_map(static fn($x) => strtolower((string)($x['code'] ?? '')), cms_language_options()))))) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="<?= cms_e($meta['robots'] ?? 'index,follow') ?>">
  <title><?= cms_e($meta['title'] ?? ($data['settings']['siteName'] ?? 'Flat CMS')) ?></title>
  <meta name="description" content="<?= cms_e($meta['description'] ?? '') ?>">
  <meta name="keywords" content="<?= cms_e($meta['keywords'] ?? '') ?>">
  <link rel="canonical" href="<?= cms_e($canonical) ?>">
  <?php foreach (cms_language_options() as $languageOption):
    $altLang = strtolower((string)($languageOption['code'] ?? ''));
    if ($altLang === '') continue;
    $altPath = !empty($routeInfo['item']) ? cms_pretty_path((string)$routeInfo['group'], (string)$routeInfo['slug'], $data, $altLang) : cms_language_home_path($data, $altLang);
    $altUrl = rtrim($base, '/') . $altPath;
  ?><link rel="alternate" hreflang="<?= cms_e($altLang) ?>" href="<?= cms_e($altUrl) ?>">
  <?php endforeach; ?>
  <link rel="alternate" hreflang="x-default" href="<?= cms_e(rtrim($base, '/') . (!empty($routeInfo['item']) ? cms_pretty_path((string)$routeInfo['group'], (string)$routeInfo['slug'], $data, $data['settings']['defaultLang'] ?? 'en') : cms_language_home_path($data, $data['settings']['defaultLang'] ?? 'en'))) ?>">
  <link rel="icon" href="<?= cms_e(cms_favicon_href($data)) ?>">
  <link rel="shortcut icon" href="<?= cms_e(cms_favicon_href($data)) ?>">
  <meta property="og:title" content="<?= cms_e($meta['title'] ?? '') ?>">
  <meta property="og:description" content="<?= cms_e($meta['description'] ?? '') ?>">
  <meta property="og:type" content="<?= cms_e($meta['type'] ?? 'website') ?>">
  <meta property="og:url" content="<?= cms_e($canonical) ?>">
  <?php if ($metaImage): ?><meta property="og:image" content="<?= cms_e($metaImage) ?>"><?php endif; ?>
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= cms_e($meta['title'] ?? '') ?>">
  <meta name="twitter:description" content="<?= cms_e($meta['description'] ?? '') ?>">
  <?php if ($metaImage): ?><meta name="twitter:image" content="<?= cms_e($metaImage) ?>"><?php endif; ?>
  <?php $appSettings = cms_app_settings($data); if (!empty($appSettings['enabled'])): ?>
  <meta name="theme-color" content="<?= cms_e((string)$appSettings['themeColor']) ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="<?= cms_e((string)$appSettings['shortName']) ?>">
  <link rel="manifest" href="<?= cms_e(rtrim(cms_route_prefix(), '/') . '/manifest.webmanifest') ?>">
  <?php if (!empty($appSettings['appleIcon']) || !empty($appSettings['icon'])): ?><link rel="apple-touch-icon" href="<?= cms_e((string)($appSettings['appleIcon'] ?? $appSettings['icon'])) ?>"><?php endif; ?>
  <?php endif; ?>
  <?php if (!empty($libs['bootstrap'])): ?><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['bootstrapIcons'])): ?><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['aos'])): ?><link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['swiper'])): ?><link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['glightbox'])): ?><link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet"><?php endif; ?>
  <link rel="stylesheet" href="<?= cms_e(rtrim(cms_route_prefix(), '/')) ?>/assets/css/lpcms-app.css?v=<?= rawurlencode(CMS_VERSION) ?>">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= cms_e(rtrim(cms_route_prefix(), '/')) ?>/assets/theme.css?v=<?= rawurlencode((string)(file_exists(DATA_FILE) ? filemtime(DATA_FILE) : time())) ?>">
  <?php foreach (($activeThemeAssets['css'] ?? []) as $themeCss): ?>
  <link rel="stylesheet" href="<?= cms_e($themeCss) ?>" data-lpcms-theme-asset="css">
  <?php endforeach; ?>
  <?php
    $orgId = rtrim($base, '/') . '/#organization';
    $orgEntity = [
      '@type' => 'Organization', '@id' => $orgId, 'name' => 'DigitalPulse',
      'url' => rtrim($base, '/') . '/',
      'description' => 'Custom WordPress plugins, PHP and React web apps, smart WooCommerce and e-commerce development.',
      'logo' => ['@type' => 'ImageObject', 'url' => rtrim($base, '/') . '/assets/img/digitalpulse-og.png'],
      'sameAs' => [
        'https://clutch.co/profile/digital-pulse-1',
        'https://www.facebook.com/raivis.kalnins',
        'https://www.linkedin.com/in/rkalnins/',
        'https://github.com/raivis-kalnins'
      ]
    ];
    $ld = [
      '@context' => 'https://schema.org',
      '@graph' => [
        $orgEntity,
        ['@type' => 'WebSite', '@id' => rtrim($base, '/') . '/#website',
          'name' => 'DigitalPulse', 'url' => rtrim($base, '/') . '/', 'publisher' => ['@id' => $orgId]],
        ['@type' => 'Service', 'name' => 'Custom WordPress plugin development',
          'description' => 'Bespoke plugins, Gutenberg blocks, ACF integrations and WordPress workflow automation.',
          'provider' => ['@id' => $orgId], 'areaServed' => ['United Kingdom','Latvia','Lithuania','Estonia','Sweden','Norway','Denmark','Finland','Iceland']],
        ['@type' => 'Service', 'name' => 'Custom web application development',
          'description' => 'PHP and React portals, dashboards, calculators and reporting tools.',
          'provider' => ['@id' => $orgId]],
        ['@type' => 'Service', 'name' => 'Smart e-commerce and WooCommerce development',
          'description' => 'WooCommerce catalogues, advanced filtering, B2B pricing, checkout and integrations.',
          'provider' => ['@id' => $orgId]]
      ]
    ];
    if (($meta['type'] ?? '') === 'article' && !empty($meta['item'])) {
      $ld = ['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$meta['item']['title'] ?? $meta['title'],'description'=>$meta['description'] ?? '','datePublished'=>$meta['item']['date'] ?? date('c'),'image'=>$metaImage ?: null,'mainEntityOfPage'=>$canonical];
    } elseif (($meta['type'] ?? '') === 'product' && !empty($meta['item'])) {
      $productPrice = $meta['item']['price'] ?? 0;
      $productCurrency = $meta['item']['currency'] ?? ($data['products']['currency'] ?? 'EUR');
      $ld = ['@context'=>'https://schema.org','@type'=>'Product','name'=>$meta['item']['title'] ?? $meta['title'],'description'=>$meta['description'] ?? '','sku'=>$meta['item']['sku'] ?? null,'image'=>$metaImage ?: null,'offers'=>['@type'=>'Offer','price'=>(string)$productPrice,'priceCurrency'=>$productCurrency,'availability'=>'https://schema.org/InStock']];
    }
  ?>
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body class="<?= !empty($activeThemeAssets['imported']) ? 'lpcms-imported-theme ' : '' ?>lpcms-theme-<?= cms_e(cms_slug($activeThemeId)) ?>">
  <div id="root"></div>
  <?php
    $seoHero = $data['hero']['slides'][0] ?? [];
    $seoHomeTitle = is_array($seoHero) ? (string)cms_localized_value($data, $seoHero, 'title', $currentLang, $meta['title'] ?? '') : (string)($meta['title'] ?? '');
    $seoHomeText = is_array($seoHero) ? (string)cms_localized_value($data, $seoHero, 'text', $currentLang, $meta['description'] ?? '') : (string)($meta['description'] ?? '');
    $seoWp = $data['wpThemes'] ?? [];
    $seoGallery = $data['gallery'] ?? [];
    $seoApps = $data['apps'] ?? [];
    $seoServices = $data['services'] ?? [];
  ?>
  <noscript>
    <main class="seo-noscript">
      <?php if (!empty($routeInfo['item'])): ?>
        <h1><?= cms_e((string)cms_localized_value($data, $routeInfo['item'], 'title', $currentLang, $meta['title'] ?? '')) ?></h1>
        <p><?= cms_e((string)($meta['description'] ?? '')) ?></p>
      <?php else: ?>
        <h1><?= cms_e($seoHomeTitle) ?></h1>
        <p><?= cms_e(strip_tags($seoHomeText)) ?></p>
        <?php if (!empty($seoServices['enabled'])): ?>
          <section><h2><?= cms_e((string)cms_localized_value($data, $seoServices, 'title', $currentLang, 'Services')) ?></h2><ul>
            <?php foreach (array_slice($seoServices['items'] ?? [], 0, 8) as $seoService): ?><li><?= cms_e((string)cms_localized_value($data, $seoService, 'title', $currentLang, '')) ?></li><?php endforeach; ?>
          </ul></section>
        <?php endif; ?>
        <?php if (!empty($seoWp['enabled'])): ?>
          <section><h2><?= cms_e((string)cms_localized_value($data, $seoWp, 'title', $currentLang, 'WordPress websites')) ?></h2><p><?= cms_e((string)cms_localized_value($data, $seoWp, 'subtitle', $currentLang, '')) ?></p></section>
        <?php endif; ?>
        <?php if (!empty($seoGallery['enabled'])): ?>
          <section><h2><?= cms_e((string)cms_localized_value($data, $seoGallery, 'title', $currentLang, 'Portfolio')) ?></h2><ul>
            <?php foreach (array_slice($seoGallery['items'] ?? [], 0, 12) as $seoProject): ?>
              <li><?php if (!empty($seoProject['url'])): ?><a href="<?= cms_e((string)$seoProject['url']) ?>"><?php endif; ?><?= cms_e((string)cms_localized_value($data, $seoProject, 'title', $currentLang, 'Project')) ?><?php if (!empty($seoProject['url'])): ?></a><?php endif; ?></li>
            <?php endforeach; ?>
          </ul></section>
        <?php endif; ?>
        <?php if (!empty($seoApps['enabled'])): ?>
          <section><h2><?= cms_e((string)cms_localized_value($data, $seoApps, 'title', $currentLang, 'DigitalPulse apps')) ?></h2><p><?= cms_e((string)cms_localized_value($data, $seoApps, 'subtitle', $currentLang, '')) ?></p><ul>
            <?php foreach (($seoApps['items'] ?? []) as $seoApp): ?>
              <?php $appUrl = (string)($seoApp['url'] ?? ''); if (!preg_match('~^https://[^[:space:]]+$~i', $appUrl)) continue; ?>
              <li><a href="<?= cms_e($appUrl) ?>" target="_blank" rel="noopener noreferrer"><?= cms_e((string)cms_localized_value($data, $seoApp, 'title', $currentLang, 'App')) ?></a> — <?= cms_e((string)cms_localized_value($data, $seoApp, 'text', $currentLang, '')) ?></li>
            <?php endforeach; ?>
          </ul></section>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </noscript>
  <script src="https://cdn.jsdelivr.net/npm/react@18/umd/react.production.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.production.min.js" defer></script>
  <?php if (!empty($libs['bootstrap'])): ?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script><?php endif; ?>
  <?php if (!empty($libs['swiper'])): ?><script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script><?php endif; ?>
  <?php if (!empty($libs['aos'])): ?><script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script><?php endif; ?>

  <?php if (!empty($libs['glightbox'])): ?><script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js" defer></script><?php endif; ?>
  <?php if (!empty($data['settings']['hcaptcha']['enabled'])): ?><script src="https://js.hcaptcha.com/1/api.js" async defer></script><?php endif; ?>
  <?php if (!empty($libs['sassBuilder'])): ?><script src="https://cdn.jsdelivr.net/npm/sass.js@0.11.1/dist/sass.sync.js" defer></script><?php endif; ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous" defer></script>
  <?php foreach (($activeThemeAssets['js'] ?? []) as $themeJs): ?><script src="<?= cms_e($themeJs) ?>" data-lpcms-theme-asset="js" defer></script><?php endforeach; ?>
  <?php if (cms_is_admin()): ?>
  <script src="<?= cms_e(rtrim(cms_route_prefix(), '/')) ?>/?asset=app-js&amp;scope=admin&amp;v=<?= rawurlencode(CMS_VERSION) ?>" defer></script>
  <?php else: ?>
  <script src="<?= cms_e(rtrim(cms_route_prefix(), '/')) ?>/assets/js/lpcms-public.js?v=<?= rawurlencode(CMS_VERSION) ?>" defer></script>
  <?php endif; ?>
</body>
</html>
