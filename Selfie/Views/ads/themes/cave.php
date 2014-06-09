<div <?php if(isset($modal) && $modal) echo 'data-selfie-modal="true"'; ?> class="selfie-div selfie-cave">
    <div class="selfie-content">
        <?php if($zone_id): ?>
        <script>broadstreet.zone(<?php echo $zone_id ?>, {selfieCallback: function() { return <?php echo json_encode($content) ?>; }, keywords: ['postid:<?php echo @$post_id ?>:<?php echo $position_id ?>']})</script>
        <?php else: ?>
        Important! Selfie isn't set up yet! Go to the Wordpress admin, and click "Selfie" on the left menu bar in order to get started.
        <?php endif; ?>
    </div>
    <div class="selfie-footer">
        <div class="selfie-footer-left">
        <i class="fa fa-eye"></i> <?php echo $views['all_time'] ?>
        </div>
        <a href="javascript:void(0)" onclick="selfieLiked(event, <?php echo $post_id ?>, <?php echo $position_id ?>)" class="selfie-footer-right">
            Like it? &nbsp;&nbsp;<i class="fa fa-thumbs-up"></i> <span id="selfie-count-<?php echo $post_id ?>-<?php echo $position_id ?>"><?php echo $likes ?></span>
        </a>
        <?php if($config->show_help): ?>
            <a class="selfie-cave-help-link" target="_blank" href="<?php echo get_bloginfo('wpurl') .'/'. Selfie_Core::SELFIE_ABOUT_SLUG ?>/">What is this?</a>
        <?php endif; ?>
        <div class="selfie-clear"></div>
    </div>
</div>