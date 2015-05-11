/**
 * BatchMailingController
 */
sygeforApp.controller('BatchMailingController', ['$scope', '$window', '$modalInstance', '$timeout', 'service', 'selected', function($scope, $window, $modalInstance, $timeout, service, selected)
{
    $scope.selected = selected;
    $scope.ok = function () {
        $modalInstance.close();
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);
