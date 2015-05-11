/**
 * Open a dialog
 */
sygeforApp.directive('inputCopyClipboard', ['$timeout', function($timeout)
{
    return {
        restrict: 'A',
        replace: true,
        scope: {
            value: '=inputCopyClipboard'
        },
        link: link,
        template: template
    };

    /**
     * @ngInject
     */
    function link(scope, element) {
        scope.copied = false;
        scope.select = function() {
            $('input', element).select();
        };
        scope.copy = function() {
            scope.copied = true;
            $timeout(function() {
                scope.copied = false;
            }, 500);
        };
    }

    /**
     * template
     * @returns {string}
     */
    function template() {
        return  '<div class="input-group input-copy-clipboard">' +
            '<input type="text" ng-show="!copied" class="form-control input-sm" ng-click="select()" ng-model="value" readonly>' +
            '<input type="text" ng-show="copied" class="form-control input-sm" value="Copié !" readonly>' +
            '<span class="input-group-btn">' +
            '    <button class="btn btn-default btn-sm" type="button" ui-zeroclip zeroclip-copied="copy()" zeroclip-model="value" tooltip="Copier dans le presse-papier" tooltip-placement="left"><span class="fa fa-clipboard"></span></button>' +
            '</span>' +
        '</div>';
    }
}]);
