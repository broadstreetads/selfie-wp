<?php
/**
 * This file contains a class which provides the AJAX callback functions required
 *  for Broadstreet.
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * A class containing functions for the AJAX functionality in Broadstreet. These
 *  aren't executed directly by any Broadstreet code -- they are registered with
 *  the Wordpress hooks in Selfie_Core::_registerHooks(), and called as needed
 *  by the front-end and Wordpress. All of these methods output JSON.
 */
class Selfie_Ajax
{
    
    public static function likeSelfie()
    {
        $post = @$_GET['post'];
        $position = @$_GET['position'];
                
        if($post && $position) {
            try {
                $new_value = Selfie_Utility::likeSelfie ($post, $position);
                Selfie_Utility::jsonResponse(array('likes' => $new_value));    
            } catch(Exception $ex) {
                Selfie_Utility::jsonResponse(array(), 'Already liked');
            }
        } else {
            Selfie_Utility::jsonResponse(array(), 'Bad request');    
        }        
    }
    
    public static function createNetwork()
    {
        $args         = json_decode(file_get_contents("php://input"));
        $network      = $args->network;
        $error        = false;
        $key_valid    = false;
        
        $api = new Broadstreet($network->api_key);
        
        try {            
            $resp = $api->createNetwork(get_bloginfo('name'));
            $network->network_id = $resp->id;
            
            Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $network->network_id);
            
            $networks  = $api->getNetworks();

            $resp = $api->createZone($resp->id, 'Selfie Zone for '.site_url(), array (
                'self_serve' => true,
                'pricing_callback_url' => site_url()
            ));   
 
            Selfie_Utility::setOption(Selfie_Core::KEY_SELFIE_ZONE_ID.'_NET_'.$network->network_id, $resp->id);  
            
            $key_valid = true;
            
        } catch(Exception $ex) {
            $networks = array();
            $key_valid = false; 
            $error = true;
        }

        $data = array(
            'networks'  => $networks,
            'key_valid' => $key_valid,
            'network_id' => $error ? null : $network->network_id,
            'api_key' => $error ? null : $network->api_key
        );
        
        if($error)
            Selfie_Utility::jsonResponse(array('network' => $data), 'Error: ' . $ex->getMessage(), 400, false);
        else            
            Selfie_Utility::jsonResponse(array('network' => $data));   

    }
    /**
     * Save a boolean value of whether to index comments on the next rebuild
     */
    public static function saveSettings()
    {
        $args         = json_decode(file_get_contents("php://input"));
        $network      = $args->network;
        $networks     = null;
        $access_token = false;
        $error        = false;
        
        $key_valid = $network->key_valid;
        
        $api = new Broadstreet($network->api_key);

        try
        {
            $networks  = $api->getNetworks();
            $key_valid = true;
            $access_token = $network->api_key;

            if(!is_int($network->network_id))
            {
                Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $networks[0]->id);
                $network->network_id = $networks[0]->id;
            }
            else
            {
                Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $network->network_id);
            }

            # Looks like the API key was good
            Selfie_Utility::setOption(Selfie_Core::KEY_API_KEY, $network->api_key);            
            Selfie_Utility::getSelfieZoneId();
            
            $error = false;
        }
        catch(Exception $ex)
        {
            $networks = array();
            $key_valid = false; 
            $error = true;
        }

        $data = array(
            'networks'  => $networks,
            'key_valid' => $key_valid,
            'network_id' => $error ? null : $network->network_id,
            'api_key' => $error ? null : $access_token
        );

        if($error)
            Selfie_Utility::jsonResponse(array('network' => $data), 'Error: ' . $ex->getMessage(), 400, false);
        else            
            Selfie_Utility::jsonResponse(array('network' => $data));   
    }  
    
    public static function saveConfig() 
    {
        $success = false;
        $pricing = json_decode(file_get_contents("php://input"));
        
        if($pricing)
        {
            Selfie_Utility::setOption(Selfie_Utility::KEY_PRICING, $pricing);
            $success = true;
        } 
        else 
        {
            $success = false;
        }
        
        die(json_encode(array('success' => true)));
    }
    
    public static function register()
    {
        $api = new Broadstreet();
        $args = json_decode(file_get_contents("php://input"));
                
        try
        {
            # Register the user by email address
            $resp = $api->register($args->email);
            $access_token = $resp->access_token;
            Selfie_Utility::setOption(Selfie_Core::KEY_API_KEY, $resp->access_token);

            # Create a network for the new user
            $resp = $api->createNetwork(get_bloginfo('name'));
            $network_id = $resp->id;
            Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $resp->id);
            
            $resp = $api->createZone($resp->id, 'Selfie Zone for '.site_url(), array (
                'self_serve' => true,
                'pricing_callback_url' => site_url()
            ));            
            
            Selfie_Utility::setOption(Selfie_Core::KEY_SELFIE_ZONE_ID.'_NET_'.$network_id, $resp->id);

            $data = array(
                'networks'  => $api->getNetworks(),
                'key_valid' => true,
                'network_id' => $network_id,
                'api_key' => $access_token
            );
            
            Selfie_Utility::jsonResponse(array('network' => $data));
        }
        catch(Exception $ex)
        {
            Selfie_Utility::jsonResponse(array(), 'Error:' . $ex->getMessage(), 500, false);
        }
    }
}