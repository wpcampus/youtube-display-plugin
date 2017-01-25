<?php
/**
 * Plugin Name:     WPCampus - YouTube
 * Plugin URI:      https://github.com/wpcampus/youtube-display-plugin
 * Description:     Displays YouTube videos.
 * Version:         1.0.0
 * Author:          WPCampus
 * Author URI:      https://wpcampus.org/
 * License:         GPL-2.0+
 * Text Domain:     wpc-youtube
 * Domain Path:     /languages
 */

/**
 * Load our main class.
 */
class WPC_YouTube {

	public function __construct() {
		add_action( 'init', array( $this, 'add_shortcodes' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function get_youtube_display( $args = array() ) {

		// Define the array of defaults.
		$defaults = array(
			'playlistid'    => '',
			'random'        => 'false',
			'title_remove'  => '',
			'orderby'       => 'title',
			'order'         => 'ASC'
		);

		// Parse incoming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		wp_enqueue_style( 'wpc-youtube', plugins_url( 'assets/css/wpc-youtube.css', __FILE__ ) );
		wp_enqueue_style( 'magnific-popup', plugins_url( 'assets/css/magnific-popup.css', __FILE__ ) );
		wp_enqueue_script( 'magnific-popup', plugins_url( 'assets/js/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'wpc-youtube', plugins_url( 'assets/js/wpc-youtube.js', __FILE__ ), array( 'jquery' ) );

		// Check the transient first.
		$transient_name = 'youtube_json_' . md5( $args['playlistid'] );

		// Get the items from the transient.
		$youtube_items = get_transient( $transient_name );

		// If transient isnt' valid, ping YouTube.
		if ( false === $youtube_items ) {

			// Build the YouTube feed URL.
			$youtube_feed_url = add_query_arg( array(
				'part' 			=> 'id%2Csnippet',
				'playlistId' 	=> $args['playlistid'],
				'key' 			=> esc_attr( get_option( 'wpc_youtube_api_key' ) ),
				'maxResults' 	=> '50',
			), 'https://www.googleapis.com/youtube/v3/playlistItems' );

			// Get the response.
			$response = wp_remote_get(
				$youtube_feed_url,
				array(
					'method'        => 'GET',
					'timeout'       => 45,
					'redirection'   => 5,
					'httpversion'   => '1.0',
					'blocking'      => true,
					'headers'       => array(),
				)
			);

			// Get the response code.
			$response_code = wp_remote_retrieve_response_code( $response );

			// No point in continuing if not valid response.
			if ( 200 != $response_code ) {
				return;
			}

			// Get the response body.
			$youtube_items = wp_remote_retrieve_body( $response );

			$youtube_items = json_decode( $youtube_items );

			// Store response in transient for an hour.
			set_transient( $transient_name, $youtube_items, 1 * HOUR_IN_SECONDS );

		}

		// Build the items.
		$items = array();

		// iterate over entries in feed
		foreach ( $youtube_items->items as $entry ) {

			$id = $entry->snippet->resourceId->videoId;
			$watch = sprintf( 'https://www.youtube.com/watch?v=%s', $id );
			$thumbnails = (array) $entry->snippet->thumbnails;

			$thumbnail = array_pop( $thumbnails )->url;

			$entry_title = $entry->snippet->title;
			if ( ! empty( $args['title_remove'] ) ) {
				$entry_title = str_replace( $args['title_remove'], '', $entry_title );
			}

			// Clean up the title.
			$entry_title = preg_replace( '/^([\s\-\:]+)/', '', trim( $entry_title ) );
			$entry_title = preg_replace( '/([\s\-\:]+)$/', '', trim( $entry_title ) );

			// Add the item.
			$items[] = array(
				'thumbnail' => $thumbnail,
				'watch'     => $watch,
				'id'        => $id,
				'title'     => trim( $entry_title ),
			);
		}

		if ( 'true' === $args['random'] ) {
			shuffle( $items );
		} else {

			// Order the items.
			switch( $args['orderby'] ) {

				case 'title':

					// Make sure we have an order.
					$order = strcasecmp( 'desc', $args['order'] ) == 0 ? 'desc' : 'asc';

					if ( 'desc' == $order ) {
						usort( $items, function ( $a, $b ) {
							return strcasecmp( preg_replace( '/([^a-z])/i', '', $b['title'] ), preg_replace( '/([^a-z])/i', '', $a['title'] ) );
						});
					} else {
						usort( $items, function ( $a, $b ) {
							return strcasecmp( preg_replace( '/([^a-z])/i', '', $a['title'] ), preg_replace( '/([^a-z])/i', '', $b['title'] ) );
						});
					}

					break;
			}
		}

		// Make sure we have items.
		if ( empty( $items ) ) {
			return;
		}

		ob_start();

		?>
		<div class="wpc-youtube">
			<?php

			foreach ( $items as $item ) :

				?>
				<div class="wpc-youtube-video">
					<a class="popup-youtube" href="<?php echo $item['watch']; ?>">
						<div class="media">
							<img src="<?php echo $item['thumbnail']; ?>" alt="<?php printf( __( '%1$s thumbnail for %2$s video', 'wpc-youtube' ), 'YouTube', $item['title'] ); ?>" />
							<span class="play"></span>
						</div>
						<span class="caption"><?php echo $item['title']; ?></span>
					</a>
				</div>
				<?php

			endforeach;

			?>
		</div>
		<?php

		return ob_get_clean();
	}

	public function add_shortcodes() {
		add_shortcode( 'wpc_youtube', array( $this, 'youtube_shortcode' ) );
	}

	public function youtube_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'playlistid'    => '',
			'random'        => 'false',
			'title_remove'  => '',
			'orderby'       => 'title',
			'order'         => 'ASC'
		), $atts );

		return $this->get_youtube_display( $atts );
	}

	public function add_admin_page() {
		add_submenu_page(
			'options-general.php',
			sprintf( __( '%s YouTube', 'wpc-youtube' ), 'WPCampus' ),
			sprintf( __( '%s YouTube', 'wpc-youtube' ), 'WPCampus' ),
			'manage_options',
			'wpc-youtube',
			array( $this,'options_page' )
		);
	}

	public function options_page() {

		?>
		<div class="wrap">
			<h1><?php printf( __( '%1$s %2$s Options', 'wpc-youtube' ), 'WPCampus', 'YouTube' ); ?></h1>
			<form method="post" action="options.php">
				<?php

				settings_fields( 'wpc_youtube' );
				do_settings_sections( 'wpc_youtube' );

				?>
				<p><label for="wpc_youtube_api_key">YouTube API Key</label> <input type="text" name="wpc_youtube_api_key" id="wpc_youtube_api_key" value="<?php echo esc_attr( get_option( 'wpc_youtube_api_key' ) ); ?>" size="50"/></p>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php

	}

	function register_settings() {
		register_setting( 'wpc_youtube', 'wpc_youtube_api_key' );
	}
}
new WPC_YouTube();
