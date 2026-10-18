<?php
/**
 * DigitalPulse location landing pages and lightweight SEO feeds.
 * Called only by index.php after CMS routing is initialized.
 * Edit locations-data.json to manage the markets/content.
 */
if (!defined('CMS_VERSION')) { http_response_code(403); exit; }

function dp_locations_data(): array {
    static $data = null;
    if ($data !== null) return $data;
    $path = __DIR__ . '/locations-data.json';
    $read = @file_get_contents($path);
    $decoded = $read !== false ? json_decode($read, true) : null;
    $data = is_array($decoded) ? $decoded : ['locations'=>[], 'languages'=>[]];
    return $data;
}
function dp_loc_escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function dp_loc_format(string $value, array $args=[]): string {
    return strtr($value, array_combine(array_map(static fn($k)=>'{'.$k.'}',array_keys($args)),array_values($args)) ?: []);
}
function dp_loc_name(array $loc,string $lang): string {
    return (string)($loc['localizedNames'][$lang]??$loc['name']??'');
}
function dp_loc_url(array $cms,string $lang,string $slug=''): string {
    return rtrim(cms_base_url($cms),'/') . cms_language_prefix($cms,$lang) . '/locations/' . ($slug!==''?rawurlencode($slug).'/':'');
}
function dp_loc_path(array $cms,string $lang,string $slug=''): string {
    return rtrim(cms_route_prefix(),'/') . cms_language_prefix($cms,$lang) . '/locations/' . ($slug!==''?rawurlencode($slug).'/':'');
}
function dp_loc_home(array $cms,string $lang,string $hash=''): string {
    return rtrim(cms_route_prefix(),'/') . cms_language_home_path($cms,$lang) . $hash;
}
/** Homepage navigation labels and assets reused on every location page. */
function dp_loc_nav_label(array $cms,string $lang,string $label): string {
    $source=dp_locations_data();
    $translated=$source['navigation'][$lang][$label]??'';
    return is_string($translated)&&$translated!=='' ? $translated : $label;
}
function dp_loc_menu_target(array $cms,string $lang,string $href): string {
    if($href==='#home'||$href==='/')return dp_loc_home($cms,$lang);
    if($href==='/locations/'||$href==='#locations')return dp_loc_path($cms,$lang);
    if(str_starts_with($href,'#'))return dp_loc_home($cms,$lang,$href);
    if(preg_match('#^https?://#i',$href))return $href;
    return dp_loc_home($cms,$lang);
}
function dp_loc_icon(string $name): string {
    $paths=[
      'search'=>'<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
      'menu'=>'<path d="M4 7h16M4 12h16M4 17h16"/>',
      'close'=>'<path d="M5 5l14 14M19 5 5 19"/>',
      'chevron'=>'<path d="m6 9 6 6 6-6"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['search']).'</svg>';
}
function dp_loc_flag(string $code): string {
    return ['en'=>'gb','lv'=>'lv','ru'=>'ru','fr'=>'fr','es'=>'es','de'=>'de','pl'=>'pl','lt'=>'lt','et'=>'ee','da'=>'dk','no'=>'no','sv'=>'se','fi'=>'fi','is'=>'is'][$code]??'gb';
}
function dp_loc_header(array $cms,array $locale,string $lang,string $canonical,string $title,string $description,string $image,array $ld,bool $isHub=false): void {
    $languageCodes=array_keys(dp_locations_data()['languages'] ?? []);
    $favicon=rtrim(cms_route_prefix(),'/').'/assets/img/favicon-32.png';
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, max-age=600, stale-while-revalidate=600');
    header('X-Content-Type-Options: nosniff');
    $imageUrl = rtrim(cms_base_url($cms),'/') . $image;
    $titleFull=$title.' | DigitalPulse';
    $home=dp_loc_home($cms,$lang);
    $currentSlug=(string)($GLOBALS['DP_LOC_CURRENT_SLUG']??'');
    $source=dp_locations_data();
    $menu=[];
    foreach(($cms['header']['menu']??[]) as $entry){
        if(!is_array($entry))continue;
        $label=(string)($entry['label']??'');
        $label=(string)($entry['i18n'][$lang]['label']??dp_loc_nav_label($cms,$lang,$label));
        $children=[];
        foreach(($entry['children']??[]) as $child){
            if(!is_array($child))continue;
            $childLabel=(string)($child['i18n'][$lang]['label']??dp_loc_nav_label($cms,$lang,(string)($child['label']??'')));
            $children[]=['label'=>$childLabel,'href'=>dp_loc_menu_target($cms,$lang,(string)($child['href']??'/locations/'))];
        }
        $menu[]=['label'=>$label,'href'=>dp_loc_menu_target($cms,$lang,(string)($entry['href']??'#home')),'children'=>$children];
    }
    if(!$menu){
        $menu=[['label'=>$locale['home'],'href'=>$home,'children'=>[]],['label'=>$locale['services'],'href'=>dp_loc_home($cms,$lang,'#services'),'children'=>[['label'=>$locale['locations'],'href'=>dp_loc_path($cms,$lang)]]],['label'=>$locale['portfolio'],'href'=>dp_loc_home($cms,$lang,'#gallery'),'children'=>[]],['label'=>$locale['contact'],'href'=>dp_loc_home($cms,$lang,'#contact'),'children'=>[]]];
    }
    $langNames=array_column($cms['settings']['languages']??[], 'name', 'code');
    $searchLabel=dp_loc_nav_label($cms,$lang,'Search');
    $menuLabel=dp_loc_nav_label($cms,$lang,'Open menu');
    $closeLabel=dp_loc_nav_label($cms,$lang,'Close menu');
    $searchPrompt=dp_loc_nav_label($cms,$lang,'Search locations');
    $logo=rtrim(cms_route_prefix(),'/').'/assets/img/digitalpulse-logo.svg';
    $assets=rtrim(cms_route_prefix(),'/').'/assets/img/flags/';
    ?>
<!doctype html>
<html lang="<?= dp_loc_escape($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="index,follow,max-image-preview:large">
<title><?= dp_loc_escape($titleFull) ?></title>
<meta name="description" content="<?= dp_loc_escape($description) ?>">
<link rel="canonical" href="<?= dp_loc_escape($canonical) ?>">
<?php foreach($languageCodes as $altLang): ?>
<link rel="alternate" hreflang="<?= dp_loc_escape($altLang) ?>" href="<?= dp_loc_escape(dp_loc_url($cms,$altLang,$currentSlug)) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= dp_loc_escape(dp_loc_url($cms,'en',$currentSlug)) ?>">
<link rel="icon" href="<?= dp_loc_escape($favicon) ?>" sizes="32x32" type="image/png">
<meta property="og:type" content="website">
<meta property="og:site_name" content="DigitalPulse">
<meta property="og:url" content="<?= dp_loc_escape($canonical) ?>">
<meta property="og:title" content="<?= dp_loc_escape($titleFull) ?>">
<meta property="og:description" content="<?= dp_loc_escape($description) ?>">
<meta property="og:image" content="<?= dp_loc_escape($imageUrl) ?>">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="675">
<meta property="og:image:alt" content="<?= dp_loc_escape(dp_loc_format($locale['coverAlt'],['location'=>$isHub?'DigitalPulse':($GLOBALS['DP_LOC_CURRENT_NAME']??'DigitalPulse')])) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= dp_loc_escape($titleFull) ?>">
<meta name="twitter:description" content="<?= dp_loc_escape($description) ?>">
<meta name="twitter:image" content="<?= dp_loc_escape($imageUrl) ?>">
<link rel="preload" as="style" href="<?= dp_loc_escape(rtrim(cms_route_prefix(),'/').'/assets/css/location-pages.css?v=3') ?>">
<link rel="stylesheet" href="<?= dp_loc_escape(rtrim(cms_route_prefix(),'/').'/assets/css/location-pages.css?v=3') ?>">
<script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@graph'=>$ld],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
</head>
<body class="dp-loc-page">
<a class="dp-skip" href="#content"><?= dp_loc_escape($locale['skip']) ?></a>
<header class="dp-topbar"><div class="dp-wrap dp-topbar-inner">
<a href="<?= dp_loc_escape($home) ?>" class="dp-brand" aria-label="DigitalPulse"><img src="<?= dp_loc_escape($logo) ?>" width="218" height="52" alt="DigitalPulse"></a>
<nav class="dp-topnav" aria-label="<?= dp_loc_escape(dp_loc_nav_label($cms,$lang,'Main navigation')) ?>">
<?php foreach($menu as $nav): ?><div class="dp-nav-entry">
<a class="dp-nav-link" href="<?= dp_loc_escape($nav['href']) ?>"><?= dp_loc_escape($nav['label']) ?><?php if($nav['children']): ?><span class="dp-nav-caret" aria-hidden="true">⌄</span><?php endif; ?></a>
<?php if($nav['children']): ?><div class="dp-nav-dropdown"><?php foreach($nav['children'] as $child): ?><a href="<?= dp_loc_escape($child['href']) ?>"<?= $child['href']===dp_loc_path($cms,$lang)?' aria-current="page"':'' ?>><?= dp_loc_escape($child['label']) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></div><?php endif; ?>
</div><?php endforeach; ?>
</nav>
<div class="dp-header-actions">
<button type="button" class="dp-header-icon" id="dp-search-open" aria-label="<?= dp_loc_escape($searchLabel) ?>" title="<?= dp_loc_escape($searchLabel) ?>"><?= dp_loc_icon('search') ?></button>
<div class="dp-languages" id="dp-languages">
<button type="button" class="dp-header-icon dp-flag-button" id="dp-lang-toggle" aria-label="<?= dp_loc_escape($locale['language'].': '.($langNames[$lang]??$locale['name'])) ?>" aria-haspopup="true" aria-controls="dp-lang-menu" aria-expanded="false" title="<?= dp_loc_escape($langNames[$lang]??$locale['name']) ?>"><img src="<?= dp_loc_escape($assets.dp_loc_flag($lang).'.svg') ?>" width="22" height="16" alt=""></button>
<div class="dp-lang-menu" id="dp-lang-menu" hidden role="menu" aria-label="<?= dp_loc_escape($locale['language']) ?>">
<?php foreach($languageCodes as $code): ?><a role="menuitem" class="dp-lang-option<?= $code===$lang?' is-current':'' ?>" href="<?= dp_loc_escape(dp_loc_path($cms,$code,$currentSlug)) ?>"<?= $code===$lang?' aria-current="page"':'' ?>><img src="<?= dp_loc_escape($assets.dp_loc_flag($code).'.svg') ?>" alt="" width="24" height="18" loading="lazy"><span><?= dp_loc_escape($langNames[$code]??$source['languages'][$code]['name']??strtoupper($code)) ?></span><small><?= dp_loc_escape($code) ?></small></a><?php endforeach; ?>
</div></div>
<button type="button" class="dp-header-icon dp-mobile-toggle" id="dp-mobile-toggle" aria-controls="dp-mobile-panel" aria-expanded="false" aria-label="<?= dp_loc_escape($menuLabel) ?>"><?= dp_loc_icon('menu') ?></button>
</div></div></header>
<div class="dp-mobile-overlay" id="dp-mobile-overlay" hidden></div>
<aside class="dp-mobile-panel" id="dp-mobile-panel" aria-label="<?= dp_loc_escape(dp_loc_nav_label($cms,$lang,'Mobile navigation')) ?>" hidden>
<div class="dp-mobile-head"><a href="<?= dp_loc_escape($home) ?>"><img src="<?= dp_loc_escape($logo) ?>" alt="DigitalPulse" width="174" height="43"></a><button type="button" class="dp-header-icon" id="dp-mobile-close" aria-label="<?= dp_loc_escape($closeLabel) ?>"><?= dp_loc_icon('close') ?></button></div>
<nav class="dp-mobile-nav"><?php foreach($menu as $nav): ?><a href="<?= dp_loc_escape($nav['href']) ?>"><?= dp_loc_escape($nav['label']) ?></a><?php foreach($nav['children'] as $child): ?><a class="dp-sub-nav-link" href="<?= dp_loc_escape($child['href']) ?>"><?= dp_loc_escape($child['label']) ?></a><?php endforeach; ?><?php endforeach; ?></nav>
</aside>
<dialog class="dp-search-dialog" id="dp-location-search" aria-label="<?= dp_loc_escape($searchLabel) ?>">
<div class="dp-search-head"><h2><?= dp_loc_escape($searchPrompt) ?></h2><button type="button" class="dp-header-icon" id="dp-search-close" aria-label="<?= dp_loc_escape($closeLabel) ?>"><?= dp_loc_icon('close') ?></button></div>
<label class="dp-visually-hidden" for="dp-search-input"><?= dp_loc_escape($searchPrompt) ?></label><input id="dp-search-input" type="search" autocomplete="off" placeholder="<?= dp_loc_escape($searchPrompt) ?>" aria-controls="dp-location-results">
<div class="dp-search-results" id="dp-location-results">
<?php foreach($source['locations'] as $it): ?><a href="<?= dp_loc_escape(dp_loc_path($cms,$lang,$it['slug'])) ?>" data-location-search="<?= dp_loc_escape(dp_loc_name($it,$lang).' '.($it['name']??'')) ?>"><?= dp_loc_escape(dp_loc_name($it,$lang)) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?>
</div></dialog>
<script src="<?= dp_loc_escape(rtrim(cms_route_prefix(),'/').'/assets/js/location-header.js?v=3') ?>" defer></script>
<?php
}
function dp_loc_footer(array $cms,array $locale,string $lang): void {
    $home=dp_loc_home($cms,$lang);
    ?>
<footer class="dp-footer"><div class="dp-wrap dp-footer-inner"><div>
<a href="<?= dp_loc_escape($home) ?>" class="dp-footer-brand">Digital<span>Pulse</span></a>
<p><?= dp_loc_escape($locale['updated']) ?></p>
</div><nav aria-label="Footer"><a href="<?= dp_loc_escape(dp_loc_path($cms,$lang)) ?>"><?= dp_loc_escape($locale['locations']) ?></a><a href="<?= dp_loc_escape(dp_loc_home($cms,$lang,'#gallery')) ?>"><?= dp_loc_escape($locale['portfolio']) ?></a><a href="<?= dp_loc_escape(dp_loc_home($cms,$lang,'#contact')) ?>"><?= dp_loc_escape($locale['contact']) ?></a><a href="https://clutch.co/profile/digital-pulse-1" target="_blank" rel="noopener noreferrer">Clutch ↗</a></nav>
<div class="dp-footer-copy">© <?= (int)date('Y') ?> DigitalPulse</div></div></footer>
</body></html>
<?php
}
function dp_loc_org_graph(array $cms): array {
    $base = rtrim(cms_base_url($cms),'/');
    return ['@type'=>'Organization','@id'=>$base.'/#organization','name'=>'DigitalPulse','url'=>$base.'/','logo'=>['@type'=>'ImageObject','url'=>$base.'/assets/img/digitalpulse-og.png'],
        'description'=>'Custom WordPress plugin development, bespoke web applications, WooCommerce and smart e-commerce solutions.',
        'sameAs'=>['https://clutch.co/profile/digital-pulse-1','https://github.com/raivis-kalnins']];
}
function dp_loc_breadcrumbs(array $cms,string $lang,string $name,string $canonical): array {
    $base = cms_base_url($cms);
    $list=[['@type'=>'ListItem','position'=>1,'name'=>'DigitalPulse','item'=>$base.cms_language_home_path($cms,$lang)],
        ['@type'=>'ListItem','position'=>2,'name'=>'Locations','item'=>dp_loc_url($cms,$lang)]];
    if($name!=='')$list[]=['@type'=>'ListItem','position'=>3,'name'=>$name,'item'=>$canonical];
    return ['@type'=>'BreadcrumbList','itemListElement'=>$list];
}
function dp_loc_related(array $items,array $loc): array {
    $region=$loc['region']; $country=$loc['country'];
    $candidates=array_values(array_filter($items,static fn($it)=>($it['slug']??'')!==$loc['slug'] && ($it['region']??'')===$region));
    usort($candidates,static function($a,$b) use($country):int {
        $aa=(int)(($a['country']??'')===$country)*3+(int)(($a['kind']??'')==='country');
        $bb=(int)(($b['country']??'')===$country)*3+(int)(($b['kind']??'')==='country');
        return $bb<=>$aa;
    });
    return array_slice($candidates,0,6);
}
function dp_loc_render_page(array $cms,array $source,string $lang,?array $loc): void {
    $locale = $source['languages'][$lang] ?? $source['languages']['en'];
    $hub = $loc===null;
    $name = $hub ? '' : dp_loc_name($loc,$lang);
    $slug = $hub ? '' : (string)$loc['slug'];
    $GLOBALS['DP_LOC_CURRENT_SLUG']=$slug;
    $GLOBALS['DP_LOC_CURRENT_NAME']=$name;
    $canonical=dp_loc_url($cms,$lang,$slug);
    $title=$hub ? $locale['hubTitle'] : dp_loc_format($locale['title'],['location'=>$name]);
    $description=$hub?$locale['hubDesc']:dp_loc_format($locale['intro'],['location'=>$name]);
    // Summarise for SERPs while keeping full page copy visible.
    if (function_exists('mb_substr')) $description=mb_substr($description,0,185,'UTF-8');
    $image = $hub?'/assets/img/locations/united-kingdom.webp':$loc['image'];
    $org=dp_loc_org_graph($cms);
    $page=['@type'=>$hub?'CollectionPage':'WebPage','@id'=>$canonical.'#webpage','url'=>$canonical,'name'=>$title,'description'=>$description,'inLanguage'=>$lang,
           'publisher'=>['@id'=>$org['@id']], 'primaryImageOfPage'=>['@type'=>'ImageObject','url'=>rtrim(cms_base_url($cms),'/').$image,'width'=>1200,'height'=>675]];
    $graph=[$org,$page,dp_loc_breadcrumbs($cms,$lang,$name,$canonical)];
    if(!$hub){
        $placeType=$loc['kind']==='city'?'City':($loc['kind']==='country'?'Country':'AdministrativeArea');
        $area=['@type'=>$placeType,'name'=>$name];
        $graph[]=['@type'=>'Service','name'=>'Custom WordPress plugin development for '.$name,'provider'=>['@id'=>$org['@id']],
                 'serviceType'=>'Custom WordPress plugins, Gutenberg blocks, ACF and integrations','areaServed'=>$area,'url'=>$canonical];
        $graph[]=['@type'=>'Service','name'=>'Web app and WooCommerce development for '.$name,'provider'=>['@id'=>$org['@id']],
                 'serviceType'=>'Bespoke PHP/React web applications and WooCommerce e-commerce','areaServed'=>$area,'url'=>$canonical];
    } else {
        $graph[]=['@type'=>'ItemList','name'=>$locale['hubTitle'], 'itemListElement'=>array_map(static fn($i,$index)=>['@type'=>'ListItem','position'=>$index+1,'name'=>dp_loc_name($i,$lang),'url'=>dp_loc_url($cms,$lang,$i['slug'])],$source['locations'],array_keys($source['locations']))];
    }
    dp_loc_header($cms,$locale,$lang,$canonical,$title,$description,$image,$graph,$hub);
    $root = rtrim(cms_route_prefix(),'/');
    $homes=dp_loc_home($cms,$lang);
    $current=dp_loc_path($cms,$lang,$slug);
    ?>
<main id="content">
<div class="dp-wrap">
<nav class="dp-breadcrumbs" aria-label="Breadcrumb"><a href="<?= dp_loc_escape($homes) ?>"><?= dp_loc_escape($locale['home']) ?></a><span aria-hidden="true">›</span><?php if(!$hub): ?><a href="<?= dp_loc_escape(dp_loc_path($cms,$lang)) ?>"><?= dp_loc_escape($locale['locations']) ?></a><span aria-hidden="true">›</span><span aria-current="page"><?= dp_loc_escape($name) ?></span><?php else: ?><span aria-current="page"><?= dp_loc_escape($locale['locations']) ?></span><?php endif; ?></nav>
<section class="dp-hero<?= $hub?' dp-hero-hub':'' ?>">
  <div class="dp-hero-content">
    <p class="dp-eyebrow"><?= dp_loc_escape($locale['kicker']) ?></p>
    <h1><?= dp_loc_escape($title) ?></h1>
    <p class="dp-intro"><?= dp_loc_escape($hub?$locale['hubDesc']:dp_loc_format($locale['intro'],['location'=>$name])) ?></p>
    <div class="dp-hero-pills"><span>WordPress / Gutenberg / ACF</span><span>PHP / React</span><span>WooCommerce</span></div>
    <div class="dp-hero-actions"><a class="dp-btn dp-btn-primary" href="<?= dp_loc_escape(dp_loc_home($cms,$lang,'#contact')) ?>"><?= dp_loc_escape($locale['ctaButton']) ?> <span aria-hidden="true">↗</span></a><a class="dp-btn dp-btn-secondary" href="<?= dp_loc_escape(dp_loc_home($cms,$lang,'#gallery')) ?>"><?= dp_loc_escape($locale['portfolio']) ?></a></div>
  </div>
  <div class="dp-hero-art"><img src="<?= dp_loc_escape($root.$image) ?>" width="1200" height="675" loading="eager" decoding="async" fetchpriority="high" alt="<?= dp_loc_escape(dp_loc_format($locale['coverAlt'],['location'=>$hub?'DigitalPulse':$name])) ?>"></div>
</section>
<?php if($hub): ?>
<section class="dp-section"><div class="dp-section-head"><p class="dp-eyebrow"><?= dp_loc_escape($locale['all']) ?></p><h2><?= dp_loc_escape($locale['hubTitle']) ?></h2></div>
<?php foreach(['uk','baltics','nordics'] as $region): ?>
<section class="dp-hub-region" aria-labelledby="group-<?= dp_loc_escape($region) ?>"><div class="dp-region-head"><span class="dp-region-flag"><?= $region==='uk'?'🇬🇧':($region==='baltics'?'🇪🇺':'❄️') ?></span><h3 id="group-<?= dp_loc_escape($region) ?>"><?= dp_loc_escape($locale[$region]) ?></h3></div>
<div class="dp-tiles">
<?php foreach($source['locations'] as $it): if($it['region']!==$region)continue; ?>
<a class="dp-tile" href="<?= dp_loc_escape(dp_loc_path($cms,$lang,$it['slug'])) ?>">
<img src="<?= dp_loc_escape($root.$it['image']) ?>" alt="<?= dp_loc_escape(dp_loc_format($locale['coverAlt'],['location'=>dp_loc_name($it,$lang)])) ?>" width="1200" height="675" loading="lazy" decoding="async" fetchpriority="low"><span class="dp-tile-copy"><strong><?= dp_loc_escape(dp_loc_name($it,$lang)) ?></strong><span><?= dp_loc_escape($source['workflows'][$lang][$it['workflow']] ?? $source['workflows']['en'][$it['workflow']]) ?></span><span class="dp-tile-link"><?= dp_loc_escape($locale['view']) ?> <span aria-hidden="true">↗</span></span></span>
</a>
<?php endforeach; ?>
</div></section>
<?php endforeach; ?></section>
<?php else:
  $sector=$source['sectors'][$lang][$loc['sector']]??$source['sectors']['en'][$loc['sector']];
  $workflow=$source['workflows'][$lang][$loc['workflow']]??$source['workflows']['en'][$loc['workflow']];
  $local=dp_loc_format($locale['localText'],['location'=>$name,'sector'=>$sector,'workflow'=>$workflow]);
?>
<section class="dp-section dp-local-section"><div class="dp-local-text">
<p class="dp-eyebrow"><?= dp_loc_escape($locale['method']) ?></p><h2><?= dp_loc_escape(dp_loc_format($locale['localTitle'],['location'=>$name])) ?></h2>
<p><?= dp_loc_escape($local) ?></p>
<?php $specificInsight=$loc['i18n'][$lang]['insight']??($lang==='en'?$loc['insight']:''); if($specificInsight!==''): ?><p><?= dp_loc_escape($specificInsight) ?></p><?php endif; ?>
</div><aside class="dp-case-card"><div class="dp-case-head"><span aria-hidden="true">✦</span><?= dp_loc_escape($locale['projectExample']) ?></div><h3><?= dp_loc_escape($locale['approachTitle']) ?></h3><p><?= dp_loc_escape(dp_loc_format($locale['approachText'],['location'=>$name])) ?></p>
<p class="dp-example"><strong><?= dp_loc_escape($workflow) ?></strong><?php if($lang==='en'): ?><span><?= dp_loc_escape($loc['example']) ?></span><?php endif; ?></p></aside>
</section>
<?php endif; ?>
<section class="dp-section dp-specialists"><div class="dp-section-head"><p class="dp-eyebrow">DIGITALPULSE</p><h2><?= dp_loc_escape($locale['specialists']) ?></h2></div><div class="dp-service-grid">
<?php foreach([['✦','plugins','pluginsText'],['↗','apps','appsText'],['◇','ecommerce','ecommerceText']] as [$icon,$key,$textKey]): ?>
<article class="dp-service"><span class="dp-service-icon" aria-hidden="true"><?= dp_loc_escape($icon) ?></span><h3><?= dp_loc_escape($locale[$key]) ?></h3><p><?= dp_loc_escape($locale[$textKey]) ?></p></article>
<?php endforeach; ?></div></section>
<section class="dp-section dp-delivery"><div class="dp-delivery-copy"><p class="dp-eyebrow"><?= dp_loc_escape($locale['quality']) ?></p><h2><?= dp_loc_escape($locale['deliver']) ?></h2><ol class="dp-checks"><li><?= dp_loc_escape($locale['d1']) ?></li><li><?= dp_loc_escape($locale['d2']) ?></li><li><?= dp_loc_escape($locale['d3']) ?></li></ol></div>
<div class="dp-tech-window" role="img" aria-label="WordPress, React and WooCommerce workflow illustration"><div class="dp-window-bar"><i></i><i></i><i></i><span>DigitalPulse / engineering</span></div><div class="dp-window-content"><div class="dp-chip">WORDPRESS + ACF</div><div class="dp-chip">PHP + REACT</div><div class="dp-chip">WOOCOMMERCE</div><div class="dp-code"><span>01 &nbsp; architecture → simplicity</span><span>02 &nbsp; speed → accessibility</span><span>03 &nbsp; testing → confidence</span></div></div></div></section>
<section class="dp-section dp-faq"><div class="dp-section-head"><h2><?= dp_loc_escape($locale['faq']) ?></h2></div><div class="dp-faq-list"><details><summary><?= dp_loc_escape(dp_loc_format($locale['faq1'],['location'=>$hub?'your market':$name])) ?></summary><p><?= dp_loc_escape($locale['fa1']) ?></p></details><details><summary><?= dp_loc_escape($locale['faq2']) ?></summary><p><?= dp_loc_escape($locale['fa2']) ?></p></details></div></section>
<?php if(!$hub): ?>
<section class="dp-section dp-related"><div class="dp-section-head"><p class="dp-eyebrow"><?= dp_loc_escape($locale['related']) ?></p><h2><?= dp_loc_escape($locale['related']) ?></h2></div>
<div class="dp-related-list"><?php foreach(dp_loc_related($source['locations'],$loc) as $it): ?><a href="<?= dp_loc_escape(dp_loc_path($cms,$lang,$it['slug'])) ?>"><?= dp_loc_escape(dp_loc_name($it,$lang)) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?><a class="dp-related-all" href="<?= dp_loc_escape(dp_loc_path($cms,$lang)) ?>"><?= dp_loc_escape($locale['back']) ?> <span aria-hidden="true">→</span></a></div></section>
<?php endif; ?>
<section class="dp-cta"><div><p class="dp-eyebrow"><?= dp_loc_escape($locale['lets']) ?></p><h2><?= dp_loc_escape(($hub?$locale['ctaHub']:dp_loc_format($locale['cta'],['location'=>$name]))) ?></h2><p><?= dp_loc_escape($locale['ctaText']) ?></p><small><?= dp_loc_escape($locale['remote']) ?></small></div><a class="dp-btn dp-btn-light" href="<?= dp_loc_escape(dp_loc_home($cms,$lang,'#contact')) ?>"><?= dp_loc_escape($locale['ctaButton']) ?> <span aria-hidden="true">↗</span></a></section>
</div></main>
<?php
    dp_loc_footer($cms,$locale,$lang);
    exit;
}
function dp_loc_sitemap(array $cms,array $source): void {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    $escape=static fn($value)=>htmlspecialchars((string)$value,ENT_XML1 | ENT_COMPAT,'UTF-8');
    $langs=array_keys($source['languages']);
    $last=gmdate('Y-m-d',max((int)@filemtime(__DIR__.'/locations-data.json'),(int)@filemtime(DATA_FILE)));
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    $emit=static function(array $urls,string $modified) use($escape):void {
        foreach($urls as $language=>$url) {
            echo '<url><loc>'.$escape($url).'</loc><lastmod>'.$escape($modified).'</lastmod>';
            foreach($urls as $alt=>$altUrl)echo '<xhtml:link rel="alternate" hreflang="'.$escape($alt).'" href="'.$escape($altUrl).'" />';
            echo '<xhtml:link rel="alternate" hreflang="x-default" href="'.$escape($urls['en']??reset($urls)).'" />';
            echo "</url>\n";
        }
    };
    $home=[];
    foreach($langs as $lang)$home[$lang]=rtrim(cms_base_url($cms),'/').cms_language_home_path($cms,$lang);
    $emit($home,$last);
    $hub=[];
    foreach($langs as $lang)$hub[$lang]=dp_loc_url($cms,$lang);
    $emit($hub,$last);
    foreach($source['locations'] as $loc){
        $group=[];
        foreach($langs as $lang)$group[$lang]=dp_loc_url($cms,$lang,$loc['slug']);
        $emit($group,$last);
    }
    // Existing CMS editorial pages only (published/draft safeguard). Do not index demo products.
    foreach(['pages'=>'pages','blog'=>'blog'] as $key=>$group){
        foreach($cms[$key]['items']??[] as $item){
            if(!is_array($item)||isset($item['enabled'])&&!$item['enabled']||in_array(strtolower((string)($item['status']??'')),['draft','private','unpublished'],true))continue;
            $slug=cms_slug((string)($item['slug']??$item['title']??''));
            if($slug==='')continue;
            $url=rtrim(cms_base_url($cms),'/').cms_pretty_path($group,$slug,$cms,'en');
            echo '<url><loc>'.$escape($url).'</loc></url>' . "\n";
        }
    }
    echo '</urlset>';
    exit;
}
function dp_loc_robots(array $cms): void {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    echo "User-agent: *\nAllow: /\nDisallow: /?api=\nDisallow: /?admin\n\nSitemap: ".rtrim(cms_base_url($cms),'/')."/sitemap.xml\n";
    exit;
}
function dp_loc_llms(array $cms,array $source): void {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    $base=rtrim(cms_base_url($cms),'/');
    echo "# DigitalPulse\n\nCustom WordPress plugin development, React/PHP web applications, smart WooCommerce and e-commerce development, technical SEO and maintenance. Remote-first collaboration; no claim of physical offices in every listed market.\n\n";
    echo "- [Services]({$base}/#services)\n- [Portfolio]({$base}/#gallery)\n- [Development markets]({$base}/locations/)\n- [Contact]({$base}/#contact)\n\n";
    echo "## Priority market pages\n";
    foreach(['northampton','london','milton-keynes','coventry','birmingham','peterborough','riga','vilnius','tallinn','stockholm','oslo','copenhagen','helsinki','reykjavik'] as $slug){
        foreach($source['locations'] as $it){if($it['slug']===$slug){echo '- ['.$it['name'].']('.dp_loc_url($cms,'en',$slug).")\n";break;}}
    }
    exit;
}
function dp_locations_handle(array $cms,string $path): void {
    if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true))return;
    if($path==='robots.txt'){dp_loc_robots($cms);}
    $source=dp_locations_data();
    if($path==='sitemap.xml'){dp_loc_sitemap($cms,$source);}
    if($path==='llms.txt'){dp_loc_llms($cms,$source);}
    $parts=explode('/',trim($path,'/'));
    $lang=(string)($cms['settings']['defaultLang']??'en');
    $codes=array_keys($source['languages']);
    if(count($parts)>0&&in_array($parts[0],$codes,true))$lang=array_shift($parts);
    if(count($parts)>=2 && preg_match('/^[a-z]{2}$/',$parts[0]) && ($parts[1]??'')==='locations' && !in_array($parts[0],$codes,true)){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');echo 'Unsupported language';exit;}
    if(($parts[0]??'')!=='locations')return;
    if(count($parts)>2){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');echo 'Page not found';exit;}
    $slug=(string)($parts[1]??'');
    if($slug!==''&&!preg_match('/^[a-z0-9-]+$/',$slug)){http_response_code(404);exit;}
    $loc=null;
    if($slug!==''){
        foreach($source['locations'] as $candidate){if(($candidate['slug']??'')===$slug){$loc=$candidate;break;}}
        if($loc===null){http_response_code(404);header('Content-Type: text/html; charset=utf-8');echo '<!doctype html><title>404 - Location not found</title><h1>404 - Location not found</h1><p><a href="'.dp_loc_escape(dp_loc_path($cms,$lang)).'">View locations</a></p>';exit;}
    }
    // One canonical URL per locale. Redirect /en/ alias and missing trailing slash.
    $canonicalPath=dp_loc_path($cms,$lang,$slug);
    $requested=(string)(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)?:'/');
    if($requested!==$canonicalPath){
        header('Location: '.$canonicalPath,true,301);exit;
    }
    dp_loc_render_page($cms,$source,$lang,$loc);
}
