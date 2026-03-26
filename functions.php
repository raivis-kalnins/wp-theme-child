<?php
/* 
	This is IWS Child Theme functions file
	You can use it to modify specific features and styling of IWS parent Theme
*/
function child_theme_enqueue_scripts_and_styles() {
	//error_log('child theme enqueue stuff');
	$manifest_child = json_decode(file_get_contents( get_stylesheet_directory() . '/dist/.vite/manifest.json') ); //var_dump($manifest);
	$customStyles = 'src/scss/public.scss';
	$customJS = 'src/js/main.js';	
	// Enqueue styles	
	wp_enqueue_style('wp-style--child', get_stylesheet_directory_uri() . '/dist/' . $manifest_child->$customStyles->file, [], '', 'all');	
	// Enqueue scripts	
	wp_enqueue_script('wp-script--child', get_stylesheet_directory_uri() . '/dist/' . $manifest_child->$customJS->file, ['jquery'], '', 'all');
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_scripts_and_styles', 2);

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