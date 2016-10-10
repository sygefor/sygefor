/**
 * TrainingBundle Provider
 */
sygeforApp.provider('$trainingBundle', [function() {
    var types = {};

    this.addType = function(type, options) {
        types[type] = options;
        return this;
    };

    this.$get = function() {
        return {
            /**
             * return the type options
             * @param type
             * @returns {*}
             */
            getTypes: function() {
                return types;
            },

            /**
             * return the type options
             * @param type
             * @returns {*}
             */
            getType: function(type) {
                // JMS Serializer bug !!
                // return types[type];
                for(key in types) {
                    if(key.replace('_', '') == type.replace('_', '')) {
                        return types[key];
                    }
                }
            },

            /**
             * return status states
             * @returns {*}
             */
            statusStates: [
                'Ouverte',
                'Reportée',
                'Annulée'
            ],

            /**
             * return registration states
             * @returns {*}
             */
            registrationStates: [
                'Désactivées',
                'Fermées',
                'Privées',
                'Publiques'
            ]
        };
    };
}]);
