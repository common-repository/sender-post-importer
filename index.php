<?php

/*
  Plugin Name: Sender Post Importer
  Plugin URI: https://sender.law/plugin
  Description: This plugin allows you to import posts from the Sender Law RSS feed for use as content in your Wordpress site
  Author: sender.law
  Version: 1.0.0
  Author URI: https://sender.law/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /lang/

  Copyright (C) 2020  Sender.Law

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

defined( 'ABSPATH' ) or die( 'Nothing to see here, please move along' );

// define some constants
if (!defined('SENDERLAW_RSS_PI_PATH')) {
  define('SENDERLAW_RSS_PI_PATH', trailingslashit(plugin_dir_path(__FILE__)));
}

if (!defined('SENDERLAW_RSS_PI_URL')) {
  define('SENDERLAW_RSS_PI_URL', trailingslashit(plugin_dir_url(__FILE__)));
}

if (!defined('SENDERLAW_RSS_PI_BASENAME')) {
  define('SENDERLAW_RSS_PI_BASENAME', plugin_basename(__FILE__));
}

if (!defined('SENDERLAW_ELA_RSS_PI_VERSION')) {
  define('SENDERLAW_ELA_RSS_PI_VERSION', '1.0.0');
}

if (!defined('SENDERLAW_ELA_RSS_PI_BASE_URL')) {
  define('SENDERLAW_ELA_RSS_PI_BASE_URL', 'https://sender.law/news-and-information/rss-full/');
}

if (!defined('SENDERLAW_ESTATE_RSS_PI_BASE_URL')) {
  define('SENDERLAW_ESTATE_RSS_PI_BASE_URL', 'https://sender.law/news-and-information/rss-full/');
}

if (!defined('SENDERLAW_ASNP_RSS_PI_BASE_URL')) {
  define('SENDERLAW_ASNP_RSS_PI_BASE_URL', 'https://sender.law/news-and-information/rss-full/');
}

if (!defined('SENDERLAW_ELA_ATTORNEY_SIGNUP_URL')) {
  define('SENDERLAW_ELA_ATTORNEY_SIGNUP_URL', 'https://sender.law/');
}

// helper classes
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/helpers/class-sender-law-rss-pi-log.php';
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/helpers/class-sender-law-rss-pi-featured-image.php';
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/helpers/class-sender-law-rss-pi-parser.php';

// admin classes
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/admin/class-sender-law-rss-pi-admin-processor.php';
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/admin/class-sender-law-rss-pi-admin.php';

// Front classes
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/front/class-sender-law-rss-pi-front.php';

// main importers
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/import/class-sender-law-rss-pi-engine.php';
include_once SENDERLAW_RSS_PI_PATH . 'app/classes/import/class-sender-law-rss-pi-cron.php';

// the main loader class
include_once SENDERLAW_RSS_PI_PATH . 'app/class-sender-law-rss-post-importer.php';

// initialise plugin as a global var
global $rss_post_importer;

$rss_post_importer = new \SenderLaw\senderLawRssPostImporter();

$rss_post_importer->init();

