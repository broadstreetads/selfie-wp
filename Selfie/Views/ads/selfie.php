<p style="<?php echo $style ?>">
    <?php if($zone_id): ?>
    <?php echo $config->message_prefix ?><script>broadstreet.zone(<?php echo $zone_id ?>, {selfieCallback: function() { return <?php echo json_encode($content) ?>; }, keywords: ['postid:<?php echo $post_id ?>:<?php echo $position_id ?>']})</script>
    <?php else: ?>
    Important! Selfie isn't set up yet! Go to the Wordpress admin, and click "Selfie" on the left menu bar in order to get started.
    <?php endif; ?>
</p>