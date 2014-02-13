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
    /**
     * Save a boolean value of whether to index comments on the next rebuild
     */
    public static function saveSettings()
    {
        Selfie_Utility::setOption(Selfie_Core::KEY_API_KEY, $_POST['api_key']);
        Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $_POST['network_id']);
        
        $api = new Broadstreet($_POST['api_key']);

        try
        {
            $networks  = $api->getNetworks();
            $key_valid = true;
            
            if($_POST['network_id'] == '-1')
            {
                Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $networks[0]->id);
            }
            
            //Selfie_Utility::refreshZoneCache();
        }
        catch(Exception $ex)
        {
            $networks = array();
            $key_valid = false;            
        }
        
        die(json_encode(array('success' => true, 'key_valid' => $key_valid, 'networks' => $networks)));
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
        
        try
        {
            # Register the user by email address
            $resp = $api->register($_POST['email']);
            Selfie_Utility::setOption(Selfie_Core::KEY_API_KEY, $resp->access_token);

            # Create a network for the new user
            $resp = $api->createNetwork(get_bloginfo('name'));
            Selfie_Utility::setOption(Selfie_Core::KEY_NETWORK_ID, $resp->id);

            die(json_encode(array('success' => true, 'network' => $resp)));
        }
        catch(Exception $ex)
        {
            die(json_encode(array('success' => false, 'error' => $ex->__toString())));
        }
    }
}