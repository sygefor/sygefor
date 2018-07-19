/**
 * Common elements of training and session elements
 */
sygeforApp.config(["$dialogProvider", function($dialogProvider) {
    /**
     * Manage material (general dialog)
     */
    $dialogProvider.dialog('material.manage', /* @ngInject */ {
        controller: 'MaterialController',
        templateUrl: 'training/material/dialogs/material/manage.html'
    });

    /**
     * Add link material dialog
     */
    $dialogProvider.dialog('material.add.link', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams, form) {
            $scope.dialog = $modalInstance;
            $scope.form = form;
            $scope.dialog.params = angular.copy($dialogParams);

            $scope.onSuccess = function(data) {
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('material.add', {
                    entity_id: $dialogParams.entity_id,
                    entity_type: $dialogParams.entityType,
                    material_type: $dialogParams.material_type,
                    isPublic: $dialogParams.isPublic
                })).then(function(response) {
                    return response.data.form;
                });
            }
        },
        templateUrl: 'training/material/dialogs/material/add-link.html'
    });

    /**
     * Remove material dialog
     */
    $dialogProvider.dialog('material.remove', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams, $http) {
            $scope.dialog = angular.copy($modalInstance);
            $scope.dialog.params = $dialogParams;

            $scope.ok = function() {
                var url = Routing.generate('material.remove', {id: $dialogParams.material.id});
                $http.post(url).then(function (data) {
                    $scope.dialog.close(data);
                });
            }
        },
        templateUrl: 'training/material/dialogs/material/remove.html'
    });
}]);
