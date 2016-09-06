/**
 * TrainingBundle
 */
sygeforApp.config(["$dialogProvider", "$widgetProvider", function($dialogProvider, $widgetProvider) {
    /**
     * DIALOGS
     */
    $dialogProvider.dialog('email.view', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, email) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.email = email;
        },
        templateUrl: 'corebundle/directives/entity-emails/email.html',
        size: 'lg',
        resolve:{
            email: function ($http, $dialogParams){
                return $http.get(Routing.generate('email.view', {id: $dialogParams.id })).then(function(response) {
                    return response.data.email;
                });
            }
        }
    });
}]);
