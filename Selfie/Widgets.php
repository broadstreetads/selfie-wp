<?php

/**
 * This is an optional widget to display a broadstreet zone
 */
class Selfie_Zone_Widget extends WP_Widget
{
    protected static $_displayCount = 0;
    
    /**
     * Set the widget options
     */
     function __construct()
     {
        $widget_ops = array('classname' => 'selfie_sidebar', 'description' => 'A Selfie sidebar zone');
        $this->WP_Widget('selfie_sidebar', 'Selfie Area', $widget_ops);
     }

     /**
      * Display the widget on the sidebar
      * @param array $args
      * @param array $instance
      */
     function widget($args, $instance)
     {
         /*
         extract($args);
         
         $title     = apply_filters('widget_title', $instance['w_title']);
         $zone_id   = $instance['w_zone'];
         $zone_data = Broadstreet_Utility::getZoneCache();
         
         if($zone_data)
         {
            echo $before_widget;
            
            if(trim($title))
                echo $before_title . $title. $after_title;

            echo $zone_data[$zone_id]->html;

            echo $after_widget;
         }
          */
         $zone_id = Selfie_Utility::getSelfieZoneId();
         $config  = Selfie_Utility::getConfigData();
         
         $content = Selfie_View::load('ads/sidebar', array (
                'attrs' => array(), 
                'content' => 'Buy this Selfie zone! You are nuts not to',
                'zone_id' => $zone_id,
                'post_id' => 'sidebar',
                'position_id' => ++self::$_displayCount,
                'style' => '',
                'config' => $config,
                'modal' => true
            ), true);
         
         //for($i = 1; $i <= 2; $i++)
            echo "<div class='selfie-sidebar-container'><div class='selfie-sidebar-box'>$content</div><span class='selfie-tip". ($i % 2 == 0 ? ' left' : '') ."'></span></div>\n";
     }

     /**
      * Update the widget info from the admin panel
      * @param array $new_instance
      * @param array $old_instance
      * @return array
      */
     function update($new_instance, $old_instance)
     {
        /*
        $instance = $old_instance;
        
        $instance['w_zone'] = $new_instance['w_zone'];
        $instance['w_title'] = $new_instance['w_title'];

        return $instance;
        */
     }

     /**
      * Display the widget update form
      * @param array $instance
      */
     function form($instance) 
     {
         ?>
            <p>This is a Selfie "Whitebox" zone.</p>
         <?php
         /*
        $defaults = array('w_title' => '', 'w_info_string' => '', 'w_opener' => '', 'w_closer' => '', 'w_zone' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
        
        $zones = Broadstreet_Utility::refreshZoneCache();
        
       ?>
        <div class="widget-content">
       <?php if(count($zones) == 0): ?>
            <p style="color: green; font-weight: bold;">You either have no zones or
                Broadstreet isn't configured correctly. Go to 'Settings', then 'Broadstreet',
            and make sure your access token is correct, and make sure you have zones set up.</p>
        <?php else: ?>
        <p>
            <label for="<?php echo $this->get_field_id('w_title'); ?>">Title (optional):</label>
            <input class="widefat" type="input" id="<?php echo $this->get_field_id('w_title'); ?>" name="<?php echo $this->get_field_name('w_title'); ?>" value="<?php echo $instance['w_title'] ?>" />
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('w_info_string'); ?>">Zone</label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'w_zone' ); ?>" name="<?php echo $this->get_field_name('w_zone'); ?>" >
                <?php foreach($zones as $id => $zone): ?>
                <option <?php if(isset($instance['w_zone']) && $instance['w_zone'] == $zone->id) echo "selected" ?> value="<?php echo $zone->id ?>"><?php echo $zone->name ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php endif; ?>
        </div>
       <?php */
     }
}