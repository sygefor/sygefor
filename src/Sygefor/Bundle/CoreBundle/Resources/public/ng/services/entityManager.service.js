/**
 * SF entity service
 */
sygeforApp.factory('$entityManager', ['$http', '$q', function($http, $q) {
    /**
     * @param type Entity type (Bundle:Entity)
     */
    return function (type) {
        var url = Routing.generate('core.entity');
        return {
            /**
             * Find an entity by id
             * @param id
             */
            find: function(id) {
                var deferred = $q.defer();
                $http({
                    method: 'POST',
                    url: url,
                    data: {'class': type, 'id': id},
                    cache: true
                }).success(function(response) {
                    deferred.resolve(response);
                 }).error(deferred.reject);
                return deferred.promise;
            }
        }
    }
}]);
