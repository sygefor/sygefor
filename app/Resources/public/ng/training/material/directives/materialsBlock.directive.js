/**
 * Include a inscription table block for a given session
 * Usage : <div materials-block="session" entity-type="'session'" is-public="0|1"></div>
 */
sygeforApp.directive('materialsBlock', ['$dialog', '$window', function($dialog, $window) {
    return {
        restrict: 'EA',
        scope: {
            entity: '=materialsBlock',
            entityType: '=',
            isPublic: '='
        },
        link: function(scope, attrs) {
            scope.emptyMsg = attrs.emptyMsg ?  attrs.emptyMsg : "Il n'y a aucun document disponible.";
        },
        controller: function($scope, $dialog) {
            $scope.materials = $scope.isPublic == true ? $scope.entity.publicMaterials : $scope.entity.privateMaterials;

            $scope.manage = function () {
                $dialog.open('material.manage', {entity: $scope.entity, entityType: $scope.entityType, isPublic: $scope.isPublic});
            };

            $scope.getMaterial = function (file) {
                $window.location = Routing.generate('material.get', {id: file.id});
            };
        },
        templateUrl: 'training/material/directives/materials.block.html'
    }
}]);
