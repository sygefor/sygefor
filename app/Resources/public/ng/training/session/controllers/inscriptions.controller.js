/**
 * Core List Controller
 */
sygeforApp
    .filter('match', function () {
        return function (inscriptions, filter) {
            if (typeof filter.status === "undefined") {
                return inscriptions;
            }

            var entities = [];
            for (var key in inscriptions) {
                var inscription = inscriptions[key];
                if (inscription[filter.status].id === filter.id) {
                    entities.push(inscription);
                }
            }

            return entities;
        };
    })
    .controller('SessionInscriptionsController', ['$scope', '$dialog', '$filter', '$taxonomy', '$timeout', '$q', function ($scope, $dialog, $filter, $taxonomy, $timeout, $q) {
        // initialize status list
        $scope.status = {
            'inscription': [],
            'presence': []
        };

        $scope.filter = {};
        $scope.filterLabel = null;
        $scope.stats = {};

        // fetch all status and count
        $q.all([
            $taxonomy.getIndexedTerms('sygefor_core.vocabulary_inscription_status'),
            $taxonomy.getIndexedTerms('sygefor_core.vocabulary_presence_status')
        ]).then(function (status) {
            $scope.status.inscription = status[0];
            $scope.status.presence = status[1];
            recalculateStats();
        });

        /**
         * Calculate inscription stats
         */
        var recalculateStats = function () {
            var inscriptionInscriptionStatus = [];
            var inscriptionPresenceStatus = [];
            for (var key in $scope.session.inscriptions) {
                var inscription = $scope.session.inscriptions[key];
                if (inscription.inscriptionStatus) {
                    if (typeof inscriptionInscriptionStatus[inscription.inscriptionStatus.id] === "undefined") {
                        inscriptionInscriptionStatus[inscription.inscriptionStatus.id] = 0;
                    }
                    inscriptionInscriptionStatus[inscription.inscriptionStatus.id]++;
                }
                if (inscription.presenceStatus) {
                    if (typeof inscriptionPresenceStatus[inscription.presenceStatus.id] === "undefined") {
                        inscriptionPresenceStatus[inscription.presenceStatus.id] = 0;
                    }
                    inscriptionPresenceStatus[inscription.presenceStatus.id]++;
                }
            }

            for (var key in $scope.status.inscription) {
                if (typeof inscriptionInscriptionStatus[$scope.status.inscription[key].id] !== "undefined") {
                    $scope.status.inscription[key].count = inscriptionInscriptionStatus[$scope.status.inscription[key].id];
                } else {
                    $scope.status.inscription[key].count = 0;
                }
            }
            for (var key in $scope.status.presence) {
                if (typeof inscriptionPresenceStatus[$scope.status.presence[key].id] !== "undefined") {
                    $scope.status.presence[key].count = inscriptionPresenceStatus[$scope.status.presence[key].id];
                } else {
                    $scope.status.presence[key].count = 0;
                }
            }
        };

        /**
         * open an inscription creation window, then process the return by adding inscription
         * @param session
         */
        $scope.addInscription = function () {
            $dialog.open('inscription.create', {session: $scope.session}).then(function (data) {
                $scope.session.inscriptions.push(data.inscription);
                recalculateStats();
            });
        }

        /**
         * Open the inscription status dialog
         *
         * @param inscription
         * @param status
         * @returns {promise|*|promise|promise|promise|promise}
         */
        $scope.updateInscriptionStatus = function (inscription, status) {
            return $dialog.open('inscription.changeStatus', {
                items: [inscription.id],
                inscriptionStatus: status
            }).then(function (data) {
                inscription.inscriptionStatus = data[0].inscriptionStatus;
                recalculateStats();
            });
        };

        /**
         * Open the presence status dialog
         *
         * @param inscription
         * @param status
         * @returns {promise|*|promise|promise|promise|promise}
         */
        $scope.updatePresenceStatus = function (inscription, status) {
            return $dialog.open('inscription.changeStatus', {
                items: [inscription.id],
                presenceStatus: status
            }).then(function () {
                inscription.presenceStatus = status;
                recalculateStats();
            });
        }

        /**
         * Remove the inscription
         */
        $scope.delete = function (inscription) {
            $dialog.open('inscription.delete', {id: inscription.id}).then(function () {
                $scope.session.inscriptions.splice($scope.session.inscriptions.indexOf(inscription), 1);
                recalculateStats();
            });
        }

        /**
         * Get the total accepted inscriptions count
         */
        $scope.totalAcceptedInscriptions = function () {
            return $filter('filter')($scope.session.inscriptions, {inscriptionStatus: {status: 2}}).length;
        }

        /**
         * Set the filter to a partiicular status
         */
        $scope.filterByStatus = function (type, status) {
            var ids = [];
            for (var key in $scope.session.inscriptions) {
                if ($scope.session.inscriptions[key][type+'Status'].id == status.id) {
                    ids.push($scope.session.inscriptions[key].id);
                }
            }
            $scope.resetFilter();
            $scope.filter = {
                'status': type + 'Status',
                id: status.id
            };
            $scope.filterLabel = status.name + '(' + status.count + ')';
        };

        /**
         * Reset the filter
         */
        $scope.resetFilter = function () {
            $scope.filter = {};
            $scope.filterLabel = null;
        };
    }]);
