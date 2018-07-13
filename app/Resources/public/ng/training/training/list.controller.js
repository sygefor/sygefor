/**
 * Core List Controller
 */
sygeforApp.controller('TrainingListController', ['$scope', '$user', '$injector', 'BaseListController', '$utils', '$state', '$timeout', '$dialog', 'search', function($scope, $user, $injector, BaseListController, $utils, $state, $timeout, $dialog, search) {
    $injector.invoke(BaseListController, this, {key: 'training', $scope: $scope, $search: search});

    /**
     * Declare batch operations
     * @var {Array}
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
    }];

    /**
     * Declare add operation
     * @var {Array}
     */
    $scope.addOperations = function (){
        var ops = [];
        var trainingTypes = $utils.getTypes();

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
                available: function (){ return $user.hasAccessRight('sygefor_core.access_right.training.all.create') || $user.hasAccessRight('sygefor_core.access_right.training.own.create');}
            });
        }
        return ops;
    }();

    /**
     * Declare facets
     */
    $scope.facets = {
        'training.organization.name.source': {
            label: 'Centre'
        },
        'year': {
            label: 'Année'
        },
        'semester': {
            label: 'Semestre'
        },
        'training.theme.name.source': {
            label: 'Domaine de connaissance'
        },
        'training.trainingCode.autocomplete': {
            label: 'Code de formation'
        },
        'trainers.fullName': {
            label: 'Intervenant'
        },
        'training.typeLabel.source': {
            label: 'Type'
        }
    };
}]);
