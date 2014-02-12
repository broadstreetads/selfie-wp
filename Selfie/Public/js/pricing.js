/**
 * Globals schmobels. I haven't time to do things fancy. 
 * I've got an MVP to write.
 * @returns {undefined}
 */
function PricingCtrl($scope, $http) {
    $scope.pricing = window.selfie_pricing;
    
    $scope.ruleTemplate = {
        target: 'post',
        type: 'older_than_days',
        param: 7,
        price_day: '10.00',
        price_week: '20.00',
        price_month: '50.00',
        price_year: '100.00',
    };    
    
    $scope.ruleTargets = [
        'post'
    ];
    
    $scope.categories = window.categories;    
    $scope.tags = window.tags;
    
    $scope.ruleTargetConfigs = {
        post: {
            older_than_days: {type: 'older_than_days', name: 'is older than', suffix: 'days', optionType: 'text'},
            has_category: {type: 'has_category', name: 'has this category', optionType: 'list', options: $scope.categories},
            has_tag: {type: 'has_tag', name: 'has this tag', optionType: 'list', options: $scope.tags}
        }
    }
    
    $scope.addRule = function() {
        var rule = angular.copy($scope.ruleTemplate);    
        $scope.pricing.rules.push(rule);
        console.log($scope.pricing);
    }
    
    $scope.removeRule = function(idx) {
        if(confirm('Just double-checking. Are you sure?'))
            $scope.pricing.rules.splice(idx, 1);
    }
    
    
    $scope.moveRuleUp = function(idx) {
        var out = $scope.pricing.rules.splice(idx, 1);
        $scope.pricing.rules.splice(idx - 1, 0, out[0]);
    }
    
    $scope.moveRuleDown = function(idx) {
        var out = $scope.pricing.rules.splice(idx, 1);
        $scope.pricing.rules.splice(idx + 1, 0, out[0]);        
    }
    
    $scope.savePricing = function() {
        var params = {action: 'save_pricing', pricing: $scope.pricing};
        $http.post(window.ajaxurl + '?action=save_pricing', $scope.pricing)
            .success(function() {
                    
            }).error(function() {
                
            });                    
    }
    
    $scope.checkCurrency = function(key, rule_idx) {
        if(rule_idx || rule_idx === 0) {
            return true == $scope.pricing.rules[rule_idx][key].match(/^\d+\.?\d{1,2}?$/);
        } else {
            return true == $scope.pricing[key].match(/^\d+\.?\d{1,2}?$/);
        }
    }
    
    
    $scope.init = function() {
        if($scope.pricing.rules.length === 0) {
            $scope.addRule();
        }
    }    
    //$scope.init();
}