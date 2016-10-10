/**
 * BatchExportCsvController
 */
sygeforApp.controller('BatchExportPdfController', ['$scope', '$window', '$modalInstance', '$timeout', '$dialogParams', function($scope, $window, $modalInstance, $timeout, $dialogParams)
{
    var service = 'sygefor_core.batch.pdf.' + $dialogParams.service;
    $scope.items = $dialogParams.items;
    $scope.dialog = $modalInstance;
    $scope.options = {};

    $scope.download = function () {
        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: service});
        var params = $.param({
            'options': $scope.options,
            'ids': $scope.items.join(",")
        });
        $window.location = url + '?' + params;
        $timeout(function() { $modalInstance.close(); }, 500);
    };

    // if the direct option was passed, close the modal and launch the download
    if($dialogParams.direct) {
        $scope.download();
    }
}]);
