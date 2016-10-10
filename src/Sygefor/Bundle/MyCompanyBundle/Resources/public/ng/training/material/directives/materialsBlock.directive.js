/**
 * Include a inscription table block for a given session
 * Usage : <div materials-block="session" entity-type="'session'"></div>
 */
sygeforApp.directive('materialsBlock', ['$dialog', '$window', function($dialog, $window) {
    return {
        restrict: 'EA',
        scope: {
            entity: '=materialsBlock',
            entityType: '='
        },
        link: function(scope, attrs) {
            // custum empty message
            scope.emptyMsg = attrs.emptyMsg ?  attrs.emptyMsg : "Il n'y a aucun support disponible.";
        },
        controller: function($scope, $dialog, $timeout)
        {
            /**
             * Manage material modal
             */
            $scope.manageMaterials = function () {
                $dialog.open('material.manage', {entity: $scope.entity, entityType: $scope.entityType});
            };

            /**
             * Download file
             * @param file
             */
            $scope.getMaterial = function (file) {
                $window.location = Routing.generate('material.get', {id: file.id});
            };
        },
        templateUrl: 'mycompanybundle/training/material/directives/materials.block.html'
    }
}]);
