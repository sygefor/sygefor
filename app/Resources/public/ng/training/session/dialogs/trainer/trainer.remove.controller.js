/**
 * Trainer Add Controller
 */
sygeforApp.controller('TrainerRemoveController', ['$scope', '$modalInstance', '$dialog', '$dialogParams', '$state', '$http', 'growl', function($scope, $modalInstance, $dialog, $dialogParams, $state, $http, growl) {
    $scope.dialog = $modalInstance;
    $scope.dialog.params = $dialogParams;
    $scope.session = $dialogParams.session;
    $scope.onSuccess = function(data) {
        growl.addSuccessMessage("L'intervenant a bien été retiré de la session.");
        $scope.dialog.close(data);
    };

    /**
     * ensures the form was correctly filed (sets an error message otherwise), then asks for server-sid
     */
    $scope.ok = function () {
        var url = Routing.generate('participation.remove', {session: $scope.dialog.params.session.id, participation: $scope.dialog.params.participation.id});
        $http({ method: 'POST', url: url}).success(function (data) {
            growl.addSuccessMessage("L'intervenant a bien été retiré de la session.");
            $scope.dialog.close(data);
        });
    };
}]);

