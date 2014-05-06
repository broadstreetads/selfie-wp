<?php if(!isset($attrs['nowrap'])): ?>

<p <?php if(isset($modal) && $modal) echo 'data-selfie-modal="true"'; ?> class="selfie-paragraph <?php if(isset($attrs['whitebox']) || $config->style == 'Whitebox') echo "selfie-whitebox-box"; ?>" style="<?php echo isset($attrs['plain']) ? '' : $style ?>">
    <?php if($config->show_help): ?>
        <span class="selfie-help-icon"><a target="_blank" href="<?php echo get_bloginfo('wpurl') .'/'. Selfie_Core::SELFIE_ABOUT_SLUG ?>/">What is this?</a></span>
    <?php endif; ?>
<?php endif; ?>
    <?php if($zone_id): ?>
    <script>broadstreet.zone(<?php echo $zone_id ?>, {selfieCallback: function() { return <?php echo json_encode($content) ?>; }, keywords: ['postid:<?php echo @$post_id ?>:<?php echo $position_id ?>']})</script>
    <?php else: ?>
    Important! Selfie isn't set up yet! Go to the Wordpress admin, and click "Selfie" on the left menu bar in order to get started.
    <?php endif; ?>
    <?php if(isset($attrs['whitebox']) || $config->style == 'Whitebox'): ?>
        <span class="selfie-whitebox-tip"></span>
    <?php endif; ?>
<?php if(!isset($attrs['nowrap'])): ?>
</p>
<?php endif;
