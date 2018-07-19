/**
 * ManageMaterialsController
 */
sygeforApp.controller('MaterialController', ['$scope', '$timeout', '$http', '$window', '$dialog', '$modalInstance', '$dialogParams', 'growl', function($scope, $timeout, $http, $window, $dialog, $modalInstance, $dialogParams, growl)
{
    $scope.dialog = $modalInstance;
    $scope.dialog.params = angular.copy($dialogParams);
    $scope.entityType = $dialogParams.entityType;
    $scope.entity = $dialogParams.entity;
    $scope.isPublic = $dialogParams.isPublic;

    /**
     * Get response from sf-file-upload
     */
    $scope.getUploadedFile = function(jsFile, data) {
        if (data.error) {
            growl.addErrorMessage(data.error);
        }
        else {
            $scope.pushMaterial(data.materials);
        }
    };

    /**
     * Add a linked material
     */
    $scope.addLink = function () {
        $dialog.open('material.add.link', {entity_id: $scope.entity.id, entity_type: $scope.entityType, material_type: "link", isPublic: $scope.isPublic}).
        then(function (data) {
            var materials = [];
            materials.push(data.material);
            $scope.pushMaterial(materials);
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
    $scope.remove = function(material) {
        return $dialog.open('material.remove', {entity_id: $scope.entity.id, entity_type: $scope.entityType, material: material}).then(function() {
            return $scope.removeMaterial(material);
        });
    };

    /**
     * Get private or public materials
     */
    $scope.getMaterials = function() {
        return ($scope.isPublic == true ?
            $scope.entity.publicMaterials :
            $scope.entity.privateMaterials);
    };

    /**
     * Push material to private or public materials
     */
    $scope.pushMaterial = function(materials) {
        for (var key in materials) {
            $scope.isPublic == true ?
                $scope.entity.publicMaterials.push(materials[key]) :
                $scope.entity.privateMaterials.push(materials[key]);
        }
    };

    /**
     * Remove material from private or public materials
     */
    $scope.removeMaterial = function(material) {
        var materials = $scope.getMaterials();
        var index = function(materials, material) {
            for (var i = 0; materials; i++) {
                if (typeof materials[i] !== "undefined" && materials[i].id === material.id) {
                    return i;
                }
            }

            return -1;
        }(materials, material);

        if (index > -1) {
            $scope.isPublic == true ?
                $scope.entity.publicMaterials.splice(index, 1):
                $scope.entity.privateMaterials.splice(index, 1);
        }
    };

    /**
     * Refresh variable materials and materialLength for template
     */
    $timeout(function() {
        $scope.materials = $scope.getMaterials();
    });
}]);