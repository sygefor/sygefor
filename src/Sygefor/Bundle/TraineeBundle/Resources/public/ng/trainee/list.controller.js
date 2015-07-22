/**
 * Core List Controller
 */
sygeforApp.controller('TraineeListController', ['$scope', '$user', '$injector', 'search', 'BaseListController', '$state', '$timeout', '$dialog', 'growl', function($scope, $user, $injector, search, BaseListController, $state, $timeout, $dialog, growl) {
    $injector.invoke(BaseListController, this, {key: 'trainee', $scope: $scope, $search: search});

    // batch operations
    $scope.batchOperations = [{
        icon: 'fa-envelope-o',
        label: 'Envoyer un email',
        execute: function(items, $dialog) {
            return $dialog.open('batch.email', { items: items, targetClass: 'SygeforTraineeBundle:Trainee' })
        }
    },{
        icon: 'fa-download',
        label: 'Exporter',
        subitems: [
            {
                icon: 'fa-file-excel-o',
                label: 'CSV',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.export.csv', { items: items, service: 'trainee' })
                }
            },{
                icon: 'fa-external-link',
                label: 'Publipostage',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.publipost', { items: items, service: 'trainee' })
                }
            }
        ]

    },{
        icon: 'fa-compress',
        label: "Fusionner",
        execute: function (items, $dialog) {
            var item = items[0];
            if (items.length < 11){
                return $dialog.open('trainee.merge', {items: items, traineeToKeep: item}).then(function(id) {
                    $state.go('trainee.detail.view', {id: id}, {reload: true});
                });
            }else{
                growl.addErrorMessage("Vous ne pouvez pas fusionner plus de 10 personnes.");
            }
        }
    }];

    // add operations
    $scope.addOperations = [{
        label: 'Ajouter un stagiaire',
        execute: function () {
            $dialog.open('trainee.create').then(function (result) {
                $state.go('trainee.detail.view', {id: result.trainee.id}, {reload: true});
            });
        },
        available: function () {
            return $user.hasAccessRight('sygefor_trainee.rights.trainee.own.create') || $user.hasAccessRight('sygefor_trainee.rights.trainee.all.create');
        }
    }];

    // facets
    $scope.facets = {
        'organization.name.source' : {
            label: 'URFIST'
        },
        'title' : {
            label: 'Civilité'
        },
        'publicCategory.source' : {
            label: 'Catégorie de public'
        },
        'professionalSituation.source' : {
            label: 'Situation professionnelle'
        },
        'disciplinaryDomain.source' : {
            label: 'Domaine disciplinaire'
        },
        'disciplinary.source' : {
            label: 'Discipline'
        },
        'institution.name.source' : {
            label: 'Établissement',
            size: 10
        },
        'createdAt' : {
            label: 'Inscription',
            type: 'range'
        },
        'publicType.isPaying' : {
            label: 'Payant',
            values: {
                'T': 'Oui',
                'F': 'Non'
            }
        }
    };
}]);
