<?php
/**
 * Plugin Name: Multisite Site Feed
 * Description: Outputs a JSON list of all sites in a WordPress installation
 * Version: 0.1
 * Author: cgrymala
 * Network: true
 * License: GPL2
 */

if ( ! class_exists( 'Multisite_Site_Feed' ) ) {
	class Multisite_Site_Feed {
		var $version = '0.1';
		var $dbversion = '2015-05-01/12:00:00';

		/**
		 * Construct our Multisite_Site_Feed object
		 *
		 * @uses Multisite_Site_Feed::dbversion
		 * @uses get_option() to see if we need to flush the rewrite rules again
		 * @uses add_action() to add multiple functions to the init action
		 */
		function __construct() {
			$dbv = get_option( '_multisite_site_feed_db_version', false );

			add_action( 'init', array( $this, 'add_feed' ) );
			/*add_filter( 'status_header', array( $this, 'status_header' ), 99, 4 );*/

			if ( $dbv != $this->dbversion ) {
				add_action( 'init', array( $this, 'flush_rules' ) );
			}
		}

		/**
		 * Retrieve a transient from the database; retrieve site-wide transient if
		 * 		this is a multisite install; otherwise, retrieve a blog-specific transient
		 *
		 * @uses is_multisite() to determine if this is a multisite install
		 * @uses get_site_transient() || get_transient() to retrieve the value
		 *
		 * @param string $key='_ms_site_feed_list' the key of the transient to be retrieved
		 *
		 * @return mixed the value of the transient if it exists & hasn't expired; bool false if
		 * 		it doesn't exist or is expired
		 */
		function _get_transient( $key='_ms_site_feed_list' ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				return maybe_unserialize( get_site_transient( $key ) );
			} else {
				return maybe_unserialize( get_transient( $key ) );
			}
		}

		/**
		 * Set a new transient in the database; set it as a network-wide transient if
		 * 		this is a multisite install; otherwise, set it as a blog-specific transient
		 *
		 * @uses apply_filters() to filter the transient timeout with the 'ms-site-feed-list-transient-timeout' filter
		 * @uses DAY_IN_SECONDS as the default timeout
		 * @uses set_site_transient() || set_transient() to set the value
		 *
		 * @param string $key='_ms_site_feed_list' the key of the transient to be set
		 * @param mixed $val=null the value to be set for the transient
		 *
		 * @return void
		 */
		function _set_transient( $key='_ms_site_feed_list', $val=null ) {
			$transient_time = apply_filters( 'ms-site-feed-list-transient-timeout', DAY_IN_SECONDS );
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				set_site_transient( $key, $val, $transient_time );
			} else {
				set_transient( $key, $val, $transient_time );
			}
		}

		/**
		 * Register the new JSON feed
		 */
		function add_feed() {
			add_feed( 'site-feed.json', array( $this, 'site_list' ) );
		}

		/**
		 * Flush the Rewrite Rules, if necessary, to make the feed available
		 *
		 * @uses WP_Rewrite::flush_rules()
		 * @uses update_option() to set a value in the database that keeps us from doing this unnecessarily
		 *
		 * @return void
		 */
		function flush_rules() {
			global $wp_rewrite;
			if ( is_object( $wp_rewrite ) ) {
				$wp_rewrite->flush_rules();
				update_option( '_multisite_site_feed_db_version', $this->dbversion );
			}
		}

		/**
		 * Retrieve the list of registered sites in this install
		 *
		 * @uses Multisite_Site_Feed::_get_transient() to retrieve a cached value
		 * @uses WPDB::get_results() to retrieve the list of sites
		 * @uses WPDB::prepare() to escape the SQL query
		 * @uses Multisite_Site_Feed::_set_transient() to set a cached value
		 * @uses json_encode() to prepare the results for consumption
		 *
		 * @return string|bool the JSON-encoded list of sites; returns bool false if this isn't multisite
		 */
		function get_site_list() {
			if ( ! function_exists( 'is_multisite' ) || ! is_multisite() ) {
				return false;
			}

			$sites = $this->_get_transient();
			if ( false !== $sites ) {
				return json_encode( $sites );
			}

			global $wpdb;
			$sites = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, domain, path, public FROM {$wpdb->blogs} WHERE public >= %d AND archived=%d AND mature=%d AND spam=%d AND deleted=%d ORDER BY domain, path", 0, 0, 0, 0, 0 ) );
			$this->_set_transient( '_ms_site_feed_list', $sites );

			return json_encode( $sites );
		}

		/**
		 * Output the list of sites & die
		 *
		 * @uses Multisite_Site_Feed::get_site_list() to retrieve the output
		 *
		 * @return void
		 */
		function site_list() {
			status_header( 200 );
			header( 'Content-Type: application/javascript' );
			echo $this->get_site_list();
			die();
		}
		
		/**
		 * Attempt to override the fact that WordPress sets 404 as the status
		 */
		function status_header( $status, $code, $description, $protocol ) {
			return "$protocol 200 OK";
		}
	}
	/* End Class Definition */

	/**
	 * Instantiate a global object of the Multisite_Site_Feed class
	 * @uses $GLOBALS['ms_site_feed_obj']
	 */
	function inst_ms_site_feed_obj() {
		global $ms_site_feed_obj;
		$ms_site_feed_obj = new Multisite_Site_Feed;
	}
	add_action( 'plugins_loaded', 'inst_ms_site_feed_obj' );
}
