(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('loginController', [ 'User', 'Messages', '$location', loginController ]);

    function loginController(User, Messages, $location)
    {
        var lc = this;

        lc.credentials = {
            email: '',
            password: ''
        };
        lc.messageQ = [];

        lc.login = function() {
            Messages.clear(lc.messageQ);
            User.login(lc.credentials.email, lc.credentials.password)
                .then(function() {
                    $location.path('/');
                }).catch(function(error) {
                    if (error.type === 'not_verified') {
                        $location.path('/verification');
                        return;
                    }

                    Messages.addErrorResponse(error, lc.messageQ);
               });
        };
    }

})();
