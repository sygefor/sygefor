/**
 * BatchConvertTypeController
 */
sygeforApp.controller('BatchConvertTypeController', ['$scope', '$http', '$window','$modalInstance', '$timeout', '$dialogParams', 'config', '$state', 'growl',
    function($scope, $http, $window, $modalInstance, $timeout, $dialogParams, config, $state, growl)
{
    var service = 'sygefor_core.batch.convert_type.' + $dialogParams.service;
    $scope.dialog = $modalInstance;
    $scope.items = $dialogParams.items;
    $scope.type = $dialogParams.type;

    /**
     * Return training type label
     * @returns {*}
     */
    $scope.getTypeLabel = function()
    {
        switch ($scope.type) {
            case 'internship':
                return 'stage';
            case 'long_training':
                return 'formation longue';
            case 'meeting':
                return 'recontre scientifique';
            default:
                return $scope.type;
        }

        return $scope.type;
    };

    /**
     * ensures the form was correctly filed (sets an error message otherwise), then asks for server-side file generation
     * if generation is performed without errors, the file is asked for download
     */
    $scope.ok = function () {
        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: service});
        var data = {
            ids: $scope.items.join(","),
            options: [{type: $scope.type}]
        };

        $http(
            {
                method: 'POST',
                url: url,
                data: data
            }).success(
            function (data) { //response should contain the file url
                $modalInstance.close(data);
                growl.addSuccessMessage("Les formations ont bien été converties vers le type " + $scope.getTypeLabel());
                $state.reload({force: true});
            });
    };
}]);
