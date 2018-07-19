/**
 * Application run
 */
sygeforApp.run(['$rootScope', function($rootScope) {

    /**
     * This helper could be used in ng-class to colorize inscription status label
     *
     * @param statusId
     */
    $rootScope.inscriptionStatusClass = function(statusId, prefix) {
        if(!prefix) {
            prefix = 'label';
        }
        var prefix = prefix ? prefix + '-' : '';
        switch(statusId) {
            case 0:
                return prefix + 'default';
            case 1:
                return prefix + 'warning';
            case 2:
                return prefix + 'success';
            case 3:
                return prefix + 'danger';
        }
    }

    /**
     * This helper could be used in ng-class to colorize presence status label
     *
     * @param statusId
     */
    $rootScope.presenceStatusClass = function(statusId, prefix) {
        if(!prefix) {
            prefix = 'label';
        }
        var prefix = prefix ? prefix + '-' : '';
        switch(statusId) {
            case 0:
                return prefix + 'danger';
            case 1:
                return prefix + 'success';
        }
    }

}]);
