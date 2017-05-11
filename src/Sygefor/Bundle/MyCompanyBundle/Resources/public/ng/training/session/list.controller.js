/**
 * Core List Controller
 */
sygeforApp.controller('SessionListController', ['$scope', '$state', '$injector', '$dialog', '$user', 'search', '$dialogParams', 'BaseListController', 'training', '$trainingBundle', function($scope, $state, $injector, $dialog, $user, search, $dialogParams, BaseListController, training, $trainingBundle) {
    $injector.invoke(BaseListController, this, {key: 'session', $scope: $scope, $search: search});

    $scope.$trainingBundle = $trainingBundle;

    /**
     * Declare add operation
     * @var {Array}
     */
     $scope.addOperations = [];

    /**
     * Batch operations
     * @var {Array}
     */
    $scope.batchOperations = [{
        icon: 'fa-bullhorn',
        label: "Modifier l'état des inscriptions",
        available: function () {
            return $user.hasAccessRight('sygefor_inscription.rights.inscription.own.update') || $user.hasAccessRight('sygefor_inscription.rights.inscription.all.update');
        },
        subitems: function (){
            var items = [];
            var options = [
                'Désactivées',
                'Fermées',
                'Privées',
                'Publiques'
            ];
            for(var i=0; i<options.length; i++) {
                (function() {
                    const _i = i;
                    items.push ({
                        label: options[i],
                        execute: function (items, $dialog) {
                            return $dialog
                                .open('session.registrationChange', {items: items, registration: _i})
                                .then(function() {
                                    // on success, reload the search page
                                    search.search();
                                });
                        }
                    });
                })();
            }
            return items;
        }()
    },{
        icon: 'fa-download',
        label: 'Exporter',
        subitems: [
            {
                icon: 'fa-file-excel-o',
                label: 'CSV',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.export.csv', { items: items, service: 'session' })
                }
            },
            {
                icon: 'fa-external-link',
                label: 'Publipostage',
                execute: function (items, $dialog) {
                    return $dialog.open('batch.publipost', {items: items, service: 'session'})
                }
            }
        ]

    }];

    /**
     * Facets
     */
    $scope.facets = {
        'training.organization.name.source' : {
            label: 'Centre'
        },
        'year' : {
            label: 'Année'
        },
        'semester' : {
            label: 'Semestre'
        },
        'training.theme.source' : {
            label: 'Domaine de compétence'
        },
        'module.source' : {
            label: 'Module'
        },
        'training.typeLabel.source' : {
            label: 'Type'
        },
        'registration' : {
            label: 'Inscriptions',
            values: {
                '0': 'Désactivées',
                '1': 'Fermée',
                '2': 'Privée',
                '3': 'Publiques'
            }
        },
        'status' : {
            label: 'Statut',
            values: {
                '0': 'Ouverte',
                '1': 'Reportée',
                '2': 'Annulée'
            }
        },
        'displayOnline' : {
            label: 'Afficher en ligne',
            values: {
                'T': 'Oui',
                'F': 'Non'
            }
        },
        'training.name.source' : {
            label: 'Formation'
        },
        'dateBegin' : {
            label: 'Date',
            type: 'range'
        },
        'participations.trainer.fullName' : {
            label: 'Formateur'
        },
        'promote' : {
            label: 'Promotion',
            values: {
                'true': 'Oui',
                'false': 'Non'
            }
        }
    };
}]);
