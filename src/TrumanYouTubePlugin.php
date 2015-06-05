<?php
namespace TrumanYouTubePlugin;

class TrumanYouTube
{
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
            'feedurl' => '',
            'random' => 'true',
        ), $atts ) );


        // read feed into SimpleXML object
        $sxml = simplexml_load_file($feedurl);
        $items = array();

        // iterate over entries in feed
        foreach ($sxml->entry as $entry) {
            $item = array();
            // get nodes in media: namespace for media information
            $media = $entry->children('http://search.yahoo.com/mrss/');

            // get video player URL
            $attrs = $media->group->player->attributes();
            if ($attrs) {
                $watch = $attrs['url'];

                // url is in this format http://www.youtube.com/watch?v=2Fsai0AmDeA&feature=youtube_gdata_player
                // we need to reformat to http://www.youtube.com/v/2Fsai0AmDeA&fs=1&autoplay=1  so it will do the full screen thing
                //$watch = str_replace("watch?v=", "v/", $watch);
                $watch = str_replace("&feature=youtube_gdata_player", "", $watch);
                $id = str_replace("http://www.youtube.com/watch?v=", "", $attrs['url']);
                $id = str_replace("&feature=youtube_gdata_player", "", $id);

                // get video thumbnail
                $attrs = $media->group->thumbnail[0]->attributes();
                //$thumbnail = str_replace("0.jpg", "mqdefault.jpg", $attrs['url']);
                $thumbnail = str_replace("0.jpg", "maxresdefault.jpg", $attrs['url']);

                $item['thumbnail'] = $thumbnail;
                $item['watch'] = $watch;
                $item['id'] = $id;
                $item['title'] = $media->group->title;
                $items[] = $item;
            }
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
