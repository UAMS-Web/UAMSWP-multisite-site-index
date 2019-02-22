<?php
/**
 * Plugin Name: UAMSWP Multisite Site Index
 * Plugin URI: http://celloexpressions.com/plugins/multisite-site-index/
 * Description: Display an index of all sites on a multisite network with a widget or a shortcode.
 * Version: 1.1
 * Author: Todd McKee, Med & Nick Halsey
 * Author URI: http://nick.halsey.co/
 * Tags: multisite, site index
 * Text Domain: multisite-site-index
 * License: GPL

=====================================================================================
Copyright (C) 2017 Nick Halsey

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

// Add the site index shortcode.
add_shortcode( 'site-index', 'uams_multisite_site_index_do_shortcode' );
function uams_multisite_site_index_do_shortcode( $atts ){
	if ( ! is_multisite() ) {
		return;
	}

	extract( shortcode_atts( array(
		'excluded' => '',
		'number' => 100,
		'recent' => 0,
		'description' => true,
		'order' => 'ASC',
		'orderby' => 'id'
	), $atts ) );

	return uams_multisite_site_index_get_markup( $excluded, $recent, $number, $description, $order, $orderby );
}

// Register 'Multisite Site Index' widget.
function uams_multisite_site_index_widget_init() {
	if ( ! is_multisite() ) {
		return;
	}
	return register_widget( 'UAMS_Multisite_Site_Index_Widget' );
}
add_action( 'widgets_init', 'uams_multisite_site_index_widget_init' );

class UAMS_Multisite_Site_Index_Widget extends WP_Widget {
	/* Constructor */
	function __construct() {
		parent::__construct( 'UAMS Multisite_Site_Index_Widget', __( 'Site Index', 'content-slideshow' ), array(
			'customize_selective_refresh' => true,
			'description' => __( 'Displays an index of sites on this multisite network', 'multisite-site-index' ),
		) );
	}

	/* This is the Widget */
	function widget( $args, $instance ) {
		extract( $args );

		if ( ! array_key_exists( 'excluded', $instance ) ) {
			$instance['excluded'] = '';
		}

		if ( ! array_key_exists( 'number', $instance ) ) {
			$instance['number'] = 100;
		}

		if ( ! array_key_exists( 'title', $instance ) ) {
			$instance['title'] = '';
		}
		if ( ! array_key_exists( 'order', $instance ) ) {
			$instance['order'] = '';
		}
		if ( ! array_key_exists( 'orderby', $instance ) ) {
			$instance['orderby'] = '';
		}
		if ( ! array_key_exists( 'description', $instance ) ) {
			$instance['description'] = true;
		}

		// Widget options
		$title = apply_filters( 'widget_title', $instance['title'] ); // Title
		$excluded = $instance['excluded'];
		$number = $instance['number'];
		$order = $instance['order'];
		$orderby = $instance['orderby'];
		$description = $instance['description'];

		// Output
		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo uams_multisite_site_index_get_markup( $excluded, 0, $number, $description, $order, $orderby );

		echo $after_widget;
	}

	/* Widget control update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$instance['excluded'] = strip_tags( $new_instance['excluded'] );
		$instance['order'] = strip_tags( $new_instance['order'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['description'] = (bool) ( $new_instance['description'] );

		return $instance;
	}

	/* Widget settings */
	function form( $instance ) {
	    if ( $instance ) {
			$title = $instance['title'];
			$number = $instance['number'];
			$excluded  = $instance['excluded'];
			$order  = $instance['order'];
			$orderby  = $instance['orderby'];
			$description = $instance['description'];
	    }
		else {
		    // These are the defaults.
			$title = '';
			$excluded = '1';
			$number = 100;
			$order = 'ASC';
			$orderby = 'id';
			$description = true;
	    }

		// The widget form. ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:', 'multisite-site-index' ); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
		</p>
		<?php if ( 100 < get_sites( array( 'count' => true ) ) ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __( 'Number of Sites to Show:', 'multisite-site-index' ); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $number; ?>" class="widefat" />
		</p>
		<?php endif; ?>
		<p>
			<label for="<?php echo $this->get_field_id('excluded'); ?>"><?php echo __( 'Excluded site IDs:', 'multisite-site-index' ); ?></label>
			<input id="<?php echo $this->get_field_id('excluded'); ?>" name="<?php echo $this->get_field_name('excluded'); ?>" type="text" value="<?php echo $excluded; ?>" class="widefat" />
			<br />
			<small><?php _e( 'Site IDs, separated by commas.' ); ?> <?php _e( 'Remove 1 to include the primary site.' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php echo __( 'Order (ASC or DESC):', 'multisite-site-index' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" class="widefat">
				<option value="ASC"<?php selected( $instance['order'], 'ASC' ); ?>><?php _e('ASC (A - Z)'); ?></option>
				<option value="DESC"<?php selected( $instance['order'], 'DESC' ); ?>><?php _e('DESC (Z - A)'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php echo __( 'Order by:', 'multisite-site-index' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" class="widefat">
			  <option value="id"<?php selected( $instance['orderby'], 'id' ); ?>><?php _e( 'Site ID' ); ?></option>
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e('Site Title'); ?></option>
				<option value="last_updated"<?php selected( $instance['orderby'], 'last_updated' ); ?>><?php _e('Last Updated'); ?></option>
			</select>
		</p>
		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" <?php checked( $description, true, true ) ?> /> <label for="<?php echo $this->get_field_name('description'); ?>"><?php _e( 'Show site description' ); ?></label></p>
	<?php
	}

} // class Multisite_Site_Index_Widget

// Return the markup for the site index.
function uams_multisite_site_index_get_markup( $excluded = '', $recent = 0, $number = 100, $description = true, $order = 'ASC', $orderby = 'id' ) {
	if ( ! is_multisite() ) {
		return '';
	}

	$ex_ids = array();
	$excluded = explode( ',', $excluded );
	foreach( $excluded as $id ) {
		$ex_ids[] = absint( trim( $id ) );
	}

	$sites = get_sites( array(
		'site__not_in' => $ex_ids,
		'orderby' => $orderby,
		'order' => $order,
		'fields' => 'ids',
		'number' => $number,
		'deleted' => 0,
	) );

	if ( empty ( $sites ) ) {
		return '';
	}

	$sortorder = array();
	foreach ( $sites as $site ) {
		switch_to_blog( $site );
		$innerhtml .= '<li class="site">';
		// Show the site icon and title - based on the code for embeds.
		$innerhtml .= sprintf(
			'<a href="%s" target="_top"><strong class="site-index-site-title">%s</strong></a>',
			esc_url( home_url() ),
			esc_html( get_bloginfo( 'name' ) )
		);
		// Add the site tagline.
		if ( false != $description ) {
			$innerhtml .= '<br><em class="site-index-site-tagline">' . get_bloginfo( 'description' ) . '</em>';
		}

		if ( 0 < $recent ) {
			// @todo build out a recent posts option.
		}

		$innerhtml .= '</li>';
		$sortorder[] = array( 'title'=> esc_html( get_bloginfo( 'name' ) ), 'html'=> $innerhtml );
		$innerhtml = '';
		restore_current_blog();
	}

	if ( 'name' == $orderby && 'ASC' == $order ) {
	  asort($sortorder);
	} elseif ( 'name' == $orderby && 'DESC' == $order ) {
		arsort($sortorder);
	}

	$html = '<ul class="site-index '. $orderby .'">';

	foreach($sortorder as $result) {
		$html .= $result['html'];
	}

	$html .= '</ul>';

	return $html;
}

