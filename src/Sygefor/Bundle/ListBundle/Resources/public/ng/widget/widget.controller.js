/**
 * WidgetListController
 */
sygeforApp.controller('WidgetListController', ['$scope', '$searchFactory', '$timeout', '$listState', '$dialog', 'options', function($scope, $searchFactory, $timeout, $listState, $dialog, options) {

    // create search service
    $scope.search = new $searchFactory(options.route);
    $scope.$dialog = $dialog;

    // load query infos
    $scope.search.query.size = options.size;
    $scope.search.query.sorts = options.sorts;
    $scope.search.query.filters = options.filters ? options.filters : {};

    $scope.$watch("search.result.total", function(total) {
        options.subtitle = (total > 0 ? ' (' + total + ')' : '');
    });

    /**
     * Refresh the widget
     * @private
     */
    $scope.refresh = function() {
        $scope.loading = true;
        $scope.search.search().then(function(result) {
            $scope.items = result.items;
            $scope.loading = false;
        });
    }

    /**
     * Open the list
     * @private
     */
    if(typeof $scope.options.open == "undefined") {
        $scope.open = function() {
            // copy the query
            var q = angular.copy($scope.search.query);
            // remove the size param
            delete q.size;
            // go the the configured state
            $listState.go(options.state, {q: angular.toJson(q)});
        }
    } else if($scope.options.open) {
        $scope.open = $scope.options.open;
    }

    /**
     * Configure the widget
     * @private
     * @todo todo
     */
//    $scope.configure = function() {
//
//    }

    /**
     * query page/size watcher
     */
    $scope.$watch('[search.query.page, search.query.size]', function(newValue, oldValue){
        if(!angular.equals(newValue, oldValue)) {
            $scope.refresh();
        }
    }, true);

    /**
     * init search
     */
    $timeout($scope.refresh);
}]);
