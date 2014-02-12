<?php
/**
 * This file contains a class for utility methods and/or wrappers for built-in
 *  Wordpress API calls
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * The class contains a number of utility methods that may be needed by various
 *  parts of Broadstreet
 */
class Selfie_Utility
{
    const KEY_ZONE_CACHE = 'BROADSTREET_ZONE_CACHE';
    const KEY_RW_FLUSH   = 'BROADSTREET_RW_FLUSH';
    const KEY_NET_INFO   = 'BROADSTREET_NET_INFO';
    const KEY_PRICING    = 'SELFIE_PRICING_DATA';
   
    
    protected static $_zoneCache = NULL;
    protected static $_apiKeyValid = NULL;        
    
    /**
     * Get the current user's Broadstreet API key
     * @return boolean 
     */
    public static function getApiKey()
    {
        $api_key = Selfie_Utility::getOption(Selfie_Core::KEY_API_KEY);
        
        if(!$api_key) 
            return FALSE;
        else
            return $api_key;
    }
    
    /**
     * Get this publication's network ID
     * @return boolean 
     */
    public static function getNetworkId()
    {
        return Selfie_Utility::getOption(Selfie_Core::KEY_NETWORK_ID);
    }
    
    /**
     * Get info about the network this blog is registered as, and cache it
     * @return boolean 
     */
    public static function getNetwork($force_refresh = false)
    {
        $info = false;
        
        if(!$force_refresh)
            $info = Selfie_Cache::get('network_info');
        
        if($info) return $info;

        try
        {
            $broadstreet = new Broadstreet(self::getApiKey());
            $info = $broadstreet->getNetwork(self::getNetworkId());

            Selfie_Cache::set('network_info', $info, Selfie_Config::get('network_cache_ttl_seconds'));
        
            self::setOption(self::KEY_NET_INFO, $info);
        }
        catch(Exception $ex)
        {
            return false;
        }
        
        return $info;
    }
    
    /**
     * Get pricing data for Selfie
     * @return type
     */
    public static function getPricingData() {
        $data = self::getOption(self::KEY_PRICING);
        
        if(!is_object($data) && !is_array($data))
            $data = array();
        
        if(is_object($data))
            $data = (array)$data;
        
        $base = array (
            'default_price' => '10.00',
            'optimize_price' => true,
            'rules' => array()
        );
        
        foreach($base as $key => $val) {
            if(isset($data[$key]))
                $base[$key] = $data[$key];
        }
        
        return (object)$base;
    }
    
    /**
     * Get the pricing data for a particular zone
     * @param type $post_id
     * @param type $position
     */
    public static function getZonePrice($post_id, $position = 0)
    {
        $pricing    = self::getPricingData();
        $post       = get_post($post_id);
        $tags       = wp_get_post_tags();
        
        $log        = array();
        $price      = $pricing->default_price;
        
        print_r($pricing);
        
        
        
        foreach($pricing->rules as $i => $rule) {
            $rule_num = $i + 1;
            
            if($rule->type === 'older_than_days') {
                $age_days = round((time() - strtotime($post->post_date))
                            / (24*60*60), 1); # 1 day
                                
                if($age_days > $rule->param) {
                    $log[] = "Matches rule #$rule_num: Post $post_id ($age_days days old) older than {$rule->param} days, setting price to {$rule->price}";
                    $price = $rule->price;
                }
            }
            
            if($rule->type === 'has_category') {
                if(in_category($rule->param, $post_id)) {
                    $log[] = "Matches rule #{$rule_num}: Post $post_id in category {$rule->param}, setting price to {$rule->price}";
                    $price = $rule->price;
                }
            }
            
            if($rule->type === 'has_tag') {
                if(has_tag($rule->param, $post_id)) {
                    $log[] = "Matches rule #$rule_num: Post $post_id has tag {$rule->param}, setting price to {$rule->price}";
                    $price = $rule->price;
                }
            }
            
        }
        
        return array('value' => $price, 'log' => $log);
    }

    /**
     * Check that the user's API key exists and is valid
     * @return boolean 
     */
    public static function checkApiKey($return_key = FALSE)
    {
        if(self::$_apiKeyValid !== NULL)
            return self::$_apiKeyValid;
        
        $api_key = self::getApiKey();
        
        if(!$api_key) 
        {
            self::$_apiKeyValid = FALSE;
            return FALSE;
        }
        else 
        {
            $api = new Broadstreet($api_key);
            
            try
            {
                $api->getNetworks();
                self::$_apiKeyValid = TRUE;
                
                if($return_key) 
                    return $api_key; 
                else 
                    return TRUE;
            }
            catch(Exception $ex)
            {
                self::$_apiKeyValid = TRUE;
                return FALSE;
            }
        }
    }
    
    /**
     * Sets a Wordpress option
     * @param string $name The name of the option to set
     * @param string $value The value of the option to set
     */
    public static function setOption($name, $value)
    {
        if (get_option($name) !== FALSE)
        {
            update_option($name, $value);
        }
        else
        {
            $deprecated = ' ';
            $autoload   = 'no';
            add_option($name, $value, $deprecated, $autoload);
        }
    }

    /**
     * Gets a Wordpress option
     * @param string    $name The name of the option
     * @param mixed     $default The default value to return if one doesn't exist
     * @return string   The value if the option does exist
     */
    public static function getOption($name, $default = FALSE)
    {
        $value = get_option($name);
        if( $value !== FALSE ) return $value;
        return $default;
    }
    
    /**
     * If rewrite rules haven't been flushed, flush them.
     * @param $clear Force a flush
     */
    public static function flushRewrites($force = FALSE)
    {
        if($force || !self::getOption(self::KEY_RW_FLUSH))
        {
            flush_rewrite_rules();
            self::setOption(self::KEY_RW_FLUSH, 'TRUE');
        }
    }
    
    /**
     * Fix a malformed URL
     * @param string $url
     * @return string
     */
    public static function fixURL($url)
    {
        if(!strstr($url, 'http://'))
            $url = "http://$url";
        
        return $url;
    }
    
    /**
     * Resize a video embed snippet's dimensions to a given width and height
     *  Height is optional
     * @param string $url
     * @return string
     */
    public static function setVideoWidth($snippet, $new_width, $new_height = false, $keep_proportional = true)
    {
        if(preg_match('#width=[\\\'"](\d+)[\\\'"]#', $snippet, $matches))
        {
            $old_width = $matches[1];
            
            if(!$new_height && preg_match('#height=[\\\'"](\d+)[\\\'"]#', $snippet, $matches))
            {
                $height = $matches[1];
            }
            else
            {
                $height = $new_height;
            }
            
            if($keep_proportional)
            {
                $ratio   = $new_width / $old_width;
                $height  = round($height*$ratio);
                $width   = $new_width;
            }
            else
            {
                $width   = $new_width;
            }
            
            $width  = "width=\"$width\"";
            $height = "height=\"$height\"";
            
            $snippet = preg_replace('#width=[\\\'"]\d+[\\\'"]#', $width, $snippet);
            $snippet = preg_replace('#height=[\\\'"]\d+[\\\'"]#', $height, $snippet);
        }
        
        return $snippet;
    }
    
    /**
     * Sets a Wordpress meta value
     * @param string $name The name of the field to set
     * @param string $value The value of the field to set
     */
    public static function setPostMeta($post_id, $name, $value)
    {
        if (get_post_meta($post_id, $name, true) !== FALSE)
        {
            update_post_meta($post_id, $name, $value);
        }
        else
        {
            add_post_meta($post_id, $name, $value);
        }
    }
    
    /**
     * Get a link to the Broadstreet interface
     * @param string $path
     * @return string
     */
    public static function broadstreetLink($path)
    {
        $path = ltrim($path, '/');
        $key = self::getOption(Selfie_Core::KEY_API_KEY);
        $url = "https://my.broadstreetads.com/$path?access_token=$key";
        return $url;
    }   

    /**
     * Gets a post meta value
     * @param string    $name The name of the field
     * @param mixed     $default The default value to return if one doesn't exist
     * @return string   The value if the field does exist
     */
    public static function getPostMeta($post_id, $name, $default = FALSE)
    {
        $value = get_post_meta($post_id, $name, true);
        if( $value !== FALSE ) return maybe_unserialize($value);
        return $default;
    }
    
    /**
     * Gets post meta values, cleaned up, singlefied (or not)
     * @param int       $post_id The id of the post
     * $param array     $defaults Assoc array of meta key names with value defaults
     * @param bool      $singles Whether to collapse value field to first value
     *  (default true)
     */
    public static function getAllPostMeta($post_id, $defaults = array(), $singles = true)
    {
        $meta = get_post_meta($post_id);
        
        foreach($defaults as $key => $value)
        {
            if(!isset($meta[$key])) {
                $meta[$key] = $value;
            }
        }
        
        if(!$singles) return $meta;
        
        $new_meta = array();
        
        # Meta fields come back nested in an array, fix that
        # unless the option is intended to be an array,
        # given the defaults
        foreach($meta as $key => $value) 
        {
            if(is_array(@$defaults[$key]) && count($value))
                $new_meta[$key] = maybe_unserialize($value[0]);
            else
                $new_meta[$key] = (is_array($value) && count($value)) ? $value[0] : $value;
        }
        
        return $new_meta;
    }
    
    /**
     * Figure out whether we're in a the_exceprt call stack
     * @return bool Whether we're in an excerpt 
     */
    public static function inExcerpt()
    {
        $stacktrace = debug_backtrace();
        
        foreach($stacktrace as $call)
            if($call['function'] == 'get_the_excerpt')
                return true;
            
        return false;
    }
    
    public static function toTime($time)
    {
        return date("g:i a", strtotime($time));
    }

    /**
     * Get a value from an associative array. The specified key may or may
     *  not exist.
     * @param array $array Array to grab the value from
     * @param mixed $key The key to check the array
     * @param mixed $default A value to return if the key doesn't exist int he array (default is FALSE)
     * @return mixed The value if the key exists, and the default if it doesn't
     */
    public static function arrayGet($array, $key, $default = FALSE)
    {
        if(array_key_exists($key, $array))
            return $array[$key];
        else
            return $default;
    }
    
    /**
     * Get the site's base URL
     * @return string
     */
    public static function getSiteBaseURL()
    {
        return get_bloginfo('url');
    }

    /**
     * Get the base URL of the plugin installation
     * @return string the base URL
     */
    public static function getBroadstreetBaseURL()
    {   
        return (WP_PLUGIN_URL . '/selfie/Selfie/');
    }

    /**
     * Get the base URL for plugin images
     * @return string
     */
    public static function getImageBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/img/';
    }
    
    /**
     * Get the base url for plugin CSS
     * @return string
     */
    public static function getCSSBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/css/';
    }

    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getJSBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/js/';
    }
    
    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getVendorBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/vendor/';
    }

    /**
     * Set PHP to call Broadstreet's custom handlers for Exceptions and Erros.
     *  This is used mainly for when drivers will still be running in the
     *  background doing something like an index build
     */
    public static function registerLogErrorHandlers()
    {
        set_error_handler(array(__CLASS__, 'handleError'));
        set_exception_handler(array(__CLASS__, 'handleException'));
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        Selfie_Log::add('error', "Error [$errno]: '$errstr' in $errfile:$errline");
    }

    public static function handleException(Exception $ex)
    {
        Selfie_Log::add('error', "Exception: ".$ex->__toString());
    }

    /**
     * Makes a call to the Broadstreet service to collect information information
     *  on the blog in case of errors and other needs.
     */
    public static function sendReport($message = 'General')
    {
        
        $report = "$message\n";
        $report .= get_bloginfo('name'). "\n";
        $report .= get_bloginfo('url'). "\n";
        $report .= get_bloginfo('admin_email'). "\n";
        $report .= 'WP Version: ' . get_bloginfo('version'). "\n";
        $report .= 'Plugin Version: ' . BROADSTREET_VERSION . "\n";
        $report .= "$message\n";

        @wp_mail('plugin@broadstreetads.com', "Report: $message", $report);
    }

    /**
     * If this is a new installation and we've never sent a report to the
     * Broadstreet server, send a packet of basic info about this blog in case
     * issues should arise in the future.
     */
    public static function sendInstallReportIfNew()
    {
        $install_key = Selfie_Core::KEY_INSTALL_REPORT;
        $upgrade_key = Selfie_Core::KEY_INSTALL_REPORT .'_'. BROADSTREET_VERSION;
        
        $installed = self::getOption($install_key);
        $upgraded  = self::getOption($upgrade_key);
 
        $sent = ($installed && $upgraded);
        
        if($sent === FALSE)
        {   
            if(!$installed)
            {
                self::sendReport("Installation");
                self::setOption($install_key, 'true');
                self::setOption($upgrade_key, 'true');
            }
            else
            {
                self::flushRewrites(true);
                self::sendReport("Upgrade");
                self::setOption($upgrade_key, 'true');
            }
        }
    }

    /**
     * Get any reports / warnings / messages from the Broadstreet server.
     * @return mized A string if a message was found, FALSE if not
     */
    public static function getBroadstreetMessage()
    {
        return false;
        //self::setOption(Selfie_Core::KEY_LAST_MESSAGE_DATE, time() - 60*60*13);
        $date = self::getOption(Selfie_Core::KEY_LAST_MESSAGE_DATE);

        if($date !== FALSE && ($date + 12*60*60) > time())
            return self::getOption(Selfie_Core::KEY_LAST_MESSAGE);

        $driver = Selfie_Config::get('driver');
        $count  = Broadstreet_Model::getPublishedPostCount();

        $url     = "http://broadstreetads.com/messages?d=$driver&c=$count";
        $content = file_get_contents($url);

        self::setOption(Selfie_Core::KEY_LAST_MESSAGE, $content);
        self::setOption(Selfie_Core::KEY_LAST_MESSAGE_DATE, time());

        if(strlen($content) == 0 || $content == "0")
            return FALSE;

        return $content;
    }

    /**
     * Return a unique identifier for the site for use with future help requests
     * @return string A unique identifier
     */
    public static function getServiceTag()
    {
        return md5($report['u'] = get_bloginfo('url'));
    }
}