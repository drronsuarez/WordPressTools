<?php

function add_slider() {
	if (!is_admin()) {
		// comment out the next two lines to load the local copy of jQuery
		//wp_deregister_script('jquery');
		//wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js', false, '1.3.2');
		wp_enqueue_script('jquery');
		wp_enqueue_script('slideanim', 'http://design.margaretekoenen.com/wp-content/themes/MKdesign/js/slideanim.js');
	}
}
add_action('init', 'add_slider');



function favicon_link() {
    echo '<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />' . "\n";
}
add_action('wp_head', 'favicon_link');

register_sidebar( array(
		'name' => __( 'Header Widget Area' ),
		'id' => 'header-widget-area',
		'description' => __( 'The header widget area'),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
add_action( 'init', 'create_post_type' );

function create_post_type() {
	register_post_type( 'organization',   //more at http://codex.wordpress.org/Function_Reference/register_post_type
		array(
			'labels' => array(
				'name' => __( 'Organizations' ),
				'singular_name' => __( 'Organization')
			),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
	    	'hierarchical' => false,
	    	'rewrite' => false,//important otherwise will not show when published
	    	'query_var' => false,
			'has_archive' => true,
	    	'supports' => array('title', 'editor', 'thumbnail', 'author', 'excerpt',  'custom-fields', 'comments', 'revisions')
		 )
	);
}

?>
