/**
 * Application config
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider", "$widgetProvider", function($listStateProvider, $dialogProvider, $widgetProvider) {

    // report states
    $listStateProvider.state('report', {
        url: "/report?q",
        templateUrl: "report.html",
        controller:"ActivityReportController",
        breadcrumb: "Bilan d'activité",
        resolve: {
            search: function ($searchFactory, $stateParams, $user) {
                var search = $searchFactory('session.search');
                search.query.filters = {
                    'year': moment().format('YYYY'),
                    'semester': Math.ceil(moment().format('M')/6)
                };

                search.extendQueryFromJson($stateParams.q);
                return search;
            },
            report: function ($http, search, $stateParams) {
                var url = Routing.generate("report.index");
                var query = getRawQuery(search.query.filters);
                $stateParams._rawQuery = query;
                return $http({method: 'POST', url: url, data: query}).then(function(response) {
                    return response.data;
                });
            }
        },
        states: {
            training: {
                url: "/training",
                abstract: true,
                template: "<div ui-view></div>",
                states: {
                    summaries: {
                        url: "/summaries",
                        controller:"ActivityReportController",
                        templateUrl: "training/summaries.html"
                    },
                    crosstabs: {
                        url: "/crosstabs",
                        controller:"ActivityReportController",
                        templateUrl: "training/crosstabs.html"
                    },
                    listings: {
                        url: "/listings",
                        controller:"ActivityReportController",
                        templateUrl: "training/listings.html"
                    }
                }
            },
            meeting: {
                url: "/meeting",
                abstract: true,
                template: "<div ui-view></div>",
                states: {
                    summaries: {
                        url: "/summaries",
                        controller:"ActivityReportController",
                        templateUrl: "meeting/summaries.html"
                    },
                    crosstabs: {
                        url: "/crosstabs",
                        controller:"ActivityReportController",
                        templateUrl: "meeting/crosstabs.html"
                    },
                    listings: {
                        url: "/listings",
                        controller:"ActivityReportController",
                        templateUrl: "meeting/listings.html"
                    }
                }
            }
        }
    });

    /**
     * Get a RAW elasticsearch query from filters
     * @param filters
     */
    function getRawQuery(filters) {
        var query = {};
        if(Object.keys(filters).length > 0) {
            var and = [];
            for(var key in filters) {
                var filter = {};
                var type = Array.isArray(filters[key]) ? 'terms' : 'term';
                filter[type] = {};
                filter[type][key] = filters[key];
                and.push(filter)
            }
            query["filter"] = {"and": and};
        }
        return query;
    }

}]);
