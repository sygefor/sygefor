/**
 * $session service
 */
sygeforApp.service('$session', [function() {
    /**
     * setSession
     * @param key
     * @param value
     * @returns {*}
     */
    this.setSession = function(key, value) {
        value = typeof value == "undefined" ? null : JSON.stringify(value);
        return sessionStorage.setItem(key, value);
    };

    /**
     * get value
     * @param key
     * @returns {*}
     */
    this.getSession = function(key) {
        return JSON.parse(sessionStorage.getItem(key));
    }

    /**
     * defineProperty history
     */
    Object.defineProperty(this, "history",{
        get: function() {
            return this.getSession('history');
        },
        set: function(value) {
            this.setSession('history', value);
        }
    });
}]);
