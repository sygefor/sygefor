/**
 * User Provider
 */
sygeforApp.provider('$user', [function() {

    var user = {
        id: 0,
        username: 'anonymous'
    };

    /**
     * set the current user object
     * @param object
     * @returns {*}
     */
    this.setUser = function(object) {
        user = object;
        return this;
    };

    /**
     * Provider this.$get
     */
    this.$get = function() {

        /**
         * hasAccess
         */
        user.hasAccessRight = function(id) {
            // user with ROLE_ADMIN role can access to everything
            if(this.roles.indexOf("ROLE_ADMIN") > -1) {
                return true;
            }
            // search for the access right in groups
            for(var j in this.accessRights){
                if (this.accessRights[j] == id) return true;
            }
            return false;
        }

        return user;
    };
}]);
