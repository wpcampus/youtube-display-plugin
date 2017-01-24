# Truman YouTube Display
This plugin uses a shortcode to displaly a YouTube playlist in a customizable layout.

## Initialization
After installing the plugin, you must run 
```shell
composer update
```

## Configuration
Using the settings page, you must enter a YouTube API key, and a Layout JSON string.
The shortcode will loop through the array putting `<div class="row">` around
the top level of the array, and then will use the defined classes for the video
 and caption elements.
 
 Example JSON:
 ```javascript
[
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
        ]
```

This wil create a row with three videos having classes col-md-8 first,
col-md-4 last, and col-md-4 last. Then another row with two videos
having class col-md-6 first and col-md-6 last.

## Shortcode
The shortcode looks like this:
```html
[truman-youtube-display playlistid="PLcYA3q7LpetDq6Qx3I_TS2Si5dU39lCez" random="false"]
```

The parameters are the playlistid and whether or not to display the videos in random order.
The default is false.