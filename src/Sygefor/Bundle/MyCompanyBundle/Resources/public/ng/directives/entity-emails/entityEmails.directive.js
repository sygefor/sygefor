/**
 * Return emails corresponding to a trainee or a session
 */
sygeforApp.directive('entityEmails', ['$searchFactory', function($searchFactory)
{
    return {
        restrict: 'A',
        replace: true,
        scope: {
            trainee: '=',
            trainer: '=',
            session: '='
        },
        templateUrl: function(elem, attr) {
            if (attr.trainee && !attr.session) {
                return 'mycompanybundle/trainee/states/detail/partials/emails.html';
            }
            else if (!attr.trainee && attr.session) {
                return 'mycompanybundle/training/session/states/detail/partials/emails.html';
            }
            else if (attr.trainer) {
                return 'mycompanybundle/trainer/states/detail/partials/emails.html';
            }
           else if (attr.trainee && attr.session) {
                console.log('need to create a template for messages');
            }
        },
        link: link,
        controller: entityEmailsController
    };

    /**
     * @ngInject
     */
    function link(scope, element, $user)
    {
        var search = $searchFactory('email.search');
        if (scope.trainee) {
            search.query.filters['trainee.id'] = scope.trainee;
            search.query.filters['userFrom.organization.id'] = scope.$user.organization.id;
        }
        else if (scope.session) {
            search.query.filters['session.id'] = scope.session;
        }
        else if (scope.trainer) {
            search.query.filters['trainer.id'] = scope.trainer;
            search.query.filters['userFrom.organization.id'] = scope.$user.organization.id;
        }
        search.query.sorts = {'sendAt': 'desc'};
        search.query.size = 20;
        scope.search = search;
        search.search().then(function() {
            // watch page
            scope.$watch('search.query.page', function(newValue, oldValue) {
                if (newValue != oldValue) {
                    search.search();
                }
            });

            scope.$emit('nbrEmails', search.result.total);
        });
    }

    /**
     * @ngInject
     */
    function entityEmailsController($scope, $dialog, $user)
    {
        $scope.$user = $user;

        /**
         * Display an email
         */
        $scope.dislayEmail = function (id) {
            $dialog.open('email.view', {id: id});
        };
    }
}]);
