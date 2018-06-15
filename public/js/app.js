(function() {
    'use strict';

    var taskTrackerApp = angular.module('taskTrackerApp', [
        'ngRoute',
        'ngCookies',
        'ngStorage',
        'ngFileUpload'
    ]);

    taskTrackerApp.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
            when('/', {
                templateUrl: '/templates/tasks.html',
                controller: 'tasksController',
                controllerAs: 'ec'
            })
            .when('/profile', {
                templateUrl: '/templates/profile.html',
                controller: 'profileController',
                controllerAs: 'pc'
            })
            .when('/invite', {
                templateUrl: '/templates/invite.html',
                controller: 'inviteController',
                controllerAs: 'ic'
            })
            .when('/login', {
               templateUrl: '/templates/login.html',
               controller: 'loginController',
               controllerAs: 'lc'
            })
            .when('/signup', {
               templateUrl: '/templates/signup.html',
               controller: 'signupController',
               controllerAs: 'sc'
            })
            .when('/verification', {
                templateUrl: '/templates/verification.html',
                controller: 'verificationController',
                controllerAs: 'vc'
            })
            .otherwise({
                redirectTo: '/'
            });
        }
    ]);

    // This fixes hash-urls in anchor tags
    taskTrackerApp.config(['$locationProvider', function($locationProvider) {
        $locationProvider.hashPrefix('');
    }]);

})();
