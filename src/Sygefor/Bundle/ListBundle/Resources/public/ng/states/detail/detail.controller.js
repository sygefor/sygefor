/**
 * ListDetailController
 */
sygeforApp.controller('ListDetailController', ['$scope', '$http', '$listState', '$state', function($scope, $http, $listState, $state) {
    $scope.resultTemplateUrl = $listState.current.data.resultTemplateUrl;
    $scope.$state = $state;

    // user $state.params instead of $stateParams to get the child state params
    var params = $listState.params;


    /**
     * previous item
     * @todo : pas concluant
     */
//    $scope.previous = function() {
//        var current = $state.params.id;
//        var state = $listState.current.name.replace(/\.view$/g, "");
//        for(var i=0; i<$scope.search.result.items.length; i++) {
//            var item = $scope.search.result.items[i];
//            if(item.id == current) {
//                var previous = $scope.search.result.items[i-1];
//                if(previous) {
//                    $listState.go(state + '.view', {id: previous.id});
//                } else if ($scope.search.query.page > 0) {
//                    var q = angular.copy($scope.search.query);
//                    q.page--;
//                    $listState.go(state, {q: angular.toJson(q)});
//                }
//            }
//        }
//    }

    /**
     * next item
     * @todo : pas concluant
     */
//    $scope.next = function() {
//        var current = $state.params.id;
//        var state = $listState.current.name.replace(/\.view$/g, "");
//        var pageMax = Math.ceil($scope.search.result.total/$scope.search.query.size);
//        for(var i=0; i<$scope.search.result.items.length; i++) {
//            var item = $scope.search.result.items[i];
//            if(item.id == current) {
//                var next = $scope.search.result.items[i+1];
//                if(next) {
//                    $listState.go(state + '.view', {id: next.id});
//                } else if ($scope.search.query.page < pageMax) {
//                    var q = angular.copy($scope.search.query);
//                    q.page++;
//                    $listState.go(state, {q: angular.toJson(q)});
//                }
//            }
//        }
//    }

    /**
     * Watch items
     */
    $scope.$watch('search.result.items', function(items) {
        if(items.length > 0 && !params.id) {
            var state = $listState.current.name.replace(/\.view$/g, "") + '.view';
            $listState.go(state, {id: items[0].id});
        }
    });
}]);
