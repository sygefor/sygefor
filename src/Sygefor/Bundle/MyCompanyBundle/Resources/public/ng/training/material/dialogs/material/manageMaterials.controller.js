/**
 * ManageMaterialsController
 */
sygeforApp.controller('ManageMaterialsController', ['$scope', '$http', '$window', '$dialog', '$modalInstance', '$dialogParams', 'growl', function($scope, $http, $window, $dialog, $modalInstance, $dialogParams, growl)
{
    $scope.dialog = $modalInstance;
    $scope.dialog.params = angular.copy($dialogParams);
    $scope.entityType = $dialogParams.entityType;
    $scope.entity = $dialogParams.entity;

    /**
     * Get response from sf-file-upload
     */
    $scope.getUploadedFile = function(jsFile, data) {
        if (data.error) {
            growl.addErrorMessage(data.error);
        }
        else {
            $scope.entity.materials.push(data.material);
        }
    };

    /**
     * Add a linked material
     */
    $scope.addLinkMaterial = function () {
        $dialog.open('material.linkmaterial.add', {entity_id: $scope.entity.id, type_entity: $scope.entityType, material_type: "link"}).
        then(function (data) {
            $scope.entity.materials.push(data.material);
        });
    };

    /**
     * function called to download file
     * @param file
     */
    $scope.getFile = function (file) {
        $window.location = Routing.generate('material.get', {id: file.id});
    };

    /**
     * calls remove material modal and updates material list
     * @param material
     */
    $scope.removeMaterial = function(material) {
        return $dialog.open('material.remove', {material: material}).then(function() {
            for (var i = 0 ; $scope.entity.materials ; i++) {
                if ($scope.entity.materials[i].id === material.id) {
                    $scope.entity.materials.splice(i, 1);
                    break;
                }
            }
        });
    };
}]);