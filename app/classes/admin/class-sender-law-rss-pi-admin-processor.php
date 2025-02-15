<?php
/*

  Copyright (C) 2015 Sender.Law

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
 * Processes the admin screen form submissions
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class senderLawRssPIAdminProcessor {

  /**
   * If we have a valid api key
   * 
   * @var boolean
   */
  var $is_key_valid;

  /**
   * Process the form result
   * 
   * @global object $rss_post_importer
   * @return null
   */
  function process() {

    // if there's nothing for processing or invalid data, bail
    if (!isset($_POST['info_update']) || !wp_verify_nonce($_POST['rss_pi_nonce'], 'settings_page')) {
      return;
    }


    // Get ids of feed-rows
    $ids = explode(",", sanitize_text_field($_POST['ids']));

    // formulate the settings array
    $settings = $this->process_settings();

    // check result for "invalid_key" flag
    $invalid_api_key = isset($settings['invalid_api_key']);
    unset($settings['invalid_api_key']);

    // update cron settings
    $this->update_cron($settings['frequency']);

    // formulate the feeds array
    $feeds = $this->process_feeds($ids);

    // save and reload the options
    $this->save_reload_options($settings, $feeds);

    global $rss_post_importer;

    wp_redirect(add_query_arg(
      array(
        'settings-updated' => 'true',
        'import' => ( $_POST['save_to_db'] == 'true' ),
        'message' => $invalid_api_key ? 2 : 1
      ),
      $rss_post_importer->page_link
    ));
    exit;
  }

  /**
   * Process submitted data to formulate settings array
   * 
   * @global object $rss_post_importer
   * @return array
   */
  private function process_settings() {

    global $rss_post_importer;

    //first, determine if the role will need updating
    $role = $rss_post_importer->options['settings']['role'];
    if($_POST['feeds_api_key'] !== $rss_post_importer->options['feeds_api_key']) {
      $url = SENDERLAW_ELA_RSS_PI_BASE_URL . 'type/role-check/token/' . urlencode(sanitize_text_field($_POST['feeds_api_key'])) . '/version/' . SENDERLAW_ELA_RSS_PI_VERSION;
      $role = trim(file_get_contents($url));
    }

    // Get selected settings for all imported posts
    $settings = array(
      'frequency' => sanitize_text_field($_POST['frequency']),
      'feeds_api_key' => sanitize_text_field($_POST['feeds_api_key']),
      'post_template' => sanitize_text_field(stripslashes_deep($_POST['post_template'])),
      'estate_post_status' => sanitize_text_field($_POST['estate_post_status']),
      'estate_author_id' => sanitize_text_field($_POST['estate_author_id']),
      'estate_allow_comments' => sanitize_text_field($_POST['estate_allow_comments']),
      'block_indexing' => sanitize_text_field($_POST['block_indexing']),
      'nofollow_outbound' => sanitize_text_field($_POST['nofollow_outbound']),
      'enable_logging' => true,
      'import_images_locally' => sanitize_text_field($_POST['import_images_locally']),
      'disable_thumbnail' => sanitize_text_field($_POST['disable_thumbnail']),
      'estate_category' => sanitize_text_field($_POST['estate_category_id']),
      'role' => $role,
      'keywords' => array()
    );

    // check if submitted api key is valid
    $this->is_key_valid = $rss_post_importer->is_valid_key($settings['feeds_api_key']);
    // save key validity state
    $settings['is_key_valid'] = $this->is_key_valid;

    return $settings;
  }

  /**
   * Update the frequency of the import cron job
   * 
   * @param string $frequency
   */
  private function update_cron($frequency) {

    // If cron settings have changed
    if (wp_get_schedule('rss_pi_cron') != $frequency) {

      // Reset cron
      wp_clear_scheduled_hook('rss_pi_cron');
      wp_schedule_event(time(), $frequency, 'rss_pi_cron');
    }
  }

  /**
   * Forms the feeds array from submitted data
   * 
   * @param array $ids feeds ids
   * @return array
   */
  private function process_feeds($ids) {

    $feeds = array();

    foreach ($ids as $id) {
      array_push($feeds, array(
        'id' => 0,
        'url' => (SENDERLAW_ESTATE_RSS_PI_BASE_URL),
        'name' => '',
        'author_id' => sanitize_text_field($_POST['estate_author_id']),
        'category_id' => (isset($_POST['estate_category_id'])) ? sanitize_text_field($_POST['estate_category_id']) : '',
        'tags_id' => (isset($_POST['estate-tags_id'])) ? sanitize_text_field($_POST['estate-tags_id']) : ''
      ));
    }

    return $feeds;
  }

  /**
   * Update options and reload global options
   * 
   * @global type $rss_post_importer
   * @param array $settings
   * @param array $feeds
   */
  private function save_reload_options($settings, $feeds) {

    global $rss_post_importer;

    // existing options
    $options = $rss_post_importer->options;

    // new data
    $new_options = array(
      'feeds' => $feeds,
      'settings' => $settings,
      'latest_import' => $options['latest_import'],
      'imports' => $options['imports']
    );

    // update in db
    update_option('rss_pi_feeds', $new_options);

    // reload so that the new options are used henceforth
    $rss_post_importer->load_options();
  }

  /**
   * Import feeds
   * 
   * @return null
   */
  private function import() {

    // if we don't need to import anything, bail
    if ($_POST['save_to_db'] != 'true') {
      return;
    }

    // initialise the engine and import
    $engine = new \SenderLaw\senderLawRssPIEngine();
    $imported = $engine->import_feed();

    return $imported;
  }
}
