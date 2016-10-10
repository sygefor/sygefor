/**
 * Core List Controller
 */
sygeforApp.controller('InscriptionListController', ['$scope', '$user', '$injector', 'BaseListController', 'search', '$state', '$timeout', '$dialog', 'session', 'inscriptionStatusList', 'presenceStatusList', function($scope, $user, $injector, BaseListController, search, $state, $timeout, $dialog, session, inscriptionStatusList, presenceStatusList) {
    $injector.invoke(BaseListController, this, {key: 'inscription', $scope: $scope, $search: search});

    /**
     * Batch operations
     */
    $scope.batchOperations = [
        {
            icon: 'fa-external-link',
            label: "Modifier le statut d'inscription",
            available: function () {
                return $user.hasAccessRight('sygefor_inscription.rights.inscription.own.update') || $user.hasAccessRight('sygefor_inscription.rights.inscription.all.update');
            },
            subitems: function () {
                var items = [];
                angular.forEach(inscriptionStatusList, function (item) {
                    items.push({
                        label: item.name,
                        execute: function (items, $dialog) {
                            return $dialog
                                .open('inscription.changeStatus', {
                                    items: items,
                                    inscriptionStatus: item,
                                    presenceStatus: undefined,
                                    session: ( session ) ? session.id : 0
                                })
                                .then(function () {
                                    // on success, reload the search page
                                    //search.search();

                                    for (var keySearch in $scope.search.result.items) {
                                        for (var keyItem in items) {
                                            if (items[keyItem] === $scope.search.result.items[keySearch].id) {
                                                $scope.search.result.items[keySearch].inscriptionStatus = item;
                                            }
                                        }
                                    }
                                });
                        }
                    });
                });
                return items;
            }()
        },
        {
            icon: 'fa-external-link',
            label: "Modifier le statut de présence",
            available: function () {
                return $user.hasAccessRight('sygefor_inscription.rights.inscription.own.update') || $user.hasAccessRight('sygefor_inscription.rights.inscription.all.update');
            },
            subitems: function () {
                var items = [];
                angular.forEach(presenceStatusList, function (item) {
                    items.push({
                        label: item.name,
                        execute: function (items, $dialog) {
                            return $dialog
                                .open('inscription.changeStatus', {
                                    items: items,
                                    presenceStatus: item,
                                    inscriptionStatus: undefined,
                                    session: ( session ) ? session.id : 0
                                })
                                .then(function () {
                                    // on success, reload the search page
                                    //search.search();

                                    for (var keySearch in $scope.search.result.items) {
                                        for (var keyItem in items) {
                                            if (items[keyItem] === $scope.search.result.items[keySearch].id) {
                                                $scope.search.result.items[keySearch].presenceStatus = item;
                                            }
                                        }
                                    }
                                });
                        }
                    });
                });
                return items;
            }()
        },
        {
            icon: 'fa-envelope-o',
            label: 'Envoyer un Email',
            execute: function (items, $dialog) {
                return $dialog.open('batch.email', {items: items, targetClass: 'SygeforInscriptionBundle:AbstractInscription'})
            }
        },
        {
            icon: 'fa-file-pdf-o',
            label: 'Attestation de présence',
            execute: function (items, $dialog) {
                return $dialog.open('batch.export.pdf', {items: items, service: 'inscription.attestation'});
            }
        },
        {
            icon: 'fa-download',
            label: 'Exporter',
            subitems: [
                {
                    icon: 'fa-file-excel-o',
                    label: 'CSV',
                    execute: function (items, $dialog) {
                        return $dialog.open('batch.export.csv', {items: items, service: 'inscription'})
                    }
                }, {
                    icon: 'fa-external-link',
                    label: 'Publipostage',
                    execute: function (items, $dialog) {
                        return $dialog.open('batch.publipost', {items: items, service: 'inscription'})
                    }
                }
            ]
        }
    ];

    // permit batch operation only if we come from session page
    if ($scope.$stateParams.session) {
        $scope.batchOperations.push({
            icon: 'fa-copy',
            label: 'Copier vers une nouvelle session',
            execute: function (items, $dialog) {
                return $dialog.open('inscription.duplicate', {items: items, service: 'inscription'})
            }
        });
    }

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
            label: 'Centre'
        },
        'inscriptionStatus.name.source' : {
            label: 'Statut inscription'
        },
        'presenceStatus.name.source' : {
            label: 'Statut présence'
        },
        'institution.name.source' : {
            label: 'Etablissement',
            size: 10
        },
        'trainee.institution.name.source' : {
            label: 'Etablissement actuelle',
            size: 10
        },
        'publicType.source' : {
            label: 'Catégorie de personnel'
        },
        'trainee.fullName.source' : {
            label: 'Stagiaire'
        },
        'session.dateBegin' : {
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
        }
    };
}]);
