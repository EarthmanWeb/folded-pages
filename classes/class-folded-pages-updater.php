<?php
/**
 * Updater Class - manage updates from Github for the Folded Pages plugin
 * modified from: https://github.com/rayman813/smashing-updater-plugin
 *
 * @package Folded_Pages
 */

namespace EMMEDIA;

/**
 * Folded_Pages_Updater
 */
class Folded_Pages_Updater {

	/**
	 * Private Var
	 *
	 * @var Object $file File object.
	 */
	private $file;

	/**
	 * Private Var
	 *
	 * @var Object $plugin Plugin object.
	 */
	private $plugin;

	/**
	 * Private Var
	 *
	 * @var String $basename SIte baseurl.
	 */
	private $basename;

	/**
	 * Private Var
	 *
	 * @var Boolean $active state.
	 */
	private $active;

	/**
	 * Private Var
	 *
	 * @var String $username Github username.
	 */
	private $username;

	/**
	 * Private Var
	 *
	 * @var String $repository Github repo id.
	 */
	private $repository;

	/**
	 * Private Var
	 *
	 * @var String $authorize_token Github authorization token
	 */
	private $authorize_token;

	/**
	 * Private Var
	 *
	 * @var String $github_response Github response to api call
	 */
	private $github_response;

	/**
	 * Undocumented function
	 *
	 * @param Object $file Plugin file object.
	 */
	public function __construct( $file ) {

		$this->file = $file;

		add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );

		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function set_plugin_properties() {
		$this->plugin   = get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active   = is_plugin_active( $this->basename );
	}

	/**
	 * Set Github Username
	 *
	 * @param [type] $username Github username for checking module info.
	 * @return void
	 */
	public function set_username( $username ) {
		$this->username = $username;
	}

	/**
	 * Set Github Repo
	 *
	 * @param [type] $repository Github repo id for checking module info.
	 * @return void
	 */
	public function set_repository( $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Github Authorize
	 *
	 * @param [type] $token Github token for checking module info.
	 * @return void
	 */
	public function authorize( $token ) {
		$this->authorize_token = $token;
	}

	/**
	 * Set Github Repo Info
	 *
	 * @return void
	 */
	private function get_repository_info() {
		if ( is_null( $this->github_response ) ) { // Do we have a response?
			$args        = array();
			$request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository ); // Build URI.

			$args = array();

			if ( $this->authorize_token ) { // Is there an access token?
				$args['headers']['Authorization'] = "bearer {$this->authorize_token}"; // Set the headers...
			}

			$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri, $args ) ), true ); // Get JSON and parse it.

			if ( is_array( $response ) ) { // If it is an array...
				$response = current( $response ); // Get the first item.
			}

			$this->github_response = $response; // Set it to our property.
		}
	}

	/**
	 * Initialize Class
	 *
	 * @return void
	 */
	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );

		// Add Authorization Token to download_package...
		add_filter(
			'upgrader_pre_download',
			function() {
				add_filter( 'http_request_args', array( $this, 'download_package' ), 15, 2 );
				return false; // upgrader_pre_download filter default return value.
			}
		);
	}

	/**
	 * Modify Transients
	 *
	 * @param Object $transient WordPress transient.
	 * @return Object $transient WordPress transient
	 */
	public function modify_transient( $transient ) {

		if ( property_exists( $transient, 'checked' ) ) { // Check if transient has a checked property...
			$checked = ! empty( $transient->checked ) ? $transient->checked : null;
			if ( $checked ) { // Did WordPress check for updates?

				$this->get_repository_info(); // Get the repo info.
				$out_of_date = version_compare( $this->github_response['tag_name'], $checked[ $this->basename ], 'gt' ); // Check if we're out of date...

				if ( $out_of_date ) {

					$new_files = $this->github_response['zipball_url']; // Get the ZIP.

					$slug = current( explode( '/', $this->basename ) ); // Create valid slug.

					$plugin = array( // setup our plugin info.
						'url'         => $this->plugin['PluginURI'],
						'slug'        => $slug,
						'package'     => $new_files,
						'new_version' => $this->github_response['tag_name'],
					);

					$transient->response[ $this->basename ] = (object) $plugin; // Return it in response.
					return $transient;

				}
			}
		}

		return $transient; // Return filtered transient.
	}

	/**
	 * Provides popup information in the WordPress plugins screen
	 *
	 * @param Object $result chained result data.
	 * @param String $action wp action method being called.
	 * @param Object $args passed arguments.
	 * @return Object $result
	 */
	public function plugin_popup( $result, $action, $args ) {

		if ( ! empty( $args->slug ) ) { // If there is a slug...

			if ( current( explode( '/', $this->basename ) ) === $args->slug ) { // And it's our slug...

				$this->get_repository_info(); // Get our repo info.

				// Set it to an array.
				$plugin = array(
					'name'              => $this->plugin['Name'],
					'slug'              => $this->basename,
					// 'requires'					=> '3.3',
					// 'tested'						=> '4.4.1',
					// 'rating'						=> '100.0',
					// 'num_ratings'				=> '10823',
					// 'downloaded'				=> '14249',
					// 'added'							=> '2016-01-05',
					'version'           => $this->github_response['tag_name'],
					'author'            => $this->plugin['AuthorName'],
					'author_profile'    => $this->plugin['AuthorURI'],
					'last_updated'      => $this->github_response['published_at'],
					'homepage'          => $this->plugin['PluginURI'],
					'short_description' => $this->plugin['Description'],
					'sections'          => array(
						'Description' => $this->plugin['Description'],
						'Updates'     => $this->github_response['body'],
					),
					'download_link'     => $this->github_response['zipball_url'],
				);

				return (object) $plugin; // Return the data.
			}
		}
		return $result; // Otherwise return default.
	}

	/**
	 * Provides link to download plugin package
	 *
	 * @param Array   $args passed arguments.
	 * @param Strings $url url for plugin download.
	 * @return Array  $args
	 */
	public function download_package( $args, $url ) {

		if ( null !== $args['filename'] ) {
			if ( $this->authorize_token ) {
				$args = array_merge( $args, array( 'headers' => array( 'Authorization' => "token {$this->authorize_token}" ) ) );
			}
		}

		remove_filter( 'http_request_args', array( $this, 'download_package' ) );

		return $args;
	}

	/**
	 * After install hook
	 *
	 * @param Object $response Response from plugin install.
	 * @param Object $hook_extra details.
	 * @param Object $result Data from install.
	 * @return Object $result data
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object.

		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory.
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir.
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack.

		if ( $this->active ) { // If it was active...
			activate_plugin( $this->basename ); // Reactivate.
		}

		return $result;
	}
}
