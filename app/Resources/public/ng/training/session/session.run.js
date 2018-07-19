/**
 * Application run
 */
sygeforApp.run(['$rootScope', function($rootScope) {

    /**
     * This helper could be used in ng-class to colorize inscription status label
     *
     * @param statusId
     */
    $rootScope.sessionInscriptionStatsClass = function(count, max, prefix) {
        if(!prefix) {
            prefix = 'label';
        }
        prefix = prefix ? prefix + '-' : '';

        if(count > max) {
            return prefix + 'danger';
        } else if(count == max || count == 0) {
            return prefix + 'default';
        } else if(count + 2 >= max) {
            return prefix + 'warning';
        } else {
            return prefix + 'success';
        }
    }

    /**
     * Get the count of accepted inscriptions from item stats
     * @returns {string}
     * @deprecated
     */
    /*$rootScope.sessionInscriptionStatsAcceptedCount = function(stats) {
        var count = 0;
        angular.forEach(stats, function (stat){
            switch(stat.status){
                case 2:
                    count += stat.count;
            }
        });
        return count;
    }*/
}]);
