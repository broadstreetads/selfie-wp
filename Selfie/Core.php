<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

require_once dirname(__FILE__) . '/Ajax.php';
require_once dirname(__FILE__) . '/Cache.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Log.php';
require_once dirname(__FILE__) . '/Utility.php';
require_once dirname(__FILE__) . '/View.php';
require_once dirname(__FILE__) . '/Widgets.php';
require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Vendor/Broadstreet.php';

if (!class_exists('Selfie_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Broadstreet plugin loader file
 *  (which is most likely at the root of the Broadstreet installation).
 */
class Selfie_Core
{
    CONST KEY_API_KEY             = 'Broadstreet_API_Key';
    CONST KEY_NETWORK_ID          = 'Broadstreet_Network_Key';
    CONST KEY_INSTALL_REPORT      = 'Selfie_Installed';
    CONST KEY_SELFIE_ZONE_ID      = 'Selfie_Zone_ID';
    CONST KEY_FAKE_POST_ID        = 'Selfie_Fake_Post_ID';
    CONST SELFIE_ABOUT_SLUG       = 'about-self-serve-messages';
    
    
    public static $globals = null;
    
    public static $overrideFields = array (
        'selfie_day' => '',
        'selfie_week' => '',
        'selfie_month' => '',
        'selfie_year' => '',
        'selfie_disabled' => 'no'
    );
    
    /**
     * Use to tell how many selfies down we are in a Wordpress post
     * @var type 
     */
    public static $selfiePositionCount = array();
    
    /**
     * The constructor
     */
    public function __construct()
    {
        Selfie_Log::add('debug', "Selfie initializing..");
    }

    /**
     * Get the Broadstreet environment loaded and register Wordpress hooks
     */
    public function execute()
    {
        $this->_registerHooks();
    }

    /**
     * Get a Broadstreet client 
     */
    public function getBroadstreetClient()
    {
        $key = Selfie_Utility::getOption(self::KEY_API_KEY);
        return new Broadstreet($key);
    }
    
    /**
     * Register Wordpress hooks required for Broadstreet
     */
    private function _registerHooks()
    {
        Selfie_Log::add('debug', "Registering hooks..");

        # -- Below is core functionality --
        add_action('admin_menu', 	 array($this, 'adminCallback'     ));
        add_action('admin_init', 	 array($this, 'adminInitCallback' ));
        add_action('wp_enqueue_scripts', array($this, 'generalScriptCallback'));
        add_action('init',           array($this, 'addZoneTag' ));
        add_action('plugins_loaded', array($this, 'pricingWebhook'));
        add_action('admin_notices',  array($this, 'adminWarningCallback'));
        add_action('wp_head',        array($this, 'addSelfieStyling'));
        add_action('widgets_init',   array($this, 'registerWidgets'));
        
        add_filter('the_content', array($this, 'autoSelfie'), 1);
        add_filter('the_posts',   array($this, 'selfieAbout'), 1);
        
        add_shortcode('selfie',   array($this, 'shortcode'));        
        
        # -- Below is administration AJAX functionality
        add_action('wp_ajax_sf_save_settings', array('Selfie_Ajax', 'saveSettings'));
        add_action('wp_ajax_sf_create_advertiser', array('Selfie_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_sf_save_config', array('Selfie_Ajax', 'saveConfig'));
        add_action('wp_ajax_sf_register', array('Selfie_Ajax', 'register'));
        add_action('wp_ajax_sf_create_network', array('Selfie_Ajax', 'createNetwork'));
        
        # -- Below is frontend ajax functionality
        add_action( 'wp_ajax_sf_like_selfie', array('Selfie_Ajax', 'likeSelfie'));
        add_action( 'wp_ajax_nopriv_like_selfie', array('Selfie_Ajax', 'likeSelfie'));
        
        # - Below are partly business-related
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'savePostMeta'));
    }
    
    /**
     * Handler for adding the Broadstreet business meta data boxes on the post
     * create/edit page 
     */
    public function addMetaBoxes()
    {
        add_meta_box( 
            'broadstreet_selfie',
            __( 'Selfie Pricing', 'selfie_textdomain'),
            array($this, 'selfieInfoBox'),
            'post' 
        );
        add_meta_box(
            'broadstreet_selfie',
            __( 'Selfie Pricing', 'selfie_textdomain'), 
            array($this, 'selfieInfoBox'),
            'page'
        );
    }
    
    /**
     * Is this a valid post type for a Selfie? Only pages and posts for now
     * @return boolean
     */
    public function validSelfiePostType() {
        return (in_array(get_post_type(), array('post', 'page')));
    }
    
    /**
     * The webhook called by Broadstreet's Selfie server, for verifying
     *  the price of a selfie slot
     */
    public function pricingWebhook()
    {
        if(isset($_GET['selfie_id'])) {
            
            $key    = $_GET['selfie_id'];
            $term   = Selfie_Utility::arrayGet($_GET, 'selfie_term');
            $length = Selfie_Utility::arrayGet($_GET, 'selfie_term_count');
            
            $log = '';
            $grid = array();
            
            list($post_type, $post_id, $position) = explode(':', $key);
            
            try {
                if($term && $length) {
                    $price = Selfie_Utility::getSelfiePrice($post_id, $term, $length, true, $grid, $log);

                    Selfie_Utility::jsonResponse(
                            array('price' => $price, 
                                  'pricing_log' => $log, 
                                  'pricing_grid' => $grid), 
                            'Pricing found (in pennies)');
                } else {
                    $grid = Selfie_Utility::getPricingGrid($post_id, true, $log);

                    Selfie_Utility::jsonResponse(
                        array('pricing_grid' => $grid, 'pricing_log' => $log), 
                        'Pricing found (in pennies)');                
                }
            } catch(Exception $ex) {
                Selfie_Utility::jsonResponse(array(), "There was an error: ".$ex->getMessage(), 400, false); 
            }
        }
    } 

    /**
     * If there's a prefix on the Selfie, we need to add a couple styles
     */
    public function addSelfieStyling() {
        $config = Selfie_Utility::getConfigData();        
        $styles = Selfie_Utility::getSelfieStyles();
        $style  = $styles[$config->style];
        
        if(trim($config->message_prefix) !== '')
        {
            echo "<style type=\"text/css\">"
            . "/* Generated by Selfie */ "
            . "p .broadstreet-selfie span:before, p .broadstreet-html-placement:before { content: '".Selfie_Utility::superEntities($config->message_prefix)." '; } "            
            . "</style>";            
        }        
        
        echo  "<style type=\"text/css\">"
            . " .selfie-whitebox-tip { width: 0px; height: 0px; border-top: 0px solid transparent; border-bottom: 20px solid transparent; border-left: 20px solid #222; display: inline-block; position: relative; left: 80%;}"
            . " .selfie-whitebox-tip.left { border-left: none; border-right: 20px solid #222; display: inline-block; position: relative; left: 20%;}"
            . " .selfie-whitebox-box { border: 8px solid #222; padding: 20px; background-color: #fff;}"
            . " .selfie-whitebox-container { margin-bottom: 7px; }"
            . " p.selfie-whitebox-box { margin-bottom: 0; }"
            . $style    
            . ".selfie-paragraph { position: relative; clear: both; } "    
            . ".selfie-help-icon { position: absolute; right: 3px; top: -20px; z-index:100; line-height: 9px; padding: 3px; background-color: rgba(0,0,0, .75); display: inline-block; border-radius: 2px; } "    
            . ".selfie-help-icon a, .selfie-help-icon a:hover { font-size: 9px; font-family: Arial; text-decoration: none; border: none; color: white;} "    
            . "</style>";   
        
        echo '<script>var selfieAjax = "' . admin_url('admin-ajax.php') . '";</script>';
    }
    
    /**
     * Add the necesarry javascript from the CDN so Selfie will work
     */
    public function addZoneTag()
    {        
        # Add Broadstreet ad zone CDN
        if(!is_admin()) 
        {
            if(is_ssl()) {
                wp_enqueue_script('Broadstreet-cdn', 'https://s3.amazonaws.com/street-production/init.js');
                wp_enqueue_script('Broadstreet-selfie-cdn', 'https://s3.amazonaws.com/street-production/init-selfie.js');
            } else {
                wp_enqueue_script('Broadstreet-cdn', 'http://cdn.broadstreetads.com/init.js');
                wp_enqueue_script('Broadstreet-selfie-cdn', 'http://cdn.broadstreetads.com/init-selfie.js');                
            }
        }
    }    

    /**
     * A callback executed whenever the user tried to access the Broadstreet admin page
     */
    public function adminCallback()
    {
        $icon_url = 'http://broadstreet-common.s3.amazonaws.com/broadstreet-blargo/broadstreet-icon.png';
                
        add_menu_page('Selfie', 'Selfie', 'manage_options', 'Selfie', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Selfie', 'Settings', 'Account Setup', 'manage_options', 'Selfie', array($this, 'adminMenuCallback'));
        add_submenu_page('Selfie', 'Help', 'How To Get Started', 'manage_options', 'Selfie-Help', array($this, 'adminMenuHelpCallback'));
        add_submenu_page('Selfie', 'Tips', 'Important Tips', 'manage_options', 'Selfie-Tips', array($this, 'adminMenuTipsCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            if(!Selfie_Utility::getSelfieZoneId())
                echo '<div class="updated"><p>You\'re <strong>almost ready</strong> to start using Selfie! Check the <a href="admin.php?page=Selfie">plugin page</a> to take care of the last steps. When that\'s done, this message will clear shortly after.</p></div>';
            
            if(isset($_GET['action']) && $_GET['action'] == 'edit') {
                $config = Selfie_Utility::getConfigData();
                $post   = get_post();
                
                if(!$post) return;
                
                if(!($config->auto_place_top # If auto-selfie isn't enabled
                        || $config->auto_place_middle 
                        || $config->auto_place_bottom) 
                    && (!stristr($post->post_content, '[selfie]'))) # And this post 
                    {
                        echo '<div class="updated" style="border-left: 4px solid #ffba00;"><p><strong>Important:</strong>You have Selfie installed, but this post isn\'t using it! Add the <strong>[selfie]</strong> shortcode and sell some sponsorships!</p></div>';
                    }
            }
        }
    }
    
    /**
     * Callback for registering general Selfie scripts/styles
     */
    public function generalScriptCallback() 
    {
        wp_enqueue_script ('Selfie-box-script', Selfie_Utility::getJSBaseURL() . 'selfie-fe.js?v='. SELFIE_VERSION);                
        wp_enqueue_style ('Selfie-box-styles', Selfie_Utility::getCSSBaseURL() . 'selfie.css?v='. SELFIE_VERSION);                
        wp_enqueue_style ('Selfie-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');                
    }

    /**
     * A callback executed when the admin page callback is a about to be called.
     *  Use this for loading stylesheets/css.
     */
    public function adminInitCallback()
    {
        # Only register javascript and css if the Broadstreet admin page is loading
        if(strstr($_SERVER['QUERY_STRING'], 'Selfie'))
        {
            wp_enqueue_style ('Selfie-styles',  Selfie_Utility::getCSSBaseURL() . 'broadstreet.css?v='. SELFIE_VERSION);
            wp_enqueue_style ('Selfie-pricing-styles',  Selfie_Utility::getCSSBaseURL() . 'pricing.css?v='. SELFIE_VERSION);
            wp_enqueue_style ('Spectrum-styles',  Selfie_Utility::getCSSBaseURL() . 'spectrum.css?v='. SELFIE_VERSION);
            wp_enqueue_style ('Tipsy-styles',  Selfie_Utility::getCSSBaseURL() . 'tipsy.css?v='. SELFIE_VERSION);
            wp_enqueue_script('angular-js', '//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.10/angular.min.js');
            wp_enqueue_script('Selfie-config'  ,  Selfie_Utility::getJSBaseURL().'selfie.js?v='. SELFIE_VERSION);
            wp_enqueue_script('Tipsy-script'  ,  Selfie_Utility::getJSBaseURL().'jquery.tipsy.js?v='. SELFIE_VERSION);
            wp_enqueue_script('Spectrum-script'  ,  Selfie_Utility::getJSBaseURL().'spectrum.js?v='. SELFIE_VERSION);
        }
                
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Selfie-pricing-styles',  Selfie_Utility::getCSSBaseURL() . 'pricing.css?v='. SELFIE_VERSION);
        }
    }

    /**
     * The callback that is executed when the user is loading the admin page.
     *  Basically, output the page content for the admin page. The function
     *  acts just like a controller method for and MVC app. That is, it loads
     *  a view.
     */
    public function adminMenuCallback()
    {
        Selfie_Log::add('debug', "Admin page callback executed");
        Selfie_Utility::sendInstallReportIfNew();
        
        $data = array();

        $data['service_tag']        = Selfie_Utility::getServiceTag();
        $data['api_key']            = Selfie_Utility::getOption(self::KEY_API_KEY, '');
        $data['network_id']         = Selfie_Utility::getOption(self::KEY_NETWORK_ID);
        $data['errors']             = array();
        $data['networks']           = array();
        $data['key_valid']          = false;
        $data['has_cc']             = false;
        $data['selfie_config']      = Selfie_Utility::getConfigData();
        $data['categories']         = get_categories(array('hide_empty' => false));
        $data['tags']               = get_tags(array('hide_empty' => false));
        $data['styles']             = Selfie_Utility::getSelfieStyles();
        
        if(!function_exists('curl_exec'))
        {
            // We don't need this anymore
            //$data['errors'][] = 'Broadstreet requires the PHP cURL module to be enabled. You may need to ask your web host or developer to enable this.';
        }
                
        if(!$data['api_key']) 
        {
            //$data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Broadstreet account, <a href="http://my.broadstreetads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
        } 
        else 
        {
            $api = new Broadstreet($data['api_key']);
            
            try
            {
                $data['networks']   = $api->getNetworks();
                $data['key_valid']  = true;
                $data['network']    = Selfie_Utility::getNetwork(true);                 
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
        }
        
        $data['network_config'] = array (
            'networks'   => $data['networks'],
            'key_valid'  => $data['key_valid'],
            'network_id' => intval($data['network_id']),
            'api_key'    => $data['api_key']
        );

        Selfie_View::load('admin/admin', $data);
    }    
    
    /**
     * Callback for displaying the Selfie tutorial
     */
    public function adminMenuHelpCallback()
    {
        Selfie_View::load('admin/help');
    }
    
    /**
     * Callback for displaying the Selfie tips
     */
    public function adminMenuTipsCallback()
    {
        Selfie_View::load('admin/tips');
    }
    
    
    /**
     * Handler for the broadstreet info box below a post or page
     * @param type $post 
     */
    public function selfieInfoBox($post) 
    {        
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'selfienoncename');

        $pricing = Selfie_Utility::getPricingGrid($post->ID);
        
        $settings = Selfie_Utility::getAllPostMeta($post->ID, self::$overrideFields);
        
        Selfie_View::load('admin/postPricing', array('pricing' => $pricing, 'settings' => $settings));
    }
    
    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {

    }

    /**
     * Handler for saving business-specific meta data
     * @param type $post_id The id of the post
     * @param type $content The post content
     */
    public function savePostMeta($post_id, $content = false)
    {
        foreach(self::$overrideFields as $key => $value)
            Selfie_Utility::setPostMeta($post_id, $key, isset($_POST[$key]) ? trim(@$_POST[$key]) : @$_POST[$key]);
    }
    
    /**
     * Auto-place selfie slots in a post
     * @param type $content
     * @return type
     */
    public function autoSelfie($content)
    {        
        if(!$this->validSelfiePostType()) return $content;
        
        if(stristr($content, '[selfie]'))
            return $content;
        
        $config = Selfie_Utility::getConfigData();
        
        # Don't show on single page if it's not configured
        if(!is_single() && $config->auto_place_single_only)
            return $content;
        
        # Check if Selfie is disabled for this post
        $disabled = Selfie_Utility::getPostMeta(get_the_ID(), 'selfie_disabled', 'no');        
        if($disabled == 'yes')
            return $content;
        
        /* Split the content into paragraphs */
        $pieces = preg_split('/(\r\n|\n|\r)+/', trim($content));
        
        if(count($pieces) <= 1 && ($config->auto_place_top || $config->auto_place_bottom)) {
            # One paragraph
            return "$content\n\n[selfie]";
        }
        
        if(count($pieces) >= 2) {
            if($config->auto_place_top)
                array_splice($pieces, 1, 0, "[selfie]");
            if($config->auto_place_middle && count($pieces) != 2)
                array_splice($pieces, ceil(count($pieces)/2)+1, 0, "[selfie]");
            if($config->auto_place_bottom)
                $pieces[] = "[selfie]";    
        }
            
        /* It's magic, :snort: :snort: 
           - Mr. Bean: https://www.youtube.com/watch?v=x0yQg8kHVcI */
        $content = implode("\n\n", $pieces);

        return $content;
    }

    /**
     * Handler for in-post shortcodes
     * @param array $attrs
     * @return string 
     */
    public function shortcode($attrs, $content = '')
    {
        if(!$this->validSelfiePostType()) return '';
        
        $zone_id = Selfie_Utility::getSelfieZoneId();
        $config  = Selfie_Utility::getConfigData();
        $the_id  = get_the_ID();
        
        # If it's disabled, gtfo
        if($config->disable_all)
            return '';
        
        if($the_id) {
            # Check if Selfie is disabled for this post
            $disabled = Selfie_Utility::getPostMeta($the_id, 'selfie_disabled', 'no');        
            if($disabled == 'yes')
                return '';

            if(!isset(self::$selfiePositionCount[$the_id]))
                self::$selfiePositionCount[$the_id] = 0;
        } else {
            $the_id = 'none';
        }
        
        if(trim($content) == '')
            $content = $config->auto_message;
        
        if($the_id) {
            $content = Selfie_Utility::interpolatePricing($content, $the_id);
        }
        
        $position_id = $the_id ? ++self::$selfiePositionCount[$the_id] : '0';
                
        $views = Selfie_Utility::incrementSelfieViewCounts($the_id);
        $likes = Selfie_Utility::getSelfieLikeCount($the_id, $position_id);       
        
        return Selfie_View::tryLoad('ads/themes/' . $config->style, 'ads/selfie', array(
                'attrs'   => $attrs, 
                'content' => $content,
                'zone_id' => $zone_id,
                'post_id' => $the_id,
                'position_id' => $position_id,
                'style'   => Selfie_Utility::getInlineSelfieStyle($config),
                'config'  => $config,
                'views'   => $views,
                'likes'   => $likes
            ), true
        );
    }
    
    /**
     * Register the Selfie Widget
     */
    public function registerWidgets() {
        register_widget('Selfie_Zone_Widget');
    }
    
    /**
     * Create a virtual page that explains all about self serve ads
     * @global type $wp
     * @global type $wp_query
     * @global boolean $selfieAboutLoadFlag
     * @param type $posts
     * @return \stdClass
     */
    public function selfieAbout($posts) {
        global $wp;
        global $wp_query;

        global $selfieAboutLoadFlag; // used to stop double loading
        $selfieAboutPageSlug = self::SELFIE_ABOUT_SLUG; // URL of the fake page
            
        $fake_post_id = Selfie_Utility::getFakePostId();

        if (!$selfieAboutLoadFlag && 
                strstr($_SERVER['REQUEST_URI'], $selfieAboutPageSlug)) {

            $selfieAboutLoadFlag = true;
            
            $post = new stdClass();
            
            $post->post_author = 1;
            $post->post_name = $selfieAboutPageSlug;
            $post->guid = get_bloginfo('wpurl') . '/' . $selfieAboutPageSlug;
            $post->post_title = "About Self Serve Messages";
            $post->post_content = Selfie_View::load('help/about-selfie', array('config' => Selfie_Utility::getConfigData()), true);
            $post->ID = $fake_post_id;
            $post->post_type = 'page';
            $post->post_status = 'publish';
            $post->comment_status = 'closed';
            $post->ping_status = 'open';
            $post->comment_count = 0;
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql', 1);
            $posts = NULL;
            $posts[] = $post;

            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            
            unset($wp_query->query["error"]);
            
            $wp_query->query_vars["error"] = "";
            $wp_query->is_404 = false;
        }
        
        return $posts;    
    }
}

endif;