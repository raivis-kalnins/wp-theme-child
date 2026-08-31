<?php
/* 
	This is IWS Child Theme functions file
	You can use it to modify specific features and styling of IWS parent Theme
*/
function child_theme_enqueue_scripts_and_styles() {
	$manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';
	if ( ! is_readable( $manifest_path ) ) {
		return;
	}

	$manifest_child = json_decode( file_get_contents( $manifest_path ) );
	$custom_styles  = 'src/scss/public.scss';
	if ( empty( $manifest_child->$custom_styles->file ) ) {
		return;
	}

	$css_relative = '/dist/' . $manifest_child->$custom_styles->file;
	$css_file     = get_stylesheet_directory() . $css_relative;
	$css_version  = is_readable( $css_file ) ? (string) filemtime( $css_file ) : null;

	wp_enqueue_style(
		'wp-style--child',
		get_stylesheet_directory_uri() . $css_relative,
		array(),
		$css_version,
		'all'
	);

	// The compiled child JavaScript only contained no-op jQuery calls.
	// Do not create a separate request for it on the public site.
}
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_scripts_and_styles', 2 );

/**
 * Custom Admin Logo
 */
function my_login_logo_child() { ?><style type="text/css">#login h1 a,.login h1 a {background: url(<?=get_stylesheet_directory_uri()?>/assets/img/logo-child.png) center / 90% auto no-repeat !important;width:280px;padding:5px}</style><?php }
add_action( 'login_enqueue_scripts', 'my_login_logo_child', 10 );


/**
 * Completely hide updates for selected premium plugins
 * Add plugin slugs to $hide_plugins array
 */

add_action( 'admin_init', function() {

    // List of plugin slugs to hide updates for
    $hide_plugins = [
        'filter-everything-pro/filter-everything.php', // Filter Everything PRO
        // 'another-premium-plugin/plugin-file.php',   // Add more here
    ];

    // 1️⃣ Remove from plugin update transients
    add_filter( 'pre_set_site_transient_update_plugins', function( $value ) use ( $hide_plugins ) {
        foreach ( $hide_plugins as $slug ) {
            if ( isset( $value->response[ $slug ] ) ) {
                unset( $value->response[ $slug ] );
            }
        }
        return $value;
    });

    add_filter( 'site_transient_update_plugins', function( $value ) use ( $hide_plugins ) {
        foreach ( $hide_plugins as $slug ) {
            if ( isset( $value->response[ $slug ] ) ) {
                unset( $value->response[ $slug ] );
            }
        }
        return $value;
    });

    // 2️⃣ Remove from get_plugin_updates() array (updates table /update-core.php)
    add_filter( 'get_plugin_updates', function( $plugins ) use ( $hide_plugins ) {
        foreach ( $hide_plugins as $slug ) {
            if ( isset( $plugins[ $slug ] ) ) {
                unset( $plugins[ $slug ] );
            }
        }
        return $plugins;
    });

});

// 3️⃣ Hide dashboard & plugins page notices via CSS
add_action( 'admin_head', function() {
    echo '<style>
        #update-nag,
        .update-nag,
        .plugin-update-tr,
        .update-plugins,
        .update-plugins .update-message,
        .notice-warning,
        .notice-info,
        #menu-updates .update-plugins,
        #wp-admin-bar-updates .ab-label {
            display: none !important;
        }
    </style>';
});

// 4️⃣ Remove update count from Admin Menu (sidebar)
add_action( 'admin_menu', function() {
    global $menu;
    foreach ( $menu as $key => $value ) {
        if ( isset($value[2]) && $value[2] === 'plugins.php' ) {
            $menu[$key][0] = preg_replace('/\(\d+\)/', '', $menu[$key][0]);
        }
    }
}, 999 );

// 5️⃣ Remove update bubble in Admin Bar (top toolbar)
add_action( 'wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('updates');
}, 999 );
/**
 * Return the current public language for the translated home pages.
 */
function amjonis_current_language_slug() {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( in_array( $lang, array( 'lv', 'en' ), true ) ) {
			return $lang;
		}
	}

	$locale = determine_locale();
	return 0 === stripos( $locale, 'en' ) ? 'en' : 'lv';
}

/**
 * Polylang uses page 8 as Latvian home and page 163 as English home.
 * is_front_page() is kept for compatibility with Polylang's translated front page handling.
 */
function amjonis_is_translated_homepage() {
	return is_front_page() || is_page( array( 8, 163 ) );
}

function amjonis_homepage_seo_data() {
	if ( 'en' === amjonis_current_language_slug() ) {
		return array(
			'title'       => 'Farm Machinery & Compact Loaders | AM JONIS',
			'description' => 'AM JONIS supplies compact loaders, tractors and agricultural machinery at sensible prices, with reliable service and practical solutions for farms.',
			'keywords'    => 'farm machinery, agricultural machinery, compact loaders, tractors, concrete splitters, AM JONIS',
		);
	}

	return array(
		'title'       => 'Lauksaimniecības tehnika un kompaktiekrāvēji | AM JONIS',
		'description' => 'AM JONIS piedāvā kompaktiekrāvējus, traktorus un lauksaimniecības tehniku par saprātīgu cenu, kā arī servisu un risinājumus saimniecībām.',
		'keywords'    => 'lauksaimniecības tehnika, traktori, kompaktiekrāvēji, betona šķēlēji, AM JONIS',
	);
}

/**
 * Language-aware Yoast metadata for the Latvian and English Polylang home pages.
 */
function amjonis_homepage_seo_title( $title ) {
	if ( amjonis_is_translated_homepage() ) {
		$data = amjonis_homepage_seo_data();
		return $data['title'];
	}
	return $title;
}
add_filter( 'wpseo_title', 'amjonis_homepage_seo_title', 20 );
add_filter( 'wpseo_opengraph_title', 'amjonis_homepage_seo_title', 20 );
add_filter( 'wpseo_twitter_title', 'amjonis_homepage_seo_title', 20 );

function amjonis_homepage_seo_description( $description ) {
	if ( amjonis_is_translated_homepage() ) {
		$data = amjonis_homepage_seo_data();
		return $data['description'];
	}
	return $description;
}
add_filter( 'wpseo_metadesc', 'amjonis_homepage_seo_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'amjonis_homepage_seo_description', 20 );
add_filter( 'wpseo_twitter_description', 'amjonis_homepage_seo_description', 20 );

/**
 * Extra metadata requested by third-party SEO audit tools.
 * Google does not use meta keywords for ranking.
 */
function amjonis_homepage_metadata_tags() {
	if ( ! amjonis_is_translated_homepage() ) {
		return;
	}

	$data = amjonis_homepage_seo_data();
	echo "\n";
	echo '<meta name="keywords" content="' . esc_attr( $data['keywords'] ) . '" />' . "\n";
	echo '<meta name="author" content="SIA AM JONIS" />' . "\n";
	echo '<meta name="publisher" content="SIA AM JONIS" />' . "\n";
}
add_action( 'wp_head', 'amjonis_homepage_metadata_tags', 3 );

/**
 * Enrich Yoast's Organization graph with public company data.
 */
function amjonis_yoast_organization_schema( $data ) {
	$data['name']      = 'SIA AM JONIS';
	$data['legalName'] = 'SIA AM JONIS';
	$data['url']       = 'https://amjonis.lv/';
	$data['email']     = 'info@amjonis.lv';
	$data['telephone'] = '+37127375000';
	$data['vatID']     = 'LV45403036833';
	$data['address']   = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Rīgas iela 230',
		'addressLocality' => 'Jēkabpils',
		'postalCode'      => 'LV-5202',
		'addressCountry'  => 'LV',
	);
	return $data;
}
add_filter( 'wpseo_schema_organization', 'amjonis_yoast_organization_schema', 20 );

/**
 * Reduce the home hero transfer size. The ACF hero stores the full attachment URL
 * in an inline background style. For the two home pages, swap it to WordPress'
 * 1024px derivative (when available), which is substantially smaller.
 */
function amjonis_optimize_home_hero_block( $block_content, $block ) {
	if ( ! amjonis_is_translated_homepage() || empty( $block['blockName'] ) || 'acf/hero' !== $block['blockName'] ) {
		return $block_content;
	}

	$image_id = isset( $block['attrs']['data']['hero_sec_background'] ) ? (int) $block['attrs']['data']['hero_sec_background'] : 0;
	if ( ! $image_id ) {
		return $block_content;
	}

	$full  = wp_get_attachment_image_url( $image_id, 'full' );
	$large = wp_get_attachment_image_url( $image_id, 'large' );
	if ( $full && $large && $full !== $large ) {
		$block_content = str_replace( $full, $large, $block_content );
	}

	return $block_content;
}
add_filter( 'render_block', 'amjonis_optimize_home_hero_block', 12, 2 );

/**
 * Preload the exact optimized hero image used above so a CSS background image
 * can start downloading before the hero block stylesheet/layout is processed.
 */
function amjonis_preload_home_hero_image() {
	if ( ! amjonis_is_translated_homepage() ) {
		return;
	}

	$hero = wp_get_attachment_image_url( 657, 'large' );
	if ( $hero ) {
		echo '<link rel="preload" as="image" href="' . esc_url( $hero ) . '" fetchpriority="high" />' . "\n";
	}
}
add_action( 'wp_head', 'amjonis_preload_home_hero_image', 2 );

/**
 * Lighthouse / agentic accessibility fixes for the navigation markup.
 */
function amjonis_accessible_nav_menu_markup( $nav_menu ) {
	if ( ! is_string( $nav_menu ) || '' === $nav_menu ) {
		return $nav_menu;
	}

	if ( false === strpos( $nav_menu, 'item-sub' ) && false === strpos( $nav_menu, '/en/home-english' ) ) {
		return $nav_menu;
	}

	$submenu_label = 'lv' === amjonis_current_language_slug() ? 'Atvērt apakšizvēlni' : 'Open submenu';
	$nav_menu = preg_replace(
		'/<input(?![^>]*\baria-label=)(?=[^>]*\bclass=(["\'])[^"\']*\bitem-sub\b[^"\']*\1)([^>]*)>/i',
		'<input aria-label="' . esc_attr( $submenu_label ) . '"$2>',
		$nav_menu
	);
	$nav_menu = preg_replace(
		'/<a(?![^>]*\baria-label=)(?=[^>]*\bhref=(["\'])[^"\']*\/en\/home-english\/?\1)([^>]*)>/i',
		'<a aria-label="English"$2>',
		$nav_menu
	);

	return $nav_menu;
}
add_filter( 'wp_nav_menu', 'amjonis_accessible_nav_menu_markup', 30, 2 );

function amjonis_accessible_rendered_block_markup( $block_content ) {
	return amjonis_accessible_nav_menu_markup( $block_content );
}
add_filter( 'render_block', 'amjonis_accessible_rendered_block_markup', 30, 1 );

/**
 * Serve a lightweight llms.txt Markdown resource from the child theme.
 */
function amjonis_serve_llms_txt() {
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
	if ( '/llms.txt' !== untrailingslashit( (string) $request_path ) ) {
		return;
	}

	$llms_file = get_stylesheet_directory() . '/llms.txt';
	if ( ! is_readable( $llms_file ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/markdown; charset=UTF-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $llms_file );
	exit;
}
add_action( 'template_redirect', 'amjonis_serve_llms_txt', 0 );
