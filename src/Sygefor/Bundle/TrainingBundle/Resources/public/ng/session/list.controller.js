/**
 * Core List Controller
 */
sygeforApp.controller('SessionListController', ['$scope', '$state', '$injector', '$dialog', '$user', 'search', '$dialogParams', 'BaseListController', 'training', function($scope, $state, $injector, $dialog, $user, search, $dialogParams, BaseListController, training) {
    $injector.invoke(BaseListController, this, {key: 'session', $scope: $scope, $search: search});

    /**
     * Declare add operation
     * @type {Array}
     */
     $scope.addOperations = [{
        //templateUrl: 'trainingbundle/session/modals/create.html',
        label: 'Ajouter une session',
        execute: function (){
            $dialog.open('session.create',{training: training.id}).then(function(data) {
                $state.go('session.detail.view', {id: data.session.id, training: data.session.training.id}, {reload: true});
            });
        },
        available: function () {
            return ( training !== null ) && ($user.hasAccessRight('sygefor_training.rights.training.all.update') || $user.hasAccessRight('sygefor_training.rights.training.own.update'));
        }
     }];

    /**
     * Batch operations
     * @type {Array}
     */
    $scope.batchOperations = [{
        icon: 'fa-bullhorn',
        label: "Modifier l'état des inscriptions",
        available: function () {
            return true;
            //return $user.hasAccessRight('sygefor_training.rights.inscription.own.update') || $user.hasAccessRight('sygefor_trainee.rights.inscription.all.update');
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
            }
        ]

    },{
        icon: 'fa-external-link',
        label: 'Publipostage',
        execute: function(items, $dialog) {
            return $dialog.open('batch.publipost', { items: items, service: 'session' })
        }
    }];

    /**
     * Facets
     */
    $scope.facets = {
        'training.organization.name.source' : {
            label: 'URFIST'
        },
        'year' : {
            label: 'Année'
        },
        'semester' : {
            label: 'Semestre'
        },
        'training.theme.source' : {
            label: 'Thématique'
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
        'training.name.source' : {
            label: 'Formation'
        },
        'dateBegin' : {
            label: 'Date',
            type: 'range'
        },
        'trainers.fullName' : {
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
