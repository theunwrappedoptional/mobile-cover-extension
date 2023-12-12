<?php /**
* Plugin Name: Mobile Cover Extension
* Plugin URI:
* Description: Upload a mobile friendly image for the Cover Block
* Version: 1.0.0
* Requires at least: 5.3
* Requires PHP: 5.3
* Author: MC
* Author URI: https://theunwrappedoptional.github.io/theunwrappedoptional/
* License: GPL v3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: mobile-cover-extension
* Domain Path:
*/

/*
	Upload a mobile friendly image for the Cover Block.

    Copyright (C) 2023 MC

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

function enqueue_mobile_cover_extension() {
  
  $dir = plugin_dir_url( __FILE__ );

  wp_register_style( 'mobile-cover-style', $dir.'css/mobile-cover-style.css' );
  wp_enqueue_style( 'mobile-cover-style' );

  wp_enqueue_script('mobile-cover', $dir.'js/mobile-cover.js', [ 'wp-blocks', 'wp-dom' ] , null, true);
}

add_action('enqueue_block_editor_assets', 'enqueue_mobile_cover_extension', 100);

function enqueue_mobile_cover_css() {
	wp_enqueue_style( 'mobile-cover-style', plugin_dir_url( __FILE__ ).'css/mobile-cover-style.css');
}

add_action( 'wp_enqueue_scripts', 'enqueue_mobile_cover_css' );

/*
	- UPDATE 2023 -
	As of latest WordPress, if you set the Cover to “Fixed Background”, the <img> will be replaced with <div role="img">. That means we can no longer uses <picture>.
	The code below has conditional to check that. If using <div>, it will add extra CSS variable instead of wrapping it in <picture>
*/

add_filter('render_block_core/cover', 'my_responsive_cover_render', 10, 2);

function my_responsive_cover_render($content, $block) {
  // If has mobile image
  if ((isset($block['attrs']['mobileImageURL'])) && ($block['attrs']['mobileImageURL'] != '')) {
    
	$image = $block['attrs']['mobileImageURL'];

    preg_match('/<div role="img"/', $content, $is_fixed);

    // If fixed background, add CSS variable
    if ($is_fixed) {
      $content = preg_replace(
        '/(<div role="img".+style=".+)(">)/Ui',
        "$1;--mobileImageURL:url({$image});$2",
        $content
      );
    }
    // If not fixed, wrap in <picture>
    else {
		$content = preg_replace(
			'/<img class="wp-block-cover__image.+\/>/Ui',
			"<picture><source srcset='{$image}' media='(max-width:767px)'>$0</picture>",
			$content
      );
    }
  }

  return $content;
}
