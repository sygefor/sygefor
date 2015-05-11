/**
 * $listState Provider
 */
sygeforApp.provider('$listState', ['$stateProvider', function($stateProvider)
{
    /**
     *
     * @type {Function}
     */
    this.state = state;
    function state(name, definition, parent) {
        definition.name = name;
        definition.root = parent ? (parent.root ? parent.root : parent) : null;
        $stateProvider.state(name, definition);
        if(definition.states !== undefined) {
            for(childName in definition.states) {
                this.state(name + "." + childName, definition.states[childName], definition);
            }
        }
    }

    /**
     * @ngdoc object
     */
    this.$get = $get;
    $get.$inject = ['$state'];
    function $get($state) {
        return $state;
    };
}]);

