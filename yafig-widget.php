<?php
/**
 * Plugin Name: YAFIG Widget
 * Plugin URI: https://github.com/pdjr-miscellany/yafig-widget
 * Description: Yet another featured image gallery widget.
 * Version: 1.0
 * Author: Paul Reeve
 * Tags: category, image, gallery, media, widget
 * License: GPL
 * Text Domain: yafig-widget

=====================================================================================
Copyright (C) 2018 Nick Halsey

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
=====================================================================================
*/

// Load Translations
add_action('plugins_loaded', 'yafig_widget_load_textdomain');
function yafig_widget_load_textdomain() {
	load_plugin_textdomain( 'yafig-widget' );
}

// Register widget.
function yafig_widget_init() {
	return register_widget('YAFIG_widget');
}
add_action('widgets_init', 'yafig_widget_init');

class YAFIG_Widget extends WP_Widget {
	/* Constructor */
	function __construct() {
		parent::__construct(
      'yafig_widget',
      __( 'YAFIG Widget', 'yafig-widget' ),
      array('description' => __( 'A gallery of featured images from category posts.', 'yafig-widget' ),
      'customize_selective_refresh' => true,
		) );
	}

	/* This is the Widget */
	function widget($args, $instance) {
		global $post;
		extract($args);

		$gallery = get_gallery_markup ( $instance );
		if ('' === $gallery) {
			return;
		} else {
			if (!array_key_exists('title', $instance)) $instance['title'] = '';

			// Widget options
			$title = apply_filters('widget_title', $instance['title']); // Title

			// Output
			echo $before_widget;
			if ($title) echo $before_title . $title . $after_title;
			echo $gallery;
			echo $after_widget;
		}
	}

	/* Widget control update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['size'] = (in_array($new_instance['size'], array('thumbnail','medium','large','full'))?$new_instance['size']:'thumbnail' );
		$instance['columns'] = absint($new_instance['columns']);
		$instance['rows'] = absint($new_instance['rows']);
		return $instance;
	}

	/* Widget settings */
	function form( $instance ) {
	  if ( $instance ) {
			$title = $instance['title'];
			$category = $instance['category'];
			$size = $instance['size'];
			$columns = $instance['columns'];
			$rows = $instance['rows'];
	  } else {
			$title = '';
			$category = '';
			$size = 'thumbnail';
			$columns = 2;
			$rows = 1;
	  }
    ?>
	    <p>
			  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'yafig-widget' ); ?></label>
			  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
		  </p>
		  <p>
			  <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php echo __( 'Category slug:', 'yafig-widget' ); ?></label>
			  <input id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="text" value="<?php echo $category; ?>" class="widefat" />
		  </p>
		  <p>
			  <label for="<?php echo $this->get_field_id('size'); ?>"><?php echo __( 'Image size:', 'yafig-widget' ); ?></label>
		    <select name="<?php echo $this->get_field_name('size'); ?>" id="<?php echo $this->get_field_id('size'); ?>" class="widefat">
				  <option value="thumbnail" <?php if( 'thumbnail' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Thumbnail', 'featured-image-gallery-widget' ) ); ?></option>
				  <option value="medium" <?php if( 'medium' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Medium', 'featured-image-gallery-widget' ) ); ?></option>
				  <option value="large" <?php if( 'large' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Large', 'featured-image-gallery-widget' ) ); ?></option>
				  <option value="full" <?php if( 'full' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Full', 'featured-image-gallery-widget' ) ); ?></option>
			  </select>
		  </p>
		  <p>
			  <label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php echo __( 'Number of columns:', 'yafig-widget' ); ?></label>
			  <input id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" type="number" min="1" max="9" value="<?php echo $columns; ?>" class="widefat" />
		  </p>
      <p>
			  <label for="<?php echo $this->get_field_id( 'rows' ); ?>"><?php echo __( 'Number of rows:', 'yafig-widget' ); ?></label>
			  <input id="<?php echo $this->get_field_id( 'rows' ); ?>" name="<?php echo $this->get_field_name( 'rows' ); ?>" type="number" min="1" max="9" value="<?php echo $rows; ?>" class="widefat" />
		  </p>
	  <?php
	}
}

// Returns the gallery markup.
function get_gallery_markup($args) {
	$query_args = array('posts_per_page' => -1, 'category_name' => $args['category']);
	$query = new WP_query($query_args);

	global $yafig_titles;
  $yafig_titles	= array();
	if ($query->have_posts()) {
		$ids = array();
		while ($query->have_posts() && (count($ids) < ($args['columns'] * $args['rows']))) {
			$post = $query->the_post();
			$image_id = get_post_thumbnail_id();
			if ($image_id) { 
        $ids[] = $image_id;
        $yafig_titles[$image_id] = get_the_title();
      }
		}
		rewind_posts(); // Reset the main query, in case it's used after this function.

		if (empty($ids)) {
			return '';
		} else {
			add_filter('attachment_link', 'rewrite_gallery_item_attachment_link', 10, 2); // Make images link to posts instead of attachments.
			add_filter('wp_get_attachment_image_attributes', 'add_title_to_gallery_item', 10, 3);
			$args = array_merge(array(
				'ids' => $ids,
				'columns' => 2,
				'rows' => 1,
				'size' => 'thumbnail',
				'link_type' => 'post', // Filtered to link to the parent post where the parent exists.			
			), $args);
			$markup = gallery_shortcode($args);

			return $markup;
		}
	} else {
		return '';
	}
}

// Filter the HTML attributes of each img tag in the gallery, adding a
// title attribute which points to the title of the linked post.
function add_title_to_gallery_item($attr, $attachment, $size) {
	global $yafig_titles;
	$attr['title'] = $yafig_titles[$attachment->ID];
	return($attr);
}

// Filter the link wrapper of each img tag in the gallery so that it
// points to the containing the attachment's parent post.
function rewrite_gallery_item_attachment_link( $link, $post_id ) {
	$post = get_post( $post_id );
	$parentlink = get_permalink( $post->post_parent );
	$parent_image = get_post_thumbnail_id( $post->post_parent );
	if ( $parentlink && $parent_image == $post->ID ) {
		return $parentlink;
	} else {
		return $link;
	}
}
