<?php if(Selfie_Utility::getSelfieZoneId()): ?>
<p>You can override your <a href="admin.php?page=Selfie">Selfie price settings</a> for this individual post.
       Don't forget that these prices will always be in effect, regardless
       of the rules you have set. If a particular term isn't available, 
       it's because your rules don't permit it.
    </p>
    <div class="sf-pricing-row">
    <table class="sf-pricing-table">
        <thead>
            <tr>
                <th>Price Per:</th>
                <?php if($pricing['day'] !== ''): ?>
                <th>Day</th>
                <?php endif; ?>
                <?php if($pricing['week'] !== ''): ?>
                <th>Week</th>
                <?php endif; ?>
                <?php if($pricing['month'] !== ''): ?>
                <th>Month</th>
                <?php endif; ?>
                <?php if($pricing['year'] !== ''): ?>
                <th>Year</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>&nbsp;</td>
                <?php if($pricing['day'] !== ''): ?>
                <td><span class="sf-input-prepend money">$</span><input value="<?php echo $settings['selfie_day'] ?>" placeholder="<?php echo $pricing['day'] ?>" class="sf-prepended-input" type="text" name="selfie_day" /></td>
                <?php endif; ?>
                <?php if($pricing['week'] !== ''): ?>
                <td><span class="sf-input-prepend money">$</span><input value="<?php echo $settings['selfie_week'] ?>" placeholder="<?php echo $pricing['week'] ?>" class="sf-prepended-input" type="text" name="selfie_week" /></td>
                <?php endif; ?>
                <?php if($pricing['month'] !== ''): ?>
                <td><span class="sf-input-prepend money">$</span><input value="<?php echo $settings['selfie_month'] ?>" placeholder="<?php echo $pricing['month'] ?>" class="sf-prepended-input" type="text" name="selfie_month" /></td>
                <?php endif; ?>
                <?php if($pricing['year'] !== ''): ?>
                <td><span class="sf-input-prepend money">$</span><input value="<?php echo $settings['selfie_year'] ?>" placeholder="<?php echo $pricing['year'] ?>" class="sf-prepended-input" type="text" name="selfie_year" /></td>
                <?php endif; ?>
            </tr>
        </tbody>
    </table>
    </div>
    <p>
        <strong>Disable Selfie for this Post</strong>: 
        <select  class="sf-prepended-input" type="text" name="selfie_disabled">
            <option <?php if($settings['selfie_disabled'] == 'no') echo 'selected="selected"'; ?> value="no">No</option>
            <option <?php if($settings['selfie_disabled'] == 'yes') echo 'selected="selected"'; ?> value="yes">Yes</option>            
        </select>
    </p>
<?php else: ?>
    <p style="color: green; font-weight: bold;">
        Selfie isn't configured correctly. <a href="admin.php?page=Selfie">Click here to get set up</a>
    </p>
<?php endif; ?>
