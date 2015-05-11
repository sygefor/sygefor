/**
 * Created by maxime on 20/06/14.
 */
/**
 * Created by maxime on 19/06/14.
 */
/**
 * TaxonomyBundle Provider
 */
sygeforApp.provider('$taxonomy', [function() {

    /**
     *
     * @returns {}
     */
    this.$get = function($http) {

        return {
            /**
             *
             * @param vocabulary
             * @returns {*|then|then}
             */
            getTerms: function (vocabulary) {
                var url = Routing.generate('taxonomy.get', {vocabularyId: vocabulary});
                return $http({ cache: true, url: url, method: 'GET'}).then(function (result) {
                    return result.data;
                });
            },

            /**
             *
             * @param vocabulary
             * @returns {*|then|then}
             */
            getIndexedTerms: function (vocabulary) {
                var url = Routing.generate('taxonomy.get', {vocabularyId: vocabulary});
                return $http({ cache: true, url: url, method: 'GET'}).then(function (result) {
                    var terms = {};
                    angular.forEach(result.data, function (term) {
                        terms[term.id] = term;
                    });
                    return terms;
                });
            }
        };
    }
}]);
