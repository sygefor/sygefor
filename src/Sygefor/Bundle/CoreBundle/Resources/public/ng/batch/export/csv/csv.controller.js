/**
 * BatchExportCsvController
 */
sygeforApp.controller('BatchExportCsvController', ['$scope', '$window', '$modalInstance', '$timeout', '$dialogParams', '$http',function($scope, $window, $modalInstance, $timeout, $dialogParams, $http)
{
    var service = 'sygefor_core.batch.csv.' + $dialogParams.service;
    $scope.items = $dialogParams.items;
    $scope.dialog = $modalInstance;

    $scope.options = {
        delimiter: ';'
    };

    $scope.export = function () {
        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: service});
        var params = {
            options: $scope.options,
            ids: $scope.items.join(",")
        };

        $http(
            {method: 'POST',
                url: url,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append('options', angular.toJson(data.options));
                    formData.append("ids", angular.toJson(data.ids));

                    return formData;
                },
                headers: {'Content-Type': undefined},
                data: params
            }).success(
            function (data) { //response should contain the file url
                if (data.fileUrl) {

                    var url = Routing.generate('sygefor_core.batch_operation.get_file', {service: service, file: data.fileUrl });
                    // changin location :
                    $window.location = url;
                }
            });

        $timeout(function() { $modalInstance.close(); }, 500);
    };
}]);
