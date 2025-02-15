<?php
/*

  Copyright (C) 2015  Sender.Law

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace SenderLaw;

/**
 * Sets a featured image
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
if (!function_exists('download_url')) {
    require_once(ABSPATH . '/wp-admin/includes/file.php');
};

if (!function_exists('media_handle_sideload')) {
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
};

class senderLawRssPIFeaturedImage {

    /**
     * Sets featured image
     * 
     * @param object $item Feed item
     * @param int $post_id Post id
     * @return boolean
     */
    function _set($item, $post_id) {

        $content = $item->get_content() != "" ? $item->get_content() : $item->get_description();

        // catch base url
        preg_match('/href="(.+?)"/i', $content, $matches);
        $baseref = (is_array($matches) && !empty($matches)) ? $matches[1] : '';

        // get the first image from content
        preg_match('/<img.+?src="(.+?)"[^}]+>/i', $content, $matches);
        $img_url = (is_array($matches) && !empty($matches)) ? $matches[1] : '';

        if (empty($img_url)) {
            return false;
        }

        $img_host = parse_url($img_url, PHP_URL_HOST);

        if (empty($img_host)) {

            if (empty($baseref)) {
                return false;
            };

            $bc = parse_url($baseref);
            $scheme = (empty($bc["scheme"])) ? "http" : $bc["scheme"];
            $port = $bc["port"];
            $host = $bc["host"];
            if (empty($host)) {
                return false;
            };

            $img_url = $scheme . ":" . $port . "//" . $host . $img_url;
        }

        // get the first image from content
        /* preg_match('/<img.+?src="(.+?)"[^}]+>/i', $content, $matches);
          $img_url = (is_array($matches) && !empty($matches)) ? $matches[1] : '';

          if (empty($img_url)) {
          return false;
          }
         */
        // sideload it
        $featured_id = $this->_sideload( $img_url, $post_id, '' );

        if ( ! is_wp_error($featured_id) ) {
//			add_action('set_rss_pi_featured_image', $featured_id, $post_id);
            do_action( 'set_rss_pi_featured_image', $featured_id, $post_id );
            // set as featured image
            $meta_id = set_post_thumbnail($post_id, $featured_id);
        } else {
            $meta_id = 0;
        }

        return $meta_id;
    }

    /**
     *  Modification of default media_sideload_image
     * 
     * @param type $file
     * @param type $post_id
     * @param type $desc
     * @return type
     */
    private function _sideload($file, $post_id, $desc = null) {

        $id = 0;

        if (!empty($file)) {
            // Set variables for storage, fix file filename for query strings.
            preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
            $file_array = array();
            $file_array['name'] = basename($file);

            // Download file to temp location.
            $file_array['tmp_name'] = download_url($file);

            // If error storing temporarily, return the error.
            if (is_wp_error($file_array['tmp_name'])) {
                return $file_array['tmp_name'];
            }

            // Do the validation and storage stuff.
            $id = media_handle_sideload($file_array, $post_id, $desc);

            // If error storing permanently, unlink.
            if (is_wp_error($id)) {
                @unlink($file_array['tmp_name']);
                return $id;
            }
        }

        return $id;
    }

}
