/**
 * Dialog provider
 */
sygeforApp.provider('$dialog', [function() {

    var profiles = {};

    /**
     * Add a dialog profile
     * @param name
     * @param params
     */
    this.dialog = function(name, params) {
        profiles[name] = params;
    }

    /**
     * this.$get
     */
    this.$get = function($modal, $dialogParams) {

        return {

            /**
             * Open a dialog
             * @param id
             */
            open: function(name, params, options) {

                // get the profil
                var profile = profiles[name];
                if(!profile) {
                    throw "Unknown dialog profile : " + name;
                }
                angular.copy(params, $dialogParams);
                var options = angular.extend({}, profile, options ? options : {});

                var modalInstance = $modal.open(options);
                return modalInstance.result;
            }
        }
    };
}]);


/**
 * $dialogParams
 */
sygeforApp.value('$dialogParams', {});
