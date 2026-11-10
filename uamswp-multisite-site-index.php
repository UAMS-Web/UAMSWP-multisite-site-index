<?php
/**
 * Plugin Name: UAMSWP Multisite Site Index
 * Plugin URI: http://celloexpressions.com/plugins/multisite-site-index/
 * Description: Display an index of all sites on a multisite network with a widget or a shortcode.
 * Version: 1.1.0.4
 * Requires PHP: 8.2
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
add_shortcode('site-index', 'uams_multisite_site_index_do_shortcode');
/**
 * Render the `[site-index]` shortcode.
 *
 * @param  array<int|string, mixed>|string  $atts  The shortcode attributes; WordPress passes an empty string when there are none.
 */
function uams_multisite_site_index_do_shortcode($atts): string
{
    if (! is_multisite()) {
        return '';
    }

    extract(shortcode_atts([
        'excluded' => '',
        'number' => 100,
        'recent' => 0,
        'description' => true,
        'order' => 'ASC',
        'orderby' => 'id',
    ], is_array($atts) ? $atts : []));

    return uams_multisite_site_index_get_markup($excluded, $recent, $number, $description, $order, $orderby);
}

/**
 * Read a saved setting as a string, or `$default` when it holds something that is not a scalar.
 */
function uams_multisite_site_index_string(mixed $value, string $default = ''): string
{
    // This file does not declare strict types, so the `: string` return type already converts a scalar the way
    // `(string)` does, and no input tells the two apart. PHPStan still needs the cast to see a string.
    // @pest-mutate-ignore: RemoveStringCast
    return is_scalar($value) ? (string) $value : $default;
}

/**
 * Read a saved setting as an integer, or `$default` when it holds something that is not a scalar.
 */
function uams_multisite_site_index_int(mixed $value, int $default = 0): int
{
    return is_scalar($value) ? (int) $value : $default;
}

// Register 'Multisite Site Index' widget.
function uams_multisite_site_index_widget_init(): void
{
    if (! is_multisite()) {
        return;
    }
    register_widget('UAMS_Multisite_Site_Index_Widget');
}
add_action('widgets_init', 'uams_multisite_site_index_widget_init');

class UAMS_Multisite_Site_Index_Widget extends WP_Widget
{
    /* Constructor */
    public function __construct()
    {
        parent::__construct('UAMS Multisite_Site_Index_Widget', __('Site Index', 'content-slideshow'), [
            'description' => __('Displays an index of sites on this multisite network', 'multisite-site-index'),
        ]);

        // WP_Widget accepts this option; the WordPress stubs' sealed options shape omits it.
        $this->widget_options['customize_selective_refresh'] = true;
    }

    /**
     * This is the Widget.
     *
     * @param  array{name: string, id: string, description: string, class: string, before_widget: string, after_widget: string, before_title: string, after_title: string, before_sidebar: string, after_sidebar: string, show_in_rest: bool, widget_id: string, widget_name: string}  $args
     * @param  array<string, mixed>  $instance
     */
    public function widget($args, $instance): void
    {
        if (! array_key_exists('excluded', $instance)) {
            $instance['excluded'] = '';
        }

        if (! array_key_exists('number', $instance)) {
            $instance['number'] = 100;
        }

        if (! array_key_exists('title', $instance)) {
            $instance['title'] = '';
        }
        if (! array_key_exists('order', $instance)) {
            $instance['order'] = 'ASC';
        }
        if (! array_key_exists('orderby', $instance)) {
            $instance['orderby'] = 'id';
        }
        if (! array_key_exists('description', $instance)) {
            $instance['description'] = true;
        }

        // Widget options
        $title = uams_multisite_site_index_string(apply_filters('widget_title', $instance['title'])); // Title
        $excluded = uams_multisite_site_index_string($instance['excluded']);
        $number = uams_multisite_site_index_int($instance['number']);
        $order = uams_multisite_site_index_string($instance['order']);
        $orderby = uams_multisite_site_index_string($instance['orderby']);
        $description = (bool) $instance['description'];

        // Output
        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'].$title.$args['after_title'];
        }

        echo uams_multisite_site_index_get_markup($excluded, 0, $number, $description, $order, $orderby);

        echo $args['after_widget'];
    }

    /**
     * Widget control update.
     *
     * @param  array<string, mixed>  $new_instance
     * @param  array<string, mixed>  $old_instance
     * @return array<string, mixed>
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags(uams_multisite_site_index_string($new_instance['title']));
        $instance['number'] = absint(uams_multisite_site_index_int($new_instance['number']));
        $instance['excluded'] = strip_tags(uams_multisite_site_index_string($new_instance['excluded']));
        $instance['order'] = strip_tags(uams_multisite_site_index_string($new_instance['order']));
        $instance['orderby'] = strip_tags(uams_multisite_site_index_string($new_instance['orderby']));
        $instance['description'] = (bool) ($new_instance['description']);

        return $instance;
    }

    /**
     * Widget settings.
     *
     * @param  array<string, mixed>  $instance
     * @return string|null Always null: the form is echoed, and the parent method returns `'noform'` only for a widget with no form.
     */
    public function form($instance): ?string
    {
        if ($instance) {
            $title = uams_multisite_site_index_string($instance['title']);
            $number = uams_multisite_site_index_int($instance['number']);
            $excluded = uams_multisite_site_index_string($instance['excluded']);
            $order = uams_multisite_site_index_string($instance['order']);
            $orderby = uams_multisite_site_index_string($instance['orderby']);
            $description = $instance['description'];
        } else {
            // These are the defaults.
            $title = '';
            $excluded = '1';
            $number = 100;
            $order = 'ASC';
            $orderby = 'id';
            $description = true;
        }

        // The widget form.?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:', 'multisite-site-index'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
		</p>
		<?php if (get_sites(['count' => true]) > 100) { ?>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __('Number of Sites to Show:', 'multisite-site-index'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $number; ?>" class="widefat" />
		</p>
		<?php } ?>
		<p>
			<label for="<?php echo $this->get_field_id('excluded'); ?>"><?php echo __('Excluded site IDs:', 'multisite-site-index'); ?></label>
			<input id="<?php echo $this->get_field_id('excluded'); ?>" name="<?php echo $this->get_field_name('excluded'); ?>" type="text" value="<?php echo $excluded; ?>" class="widefat" />
			<br />
			<small><?php _e('Site IDs, separated by commas.'); ?> <?php _e('Remove 1 to include the primary site.'); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php echo __('Order (ASC or DESC):', 'multisite-site-index'); ?></label>
			<select name="<?php echo esc_attr($this->get_field_name('order')); ?>" id="<?php echo esc_attr($this->get_field_id('order')); ?>" class="widefat">
				<option value="ASC"<?php selected($order, 'ASC'); ?>><?php _e('ASC (A - Z)'); ?></option>
				<option value="DESC"<?php selected($order, 'DESC'); ?>><?php _e('DESC (Z - A)'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php echo __('Order by:', 'multisite-site-index'); ?></label>
			<select name="<?php echo esc_attr($this->get_field_name('orderby')); ?>" id="<?php echo esc_attr($this->get_field_id('orderby')); ?>" class="widefat">
			  <option value="id"<?php selected($orderby, 'id'); ?>><?php _e('Site ID'); ?></option>
				<option value="name"<?php selected($orderby, 'name'); ?>><?php _e('Site Title'); ?></option>
				<option value="last_updated"<?php selected($orderby, 'last_updated'); ?>><?php _e('Last Updated'); ?></option>
			</select>
		</p>
		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" <?php checked($description, true, true) ?> /> <label for="<?php echo $this->get_field_name('description'); ?>"><?php _e('Show site description'); ?></label></p>
	<?php

        return null;
    }
} // class Multisite_Site_Index_Widget

/**
 * Return the markup for the site index.
 *
 * @todo Build out a recent posts option: `$recent` is reserved for it, and callers still pass it positionally.
 *
 * @param  string  $excluded  Comma-separated site IDs to leave out.
 * @param  int|string  $recent  Deprecated and unused; kept in the signature so positional callers keep working.
 * @param  int|string  $number  The most sites to list.
 * @param  bool|int|string  $description  Whether to show each site's tagline.
 * @param  string  $order  `ASC` or `DESC`.
 * @param  string  $orderby  `id`, `name` or `last_updated`.
 */
function uams_multisite_site_index_get_markup($excluded = '', $recent = 0, $number = 100, $description = true, $order = 'ASC', $orderby = 'id'): string
{
    if (! is_multisite()) {
        return '';
    }

    $ex_ids = [];
    $excluded = explode(',', $excluded);
    foreach ($excluded as $id) {
        // absint() is abs(intval()), and intval() ignores surrounding whitespace, so trim() changes nothing.
        // @pest-mutate-ignore: UnwrapTrim
        $ex_ids[] = absint(trim($id));
    }

    $sites = get_sites([
        'site__not_in' => $ex_ids,
        'orderby' => $orderby,
        'order' => $order,
        'fields' => 'ids',
        'number' => absint($number),
        'deleted' => 0,
    ]);

    if (empty($sites)) {
        return '';
    }

    $sortorder = [];
    $innerhtml = '';
    foreach ($sites as $site) {
        switch_to_blog($site);
        // $innerhtml is empty here (declared above, reset at the end of each pass), so `.=` and `=` agree.
        // @pest-mutate-ignore: ConcatEqualToEqual
        $innerhtml .= '<li class="site">';
        // Show the site icon and title - based on the code for embeds.
        $innerhtml .= sprintf(
            '<a href="%s" target="_top"><strong class="site-index-site-title">%s</strong></a>',
            esc_url(home_url()),
            esc_html(get_bloginfo('name'))
        );
        // Add the site tagline.
        if ($description != false) {
            $innerhtml .= '<br><em class="site-index-site-tagline">'.get_bloginfo('description').'</em>';
        }

        $innerhtml .= '</li>';
        $sortorder[] = ['title' => esc_html(get_bloginfo('name')), 'html' => $innerhtml];
        $innerhtml = '';
        restore_current_blog();
    }

    // `==` and `===` differ only for a non-string value, and the shortcode and the widget always pass strings.
    // @pest-mutate-ignore: EqualToIdentical
    $sortByName = $orderby == 'name';
    // Same reason as $sortByName.
    // @pest-mutate-ignore: EqualToIdentical
    $ascending = $order == 'ASC';
    // Same reason as $sortByName.
    // @pest-mutate-ignore: EqualToIdentical
    $descending = $order == 'DESC';

    if ($sortByName && $ascending) {
        asort($sortorder);
    } elseif ($sortByName && $descending) {
        arsort($sortorder);
    }

    $html = '<ul class="site-index '.$orderby.'">';

    foreach ($sortorder as $result) {
        $html .= $result['html'];
    }

    $html .= '</ul>';

    return $html;
}
