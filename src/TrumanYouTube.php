<?php
namespace TrumanYouTubePlugin;

class TrumanYouTube
{
    const APIKEY = 'AIzaSyCbstE__HeWazle17lK2tn9OAkf3QkNuys';
    const PART  = 'id%2Csnippet';

    public function __construct()
    {
        add_action('init', array($this, 'add_truman_youtube_stylesheet'));

    }

    public function add_truman_youtube_stylesheet()
    {
        wp_enqueue_style( 'truman_youtube_style', plugins_url('../css/truman_youtube_display.css', __FILE__) );
        wp_enqueue_style( 'magnific-popup', plugins_url('../css/magnific-popup.css', __FILE__) );
        wp_enqueue_script( 'magnific-popup', plugins_url('../js/jquery.magnific-popup.min.js', __FILE__), array( 'jquery' ) );
        add_shortcode( 'truman-youtube-display', array($this,'truman_youtube_shortcode') );
    }

    public function truman_youtube_shortcode($atts)
    {
        ob_start();
        extract( shortcode_atts( array (
            'playlistid' => '',
            'random' => 'true',
        ), $atts ) );

        $feedurl = sprintf("https://www.googleapis.com/youtube/v3/playlistItems?part=%s&playlistId=%s&key=%s&maxResults=50", $this::PART, $playlistid, $this::APIKEY);
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
        $json = json_decode($response['body']);
        $items = array();

        // iterate over entries in feed
        foreach ($json->items as $entry) {
            $item = array();
            $id = $entry->snippet->resourceId->videoId;
            $watch = sprintf("https://www.youtube.com/watch?v=%s&index=1&list=%s", $id, $playlistid);
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
//  $layoutstring = '[{ "vid1":"eightcol first", "vid2":"fourcol last", "vid3":"fourcol last"}, { "vid1":"sixcol first", "vid2":"sixcol last"}, { "vid1":"fourcol first", "vid2":"fourcol first", "vid3":"eightcol last"},{ "size":"sixcol first", "size":"sixcol last"}]';
//  $layoutstring = '[{ "vid1":"eightcol first", "vid2":"fourcol last", "vid3":"fourcol last"}, { "vid1":"sixcol first", "vid2":"sixcol last"}]';
        $layoutstring = '[{ "vid1":{"vidclass" : "eightcol first", "captionclass": "redBg"}, "vid2":{"vidclass" : "fourcol last", "captionclass" : "greenBg"}, "vid3": {"vidclass" : "fourcol last", "captionclass" : "blueBg"}}, { "vid1":{"vidclass" : "sixcol first", "captionclass" : "orangeBg"}, "vid2":{"vidclass": "sixcol last", "captionclass" : "magentaBg"}}]';
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
                        $output .= sprintf('<a class="popup-youtube" href="%s"><img src="%s" alt="youtube thumbnail" /><i class="fa fa-play fa-inverse"></i><span class="vid-caption %s">%s</span></a>', $item['watch'], $item['thumbnail'], $value->captionclass, $item['title']);
                        //printf('<a class="thickbox" href="#TB_inline?width=853&height=480&inlineId=embed%s""><img src="%s" alt="youtube thumbnail" /><i class="fa fa-play fa-inverse"></i><span class="vid-caption %s">%s</span></a>', $itemcounter, $item['thumbnail'], $value->captionclass, $item['title']);
                        $output .= '</div>';
                        $output .= '</div>';
                        $itemcounter++;
                    }
                }
                $output .= "</div>";
            }
        }

        $output .= "<script type=\"text/javascript\">
      jQuery(document).ready(function() {
        jQuery('.popup-youtube').magnificPopup({
          disableOn: 700,
          type: 'iframe',
          mainClass: 'mfp-fade',
          removalDelay: 160,
          preloader: false,

          fixedContentPos: false
        });
      });
    </script>";
        return $output;
    }
}
