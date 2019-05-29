/**
 * Trainer List Controller
 */
sygeforApp.controller('TrainerListController', ['$scope', '$user', '$injector', 'search', 'BaseListController', '$state', '$timeout', '$dialog', function($scope, $user, $injector, search, BaseListController, $state, $timeout, $dialog) {
    $injector.invoke(BaseListController, this, {key: 'trainer', $scope: $scope, $search: search});

    // by default, order by createdAt
    $scope.search.query.sorts = {'lastName.source': 'asc'};

    // facets
    $scope.facets = {
        'organization.name.source' : {
            label: 'Centre'
        },
        'isOrganization' : {
            label: 'Statut',
            values: {
                'T': 'Intervenant interne',
                'F': 'Intervenant extérieur'
            }
        },
        'isArchived' : {
            label: 'Archivé',
            values: {
                'T': 'Oui',
                'F': 'Non'
            }
        }
    };

    // batch operations
    $scope.batchOperations = [{
            icon: 'fa-envelope-o',
            label: 'Envoyer un Email',
            execute: function(items, $dialog) {
                return $dialog.open('batch.email', { items: items, targetClass: "AppBundle\\Entity\\Trainer" })
            }
        },
        {
            icon: 'fa-download',
            label: 'Exporter',
            subitems: [
                {
                    icon: 'fa-file-excel-o',
                    label: 'CSV',
                    execute: function(items, $dialog) {
                        return $dialog.open('batch.export.csv', { items: items, service: 'trainer' })
                    }
                },
                {
                    icon: 'fa-external-link',
                    label: 'Publipostage',
                    execute: function(items, $dialog) {
                        return $dialog.open('batch.publipost', { items: items, service: 'trainer' })
                    }
                }
            ]
        }
    ];

    // add operations
    $scope.addOperations = [{
        label: 'Ajouter un intervenant',
        execute: function () {
            $dialog.open('trainer.create').then(function(data) {
                $state.go('trainer.detail.view', {id: data.trainer.id}, {reload: true});
            })
        },
        available: function () {
            return $user.hasAccessRight('sygefor_core.access_right.trainer.all.create') || $user.hasAccessRight('sygefor_core.access_right.trainer.own.create');
        }
    }];
}]);
