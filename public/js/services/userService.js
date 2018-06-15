(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .factory('User', [ 'Request', '$cookies', '$localStorage', User ]);

    function User(Request, $cookies, $localStorage)
    {
        /////////////////////////////////
        // Private

        var _sessionInfo = null;

        var setSessionInfo = function(newSessionInfo) {
            _sessionInfo = newSessionInfo;
            if (!newSessionInfo) {
                $cookies.remove('sessionInfo');
            } else {
                $cookies.putObject('sessionInfo', newSessionInfo);
            }
        };

        var getSessionInfo = function() {
            if (!_sessionInfo) {
                var sessionInfoFromCookies = $cookies.getObject('sessionInfo');
                if (sessionInfoFromCookies) {
                    _sessionInfo = sessionInfoFromCookies;
                }
            }
            return _sessionInfo;
        };


        /////////////////////////////////
        // Public

        var isLoggedIn = function() {
            return getSessionInfo() !== null;
        };

        var getUserObject = function() {
            var sessionInfo = getSessionInfo();
            return sessionInfo ? Object.assign({}, sessionInfo.user) : null;
        };

        var getManagedUsers = function() {
            var sessionInfo = getSessionInfo();
            return sessionInfo ? sessionInfo.managed_users.slice() : null;
        };

        var getSessionToken = function() {
            var sessionInfo = getSessionInfo();
            if (!sessionInfo || !sessionInfo.hasOwnProperty('token')) {
                return null;
            }
            return sessionInfo.token;
        };

        var login = function(email, password) {
            return Request.post('/api/users/login', { email: email, password: password })
                .then(function (data) {
                    setSessionInfo(data.sessionInfo);
                });
        };

        var logout = function() {
            $localStorage.$reset();
            setSessionInfo(null);
        };

        var signup = function(newUser) {
            return Request.post('/api/users', newUser)
        };

        var save = function(user) {
            var token  = getSessionToken();
            return Request.put('/api/users/' + user.id, user, token)
                .then(function(data) {
                    return Request.get('/api/users/' + user.id + '/session', {}, token)
                        .then(function (data) {
                            setSessionInfo(data.sessionInfo);
                        });
                });
        };

        var setVerified = function(email, verificationCode) {
            return Request.post('/api/users/setVerified', {
                email: email,
                verificationCode: verificationCode
            }).then(function(data) {
                setSessionInfo(data.sessionInfo);
            });
        };

        var resendVerificationCode = function(email) {
            return Request.post('/api/users/resendVerificationCode', {
                email: email
            });
        };

        var sendInvite = function(email) {
            var token  = getSessionToken();
            return Request.post('api/users/' + getUserObject().id + '/invite', { email: email }, token);
        };


        return {
            isLoggedIn:             isLoggedIn,
            getUserObject:          getUserObject,
            getManagedUsers:        getManagedUsers,
            getSessionToken:        getSessionToken,
            login:                  login,
            logout:                 logout,
            signup:                 signup,
            save:                   save,
            setVerified:            setVerified,
            resendVerificationCode: resendVerificationCode,
            sendInvite:             sendInvite

        }
    }

})();
