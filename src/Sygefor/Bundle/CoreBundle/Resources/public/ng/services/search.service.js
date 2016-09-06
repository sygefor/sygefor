/**
 * Search Service Factory
 */
SearchServiceFactory.$inject = ['$http', '$q'];
function SearchServiceFactory($http, $q) {
    return function (route, params) {

        /**
         * search url
         */
        var url = Routing.generate(route, params);

        return {

            /**
             * processing
             */
            processing: false,

            /**
             * query object
             */
            query: {
                keywords: null,
                filters: {},
                aggs: {},
                sorts: {'_score': 'desc'},
                size: 50,
                page: 1
            },

            /**
             * global filters object
             */
            filters: {},

            /**
             * result object
             */
            result: {
                done: false,
                total:0,
                items:[],
                aggs:[]
            },

            /**
             * executed
             */
            executed: false,

            /**
             * Execute the query and return a promise
             */
            search: function() {
                var deferred = $q.defer();
                this.processing = true;
                var that = this;

                var query = angular.copy(this.query);
                angular.extend(query.filters, this.filters);
                $http({method: 'POST', url: url, data: query})
                    .success(function(response) {
                        that.result = response;
                        that.result.nbPages = Math.ceil(response.total/that.query.size);
                        that.processing = false;
                        that.executed = true;
                        deferred.resolve(that.result);
                    })
                    .error(deferred.reject);
                return deferred.promise;
            },

            /**
             * Fetch all the result without limit
             */
            fetchAll: function(fields) {
                var deferred = $q.defer();
                var query = angular.copy(this.query);
                angular.extend(query.filters, this.filters);
                angular.extend(query, {
                    page: 1,
                    size:99999,
                    fields: fields
                });
                $http({method: 'POST', url: url, data: query})
                    .success(function(response) {
                        deferred.resolve(response.items);
                    })
                    .error(deferred.reject);
                return deferred.promise;
            },

            /**
             * Fetch one aggregation with no limit on items
             */
            fetchAggregation: function(name, options) {
                var deferred = $q.defer();
                // build the new aggs query
                var aggs = {};
                aggs[name] = options ? options : {};

                // copy the query
                var query = angular.copy(this.query);
                // remove same name filter
                if(query.filters[name]) {
                    delete query.filters[name];
                }
                // extend with global filters
                angular.extend(query.filters, this.filters);
                angular.extend(query, {
                    size: 1,
                    fields: [],
                    aggs: aggs
                });

                // all filters become query filter
                query.query_filters = angular.copy(query.filters);
                delete query.filters;

                // query the server
                $http({method: 'POST', url: url, data: query})
                    .success(function(response) {
                        var agg = response.aggs[name];
                        if(agg[name]) {
                            // support filtered aggregation (see hack in SearchService)
                            // @todo better way ?
                            deferred.resolve(agg[name]);
                        }
                        deferred.resolve(agg);
                    })
                    .error(deferred.reject);
                // return the promise
                return deferred.promise;
            },

            /**
             * setFilter
             */
            setFilter: function(field, value) {
                if(!value)
                    delete this.search.query.filters[field];
                else
                    this.search.query.filters[field] = value;
            },

            /**
             * setSort
             */
            setSort: function(field, order) {
                this.query.sorts = {};
                if(order) {
                    this.query.sorts[field] = order;
                }
            },

            /**
             * extend current query from a JSON encoded object
             */
            extendQueryFromJson: function(json) {
                if(json) {
                    var query = angular.fromJson(json);
                    angular.extend(this.query, query);
                }
            }
        }
    }
}

/**
 * search service factory
 */
sygeforApp.factory('$searchFactory', SearchServiceFactory);
