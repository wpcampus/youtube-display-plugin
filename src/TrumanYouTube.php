<?php
namespace TrumanYouTubePlugin;

class TrumanYouTube
{
    const PART  = 'id%2Csnippet';

    public function __construct()
    {
        add_action('init', array($this, 'add_truman_youtube_stylesheet'));

    }

    public function add_truman_youtube_stylesheet()
    {
        add_shortcode( 'truman-youtube-display', array($this,'truman_youtube_shortcode') );
        if ( is_admin() ){
            add_action('admin_menu', array($this, 'adminMenu'));
            add_action( 'admin_init', array($this, 'register_mysettings'));
        }
    }

    public function truman_youtube_shortcode($atts)
    {
        wp_enqueue_style( 'truman_youtube_style', plugins_url('../css/truman_youtube_display.css', __FILE__) );
        wp_enqueue_style( 'magnific-popup', plugins_url('../css/magnific-popup.css', __FILE__) );
        wp_enqueue_script( 'magnific-popup', plugins_url('../js/jquery.magnific-popup.min.js', __FILE__), array( 'jquery' ) );
        wp_enqueue_script( 'truman_youtube_script', plugins_url('../js/truman_youtube_display.js', __FILE__), array( 'jquery' ) );

        ob_start();
        extract( shortcode_atts( array (
            'playlistid' => '',
            'random' => 'true',
        ), $atts ) );

        $transient_name = 'youtube_json_' . md5($playlistid);
        if ( false === ( $json = get_transient( $transient_name ) ) ) {

            $feedurl = sprintf("https://www.googleapis.com/youtube/v3/playlistItems?part=%s&playlistId=%s&key=%s&maxResults=50", $this::PART, $playlistid, esc_attr(get_option('api_key')));
            $response = wp_remote_get(
                $feedurl,
                array(
                    'method' => 'GET',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array()
                )
            );
            $json = $response['body'];

            set_transient( $transient_name, $json, 1 * HOUR_IN_SECONDS );
        }
        $json = json_decode($json);

        $items = array();

        // iterate over entries in feed
        foreach ($json->items as $entry) {
            $item = array();
            $id = $entry->snippet->resourceId->videoId;
            $watch = sprintf("https://www.youtube.com/watch?v=%s", $id);
            $thumbnails = (array)$entry->snippet->thumbnails;

            $thumbnail = array_pop($thumbnails)->url;

            $item['thumbnail'] = $thumbnail;
            $item['watch'] = $watch;
            $item['id'] = $id;
            $item['title'] = $entry->snippet->title;
            $items[] = $item;
        }

        if ($random === 'true') {
            shuffle($items);
        }

        $itemcounter = 0;

        $layoutstring = get_option('layout_json');

        $sizearray = json_decode($layoutstring);
        $output = "";

        while ($itemcounter < count($items)) {
            foreach ($sizearray as $key => $jsons) { // This will search in the 2 jsons
                $output .= "<div class=\"row\">";
                foreach($jsons as $key => $value) {
                    if ($itemcounter < count($items)) {
                        $item = $items[$itemcounter];
                        $output .= sprintf('<div class="%s">', $value->vidclass);
                        $output .= '<div class="vid">';
                        $output .= sprintf('<a class="popup-youtube" href="%s">
                                                <img src="%s" alt="youtube thumbnail" />
                                                <span class="play"></span>
                                                <span class="vid-caption %s">%s</span>
                                             </a>',
                            $item['watch'],
                            $item['thumbnail'],
                            $value->captionclass,
                            $item['title']
                        );
                        $output .= '</div>';
                        $output .= '</div>';
                        $itemcounter++;
                    }
                }
                $output .= "</div>";
            }
        }

        return $output;
    }


    public function adminMenu()
    {
        add_submenu_page(
            'options-general.php',
            'YouTube Display',
            'YouTube Display',
            'manage_options',
            'youtube_options',
            array($this,'options_page')
        );

    }

    public function options_page()
    {
    ?>
    <div class="wrap">
        <h1>YouTube Display Plugin Options</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'truman_youtube_display' ); ?>
            <?php do_settings_sections( 'truman_youtube_display' ); ?>
            <p><label for="api_key">YouTube API Key</label> <input type="text" name="api_key" id="api_key" value="<?php echo esc_attr( get_option('api_key') ); ?>" size="50"/></p>
            <p><label for="layout_json">Layout JSON</label> <textarea name="layout_json" id="layout_json" style="width: 100%; height: 300px"><?php echo esc_attr( get_option('layout_json') ); ?></textarea></p>
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


    function register_mysettings() {
        register_setting( 'truman_youtube_display', 'api_key' );
        register_setting( 'truman_youtube_display', 'layout_json' );
    }
}
