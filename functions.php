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
