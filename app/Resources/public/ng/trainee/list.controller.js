/**
 * Core List Controller
 */
sygeforApp.controller('TraineeListController', ['$scope', '$user', '$injector', 'search', 'BaseListController', '$state', '$timeout', '$dialog', 'growl', function($scope, $user, $injector, search, BaseListController, $state, $timeout, $dialog, growl) {
    $injector.invoke(BaseListController, this, {key: 'trainee', $scope: $scope, $search: search});

    // batch operations
    $scope.batchOperations = [
        {
        icon: 'fa-envelope-o',
        label: 'Envoyer un email',
        execute: function(items, $dialog) {
            return $dialog.open('batch.email', { items: items, targetClass: "AppBundle\\Entity\\Trainee\\Trainee" })
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
                        return $dialog.open('batch.export.csv', { items: items, service: 'trainee' })
                    }
                },
                {
                    icon: 'fa-external-link',
                    label: 'Publipostage',
                    execute: function(items, $dialog) {
                        return $dialog.open('batch.publipost', { items: items, service: 'trainee' })
                    }
                }
            ]
        }
    ];

    // add operations
    $scope.addOperations = [{
        label: 'Ajouter un stagiaire',
        execute: function () {
            $dialog.open('trainee.create').then(function (result) {
                $state.go('trainee.detail.view', {id: result.trainee.id}, {reload: true});
            });
        },
        available: function () {
            return $user.hasAccessRight('sygefor_core.access_right.trainee.own.create') || $user.hasAccessRight('sygefor_core.access_right.trainee.all.create');
        }
    }];

    // facets
    $scope.facets = {
        'organization.name.source' : {
            label: 'Centre'
        },
        'title' : {
            label: 'Civilité'
        },
        'institution.code.source' : {
            label: 'Code unité',
            size: 999
        },
        'createdAt' : {
            label: 'Inscription',
            type: 'range'
        },
        'publicType.source': {
            label: 'Catégorie de public'
        },
        'employmentContractType.source': {
            label: 'Nature de personnel'
        },
        'isActive': {
            label: 'Validé',
            values: {
                'T': 'Oui',
                'F': 'Non'
            }
        }
    };
}]);
