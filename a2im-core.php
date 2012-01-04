<?php

/*
Plugin Name: a2im app
Plugin URI: http://mu.danpolant.com	
Description: Plugin for a2im
Author: Dan Polant
Author URI: http://danpolant.com
*/

//nav preferences
add_action("plugins_loaded" , "atwo_set_nav");

// register a2im group tag and category widgets
add_action('widgets_init', create_function('', 'return register_widget("atwo_groups_tags_widget");'));
add_action('widgets_init', create_function('', 'return register_widget("atwo_groups_cats_widget");'));

//authorize activity widget
function bp_activity_unregister_widgets() {
	if (!is_user_logged_in() ){
		add_action('widgets_init', create_function('', 'return unregister_widget("BP_Activity_Widget");') );
		add_action('widgets_init', create_function('', 'return unregister_widget("atwo_groups_tags_widget");') );
		add_action('widgets_init', create_function('', 'return unregister_widget("atwo_groups_cats_widget");') );
	}
}
add_action( 'plugins_loaded', 'bp_activity_unregister_widgets' );

//unregister activity widget for not logged in
add_action('widgets_init', create_function('', 'return unregister_sidebar_widget("BP_Activity_Widget");'));

// register a2im sidebars
add_action('init', 'atwo_groups_sidebar');
add_action('init', 'atwo_contents_sidebar');
add_action('init', 'atwo_members_sidebar');
add_action('init', 'atwo_welcome_area');

// unregister widgets if not logged in
add_action('widgets_init', 'atwo_unregister_widgets');

//remove blogs menu items
remove_action( 'bp_adminbar_menus', 'bp_adminbar_blogs_menu', 6);
remove_action( 'plugins_loaded', 'bp_blogs_setup_nav' );
remove_action( 'admin_menu', 'bp_blogs_setup_nav' );

//remove random menu
remove_action( 'bp_adminbar_menus', 'bp_adminbar_random_menu', 100 );

//scripts, just menus for now
add_action('init', 'atwo_scripts');

//styles, just menus for now
add_action('init', 'atwo_styles');

//restrict non-logged in users
add_action( 'plugins_loaded', 'atwo_restrict_access', 3 );

//restrict non-logged in feed viewing
add_action( 'plugins_loaded', 'atwo_restrict_feed');

function atwo_restrict_feed(){
	
	if (!is_user_logged_in()) {
		remove_action( 'wp', 'bp_activity_action_sitewide_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_personal_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_friends_feed', 3 );
	}
}

function atwo_restrict_access(){
	global $bp, $bp_unfiltered_uri;
	
	if (!is_user_logged_in() && ($bp_unfiltered_uri[0] == BP_MEMBERS_SLUG || $bp_unfiltered_uri[0] == BP_BLOGS_SLUG || $bp_unfiltered_uri[0] == BP_FORUMS_SLUG ))
		bp_core_redirect( get_option('home') . "?l=true");
}

function atwo_unregister_widgets(){
	//no comments widget for non-authenticated
	if (!is_user_logged_in()) 
		 unregister_widget('WP_Widget_Recent_Comments');
}

function atwo_login_prompt(){
	if (isset($_REQUEST['l']))
		echo "<span id='login-prompt'>You must be logged in to view that part of the site!</span>";
}

function atwo_scripts(){
	wp_enqueue_script('droppy', WP_CONTENT_URL . "/menus/droppy-0.1.2/src/javascripts/jquery.droppy.js", array('jquery'));
}

function atwo_styles(){
	wp_enqueue_style('droppy-styles', WP_CONTENT_URL . "/menus/droppy-0.1.2/src/stylesheets/droppy.css");
}

function atwo_set_nav() {
	global $bp; 

	remove_action( 'bp_nav_items', 'oci_bpc_nav_item');
	
	if (!current_user_can('activate_plugins')) {
		bp_core_remove_subnav_item( $bp->groups->slug, 'create');
		bp_core_remove_subnav_item( $bp->groups->slug, 'leave-group');
	}
	
	bp_core_remove_subnav_item( $bp->groups->slug, 'request-membership');
}

function atwo_sort_members(){
	global $sites_group_template;
	
	sort($sites_group_template->groups);
}

function atwo_search_form_type_select() {
	// Eventually this won't be needed and a page will be built to integrate all search results.
	$selection_box = '<select name="search-which" id="search-which" style="width: auto">';
	
	if ( function_exists( 'groups_install' ) ) {
		$selection_box .= '<option value="groups">' . __( 'Member orgs', 'buddypress' ) . '</option>';
	}	
	
	if ( function_exists( 'xprofile_install' ) && is_user_logged_in()) {
		$selection_box .= '<option value="members">' . __( 'People', 'buddypress' ) . '</option>';
	}
		
	$selection_box .= '</select>';
	
	return apply_filters( 'bp_search_form_type_select', $selection_box );
}

function atwo_welcome_area() {

	$args = array(
		'name'          => 'welcome text',
		'id'            => 'atwo-welcome',
		'before_widget' => '<div class= "widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>' );
		
	register_sidebar($args);
}

function atwo_groups_sidebar() {

	$args = array(
		'name'          => 'member orgs Sidebar',
		'id'            => 'groups-sidebar',
		'before_widget' => '<div class= "widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>' );
		
	register_sidebar($args);
}

function atwo_contents_sidebar() {

    $args = array(
        'name'          => 'member orgs categories sidebar',
        'id'            => 'items-sidebar',
        'before_widget' => '<div class= "widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>' );
 
    register_sidebar($args);
}

function atwo_members_sidebar() {

    $args = array(
        'name'          => 'people sidebar',
        'id'            => 'members-sidebar',
        'before_widget' => '<div class= "widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>' );
 
    register_sidebar($args);
}

//this creates the string that get_terms() uses to define its terms
function atwo_group_tag_cloud_str($cat_id) {
	global $wpdb;
	
	$sql = "SELECT wp_1_terms.term_id AS term_id
			FROM wp_1_term_taxonomy, wp_1_term_relationships, wp_1_terms
			WHERE wp_1_term_relationships.term_taxonomy_id = wp_1_term_taxonomy.term_taxonomy_id
			AND wp_1_term_taxonomy.term_id = wp_1_terms.term_id
			AND wp_1_term_taxonomy.taxonomy = 't_sitewide_group'
			AND wp_1_term_relationships.object_id IN (
			
			SELECT wp_1_term_relationships.object_id
			FROM wp_1_term_taxonomy, wp_1_term_relationships
			WHERE wp_1_term_relationships.term_taxonomy_id = wp_1_term_taxonomy.term_taxonomy_id
			AND wp_1_term_relationships.term_taxonomy_id = %d
			)";

	$label_tags = $wpdb->get_results($wpdb->prepare($sql, $cat_id), ARRAY_A);
	$tags_list = array();
	
	foreach ($label_tags as $label_tag) {
		$tag_to_str = implode($label_tag);
		$tags_list[] = $tag_to_str;
	}
	
	$label_tags_str = implode(', ', $tags_list);
	
	return $label_tags_str;
}

//group tags widget
class atwo_groups_tags_widget extends WP_Widget {
    /** constructor */
    function atwo_groups_tags_widget() {
        parent::WP_Widget(false, $name = 'A2 Group Tag By Category');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$cat_id = $instance['cat_id'];
		
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
				echo $before_title . $title . $after_title; ?>

				<?php $args = array("include" => atwo_group_tag_cloud_str(4));?>
                <h2 class="widgettitle">Tags for Associate Member Companies</h2>
                <div class="atwo_tags">
                
                    <?php oci_the_group_tag_cloud($args);?>

                </div>

			   <?php $args = array("include" => atwo_group_tag_cloud_str(3));?>
               <h2 class="widgettitle">Tags for Labels</h2>
                <div class="atwo_tags"> 
                    <?php oci_the_group_tag_cloud($args);?>
                    
                </div>

              <?php echo $after_widget; ?>
              
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
		
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
    
    <?php        
    }

}

//group categories widget
class atwo_groups_cats_widget extends WP_Widget {
    /** constructor */
    function atwo_groups_cats_widget() {
        parent::WP_Widget(false, $name = 'A2 Group Categories');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
				echo $before_title . $title . $after_title; ?>
                
                <div class="atwo_cats">
                	<ul>
						<?php 
						$args = array('orderby' => 'ID');
						oci_the_group_category_list($args); ?>
                    </ul>
				</div>

              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
		
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
    
    <?php        
    } 

}
add_filter( 'bp_get_activity_filter_links', 'atwo_activity_filter_edit');

function atwo_activity_filter_edit( $component_links ){
	$component_links = preg_replace('/Blogs/', 'News', $component_links);
	return $component_links;
}

function atwo_alter_blog_post_notification( $activity_content, &$post, $post_permalink ){	

	$activity_content = sprintf( __( '%s posted: %s', 'buddypress' ), bp_core_get_userlink( (int)$post->post_author ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>' );
	$activity_content .= "<blockquote>" . bp_create_excerpt( $post->post_content ) . "</blockquote>";
	
	return $activity_content;
}

add_filter( 'bp_blogs_activity_new_post', 'atwo_alter_blog_post_notification', 3, 3 );

function atwo_alter_comment_notification( $activity_content, &$comment, &$recorded_comment, $comment_link ){
	$activity_content = sprintf( __( '%s commented on %s', 'buddypress' ), bp_core_get_userlink( $user_id ), '<a href="' . $comment_link . '#comment-' . $comment->comment_ID . '">' . $comment->post->post_title . '</a>' );			
	$activity_content .= '<blockquote>' . bp_create_excerpt( $comment->comment_content ) . '</blockquote>';
	
	return $activity_content;
}

add_filter( 'bp_blogs_activity_new_comment', 'atwo_alter_comment_notification', 3, 4 );

function wp_sanitize_redirect($location) {
	$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!@]|i', '', $location);
	$location = wp_kses_no_null($location);

	// remove %0d and %0a from location
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$location = _deep_replace($strip, $location);
	return $location;
}