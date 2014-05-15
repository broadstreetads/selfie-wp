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
    const KEY_VIEW_COUNT = 'SELFIE_VIEW_COUNT';
    const KEY_LIKE_COUNT = 'SELFIE_LIKE_COUNT';
   
    
    protected static $_zoneCache = NULL;
    protected static $_apiKeyValid = NULL;  
    protected static $_configCache = NULL;
    protected static $_viewCache = NULL;
    protected static $_likeCache = NULL;
    
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
     * Interpolate pricing into post
     * @param type $message
     * @param type $post_id
     * @return type
     */
    public static function interpolatePricing($message, $post_id) {
        $grid = self::getPricingGrid($post_id);
        
        foreach($grid as $term => $price)            
            $message = str_ireplace("{{$term}_price}", ($price === null || $price === '') 
                    ? '[price unavailable!]' 
                    : number_format((double)$grid[$term], 2), $message);
            
        return $message;
    }
    
    /**
     * Get the selfie inline css based on the configuration
     * @param type $config
     * @return string The inline CSS styles
     */
    public static function getInlineSelfieStyle($config)
    {
        $config = (array)$config;        
        $style  = '';
        
        if(isset($config['font_bold']) && $config['font_bold'])
            $style.= 'font-weight:bold !important;';
        
        if(isset($config['font_italic']) && $config['font_italic'])
            $style.= 'font-style:italic !important;';        
        
        if (isset($config['font_underline']) && $config['font_underline'])
            $style.= 'text-decoration:underline !important;';
        
        if (isset($config['font_size']) && $config['font_size'])
            $style.= "font-size:{$config['font_size']} !important;";
            
        if (isset($config['font_color']) && $config['font_color'])
            $style.= "color:{$config['font_color']} !important;";
            
        return $style;        
    }
    
    /**
     * Set the selfie zone id
     * @param type $zone_id
     */
    public static function setSelfieZoneId($zone_id, $network_id) {
        Selfie_Utility::setOption(Selfie_Core::KEY_SELFIE_ZONE_ID.'_NET_'.$network_id, $zone_id);
    }
    
    /**
     * Get the selfie zone id. If there isn't one, create it if possible
     * @return type
     */
    public static function getSelfieZoneId() {
        /* Let's see if they have a zone id. If they don't, create it */
        $network_id = self::getNetworkId();
        $zone_id = self::getOption(Selfie_Core::KEY_SELFIE_ZONE_ID.'_NET_'.$network_id);

        if(!$zone_id)
        {
            $api_key = self::getApiKey();
            
            if($api_key) 
            {
                
                if($network_id) 
                {
                    # Looks like everything's kosher, create a Selfie zone
                    try 
                    {
                        $api = new Broadstreet($api_key);                         
                        $resp = $api->createZone($network_id, 'Selfie Zone for '.site_url(), array (
                            'self_serve' => true,
                            'pricing_callback_url' => site_url()
                        ));
                        
                        $zone_id = $resp->id;
                        self::setSelfieZoneId($zone_id, $network_id);
                        self::getFakePostId();
                        
                    } catch (Exception $ex) {
                        $zone_id = null;               
                    }                    
                }
            }        
        }
        
        return $zone_id;        
    }
    
    /**
     * Get a fake post id to use for virtual pages
     */
    public static function getFakePostId() {
        
        $id = self::getOption(Selfie_Core::KEY_FAKE_POST_ID);        
        if($id) return $id;
        
        $post = new stdClass();
        $post->post_author = 1;
        $post->post_name = Selfie_Core::SELFIE_ABOUT_SLUG;
        $post->guid = get_bloginfo('wpurl') . '/' . Selfie_Core::SELFIE_ABOUT_SLUG;
        $post->post_title = "Selfie Placeholder - Do Not Delete or Publish";
        $post->post_content = "This is a placeholder for the Selfie user education page. "
                . "Do not delete it if you want the education functionality to "
                . "work correctly!";
        $post->ID = null;
        $post->post_type = 'page';
        $post->post_status = 'draft';
        $post->comment_status = 'closed';
        $post->ping_status = 'open';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);        
        $post = (array)$post;
        
        $id = wp_insert_post($post);
        
        if($id) {
            self::setOption(Selfie_Core::KEY_FAKE_POST_ID, $id);     
            return $id;
        }
        
        return false;
    }
    
    /**
     * Thank you random guy on StackOverflow
     * @link http://stackoverflow.com/a/18883220/219595
     * @param type $str
     * @return type
     */
    public static function superEntities($str){
        // get rid of existing entities else double-escape
        $str = html_entity_decode(stripslashes($str), ENT_QUOTES, 'UTF-8');
        $str2 = '';
        $ar = preg_split('/(?<!^)(?!$)/u', $str );  // return array of every multi-byte character
        foreach ($ar as $c){
            $o = ord($c);
            if ( (strlen($c) > 1) || /* multi-byte [unicode] */
                ($o <32 || $o > 126) || /* <- control / latin weirdos -> */
                ($o >33 && $o < 40) ||/* quotes + ambersand */
                ($o >59 && $o < 63) /* html */
            ) {
                // convert to numeric entity
                $c = mb_encode_numericentity($c,array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
            }
            $str2 .= $c;
        }
        return $str2;
    }
    
    /**
     * Get pricing data for Selfie
     * @return type
     */
    public static function getConfigData() {        
        if(self::$_configCache !== NULL)
            return self::$_configCache;
        
        $data = self::getOption(self::KEY_PRICING);
        
        if(!is_object($data) && !is_array($data))
            $data = array();
        
        if(is_object($data))
            $data = (array)$data;
        
        $base = array (
            'message_prefix' => '',
            'admin_email' => get_bloginfo('admin_email'),
            'price_day' => '10.00',
            'price_week' => '20.00',
            'price_month' => '50.00',
            'price_year' => '100.00',
            'optimize_price' => true,
            'rules' => array(),
            'font_bold' => true,
            'font_italic' => false,
            'font_underline' => false,
            'font_color' => null,
            'font_size' => '100%',
            'center' => false,
            'style' => 'Metro',
            'auto_place_top' => true,
            'auto_place_middle' => false,
            'auto_place_bottom' => false,
            'auto_place_single_only' => false,
            'auto_message' => 'Click and write your message or classified here! Reach everyone reading this post!',
            'show_help' => true,
            'disable_all' => false
        );
        
        foreach($base as $key => $val) {
            if(isset($data[$key]))
                $base[$key] = $data[$key];
        }
        
        self::$_configCache = (object)$base;
        
        return self::$_configCache;
    }
    
    /**
     * Send a JSON response, set the header, and exit
     * @param type $message
     * @param type $status
     * @param type $success
     */
    public static function jsonResponse($data = array(), $message = 'OK', $status = 200, $success = true)
    {
        status_header($status);
        header('Content-type: application/json');
        die(json_encode(array_merge(array(
            'success' => $success,
            'status' => $status,
            'message' => $message
        ), $data)));
    }
    
    /**
     * Get the final pricing for a Selfie zone
     * @param type $post_id
     * @param type $term day, week, month, year
     * @param type $length The total number of terms
     */
    public static function getSelfiePrice($post_id, $term, $length, $in_pennies = false, &$grid = array(), &$log = '')
    {
        $length = (int)$length;
        
        if(!in_array($term, array('day', 'week', 'month', 'year')))
            throw new Exception ("The term '$term' is not one of day, week, month, or year.");
        
        if(!is_int($length) && $length > 0)
            throw new Exception ("The length '$length' must be a positive integer");
                
        $grid = self::getPricingGrid($post_id, $in_pennies, $log);
        
        if(!isset($grid[$term]))
            throw new Exception ("There is no price available for this combination of rule and term");
        
        # Calculate the final price
        $price = round($grid[$term] * $length, 2);
        
        return $price;
    }
    
    /**
     * Get the pricing data for a particular zone
     * @param type $post_id
     * @param type &$debug An out param with logging information
     * @return int Pricing grid in pennies
     */
    public static function getPricingGrid($post_id, $in_pennies = false, &$debug = '')
    {
        $pricing = self::getConfigData();
                
        if($post_id === self::getFakePostId()) {
           
            $grid =  array (
                'day' => ($pricing->price_day),
                'week' => ($pricing->price_week),
                'month' => ($pricing->price_month),
                'year' => ($pricing->price_year)
            );
            
        } else {
            
            $post = get_post($post_id);

            if(!$post)
                throw new Exception ("The post with id $post_id could not be found");

            $tags          = wp_get_post_tags();        
            $log           = array();
            $rule_to_apply = null;

            foreach($pricing->rules as $i => $rule) {
                $rule_num = $i + 1;

                if($rule->type === 'older_than_days') {
                    $age_days = round((time() - strtotime($post->post_date))
                                / (24*60*60), 1); # 1 day

                    if($age_days > $rule->param) {
                        $log[] = "Matches rule #$rule_num: Post $post_id ($age_days days old) older than {$rule->param} days, setting price";
                        $rule_to_apply = $rule;
                    }
                }

                if($rule->type === 'younger_than_days') {
                    $age_days = round((time() - strtotime($post->post_date))
                                / (24*60*60), 1); # 1 day

                    if($age_days < $rule->param) {
                        $log[] = "Matches rule #$rule_num: Post $post_id ($age_days days old) younger than {$rule->param} days, setting price";
                        $rule_to_apply = $rule;
                    }
                }

                if($rule->type === 'has_category') {
                    if(in_category($rule->param, $post_id)) {
                        $log[] = "Matches rule #{$rule_num}: Post $post_id in category {$rule->param}, setting price";
                        $rule_to_apply = $rule;
                    }
                }

                if($rule->type === 'has_tag') {
                    if(has_tag($rule->param, $post_id)) {
                        $log[] = "Matches rule #$rule_num: Post $post_id has tag {$rule->param}, setting price";
                        $rule_to_apply = $rule;
                    }
                }            
            }

            if($rule_to_apply === null) {
                $log[] = "No matching rules to apply. Using base pricing.";
                # Pricing has the same properties as a rule
                $rule_to_apply = $pricing;
            }

            $overrides = self::getAllPostMeta($post_id);

            # Now for individual pricing overrides
            if(isset($overrides['selfie_day']) && $overrides['selfie_day'] !== '') {
                $log[] = "Individual day pricing ({$overrides['selfie_day']}) override on post: $post_id";
                $rule_to_apply->price_day = $overrides['selfie_day'];
            }

            if(isset($overrides['selfie_week']) && $overrides['selfie_week'] !== '') {
                $log[] = "Individual week pricing ({$overrides['selfie_week']}) override on post: $post_id";
                $rule_to_apply->price_week = $overrides['selfie_week'];
            }

            if(isset($overrides['selfie_month']) && $overrides['selfie_month'] !== '') {
                $log[] = "Individual month pricing ({$overrides['selfie_month']}) override on post: $post_id";
                $rule_to_apply->price_month = $overrides['selfie_month'];
            }

            if(isset($overrides['selfie_year']) && $overrides['selfie_year'] !== '') {
                $log[] = "Individual year pricing ({$overrides['selfie_year']}) override on post: $post_id";
                $rule_to_apply->price_year = $overrides['selfie_year'];
            }            

            $grid = array (
                'day' => ($rule_to_apply->price_day),
                'week' => ($rule_to_apply->price_week),
                'month' => ($rule_to_apply->price_month),
                'year' => ($rule_to_apply->price_year)
            );
        }
        
        if($in_pennies) {
            foreach($grid as $term => $price)
            {
                # If the price is empty, it's unavailable
                if($price === '')
                    $grid[$term] = null;
                else
                    $grid[$term] = intval(round($price * 100, 2));
            }
        }
        
        $debug = $log;
        
        return $grid;
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
            $autoload   = 'yes';
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

    /**
     * Handle an error
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        Selfie_Log::add('error', "Error [$errno]: '$errstr' in $errfile:$errline");
    }

    /**
     * Handle an exception
     * @param Exception $ex
     */
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
        $report .= 'Plugin Version: ' . SELFIE_VERSION . "\n";
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
        $upgrade_key = Selfie_Core::KEY_INSTALL_REPORT .'_'. SELFIE_VERSION;
        
        $installed = self::getOption($install_key);
        $upgraded  = self::getOption($upgrade_key);
 
        $sent = ($installed && $upgraded);
        
        if($sent === FALSE)
        {   
            if(!$installed)
            {
                self::sendReport("Installation: Selfie");
                self::setOption($install_key, 'true');
                self::setOption($upgrade_key, 'true');
            }
            else
            {
                self::flushRewrites(true);
                self::sendReport("Upgrade: Selfie");
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
     * Get the total likes for a Selfie
     * @param type $post_id
     * @param type $position_id
     * @return int
     */
    public static function getSelfieLikeCount($post_id, $position_id) {
        if(self::$_likeCache === NULL)
            self::$_likeCache = array();
        
        if(isset(self::$_likeCache[$post_id])
            && isset(self::$_likeCache[$post_id][$position_id]))
            return self::$_likeCache[$post_id][$position_id];
        
        $meta = self::getPostMeta($post_id, self::KEY_LIKE_COUNT);
        
        $value = 0;
        if($meta && isset($meta[$position_id]))
            $value = $meta[$position_id];        
        
        self::$_likeCache[$post_id][$position_id] = $value;
        
        return $value;
    }
    
    /**
     * Like a Selfie and increment the like count
     * @param type $post_id
     * @param type $position_id
     */
    public static function likeSelfie($post_id, $position_id) {        
        
        $likes = json_decode(base64_decode($_COOKIE['SelfieLikes']));
        $key = "$post_id:$position_id";
        
        if(!$likes) $likes = array();
        
        $meta = self::getPostMeta($post_id, self::KEY_LIKE_COUNT);
        
        if(in_array($key, $likes))
            throw new Exception('Already liked');
        
        if($meta) {
            if(isset($meta[$position_id]))
                $meta[$position_id]++;
            else
                $meta[$position_id] = 1;            
        } else {
            $meta = array (
                $position_id => 1
            );
        }

        $likes[] = $key;
        
        self::setPostMeta($post_id, self::KEY_LIKE_COUNT, $meta);                        
        setcookie('SelfieLikes', base64_encode(json_encode($likes)));
        
        return $meta[$position_id];               
    }
    
    /**
     * Increment a Selfie's view count
     * @param type $post_id
     * @param type $position
     */
    public static function incrementSelfieViewCounts($post_id) {
        
        if(self::$_viewCache === NULL)
            self::$_viewCache = array();
        
        if(isset(self::$_viewCache[$post_id]))
            return self::$_viewCache[$post_id];
        
        $date_str_m = date('Y-m-*');
        $date_str_d = date('Y-*-d');
        $date_str_a = 'all_time';
        
        # Get the counts
        $meta = self::getPostMeta($post_id, self::KEY_VIEW_COUNT);
        
        # Only do this once every 7 times to keep MySQL from ahting us
        if(self::luckyShot(7)) {
        
            if($meta && isset($meta[$date_str_m])) {
                $meta[$date_str_d] += 1;
                $meta[$date_str_m] += 1;  
                $meta[$date_str_a] += 1;
            } else {
                # Keep track of all time stats
                if(isset($meta[$date_str_a])) 
                    $meta = array($date_str_a => $meta[$date_str_a]);
                else
                    $meta = array();
                
                $meta[$date_str_d] = 1;
                $meta[$date_str_m] = 1;
            }

            self::setPostMeta($post_id, self::KEY_VIEW_COUNT, $meta);
        }
        
        if(!isset($meta[$date_str_d])) $meta[$date_str_d] = 1;
        if(!isset($meta[$date_str_m])) $meta[$date_str_m] = 1;
        if(!isset($meta[$date_str_a])) $meta[$date_str_a] = 1;
        
        self::$_viewCache[$post_id] = array (
            'day' => $meta[$date_str_d] * 7,
            'month' => $meta[$date_str_m] * 7,
            'all_time' => $meta[$date_str_a] * 7
        );
        
        return self::$_viewCache[$post_id];
    }
    
    /* Return true/false based on a test of probably based on the given
     * param
     * @param $outOf int "With a 1 out of $outOf chance"
     */
    public static function luckyShot($outOf) {
        return 1 === rand(1, $outOf);        
    }
            

    /**
     * Return a unique identifier for the site for use with future help requests
     * @return string A unique identifier
     */
    public static function getServiceTag()
    {
        return md5($report['u'] = get_bloginfo('url'));
    }
    
    /**
     * Get the Selfie CSS styles
     */
    public static function getSelfieStyles() {
        
        $classified_badge = self::getImageBaseURL() . 'classified-badge.png';
        $selfie_badge     = self::getImageBaseURL() . 'selfie-badge.png';
        $message_badge    = self::getImageBaseURL() . 'message-badge.png';
        $greenline_badge    = self::getImageBaseURL() . 'greenline-badge.png';
        
        $badge_css = " .selfie-paragraph .broadstreet-selfie {
                background-image: url($classified_badge);
                background-position: 0px center;
                background-repeat: no-repeat;
                padding-left: 25px;
                margin-left: -15px;
                min-height: 52px;
        }" ;
        
        $gradient_css = " background: #f7f7f7; /* Old browsers */
                background: -moz-linear-gradient(top,  #f7f7f7 0%, #e8e8e8 100%); /* FF3.6+ */
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f7f7f7), color-stop(100%,#e8e8e8)); /* Chrome,Safari4+ */
                background: -webkit-linear-gradient(top,  #f7f7f7 0%,#e8e8e8 100%); /* Chrome10+,Safari5.1+ */
                background: -o-linear-gradient(top,  #f7f7f7 0%,#e8e8e8 100%); /* Opera 11.10+ */
                background: -ms-linear-gradient(top,  #f7f7f7 0%,#e8e8e8 100%); /* IE10+ */
                background: linear-gradient(to bottom,  #f7f7f7 0%,#e8e8e8 100%); /* W3C */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f7f7f7', endColorstr='#e8e8e8',GradientType=0 ); /* IE6-9 */  ";
        
        return array (
            'None'     => '',
            'Metro'    => '',
            'Cave'     => '',
            'Whitebox' => '.selfie-paragraph {margin-bottom: 0 !important;}',
            'Pilot'    => '.selfie-paragraph { padding: 5px 0 5px 0; border-top: 4px solid #ccc; border-bottom: 4px solid #ccc; }',
            'Greenie'  => '.selfie-paragraph { border-left: 5px solid lightgreen; padding: 10px 0 10px 10px; background-color: #eee; }',
            'Bluesy'   => '.selfie-paragraph { border-left: 5px solid #0074a2; padding: 10px 0 10px 10px; background-color: #eee; }',
            'Fancy'     => ".selfie-paragraph {
                padding: 15px 10px 15px 10px;
                border: 1px solid #ccc;
                border-radius: 2px;". $gradient_css .
            "}",
            'Light'     => '.selfie-paragraph { padding: 10px 0 10px 10px; border-top: 1px dotted #eee; border-bottom: 1px dotted #eee;}',
            'Classified Red' => "p.selfie-paragraph { 
                border-left: 15px solid red;
                padding: 10px 10px 10px 0px;
                background-color: #eee; 
                border-radius: 1px;
              }
            ".$badge_css,
            'Classified Blue' => "p.selfie-paragraph { 
                border-left: 15px solid #005493;
                padding: 10px 10px 10px 0px;
                background-color: #eee; 
                border-radius: 1px;
             }".$badge_css,
            'Message Blue' => "p.selfie-paragraph { 
                border-left: 15px solid #005493;
                padding: 10px 10px 10px 0px;
                background-color: #eee; 
                border-radius: 1px;
                $gradient_css
             }".$badge_css." p.selfie-paragraph .broadstreet-selfie { background-image: url($message_badge); } ",
            'Message Red' => "p.selfie-paragraph { 
                border-left: 15px solid red;
                padding: 10px 10px 10px 0px;
                background-color: #eee; 
                $gradient_css
             }".$badge_css." p.selfie-paragraph .broadstreet-selfie { background-image: url($message_badge); } ",
            'Red Bank Green' => ".selfie-paragraph { 
                border-left: 15px solid rgb(0,204,41);
                padding: 10px 10px 10px 0px;
                background-color: #eee; 
                $gradient_css
             }".$badge_css." p.selfie-paragraph .broadstreet-selfie { background-image: url($greenline_badge); } "
                          
        );
    }
}