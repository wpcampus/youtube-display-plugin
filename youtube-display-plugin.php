<?php
/**
 * Plugin Name:     Truman YouTube Display
 * Plugin URI:      http://its.truman.edu
 * Description:     Provides an example plugin to build off of.
 * Version:         1.1.0
 * Author:          Greg Marshall
 * Author URI:      http://its.truman.edu
 * License:         Proprietary
 */

/**
 * Load our main class.
 */
class TrumanYouTube {

	public function __construct() {
		add_action( 'init', array( $this, 'add_shortcodes' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function get_youtube_display( $args = array() ) {

		// Define the array of defaults.
		$defaults = array(
			'playlistid'    => '',
			'random'        => 'true',
		);

		// Parse incoming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		wp_enqueue_style( 'truman_youtube_style', plugins_url( 'assets/css/truman_youtube_display.css', __FILE__ ) );
		wp_enqueue_style( 'magnific-popup', plugins_url( 'assets/css/magnific-popup.css', __FILE__ ) );
		wp_enqueue_script( 'magnific-popup', plugins_url( 'assets/js/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'truman_youtube_script', plugins_url( 'assets/js/truman_youtube_display.js', __FILE__ ), array( 'jquery' ) );

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
				'key' 			=> esc_attr( get_option( 'youtube_display_api_key' ) ),
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

			// Add the item.
			$items[] = array(
				'thumbnail' => $thumbnail,
				'watch'     => $watch,
				'id'        => $id,
				'title'     => $entry->snippet->title,
			);
		}

		if ( 'true' === $args['random'] ) {
			shuffle( $items );
		}

		$itemcounter = 0;

		$layoutstring = get_option( 'youtube_display_layout_json' );

		$sizearray = json_decode( $layoutstring );

		ob_start();

		while ( $itemcounter < count( $items ) ) {

			// This will search in the 2 jsons
			foreach ( $sizearray as $key => $jsons ) {

				?>
				<div class="wpc-youtube">
					<?php

					foreach ( $jsons as $key => $value ) :
						if ( $itemcounter < count( $items ) ) :

							$item = $items[ $itemcounter ];

							?>
							<div class="wpc-youtube-video <?php echo $value->vidclass; ?>">
								<div class="vid">
									<a class="popup-youtube" href="<?php echo $item['watch']; ?>">
										<img src="<?php echo $item['thumbnail']; ?>" alt="YouTube thumbnail for <?php echo $item['title']; ?> video" />
										<span class="play"></span>
										<span class="vid-caption <?php echo $value->captionclass; ?>"><?php echo $item['title']; ?></span>
									</a>
								</div>
							</div>
							<?php

							$itemcounter++;

						endif;
					endforeach;

					?>
				</div>
				<?php

			}
		}

		return ob_get_clean();
	}

	public function add_shortcodes() {
		add_shortcode( 'truman-youtube-display', array( $this, 'truman_youtube_shortcode' ) );
	}

	public function truman_youtube_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'playlistid'    => '',
			'random'        => 'true',
		), $atts );

		return $this->get_youtube_display( $atts );
	}

	public function add_admin_page() {
		add_submenu_page(
			'options-general.php',
			'YouTube Display',
			'YouTube Display',
			'manage_options',
			'youtube_options',
			array( $this,'options_page' )
		);
	}

	public function options_page() {

		?>
		<div class="wrap">
			<h1>YouTube Display Plugin Options</h1>
			<form method="post" action="options.php">
				<?php

				settings_fields( 'truman_youtube_display' );
				do_settings_sections( 'truman_youtube_display' );

				?>
				<p><label for="youtube_display_api_key">YouTube API Key</label> <input type="text" name="youtube_display_api_key" id="youtube_display_api_key" value="<?php echo esc_attr( get_option( 'youtube_display_api_key' ) ); ?>" size="50"/></p>
				<p><label for="youtube_display_layout_json">Layout JSON</label> <textarea name="youtube_display_layout_json" id="youtube_display_layout_json" style="width: 100%; height: 300px"><?php echo esc_attr( get_option( 'youtube_display_layout_json' ) ); ?></textarea></p>
				<?php submit_button(); ?>
			</form>
			<p>Sample Layout JSON:</p>
			<pre>[
			{
			"vid1":{
			"vidclass":"col-md-8 first",
			"captionclass":"videocaption"
			},
			"vid2":{
			"vidclass":"col-md-4 last",
			"captionclass":"videocaption"
			},
			"vid3":{
			"vidclass":"col-md-4 last",
			"captionclass":"videocaption"
			}
			},
			{
			"vid1":{
			"vidclass":"col-md-6 first",
			"captionclass":"videocaption"
			},
			"vid2":{
			"vidclass":"col-md-6 last",
			"captionclass":"videocaption"
			}
			}
			]</pre>
		</div>
		<?php

	}

	function register_settings() {
		register_setting( 'truman_youtube_display', 'youtube_display_api_key' );
		register_setting( 'truman_youtube_display', 'youtube_display_layout_json' );
	}
}
new TrumanYouTube();
