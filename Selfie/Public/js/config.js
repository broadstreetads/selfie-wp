/**
 * Globals schmobels. I haven't time to do things fancy. 
 * I've got an MVP to write.
 * @returns {undefined}
 */
function ConfigCtrl($scope, $http) {
    $scope.config = window.selfie_config;
    $scope.network= window.network_config;
    $scope.showAdvancedSignup = false;
    
    $scope.ruleTemplate = {
        target: 'post',
        type: 'younger_than_days',
        param: 1,
        price_day: '10.00',
        price_week: '20.00',
        price_month: '50.00',
        price_year: '100.00'
    };    
    
    $scope.priceRegex = /^(\d+(\.\d?\d?)?)?$/;
    
    $scope.ruleTargets = [
        'post'
    ];

    $scope.categories = window.categories;    
    $scope.tags = window.tags;
    
    $scope.ruleTargetConfigs = {
        post: {
            older_than_days: {type: 'older_than_days', name: 'is older than', suffix: 'days', optionType: 'text'},
            younger_than_days: {type: 'younger_than_days', name: 'is younger than', suffix: 'days', optionType: 'text'},
            has_category: {type: 'has_category', name: 'has the category', optionType: 'list', options: $scope.categories},
            has_tag: {type: 'has_tag', name: 'has the tag', optionType: 'list', options: $scope.tags}
        }
    }
    
    $scope.registerUser = function() {
        var params = {action: 'register', email: $scope.config.admin_email};
        $http.post(window.ajaxurl + '?action=register', params)
            .success(function(response) {
                angular.extend($scope.network, response.network);
           }).error(function() {
                
           });
    }
    
    $scope.updateUser = function() {
        var params = {action: 'save_settings', network: $scope.network};
        $http.post(window.ajaxurl + '?action=save_settings', params)
            .success(function(response) {
                angular.extend($scope.network, response.network);
           }).error(function(response) {
                angular.extend($scope.network, response.network);
           });
    }
    
    $scope.createNetwork = function() {
        var params = {action: 'create_network', network: $scope.network};
        $http.post(window.ajaxurl + '?action=create_network', params)
            .success(function(response) {
                angular.extend($scope.network, response.network);
           }).error(function(response) {
                angular.extend($scope.network, response.network);
           });
    }
    
    /**
     * TODO: Make this work
     * @returns {undefined}
     */
    $scope.saveNetworkConfig = function() {
        var params = {action: 'register', email: $scope.config.admin_email};
        $http.post(window.ajaxurl + '?action=register', params)
            .success(function(response) {
                angular.extend($scope.network, response.network);
           }).error(function() {
                
           });
    }
    
    $scope.addRule = function() {
        var rule = angular.copy($scope.ruleTemplate);    
        $scope.config.rules.push(rule);
    }
    
    $scope.removeRule = function(idx) {
        if(confirm('Just double-checking. Are you sure?'))
            $scope.config.rules.splice(idx, 1);
    }
    
    
    $scope.moveRuleUp = function(idx) {
        var out = $scope.config.rules.splice(idx, 1);
        $scope.config.rules.splice(idx - 1, 0, out[0]);
    }
    
    $scope.moveRuleDown = function(idx) {
        var out = $scope.config.rules.splice(idx, 1);
        $scope.config.rules.splice(idx + 1, 0, out[0]);        
    }
    
    $scope.saveConfig = function() {
        var params = {action: 'save_config', pricing: $scope.config};
        $http.post(window.ajaxurl + '?action=save_config', $scope.config)
            .success(function() {
                    
            }).error(function() {
                
            });                    
    }
    
    $scope.checkCurrency = function(key, rule_idx) {
        if(rule_idx || rule_idx === 0) {
            return true == $scope.config.rules[rule_idx][key].match(/^\d+\.?\d{1,2}?$/);
        } else {
            return true == $scope.config[key].match(/^\d+\.?\d{1,2}?$/);
        }
    }
    
    $scope.init = function() {
        
    }    
    
    $scope.init();
}