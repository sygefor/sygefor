/**
 * Created by maxime on 12/06/14.
 */
/**
 * BatchMailingController
 */
sygeforApp.controller('SessionRegistrationChange', ['$scope', '$http', '$window','$modalInstance', '$dialogParams', function($scope, $http, $window, $modalInstance, $dialogParams)
{
    $scope.dialog = $modalInstance;
    $scope.items = $dialogParams.items;
    $scope.registration = $dialogParams.registration;

    $scope.registrationOpts = [
        {id: 0, label: 'Désactivées'},
        {id: 1, label: 'Fermées'},
        {id: 2, label: 'Privées'},
        {id: 3, label: 'Publiques'}
    ];

    /**
     *
     */
    $scope.ok = function () {
        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: 'sygefor_training.batch.session_registration_change'});
        var data = {
            options: {
                registration: $scope.registration
            },
            ids: $scope.items.join(",")
        };
        $http({method: 'POST',
                url: url,
                data: data
            }).success(
            function () { //no response expected...
                $scope.dialog.close();
            }
        );
    };

    /**
     *
     */
    $scope.cancel = function () {
        $modalInstance.dismiss();
    }

}]);
