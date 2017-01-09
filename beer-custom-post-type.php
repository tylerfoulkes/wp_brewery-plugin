<?php
/*
Plugin Name: Beer Custom Post Type
Plugin URI: 
Description: Beer Custom Post Type
Author: Tyler Foulkes
Author URI: 
Version: 1.0

	Copyright: 
	License: GNU General Public License v3.0
	License URI: 
*/

class Custom_Post_Type_Image_Upload {
	
	
	public function __construct() {
		
		add_action( 'init', array( &$this,'create_beer_tax') );
		add_action( 'init', array( &$this, 'init' ) );
		
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
		}
	}
	
	/** Frontend methods ******************************************************/
	
	
	/**
	 * Register the custom post type
	 */
	public function init() {
		// Set UI labels for Custom Post Type
	  	$labels = array(
	    	'name'                => _x( 'beer', 'Post Type General Name'),
	    	'singular_name'       => _x( 'beer', 'Post Type Singular Name'),
	    	'menu_name'           => __( 'Beers')
	  	);
	  
		// Set other options for Custom Post Type
	  
	  	$args = array(
	    	'label'               => __( 'beer'),
	    	'description'         => __( 'beer'),
	    	'labels'              => $labels,
	    	// Features this CPT supports in Post Editor

	    	// You can associate this CPT with a taxonomy or custom taxonomy. 
	      
		    'hierarchical'        => TRUE,
		    'public'              => TRUE,
		    'show_ui'             => TRUE,
		    'label'               => 'Beers',
		    'show_in_menu'        => TRUE,
		    'show_in_nav_menus'   => TRUE,
		    'show_in_admin_bar'   => TRUE,
		    'can_export'          => TRUE,
		    'has_archive'         => TRUE,
		    'exclude_from_search' => FALSE,
		    'publicly_queryable'  => TRUE
		    // 'rewrite'               => array( 'slug' => '/%show_category%', 'with_front' => true )
		  );

	  
	  	// Registering your Custom Post Type
	  	register_post_type( 'beer', $args );

	}


	public function create_beer_tax() {
		  register_taxonomy(
		    'types',
		    'beer',
		    array(
		      	'label' => __( 'Types' ),
		      	'hierarchical' 				 => TRUE,
		    	'show_in_nav_menus'          => TRUE,
		    )
		);
	}
	
	
	/** Admin methods ******************************************************/
	
	
	/**
	 * Initialize the admin, adding actions to properly display and handle 
	 * the Book custom post type add/edit page
	 */

	public function admin_init() {
		global $pagenow;
		
		if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' ) {
			
			add_action( 'add_meta_boxes', array( &$this, 'meta_boxes' ) );
			add_filter( 'enter_title_here', array( &$this, 'enter_title_here' ), 1, 2 );
			
			add_action( 'save_post', array( &$this, 'meta_boxes_save' ), 1, 2 );
			add_action( 'init', remove_post_type_support( 'beer', 'editor' ) );

			// Load upload an thickbox script
	        wp_enqueue_script('media-upload');
	        wp_enqueue_script('thickbox');

	        // Load thickbox CSS
	        wp_enqueue_style('thickbox');
		}
	}
	
	/**
	 * Save meta boxes
	 * 
	 * Runs when a post is saved and does an action which the write panel save scripts can hook into.
	 */
	public function meta_boxes_save( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $post->post_type != 'beer' ) return;
			
		$this->process_beer_meta( $post_id, $post );
	}
	
	
	/**
	 * Function for processing and storing all book data.
	 */
	private function process_beer_meta( $post_id, $post ) {
		/* Get the meta key. */
  		$image_meta_key = '_image_id';
		$image_meta_value = get_post_meta( $post_id, $image_meta_key, true );
		/* Get the posted data and sanitize it for use as an HTML class. */
  		$image_new_meta_value = ( isset( $_POST['upload_image_id'] ) ? sanitize_html_class( $_POST['upload_image_id'] ) : '' );
		
		/* If a new meta value was added and there was no previous value, add it. */
  		if ( $image_new_meta_value && '' == $image_meta_value ) {
  			add_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
  		}

  		/* If the new meta value does not match the old value, update it. */
  		elseif ( $image_new_meta_value && $image_new_meta_value != $image_meta_value ) {
    		update_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
		}


  		/* If there is no new meta value but an old value exists, delete it. */
  		elseif ( '' == $image_new_meta_value && $image_meta_value ) {
    		delete_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
		}

		$this->process_desc_meta($post_id, $post);
		$this->process_abv_meta($post_id, $post);
		$this->process_ibu_meta($post_id, $post);

		

		

		
	}


	public function process_desc_meta($post_id, $post) {
		$desc_meta_key = '_beer_desc_id';
  		$desc_meta_value = get_post_meta( $post_id, $desc_meta_key, true );
  		if(isset( $_POST['beer_desc'] )) {
  			$desc_new_meta_value = $_POST['beer_desc'];
  		}

		if ( $desc_new_meta_value && '' == $desc_meta_value ) {
  			add_post_meta( $post_id, '_beer_desc_id', $desc_new_meta_value);
  		}

  		elseif ( $desc_new_meta_value && $desc_new_meta_value != $desc_meta_value) {
			update_post_meta( $post_id, '_beer_desc_id', $desc_new_meta_value);
		}

		elseif ( '' == $desc_new_meta_value && $desc_meta_value) {
			delete_post_meta( $post_id, '_beer_desc_id', $desc_meta_value);
		}
	}

	public function process_abv_meta($post_id, $post) {
		$abv_meta_key = '_beer_abv_id';
  		$abv_meta_value = get_post_meta( $post_id, $abv_meta_key, true );
  		if(isset( $_POST['beer_abv'] )) {
  			$abv_new_meta_value = $_POST['beer_abv'];
  		}

		if ( $abv_new_meta_value && '' == $abv_meta_value ) {
  			add_post_meta( $post_id, '_beer_abv_id', $abv_new_meta_value);
  		}

  		elseif ( $abv_new_meta_value && $abv_new_meta_value != $abv_meta_value) {
			update_post_meta( $post_id, '_beer_abv_id', $abv_new_meta_value);
		}

		elseif ( '' == $abv_new_meta_value && $abv_meta_value) {
			delete_post_meta( $post_id, '_beer_abv_id', $abv_meta_value);
		}
	}


	public function process_ibu_meta($post_id, $post) {
		$ibu_meta_key = '_beer_ibu_id';
  		$ibu_meta_value = get_post_meta( $post_id, $ibu_meta_key, true );
  		if(isset( $_POST['beer_ibu'] )) {
  			$ibu_new_meta_value = $_POST['beer_ibu'];
  		}

		if ( $ibu_new_meta_value && '' == $ibu_meta_value ) {
  			add_post_meta( $post_id, '_beer_ibu_id', $ibu_new_meta_value);
  		}

  		elseif ($ibu_new_meta_value && $ibu_new_meta_value != $ibu_meta_value) {
			update_post_meta( $post_id, '_beer_ibu_id', $ibu_new_meta_value);
		}

		elseif ( '' == $ibu_new_meta_value && $ibu_meta_value) {
			delete_post_meta( $post_id, '_beer_ibu_id', $ibu_meta_value);
		}
	}
	
	
	/**
	 * Set a more appropriate placeholder text for the New Book title field
	 */
	public function enter_title_here( $text, $post ) {
		if ( $post->post_type == 'beer' ) return __( 'Beer Title' );
		return $text;
	}
	
	
	/**
	 * Add and remove meta boxes from the edit page
	 */
	public function meta_boxes() {
		add_meta_box( 'beer-image', __( 'Beer Image' ), array( &$this, 'beer_image_meta_box' ), 'beer', 'normal', 'high' );
		add_meta_box('beer-desc', __('Beer Description'), array( &$this,'beer_description'), 'beer', 'normal', 'default');
		add_meta_box('beer-abv', __('Beer ABV'), array(&$this, 'beer_abv'), 'beer', 'normal', 'default');
		add_meta_box('beer-ibu', __('Beer IBU'), array(&$this, 'beer_ibu'), 'beer', 'normal', 'default');
	}


	public function beer_description() {
		?>
			<input class="widefat" type="text" name="beer_desc" id="beer_desc" value="<?php echo get_post_meta($GLOBALS['post']->ID, '_beer_desc_id', true); ?>" size="30" />
		<?php 
	}

	public function beer_abv() {
		?>
			<input class="widefat" type="text" name="beer_abv" id="beer_abv" value="<?php echo get_post_meta($GLOBALS['post']->ID, '_beer_abv_id', true); ?>" size="5" />
		<?php 
	}

	public function beer_ibu() {
		?>
			<input class="widefat" type="text" name="beer_ibu" id="beer_ibu" value="<?php echo get_post_meta($GLOBALS['post']->ID, '_beer_ibu_id', true); ?>" size="5" />
		<?php 
	}

	

	/**
	 * Display the image meta box
	 */
	public function beer_image_meta_box() {
		global $post;
		
		$image_src = '';
		
		$image_id = get_post_meta( $post->ID, '_image_id', true );
		$image_src = wp_get_attachment_url( $image_id );


		
		?>
		<img id="beer_image" src="<?php echo $image_src ?>" style="max-width:100%;" />
		<input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $image_id; ?>" />
		<p>
			<a title="<?php esc_attr_e( 'Set beer image' ) ?>" href="#" id="set-beer-image"><?php _e( 'Set beer image' ) ?></a>
			<a title="<?php esc_attr_e( 'Remove beer image' ) ?>" href="#" id="remove-beer-image" style="<?php echo ( ! $image_id ? 'display:none;' : '' ); ?>"><?php _e( 'Remove beer image' ) ?></a>
		</p>
		
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			
			// save the send_to_editor handler function
			window.send_to_editor_default = window.send_to_editor;
	
			$('#set-beer-image').click(function(){
				
				// replace the default send_to_editor handler function with our own
				window.send_to_editor = window.attach_image;
				tb_show('', 'media-upload.php?post_id=<?php echo $post->ID ?>&amp;type=image&amp;TB_iframe=true');
				
				return false;
			});
			
			$('#remove-beer-image').click(function() {
				
				$('#upload_image_id').val('');
				$('img').attr('src', '');
				$(this).hide();
				
				return false;
			});
			
			// handler function which is invoked after the user selects an image from the gallery popup.
			// this function displays the image and sets the id so it can be persisted to the post meta
			window.attach_image = function(html) {
				
				// turn the returned image html into a hidden image element so we can easily pull the relevant attributes we need
				$('body').append('<div id="temp_image">' + html + '</div>');
					
				var img = $('#temp_image').find('img');
				
				imgurl   = img.attr('src');
				imgclass = img.attr('class');
				imgid    = parseInt(imgclass.replace(/\D/g, ''), 10);
	
				$('#upload_image_id').val(imgid);
				$('#remove-beer-image').show();
	
				$('img#beer_image').attr('src', imgurl);
				try{tb_remove();}catch(e){};
				$('#temp_image').remove();
				
				// restore the send_to_editor handler function
				window.send_to_editor = window.send_to_editor_default;
				
			}
	
		});
		</script>
		<?php
	}
}

// finally instantiate our plugin class and add it to the set of globals
$GLOBALS['custom_post_type_image_upload'] = new Custom_Post_Type_Image_Upload();
