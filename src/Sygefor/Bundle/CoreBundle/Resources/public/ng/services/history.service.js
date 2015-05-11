/**
 * $history service
 */
sygeforApp.service('$history', ['$location', '$state', '$session', function($location, $state, $session) {
    var locationChanged = false;
    var currentPath = null;

    /**
     * stateChange
     * @param stateName
     * @param stateParams
     */
    this.stateChange = function(stateName, stateParams) {
        if(locationChanged) {
            locationChanged = false;
            return;
        }

        var url = this.getUrl();
        this.push(url, {
            name: stateName,
            params: stateParams
        });
        currentPath = url;
        $location.path(url);
    }

    /**
     * locationChange
     * @param url
     * @returns {promise}
     */
    this.locationChange = function(url) {
        if (url === currentPath || ($session.history == null)) {
            return;
        }
        var state = $session.history[url];
        if (state == null) {
            return;
        }
        locationChanged = true;
        currentPath = url;
        return $state.go(state.name, state.params);
    }

    /**
     * push
     * @param key
     * @param item
     * @returns {*}
     */
    this.push = function(key, item) {
        var history;
        history = $session.history;
        if (history == null) {
            history = {};
        }
        history[key] = item;
        return $session.history = history;
    }

    /**
     * getUrl
     * @returns {*}
     */
    this.getUrl = function() {
        return ("/dssdfsdfsdf/") + ("" + (Math.random().toString(16)) + "000000000").substr(2, 8);
    }
}]);
