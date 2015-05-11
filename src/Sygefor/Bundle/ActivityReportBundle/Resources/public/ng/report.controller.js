/**
 * Activity Report Controller
 */
sygeforApp.controller('ActivityReportController', ['$scope', '$window', '$user', '$injector', 'BaseListController', '$state', '$stateParams', '$timeout', '$dialog', 'search', 'report', function($scope, $window, $user, $injector, BaseListController, $state, $stateParams, $timeout, $dialog, search, report) {
    $injector.invoke(BaseListController, this, {key: 'session', $scope: $scope, $search: search});
    $scope.report = report;

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
        }
    };

    /**
     * Download
     */
    $scope.download = function(format) {
        var url = Routing.generate("report.download");
        $window.location.href = url + '?' + serialize($stateParams._rawQuery);
    }

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
