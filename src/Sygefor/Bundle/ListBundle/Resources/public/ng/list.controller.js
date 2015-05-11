/**
 * List Controller Factory
 * // cf http://stackoverflow.com/questions/19440886/angularjs-injector-invoke-parentcontroller-is-not-defined
 */
sygeforApp.factory('BaseListController', ['$location', '$timeout', '$modal', '$listState', '$stateParams', '$http', '$injector', '$sessionStorage', function ($location, $timeout, $modal, $listState, $stateParams, $http, $injector, $sessionStorage) {
    function BaseListController($scope, $search, key) {
        var timer = null;

        $scope.search = $search;
        $scope.selected = initSelected();

        // views
        $scope.$state = $listState;
        $scope.$stateParams = $stateParams;

        /**
         * Init the selected array to keep selected items in the
         * session storage
         * @returns {*}
         */
        function initSelected() {
            if($sessionStorage.listSelected) {
                for(var k in $sessionStorage.listSelected) {
                    if(key == k) {
                        return $sessionStorage.listSelected[k];
                    }
                    break;
                }
            }
            $sessionStorage.listSelected = {};
            $sessionStorage.listSelected[key] = [];
            return $sessionStorage.listSelected[key];
        }

        /**
         * keywords watcher
         */
        $scope.$watch('search.query.keywords', function(newValue, oldValue){
            if(newValue != oldValue) {
                if(timer !== null) {
                    $timeout.cancel(timer);
                }
                timer = $timeout(function(){
                    $scope._search();
                }, 250);
            }
        });

        /**
         * query page/size watcher
         */
        $scope.$watch('[search.query.page, search.query.size]', function(newValue, oldValue){
            if(!angular.equals(newValue, oldValue)) {
                $scope._search();
            }
        }, true);

        /**
         * query filters/sorts watcher
         */
        $scope.$watch('[search.query.filters, search.query.sorts]', function(newValue, oldValue){
            if(!angular.equals(newValue, oldValue)) {
                if($scope.search.query.page > 1) {
                    $scope.search.query.page = 1;
                } else {
                    $scope._search();
                }
            }
        }, true);

        /**
         * path helper
         */
        $scope.path = function(name, opt_params, absolute) {
            return Routing.generate(name, opt_params, absolute);
        }

        /**
         * Update the current state url to reload the controller with the right query
         */
        $scope._search = function(options) {
            var query = angular.copy($scope.search.query);
            var q = angular.toJson(query);
            var params = angular.extend($stateParams, {q: q});
            $timeout(function() {
                $scope.$state.go($listState.current.name, params, options);
            });
        }

        /**
         * switchSelectItem
         */
        $scope.switchSelect = function(id) {
            var index = $scope.selected.indexOf(id);
            if(index > -1) {
                $scope.selected.splice(index, 1);
            } else {
                $scope.selected.push(id);
            }
        };

        /**
         * isSelected
         */
        $scope.isSelected = function(id) {
            return ($scope.selected.indexOf(id) > -1);
        };

        /**
         * deselectAll
         */
        $scope.deselectAll = function() {
            $scope.selected.splice(0, $scope.selected.length);
        };

        /**
         * selectAll
         */
        $scope.selectAll = function() {
            $scope.search.fetchAll(['_id'])
                .then(function(items, status, headers, config) {
                    for(var i=0; i< items.length; i++) {
                        var id = items[i].id;
                        if($scope.selected.indexOf(id) < 0) {
                            $scope.selected.push(id);
                        }
                    }
                });
        };

        /**
         * Launch a batch operation
         * @return promise
         */
        $scope.batch = function(operation) {
            var promise = $injector.invoke(operation.execute, null, {items: $scope.selected})
            //when promise is resolved, all selected items are deselected.
            promise.then(function (){$scope.deselectAll();});
        };

        /**
         * When detail entity property values are updated, result view item is updated too
         * @param item is updated item
         * @param prop is used for training update
         */
        $scope.updateActiveItem = function(item, prop) {
            angular.forEach($scope.search.result.items, function(result) {
                result = prop ? result[prop] : result;
                if (item.id == result.id) {
                    for (var key in item) {
                        result[key] = item[key];
                    }
                }
            });
        }
    }
    return (BaseListController);
}]);
