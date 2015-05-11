/**
 * SearchBoxController
 */
sygeforApp.controller('SearchBoxController', ['$scope', '$timeout', function($scope, $timeout) {
    $scope.sbfacets = [];

    /**
     * Callback to get filtered facet list from the server
     */
    var getFacetItems = function(input, name) {

        // default options
        var options = {
            size: 999,
            order: { "_term" : "asc" }
        };

        // allow customize in facet config
        if($scope.facets[name]) {
            for(var key in $scope.facets[name]) {
                if(options[key]) {
                    options[key] = $scope.facets[name][key];
                }
            }
        }

        // set the input
        if(input) {
            options.include = {
                "pattern" : ".*" + input + ".*",
                "flags" : "CANON_EQ|CASE_INSENSITIVE"
            };
        }

        // cancel the previous promise
        if(typeof getFacetItemsTimeout !== "undefined") {
            $timeout.cancel(getFacetItemsTimeout);
        }

        // launch the promise, with delay
        getFacetItemsTimeout = $timeout(function() {
            return $scope.search.fetchAggregation(name, options).then(function(agg) {
                var items = [];
                for(var i=0; i<agg.buckets.length; i++) {
                    var key = agg.buckets[i].key;
                    items.push({
                        name: key,
                        label: ($scope.facets[name] && $scope.facets[name].values && $scope.facets[name].values[key]) || key,
                        count: agg.buckets[i].doc_count
                    });
                }
                return items;
            });
        }, 500);

        return getFacetItemsTimeout;
    };

    /**
     * Add the facets
     */
    var sbfacets = [];
    for(key in $scope.facets) {
        var facet = {
            name: key,
            label: $scope.facets[key].label,
            type: $scope.facets[key].type || 'string',
            items: typeof $scope.facets[key].items != "undefined" ? $scope.facets[key].items : getFacetItems
        }
        sbfacets.push(facet);
    }
    $scope.sbfacets = sbfacets;


    /**
     * Update search box parameters
     */
    var updateParameters = function() {
        var sbparameters = [];
        var filters = angular.copy($scope.search.query.filters);
        for(var key in filters) {
            // if the filter is not an array, transform
            if(!Array.isArray(filters[key])) {
                filters[key] = [filters[key]];
            }
            for(var i=0; i < filters[key].length; i++) {
                sbparameters.push({
                    key: key,
                    value: filters[key][i]
                });
            }
        }
        if($scope.search.query.keywords) {
            sbparameters.push({key: 'text', value: $scope.search.query.keywords});
        }
        $scope.sbparameters = sbparameters;
    }

    /**
     * Watch search.query.filters
     * Wath search.keywords
     */
    $scope.$watchCollection("search.query.filters", updateParameters);
    $scope.$watch("search.query.keywords", updateParameters);

    /**
     * Watch searchbox parameters to update the query
     */
    $scope.$watch("sbparameters", function(params, oldParams) {
        if(params) {
            // prepare an object to merge
            var query = {
                keywords: null,
                filters: $scope.search.query.filters
            };

            // empty all the filters based on facets
            // @todo : searchbox must return empty value for each facet
            for(var i = 0; i < oldParams.length; i++) {
                delete query.filters[oldParams[i].key];
            }

            // fill the query with params
            for(var i = 0; i < params.length; i++) {
                var param = params[i];
                // keywords
                if(param.key == 'text') {
                    query.keywords = param.value;
                } else if(param.value) {
                    if(query.filters[param.key]) {
                        // if the filter is already defined
                        if(!Array.isArray(query.filters[param.key])) {
                            // if the filter is not yet an array
                            query.filters[param.key] = [query.filters[param.key]];
                        }
                        query.filters[param.key].push(param.value);
                    } else {
                        // initialize the filter
                        query.filters[param.key] = param.value;
                    }
                }
            }

            // extends the search query with our query
            $scope.search.query = angular.extend($scope.search.query, query);
        }
    }, true);

}]);
