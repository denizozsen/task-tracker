(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('mainController', [ '$location', 'User', mainController ]);

    function mainController($location, User)
    {
        var mc = this;

        mc.showLink = function(linkName) {
            switch (linkName) {
                case '':
                case 'profile':
                    return User.isLoggedIn();
                case 'login':
                case 'signup':
                    return !User.isLoggedIn();
                case 'invite':
                    return User.isLoggedIn() && User.getUserObject().role === 'admin';
                default:
                    return true;
            }
        };

        mc.isLinkActive = function(linkName) {
            return $location.path() === '/' + linkName;
        };

        mc.isUserLoggedIn = function() {
            return User.isLoggedIn();
        };

        mc.logout = function() {
            User.logout();
            $location.path('/login');
        };

        mc.getLoggedInAs = function() {
            var user = User.getUserObject()
            return user ? user.name : null;
        };
    }

})();
