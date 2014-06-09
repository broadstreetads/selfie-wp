<?php
/*
Plugin Name: Selfie
Plugin URI: http://broadstreetads.com/selfie
Description: Native in-post text ads that your readers can purchase themselves.
Version: 0.2.2
Author: Broadstreet
Author URI: http://broadstreetads.com
*/

require dirname(__FILE__) . '/Selfie/Core.php';

# Start the beast
$engine = new Selfie_Core;
$engine->execute();
