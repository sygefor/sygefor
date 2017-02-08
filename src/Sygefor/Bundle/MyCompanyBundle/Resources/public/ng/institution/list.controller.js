/**
 * Core List Controller
 */
sygeforApp.controller('InstitutionListController', ['$scope', '$user', '$injector', 'search', 'BaseListController', '$state', '$timeout', '$dialog', function($scope, $user, $injector, search, BaseListController, $state, $timeout, $dialog) {
    $injector.invoke(BaseListController, this, {key: 'institution', $scope: $scope, $search: search});

    // batch operations
    $scope.batchOperations = [
        {
            icon: 'fa-download',
            label: 'Exporter',
            subitems: [
                {
                    icon: 'fa-file-excel-o',
                    label: 'CSV',
                    execute: function(items, $dialog) {
                        return $dialog.open('batch.export.csv', { items: items, service: 'institution' })
                    }
                }
            ]

        }
    ];

    // add operations
    $scope.addOperations = [{
        label: 'Ajouter un établissement',
        execute: function () {
            $dialog.open('institution.create').then(function (result) {
                $state.go('institution.detail.view', {id: result.institution.id}, {reload: true});
            });
        },
        available: function () {
            return $user.hasAccessRight('sygefor_institution.rights.institution.all.create') || $user.hasAccessRight('sygefor_institution.rights.institution.own.create');
        }
    }];

    // facets
    $scope.facets = {
        'organization.name.source': {
            label: 'Centre'
        },
        'city.source' : {
            label: 'Ville'
        }
    };
}]);
