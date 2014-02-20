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
    
    public static $globals = null;
    
    public static $overrideFields = array (
        'selfie_day' => '',
        'selfie_week' => '',
        'selfie_month' => '',
        'selfie_year' => ''
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
        add_action('admin_menu', 	array($this, 'adminCallback'     ));
        add_action('admin_init', 	array($this, 'adminInitCallback' ));
        add_action('init',          array($this, 'addZoneTag' ));
        add_action('init',         array($this, 'pricingWebhook'));
        add_action('admin_notices', array($this, 'adminWarningCallback'));
        add_shortcode('selfie',     array($this, 'shortcode'));        
        
        # -- Below is administration AJAX functionality
        add_action('wp_ajax_save_settings', array('Selfie_Ajax', 'saveSettings'));
        add_action('wp_ajax_create_advertiser', array('Selfie_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_save_config', array('Selfie_Ajax', 'saveConfig'));
        add_action('wp_ajax_register', array('Selfie_Ajax', 'register'));
        add_action('wp_ajax_create_network', array('Selfie_Ajax', 'createNetwork'));
        
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
    
    public function pricingWebhook()
    {
        if(isset($_GET['selfie_id'])) {
            
            $key    = $_GET['selfie_id'];
            $term   = $_GET['selfie_term'];
            $length = $_GET['selfie_term_count'];
            
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
    
    public function addPostStyles()
    {
        if(false)
        {
            wp_enqueue_style ('Broadstreet-styles-listings', Selfie_Utility::getCSSBaseURL() . 'listings.css?v=' . BROADSTREET_VERSION);
        }
    }   
    
    public function addZoneTag()
    {        
        # Add Broadstreet ad zone CDN
        if(!is_admin()) 
        {
            wp_enqueue_script('Broadstreet-dev-js', 'http://192.168.1.2:3000/init-development.js');
            wp_enqueue_script('Broadstreet-dev-selfie-js', 'http://192.168.1.2:3000/init-development-selfie.js');
            //wp_enqueue_script('Broadstreet-cdn', 'http://cdn.broadstreetads.com/init.js');
        }
    }    

    /**
     * A callback executed whenever the user tried to access the Broadstreet admin page
     */
    public function adminCallback()
    {
        $icon_url = 'http://broadstreet-common.s3.amazonaws.com/broadstreet-blargo/broadstreet-icon.png';
                
        add_menu_page('Selfie', 'Selfie', 'edit_pages', 'Selfie', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Selfie', 'Settings', 'Account Setup', 'edit_pages', 'Selfie', array($this, 'adminMenuCallback'));
        #add_submenu_page('Broadstreet', 'Advanced', 'Advanced', 'edit_pages', 'Broadstreet-Layout', array($this, 'adminMenuLayoutCallback'));
        add_submenu_page('Selfie', 'Help', 'How To Get Started', 'edit_pages', 'Selfie-Help', array($this, 'adminMenuHelpCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Selfie_Utility::getNetwork();

            //if(!$info || !$info->cc_on_file)
            //    echo '<div class="updated"><p>You\'re <strong>almost ready</strong> to start using Broadstreet! Check the <a href="admin.php?page=Broadstreet">plugin page</a> to take care of the last steps. When that\'s done, this message will clear shortly after.</p></div>';
        }
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
            wp_enqueue_style ('Selfie-styles',  Selfie_Utility::getCSSBaseURL() . 'broadstreet.css?v='. BROADSTREET_VERSION);
            wp_enqueue_style ('Selfie-pricing-styles',  Selfie_Utility::getCSSBaseURL() . 'pricing.css?v='. BROADSTREET_VERSION);
            wp_enqueue_style ('Tipsy-styles',  Selfie_Utility::getCSSBaseURL() . 'tipsy.css?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Selfie-main'  ,  Selfie_Utility::getJSBaseURL().'broadstreet.js?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Selfie-config'  ,  Selfie_Utility::getJSBaseURL().'config.js?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Tipsy-script'  ,  Selfie_Utility::getJSBaseURL().'jquery.tipsy.js?v='. BROADSTREET_VERSION);
        }
                
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Selfie-pricing-styles',  Selfie_Utility::getCSSBaseURL() . 'pricing.css?v='. BROADSTREET_VERSION);
        }
        
        # Include thickbox on widgets page
        if($GLOBALS['pagenow'] == 'widgets.php')
        {
            wp_enqueue_script('thickbox');
            wp_enqueue_style( 'thickbox' );
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
    
    public function adminMenuEditableCallback()
    {
        Selfie_View::load('admin/editable');
    }
    
    
    public function adminMenuHelpCallback()
    {
        Selfie_View::load('admin/help');
    }
    
    public function adminMenuLayoutCallback()
    {
        Selfie_View::load('admin/layout');
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
     * Handler for the broadstreet info box below a post or page
     * @param type $post 
     */
    public function broadstreetBusinessBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetnoncename');
        
        $meta = Selfie_Utility::getAllPostMeta($post->ID, self::$_businessDefaults);
        
        $network_id       = Selfie_Utility::getOption(self::KEY_NETWORK_ID);
        $advertiser_id    = Selfie_Utility::getPostMeta($post->ID, 'bs_advertiser_id');
        $advertisement_id = Selfie_Utility::getPostMeta($post->ID, 'bs_advertisement_id');
        $network_info     = Selfie_Utility::getNetwork();
        $show_offers      = (Selfie_Utility::getOption(self::KEY_SHOW_OFFERS) == 'true');
        
        $api = $this->getBroadstreetClient();
        
        if($network_id && $advertiser_id && $advertisement_id)
        {
            $meta['preferred_hash_tag'] = $api->getAdvertisement($network_id, $advertiser_id, $advertisement_id)
                                    ->preferred_hash_tag;
        }
        
        try
        {
            $advertisers = $api->getAdvertisers($network_id);
        } 
        catch(Exception $ex)
        {
            $advertisers = array();
        }
        
        Selfie_View::load('admin/businessMetaBox', array(
            'meta'        => $meta, 
            'advertisers' => $advertisers, 
            'network'     => $network_info,
            'show_offers' => $show_offers
        ));
    }

    /**
     * Handler used for attaching post meta data to post query results
     * @global object $wp_query
     * @param array $posts
     * @return array 
     */
    public function businessQuery($posts) 
    {
        global $wp_query;
        
        if(@$wp_query->query_vars['post_type'] == self::BIZ_POST_TYPE
            || @$wp_query->query_vars['taxonomy'] == self::BIZ_TAXONOMY)
        {
            $ids = array();
            foreach($posts as $post) $ids[] = $post->ID;

            $meta = Broadstreet_Model::getPostMeta($ids, self::$_businessDefaults);

            for($i = 0; $i < count($posts); $i++)
            {
                if(isset($meta[$posts[$i]->ID]))
                {
                    $posts[$i]->meta = $meta[$posts[$i]->ID];
                }
            }
        }
        
        return $posts;
    }
    
    /**
     * Handler used for changing the wording of the comment form for business
     * listings.
     * @param array $defaults
     * @return string 
     */
    public function commentForm($defaults)
    {
        $defaults['title_reply'] = 'Leave a Review or Comment';
        return $defaults;
    }
     
    public function createPostTypes()
    {        
        register_post_type(self::BIZ_POST_TYPE,
            array (
                'labels' => array(
                    'name' => __( 'Businesses'),
                    'singular_name' => __( 'Business'),
                    'add_new_item' => __('Add New Business Profile', 'your_text_domain'),
                    'edit_item' => __('Edit Business', 'your_text_domain'),
                    'new_item' => __('New Business Profile', 'your_text_domain'),
                    'all_items' => __('All Businesses', 'your_text_domain'),
                    'view_item' => __('View This Business', 'your_text_domain'),
                    'search_items' => __('Search Businesses', 'your_text_domain'),
                    'not_found' =>  __('No businesses found', 'your_text_domain'),
                    'not_found_in_trash' => __('No businesses found in Trash', 'your_text_domain'), 
                    'parent_item_colon' => '',
                    'menu_name' => __('Businesses', 'your_text_domain')
                ),
            'description' => 'Businesses for inclusion in the Broadstreet business directory',
            'public' => true,
            'has_archive' => true,
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'rewrite' => array( 'slug' => self::BIZ_SLUG),
            'taxonomies' => array('business_category')
            )
        );
        
        $this->addBusinessTaxonomy();
        Selfie_Utility::flushRewrites();
    }
    
    /**
     * Handler for modifying business/archive listings
     * @param type $query 
     */
    public function modifyPostListing($query)
    {
        if(is_post_type_archive(self::BIZ_POST_TYPE))
        {
            $query->query_vars['posts_per_page'] = 50;
            $query->query_vars['orderby'] = 'title';
            $query->query_vars['order'] = 'ASC';
        }
    }
    
    /**
     * Handler used for modifying the way business listings are displayed
     * @param string $content The post content
     * @return string Content
     */
    public function postTemplate($content)
    {   
        # Only do this for business posts, and don't do it
        #  for excerpts
        if(!Selfie_Utility::inExcerpt() 
                && get_post_type() == self::BIZ_POST_TYPE)
        {   
            $meta = $GLOBALS['post']->meta;
            
            # Make sure the image meta is unserialized properly
            if(isset($meta['bs_images']))
                $meta['bs_images'] = maybe_unserialize($meta['bs_images']);
            
            if(is_single())
            {
                return Selfie_View::load('listings/single/default', array('content' => $content, 'meta' => $meta), true);
            }
            else
            {   
                return Selfie_View::load('listings/archive/default', array('content' => $content, 'meta' => $meta), true);
            }
        }
        
        return $content;
    }
    
    /**
     * Handler used for modifying the way business listings are displayed
     *  in exceprts, for themes that use excerpts
     * @param type $content
     * @return type 
     */
    public function postExcerpt($content)
    {
        if(get_post_type() == self::BIZ_POST_TYPE)
        {
            $meta = $GLOBALS['post']->meta;
            $this->_excerptRan = true;
            return Selfie_View::load('listings/archive/excerpt', array('content' => $content, 'meta' => $meta), true);
        }
        else 
        {
            # Special thanks to Justin
            return get_the_excerpt();
        }


    }
    
    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {
        register_widget('Broadstreet_Zone_Widget');
        register_widget('Broadstreet_SBSZone_Widget');
        register_widget('Broadstreet_Multiple_Zone_Widget');
        register_widget('Broadstreet_Business_Listing_Widget');
        register_widget('Broadstreet_Business_Profile_Widget');
        register_widget('Broadstreet_Business_Categories_Widget');
        register_widget('Broadstreet_Editable_Widget');
    }

    /**
     * Handler for saving business-specific meta data
     * @param type $post_id The id of the post
     * @param type $content The post content
     */
    public function savePostMeta($post_id, $content = false)
    {
        foreach(self::$overrideFields as $key => $value)
            Selfie_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
    }

    /**
     * Handler for in-post shortcodes
     * @param array $attrs
     * @return string 
     */
    public function shortcode($attrs, $content = '')
    {
        $zone_id = Selfie_Utility::getOption(self::KEY_SELFIE_ZONE_ID.'_NET_'.Selfie_Utility::getNetworkId());
        $the_id = get_the_ID();
        
        if(!isset(self::$selfiePositionCount[$the_id]))
            self::$selfiePositionCount[$the_id] = 0;
        
        return Selfie_View::load('ads/selfie', array(
                'attrs' => $attrs, 
                'content' => $content,
                'zone_id' => $zone_id,
                'post_id' => get_the_ID(),
                'position_id' => ++self::$selfiePositionCount[$the_id]
            ), true
        );
    }
}

endif;