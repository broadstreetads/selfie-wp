<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<script>
    window.selfie_config = <?php echo json_encode($selfie_config); ?>;
    window.categories = <?php echo json_encode($categories); ?>;
    window.tags = <?php echo json_encode($tags); ?>;
    window.network_config = <?php echo json_encode($network_config); ?>;
    window.styles = <?php echo json_encode($styles); ?>
</script>
<div id="main" ng-app="selfie">
    
      <?php Selfie_View::load('admin/global/header') ?>
    
    <div class="left_column" ng-controller="ConfigCtrl">
        <style id="selfie-style">
            {{generateBasicStyles()}}
            {{styles[config.style]}}
        </style>
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Selfie_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
                            Alerts
                        </div>
                        <div class="content">
                            <p>
                                Nice to have you! We've noticed some things you may want to take
                                care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="shadow_bottom"></div>
             </div>
         <?php endif; ?>
          <div id="controls">
            <div class="box">
                <div class="title">Selfie Setup</div>
                <div class="content">
                    <div class="option" ng-show="network.key_valid">
                        <div class="control-label">
                            <div class="name nomargin">
                                Woot! You're all set!
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="long-desc nomargin">
                            You're ready to start using Selfie. Move on the
                            configuration section below. Or, read the <a href="?page=Selfie-Help">getting started guide</a>. If you need to
                            edit your account info, <a href="#" ng-click="showAdvancedSignup = !showAdvancedSignup">click here</a>.
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break" ng-show="network.key_valid && showAdvancedSignup"></div>
                    <div class="option" ng-show="!network.key_valid">
                        <div class="control-label">
                            <div class="name nomargin">
                                Get Rolling on Broadstreet (One Click)
                            </div>
                            <div class="desc nomargin">
                                If you don't have a Broadstreet account yet, register for one to the right.
                                You'll receive a welcome email. If you do,
                                <a href="#" ng-click="showAdvancedSignup = !showAdvancedSignup">click here</a>.
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="email" ng-model="config.admin_email" />
                            <input type="button" ng-click="registerUser()" value="Vroom!" class="button button-primary button-large" name="" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break" ng-show="!network.key_valid && showAdvancedSignup"></div>
                    <div class="option" ng-show="showAdvancedSignup">
                        <div class="control-label">
                            <div class="name nomargin">
                                Access Token
                                
                                <span class="error" ng-show="!network.key_valid">Invalid</span>
                                <span class="success" ng-show="network.key_valid">Valid</span>
                                
                            </div>
                            <div class="desc nomargin">
                                This can be found <a target="_blank" href="http://my.broadstreetads.com/access-token">here</a> when you're logged in to Broadstreet.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input ng-model="network.api_key" type="text" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break" ng-show="showAdvancedSignup"></div>
                    <div class="option" ng-show="showAdvancedSignup">
                        <div class="control-label">
                            <div class="name nomargin">
                                Publisher Selection                                
                            </div>
                            <div class="desc nomargin">
                                Which publisher or network does this site fall under?
                                If you don't see it, <a href="" ng-click="createNetwork()">create it</a>.
                            </div>
                        </div>
                        <div class="control-container">
                            <select ng-options="net.id as net.name for net in network.networks" type="text" ng-model="network.network_id"></select>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break" ng-show="showAdvancedSignup"></div>
                    <div class="option" ng-show="showAdvancedSignup">
                        <div class="control-label">
                            <div class="name nomargin">
                                <a href="?page=Selfie-Help">How to Get Started</a>
                            </div>
                        </div>
                        <div class="save-container">
                            <input ng-click="updateUser()" type="button" value="Save" class="button button-primary button-large" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
          
        <!-- Pricing data box -->
        <form name="selfieConfigForm" class="selfie-config-form">
        <div id="controls">
            <div class="box">
                <div class="title">Selfie Settings</div>
                <div class="content">
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Selfie Prefix
                            </div>
                            <div class="desc nomargin">
                                When a Selfie is purchased, this text will
                                appear right before the buyer's message (optional).
                            </div>
                        </div>
                        <div class="control-container">
                            <input placeholder="Awesome Sponsor:" type="text" ng-model="config.message_prefix" tip="Leaving this blank may be more attractive to purchase. But if you use it, show some appreciation for your sponsors!" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Selfie Pricing
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="long-desc">
                            Set the default prices that a Selfie message should
                            cost your end users. You can add more sophisticated
                            rules below.
                        </div>
                        <div class="clear-break"></div>
                        <table class="sf-pricing-table">
                            <thead>
                                <tr>
                                    <th>Price Per:</th>
                                    <th>Day</th>
                                    <th>Week</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="config.price_day" /></td>
                                    <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="config.price_week" /></td>
                                    <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="config.price_month" /></td>
                                    <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="config.price_year" /></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Special Pricing Rules
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="long-desc">
                            You can add specific pricing rules to categories,
                            tags, and posts below. Remember that rules are
                            processed in order. <strong>The farther down the rule is
                            the greater precedence it has.</strong>
                        </div>

                        <div style="clear:both;"></div>
                    </div>
                    <div class="clear-break"></div>
                    <div ng-repeat="rule in config.rules">
                    
                    <div class="pricing-row-header">
                        Rule {{$index+1}}
                    </div>
                    <div class="option pricing-rule">
                        <div class="pricing-rule-picker">
                            <div class="nomargin">
                                If a post <select ng-options="ruleConfigType as ruleConfig.name for (ruleConfigType, ruleConfig) in ruleTargetConfigs.post" ng-model="rule.type"></select>
                                <span ng-show="ruleTargetConfigs.post[rule.type].optionType == 'text'">
                                    <input class="pricing-config-input" type="text" ng-model="rule.param" /> {{ruleTargetConfigs.post[rule.type].suffix}}
                                </span>
                                <span ng-show="ruleTargetConfigs.post[rule.type].optionType == 'list'">
                                    <select ng-options="option.term_id as option.name for option in ruleTargetConfigs.post[rule.type].options" ng-model="rule.param"></select>
                                </span>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="break"></div>
                        <div class="sf-pricing-row">
                            <table class="sf-pricing-table">
                                <thead>
                                    <tr>
                                        <th>Price Per:</th>
                                        <th>Day</th>
                                        <th>Week</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="rule.price_day" /></td>
                                        <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="rule.price_week" /></td>
                                        <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="rule.price_month" /></td>
                                        <td><span class="sf-input-prepend money">$</span><input ng-pattern="priceRegex" class="sf-prepended-input" type="text" ng-model="rule.price_year" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="pricing-row-controls">
                        <div style="float: left;">
                            <span class="sf-move-ctrl" ng-click="moveRuleUp($index)" ng-show="$index > 0">Move Up</span> <span ng-show="$index < config.rules.length - 1" ng-click="moveRuleDown($index)" class="sf-move-ctrl">Move Down</span>
                        </div>    
                        <span class="sf-remove-ctrl" ng-click="removeRule($index)">Remove</span>
                        <div style="clear:both;"></div>
                    </div>
                    </div>
                    
                    <div class="option pricing-rule">
                        <div class="control-label">
                            <div class="desc nomargin">
                                <span class="add-pricing-rule" ng-click="addRule()">+ Add a Rule</span>
                            </div>
                        </div>
                        <div class="control-container">
                            
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Look n' Feel
                            </div>
                        </div>    
                        <div style="clear:both;"></div>
                        <div class="long-desc">
                            How should the Selfie text look when displayed? This can stand out.
                            Leave the color empty to revert to the post's default text color.
                        </div>
                        <div class="sf-pricing-row">
                            <table class="sf-pricing-table">
                                <thead>
                                    <tr>
                                        <th>Bold?</th>
                                        <th>Italic?</th>
                                        <th>Underline?</th>
                                        <th>Center?</th>
                                        <th>Font-Size?</th>
                                        <th>Font Color?</th>
                                        <th>Style?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" ng-model="config.font_bold" /></td>
                                        <td><input type="checkbox" ng-model="config.font_italic" /></td>
                                        <td><input type="checkbox" ng-model="config.font_underline" /></td>
                                        <td><input type="checkbox" ng-model="config.center" /></td>
                                        <td>
                                            <select ng-model="config.font_size">
                                                <option value="100%">Normal</option>
                                                <option value="105%">Big</option>
                                                <option value="110%">Bigger</option>
                                                <option value="115%">Biggest</option>
                                            </select>
                                        </td>
                                        <td><input type="color" data-allow-empty="true" data-preferred-format="hex" data-show-input="true" data-show-palette="true" ng-model="config.font_color" /></td>
                                        <td><select ng-model="config.style" ng-options="key as key for (key, value) in styles | orderBy:key"></select></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="style-chooser">
                            <div class="style-chooser-title"><strong>Selfie Preview</strong> &minus; The final font rendered will match your theme's font.</div>
                            <p class="selfie-paragraph">
                                <span>
                                    <span class="broadstreet-selfie" style="display: block; position: relative;">
                                        <span data-placeholder="Start typing your message here..." style="outline: none; display: block;">
                                            {{config.auto_message}}
                                        </span>                                            
                                    </span>                                        
                                </span>                                
                            </p>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Halp!
                            </div>
                            <div class="desc nomargin">
                                Should we show an auto-generated help link?<br/>
                                This will help educate readers on what Selfie is.
                            </div>
                        </div>
                        <div class="control-container" style="padding-top: 10px; padding-left: 30px;">
                            <input ng-model="config.show_help" type="checkbox" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Auto Selfie
                            </div>
                        </div>    
                        <div style="clear:both;"></div>
                        <div class="long-desc">
                            You can have Selfie automatically insert itself into posts
                            that <strong>do not</strong> have a Selfie already inserted. This
                            will not modify the post's content.
                        </div>
                        <div class="sf-pricing-row">
                            <table class="sf-pricing-table">
                                <thead>
                                    <tr>
                                        <th>Top of Post?</th>
                                        <th>Middle of Post?</th>
                                        <th>Bottom of Post?</th>
                                        <th>Only on Post Page?</th>
                                        <th>Message to Place</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" ng-model="config.auto_place_top" tip="This will appear after the first paragraph" /></td>
                                        <td><input type="checkbox" ng-model="config.auto_place_middle" tip="This will appear if there are at least three paragraphs in the post" /></td>
                                        <td><input type="checkbox" ng-model="config.auto_place_bottom" tip="This will appear at the very bottom of the post" /></td>
                                        <td><input type="checkbox" ng-model="config.auto_place_single_only" tip="If this is checked, Selfies will not appear on the front page or category pages. Only the post page." /></td>
                                        <td><input ng-model="config.auto_message" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Disable Site-Wide
                            </div>
                            <div class="desc nomargin">
                                This will disable all Selfies, everywhere, if you need it.
                            </div>
                        </div>
                        <div class="control-container" style="padding-top: 10px; padding-left: 30px;">
                            <input ng-model="config.disable_all" type="checkbox" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                <a href="?page=Selfie-Help" style='text-decoration: none;'>When You're Done Saving, Read This</a>
                            </div>
                        </div>
                        <div class="save-container">
                            
                            <input class="button button-primary button-large" ng-disabled="!selfieConfigForm.$valid" ng-click="saveConfig()" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        </form>
        <div class="selfie-loading-box" ng-show="loadingMessage !== null">
            <img src="<?php echo Selfie_Utility::getImageBaseURL() . 'ajax-loader-bar.gif'; ?>" alt="Loading Image"/>
            <span>{{loadingMessage}}</span>
        </div>
      </div>
          

      <div class="right_column">
          <?php Selfie_View::load('admin/global/sidebar') ?>
      </div>
    </div>
    <script>
        jQuery('[tip]').tipsy({title: 'tip'});
    </script>
    <div class="clearfix"></div>
    
    <!-- Facebook Conversion Code for Selfie Signups -->
    <script type="text/javascript">
    var fb_param = {};
    fb_param.pixel_id = '6013410856817';
    fb_param.value = '0.01';
    fb_param.currency = 'USD';
    (function(){
    var fpw = document.createElement('script');
    fpw.async = true;
    fpw.src = '//connect.facebook.net/en_US/fp.js';
    var ref = document.getElementsByTagName('script')[0];
    ref.parentNode.insertBefore(fpw, ref);
    })();
    </script>
    <noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6013410856817&amp;value=0.01&amp;currency=USD" /></noscript>