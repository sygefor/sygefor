/**
 * Core List Controller
 */
sygeforApp.controller('TrainingListController', ['$scope', '$user', '$injector', 'BaseListController', '$trainingBundle', '$state', '$timeout', '$dialog', 'search', function($scope, $user, $injector, BaseListController, $trainingBundle, $state, $timeout, $dialog, search) {
    $injector.invoke(BaseListController, this, {key: 'training', $scope: $scope, $search: search});

    /**
     * Declare batch operations
     * @type {Array}
     */
    $scope.batchOperations = [{
        icon: 'fa-download',
        label: 'Exporter',
        subitems: [
            {
                icon: 'fa-file-excel-o',
                label: 'CSV',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.export.csv', { items: items, service: 'semestered_training' })
                }
            },{
                icon: 'fa-file-pdf-o',
                label: 'PDF',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.export.pdf', { items: items, service: 'training' }) // warning : use of 'training' instead of 'semestered_training' is waiting !
                }
            },{
                icon: 'fa-external-link',
                label: 'Publipostage',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.publipost', { items: items, service: 'semestered_training' })
                }
            }
        ]
    },{
        icon: 'fa-exchange',
        label: 'Changer de type',
        subitems: [
            {
                label: 'Stage',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.convert_type', { items: items, service: 'semestered_training', type: 'internship' })
                }
            },
            {
                label: 'Enseignement de cursus',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.convert_type', { items: items, service: 'semestered_training', type: 'training_course' })
                }
            },
            {
                label: 'Formation doctorale',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.convert_type', { items: items, service: 'semestered_training', type: 'doctoral_training' })
                }
            },
            {
                label: 'Action diverse',
                execute: function(items, $dialog) {
                    return $dialog.open('batch.convert_type', { items: items, service: 'semestered_training', type: 'diverse_training' })
                }
            },
        ]
    }];

    /**
     * Declare add operation
     * @type {Array}
     */
    $scope.addOperations = function (){
        var ops = [];
        var trainingTypes = $trainingBundle.getTypes();

        for (var key in trainingTypes) {
            var type = trainingTypes[key];

            ops.push({
                key: key,
                label: ( typeof type.label != "undefined" ) ? type.label : key,
                execute: function (key){
                    $dialog.open('training.create', { type: key, filters: search.query.filters }).then(function(data) {
                        $state.go('training.detail.view', {id: data.training.id}, {reload: true});
                    });
                },
                available: function (){ return $user.hasAccessRight('sygefor_training.rights.training.all.create') || $user.hasAccessRight('sygefor_training.rights.training.own.create');}
            });
        }
        return ops;
    }();

    /**
     * Declare facets
     */
    $scope.facets = {
        'training.organization.name.source': {
            label: 'URFIST'
        },
        'year': {
            label: 'Année'
        },
        'semester': {
            label: 'Semestre'
        }
    };

    if ($scope.$state.params !== undefined && $scope.$state.params.type === "doctoral_training") {
        $scope.facets.collegeSemester = {
            label: 'Semestre universitaire'
        };
        $scope.facets.collegeYear = {
            label: 'Année universitaire'
        };
    }

    angular.extend($scope.facets, {
        'training.theme.source' : {
            label: 'Thématique'
        },
        'trainers.fullName' : {
            label: 'Formateur'
        },
        'training.typeLabel.source' : {
            label: 'Type'
        },
        'training.number' : {
            label: 'Numéro',
            items: []
        },
        'nextSession.promote' : {
            label: 'Promotion',
            values: {
                'true': 'Oui',
                'false': 'Non'
            }
        }
    });
}]);
