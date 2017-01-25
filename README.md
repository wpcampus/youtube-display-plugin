# WPCampus YouTube - WordPress Plugin

Thi WordPress plugin uses a shortcode to display a YouTube playlist in a customizable layout.

## Disclaimer

This repo is shared for educational purposes. Feel free to explore, copy, submit fixes, and share the code.

**However, please respect that the WPCampus branding and design are intended solely for the WPCampus organization.**

## Development
After installing the plugin, you must run 
```
composer install
```

## Configuration
Using the settings page, you must enter a YouTube API key.

## Shortcode
The shortcode looks like this:
```
[wpc_youtube playlistid="PLcYA3q7LpetDq6Qx3I_TS2Si5dU39lCez" random="false"]
```

The parameters are the playlistid and whether or not to display the videos in random order.
The default is false.