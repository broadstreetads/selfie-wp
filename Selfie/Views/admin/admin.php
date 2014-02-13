<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<script>
    window.selfie_config = <?php echo json_encode($selfie_config); ?>;
    window.categories = <?php echo json_encode($categories); ?>;
    window.tags = <?php echo json_encode($tags); ?>;
</script>
<div id="main" ng-app>
    
      <?php Selfie_View::load('admin/global/header') ?>
    
    <div class="left_column" ng-controller="ConfigCtrl">
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
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Access Token
                                
                                <span class="error <?php if(!$key_valid) echo "visible"; ?>" id="key-invalid">Invalid</span>
                                <span class="success <?php if($key_valid) echo "visible"; ?>" id="key-valid">Valid</span>
                                
                            </div>
                            <div class="desc nomargin">
                                This can be found <a target="_blank" href="http://my.broadstreetads.com/access-token">here</a> when you're logged in to Broadstreet.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input id="api_key" type="text" value="<?php echo $api_key ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Publisher Selection                                
                            </div>
                            <div class="desc nomargin">
                                Which publisher or network does this site fall under?
                            </div>
                        </div>
                        <div class="control-container">
                            <select id="network" type="text">
                                <?php foreach($networks as $network): ?>
                                <option <?php if($network_id == $network->id) echo "selected"; ?> value="<?php echo $network->id ?>"><?php echo htmlentities($network->name) ?></option>
                                <?php endforeach; ?>
                                <?php if(count($networks) == 0): ?>
                                <option value="-1">Enter a valid token above</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                <a href="?page=Selfie-Help">How to Get Started</a>
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input id="save" type="button" value="Save" name="" />
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
                <div class="title">Selfie Configuration</div>
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
                            <input type="text" ng-model="messagePrefix" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Base Selfie Pricing
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="pricing-long-desc">
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
                        <div class="pricing-long-desc">
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
                                    <input class="pricing-config-input" type="number" ng-model="rule.param" /> {{ruleTargetConfigs.post[rule.type].suffix}}
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
                                
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input ng-disabled="!selfieConfigForm.$valid" ng-click="savePricing()" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        </form>
      </div>
          

      <div class="right_column">
          <?php Selfie_View::load('admin/global/sidebar') ?>
      </div>
    </div>
      <div class="clearfix"></div>
      <!-- <img src="http://report.Broadstreet2.com/checkin/?s=<?php echo $service_tag.'&'.time(); ?>" alt="" /> -->