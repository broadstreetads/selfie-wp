<p data-selfie-modal="true" class="selfie-whitebox-box">
    <?php if($zone_id): ?>
    <script>broadstreet.zone(<?php echo $zone_id ?>, {selfieCallback: function() { return <?php echo json_encode($content) ?>; }, keywords: ['postid:<?php echo @$post_id ?>:<?php echo $position_id ?>']})</script>
    <?php else: ?>
    Important! Selfie isn't set up yet! Go to the Wordpress admin, and click "Selfie" on the left menu bar in order to get started.
    <?php endif; ?>
    <span class="selfie-whitebox-tip"></span>
</p>
