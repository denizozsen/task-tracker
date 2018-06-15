(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('signupController', [ 'User', 'Messages', '$location', signupController ]);

    function signupController(User, Messages, $location)
    {
        var sc = this;

        sc.newUser = {
            name: '',
            email: '',
            password: ''
        };
        sc.messageQ = [];

        sc.submit = function() {
            Messages.clear(sc.messageQ);
            User.signup(sc.newUser)
                .then(function() {
                    $location.path('/verification');
                }).catch(function(error) {
                    Messages.addErrorResponse(error, sc.messageQ);
                });
        };
    }

})();
