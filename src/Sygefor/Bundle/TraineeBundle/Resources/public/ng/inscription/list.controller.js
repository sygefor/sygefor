/**
 * Core List Controller
 */
sygeforApp.controller('InscriptionListController', ['$scope', '$user', '$injector', 'BaseListController', 'search', '$state', '$timeout', '$dialog', 'session', 'inscriptionStatusList', 'presenceStatusList', function($scope, $user, $injector, BaseListController, search, $state, $timeout, $dialog, session, inscriptionStatusList, presenceStatusList) {
    $injector.invoke(BaseListController, this, {key: 'inscription', $scope: $scope, $search: search});

    /**
     * Batch operations
     */
    $scope.batchOperations = [{
        icon: 'fa-external-link',
        label: "Modifier le statut d'inscription",
        available: function () {
            return $user.hasAccessRight('sygefor_trainee.rights.inscription.own.update') || $user.hasAccessRight('sygefor_trainee.rights.inscription.all.update');
        },
        subitems: function (){
            var items = [];
            angular.forEach (inscriptionStatusList, function (item) {
                items.push ({
                    label: item.name,
                    execute: function (items, $dialog) {
                        return $dialog
                            .open('inscription.changeStatus', {items: items, inscriptionStatus: item, session: ( session )? session.id : 0})
                            .then(function() {
                                // on success, reload the search page
                                search.search();
                            });
                    }
                });
            });
            return items;
        }()
    },{
        icon: 'fa-external-link',
        label: "Modifier le statut de présence",
        available: function () {
            return $user.hasAccessRight('sygefor_trainee.rights.inscription.own.update') || $user.hasAccessRight('sygefor_trainee.rights.inscription.all.update');
        },
        subitems: function (){
            var items = [];
            angular.forEach (presenceStatusList, function (item) {
                items.push ({
                    label: item.name,
                    execute: function (items, $dialog) {
                        return $dialog
                            .open('inscription.changeStatus', {items: items, presenceStatus: item, session: ( session )? session.id : 0})
                            .then(function() {
                                // on success, reload the search page
                                search.search();
                            });
                    }
                });
            });
            return items;
        }()
    },{
        icon: 'fa-envelope-o',
        label: 'Envoyer un Email',
        execute: function(items, $dialog) {
            return $dialog.open('batch.email', { items: items, targetClass: 'SygeforTraineeBundle:Inscription' })
        }
    },{
        icon: 'fa-file-pdf-o',
        label: 'Attestation de présence',
        execute: function(items, $dialog) {
            return $dialog.open('batch.export.pdf', { items: items, service: 'inscription.attestation' });
        }
    },{
        icon: 'fa-download',
        label: 'Exporter',
        subitems: [
            {
                icon: 'fa-file-excel-o',
                label: 'CSV',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.export.csv', { items: items, service: 'inscription' })
                }
            },{
                icon: 'fa-external-link',
                label: 'Publipostage',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.publipost', { items: items, service: 'inscription' })
                }
            }
        ]

    }];

    /**
     * Add operations
     */
    $scope.addOperations = [{
        label: 'Ajouter une inscription',
        execute: function () {
            $dialog.open('inscription.create',{session: session}).then(function(data) {
                $scope.search.search();
            });
        },
        available: function () {
            return (!!session && session._accessRights.edit);
        }
    }];

    /**
     * Facets
     */
    $scope.facets = {
        'session.training.organization.name.source' : {
            label: 'URFIST'
        },
        'inscriptionStatus.name.source' : {
            label: 'Statut inscription'
        },
        'presenceStatus.name.source' : {
            label: 'Statut présence'
        },
        'institution.name.source' : {
            label: 'Établissement',
            size: 10
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
        'trainee.fullName.source' : {
            label: 'Stagiaire'
        },
        'session.training.name.source' : {
            label: 'Stage'
        },
        'range:session.dateBegin' : {
            label: 'Date de session',
            type: 'range'
        },
        'session.year' : {
            label: 'Année de session'
        },
        'session.semester' : {
            label: 'Semestre de session'
        },
        'session.training.typeLabel.source' : {
            label: 'Type'
        },
        'session.training.name.source' : {
            label: 'Formation'
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
