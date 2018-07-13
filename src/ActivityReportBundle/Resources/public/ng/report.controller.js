/**
 * Activity Report Controller
 */
sygeforApp.controller('ActivityReportController',  ['$scope', '$location', '$anchorScroll', '$window', '$user', '$injector', 'BaseListController', '$state', '$stateParams', '$timeout', '$dialog', 'search', 'report', function($scope, $location, $anchorScroll, $window, $user, $injector, BaseListController, $state, $stateParams, $timeout, $dialog, search, report) {
    $injector.invoke(BaseListController, this, {key: 'session', $scope: $scope, $search: search});
    $scope.report = report;

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
        }
    };

    $scope.goToAnchor = function(anchor) {
        $location.hash(anchor);
        $anchorScroll();
    };

    $scope.getReportValue = function(crosstab, entity, type) {
        crosstab = $scope.$parent.$parent.report.crosstabs[crosstab];
        for (var i in crosstab.rows) {
            var row = crosstab.rows[i];
            if (row.key.indexOf(entity) >= 0) {
                if (typeof type !== "undefined") {
                    for (var j in row.data) {
                        if (row.data[j].key.indexOf(type) >= 0) {
                            return row.data[j].value;
                        }
                    }
                }
                else {
                    return crosstab.rows[i].value;
                }
            }
        }

        return 0;
    };

    /**
     * Download
     */
    $scope.download = function(format) {
        var url = Routing.generate("report.download");
        $window.location.href = url + '?' + serialize($stateParams._rawQuery);
    };

    /**
     * Serialize js object to querystring
     *
     * @param obj
     * @param prefix
     * @returns {string}
     */
    var serialize = function(obj, prefix) {
        var str = [];
        for(var p in obj) {
            if (obj.hasOwnProperty(p)) {
                var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
                str.push(typeof v == "object" ?
                    serialize(v, k) :
                encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
        }
        return str.join("&");
    }
}]);
