/**
 * Include a participation table block for a given trainer
 * Usage : <div participations-block="trainer"></div>
 */
sygeforApp.directive('participationsBlock', ['$dialog', '$searchFactory', function($dialog, $searchFactory) {
    return {
        restrict: 'EA',
        scope: {
            trainer: '=participationsBlock'
        },
        link: function(scope, element, attrs) {
            // custum empty message
            scope.emptyMsg = attrs.emptyMsg ?  attrs.emptyMsg : "Il n'y a aucune session pour cet intervenant.";
            scope.$dialog = $dialog;

            // get participations from elasticsearch
            var search = $searchFactory('participation.search');
            search.query.filters['trainer.id'] = scope.trainer.id;
            search.query.sorts = {'session.dateBegin': 'desc'};
            search.query.size = 20;
            scope.search = search;
            search.search().then(function() {
                // watch page
                scope.$watch('search.query.page', function(newValue, oldValue) {
                    if (newValue != oldValue) {
                        search.search();
                    }
                });
            });
        },
        templateUrl: 'trainer/directives/participations.block.html'
    }
}]);
