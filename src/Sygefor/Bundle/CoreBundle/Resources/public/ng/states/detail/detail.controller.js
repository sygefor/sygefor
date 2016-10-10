/**
 * ListDetailController
 */
sygeforApp.controller('ListDetailController', ['$scope', '$http', '$listState', '$state', function($scope, $http, $listState, $state) {
    $scope.resultTemplateUrl = $listState.current.data.resultTemplateUrl;
    $scope.$state = $state;

    // user $state.params instead of $stateParams to get the child state params
    var params = $listState.params;

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
