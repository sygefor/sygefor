/**
 * Search Table Directive
 */
sygeforApp.directive('searchTable', ['$timeout', function($timeout) {
    return {
        restrict: 'A',
        replace: true,
        transclude: true,
        link: function(scope, element) {
            /**
             * Watch the items
             * Remove filtered column
             */
            scope.$watch("search.result.items", function(items) {
                $timeout(function() {
                    var filters = scope.search.query.filters;
                    $("thead th", element).show();
                    $("tbody tr td", element).show();
                    $("thead th", element).each(function(i) {
                        var field = $(this).attr('field');
                        if(field && filters[field] !== undefined && !Array.isArray(filters[field]) && !$(this).hasClass('visible')) {
                            $(this).hide();
                            $("tbody tr", element).each(function(j) {
                                $("td", $(this)).eq(i).hide();
                            });
                        }
                    });
                });
            });
        },
        controller:function($scope) {

        },
        template: '<table class="table table-search table-hover" ng-transclude></table>'
    }
}]);

/**
 * Search Table Directive : Headers
 */
sygeforApp.directive('searchTableTh', [function() {
    return {
        restrict: 'A',
        replace: false,
        transclude: true,
        require: '^searchTable',
        scope:{},
        link: function(scope, element, attrs, searchTableCtrl) {
            scope.search = scope.$parent.search;
            scope.field = attrs.field;

            /**
             * isSelected
             */
            scope.getOrder = function() {
                if(scope.search.query.sorts[attrs.field] !== undefined) {
                    return scope.search.query.sorts[attrs.field];
                }
                return false;
            }

            /**
            * getIconClass
            */
            scope.getIconClass = function() {
                var order = scope.getOrder();
                if(order == 'desc') {
                    return attrs.iconAsc ? attrs.iconAsc : "fa fa-sort-alpha-desc";
                } else if(order == 'asc') {
                    return attrs.iconDesc ? attrs.iconDesc : "fa fa-sort-alpha-asc";
                } else {
                    return false;
                }
            }

            /**
             * sort
             */
            scope.sort = function() {
                var order = scope.getOrder() == 'asc' ? 'desc' : 'asc';
                scope.search.setSort(attrs.field, order);
            }
        },
        controller: function($scope, $element) {

        },
        template: '<div ng-class="{sortable: !!field, sort: !!getOrder()}" ng-attr-title="Trier par ordre {{ getOrder() && getOrder() == \'desc\' ? \'ascendant\' : \'descendant\' }}" ng-click="sort()">'
            + '<i class="fa" ng-show="!!getOrder()" ng-class="getIconClass()"></i> <span ng-transclude></span>'
            + '</div>'
    };
}]);

/**
 * Search Table Directive : controls
 */
sygeforApp.directive('searchTableControls', [function() {
    return {
        restrict: 'A',
        replace: true,
        template: '<div class="pagination-wrapper">' +
            '<pagination ng-if="search.result.total > 0" ng-model="search.query.page" total-items="search.result.total" items-per-page="search.query.size" boundary-links="true" rotate="false" max-size="15" next-text="Suivant" previous-text="Précédent" first-text="Début" last-text="Fin"></pagination>' +
        '</div>'
    };
}]);


/**
 * Directive : Stop event
 */
sygeforApp.directive('stopEvent', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            element.bind('click', function (e) {
                e.stopPropagation();
            });
        }
    };
});
