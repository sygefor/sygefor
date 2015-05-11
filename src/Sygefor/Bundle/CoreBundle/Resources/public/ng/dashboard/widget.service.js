/**
 * Widget provider
 */
sygeforApp.provider('$widget', [function() {

    var widgets = {};

    /**
     * Add a widget profile
     * @param name
     * @param params
     */
    this.widget = function(name, params) {
        widgets[name] = params;
    }

    /**
     * this.$get
     */
    this.$get = function($modal, $dialogParams) {
        return {
            get: function(name) {
                return widgets[name];
            }
        }
    };
}]);
