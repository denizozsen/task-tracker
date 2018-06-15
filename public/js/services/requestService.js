(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .factory('Request', requestService, ['$http', '$q', 'Upload']);

    function requestService($http, $q, Upload)
    {
        var makeRequest = function(options, token, upload) {
            if (token) {
                addAuthHeader(options, token);
            }

            var fn = upload ? Upload.upload : $http;

            return fn(options)
                .then(function(response) {
                    if (!response.data.success) {
                        return $q.reject(response.data.error);
                    }
                    return response.data;
                }).catch(function(response) {
                    var error = response.data.hasOwnProperty('error') ? response.data.error : {
                        type: '',
                        error: 'An error occurred'
                    };
                    return $q.reject(error);
                });
        };

        var addAuthHeader = function(requestOptions, token) {
            if (!token) {
                return;
            }

            requestOptions.headers = { Authorization: 'Bearer ' + token };
        };

        var getUserId = function(requestOptions) {
            if (requestOptions.hasOwnProperty('data') && requestOptions.data.hasOwnProperty('userId')) {
                return requestOptions.data.userId;
            }
            if (requestOptions.hasOwnProperty('params') && requestOptions.params.hasOwnProperty('userId')) {
                return requestOptions.params.userId;
            }
            return 0;
        };

        return {

            get: function (url, data, token) {
                data = data || {};
                return makeRequest({
                    method: 'GET',
                    params: data,
                    url: url
                }, token);
            },

            post: function (url, data, token) {
                return makeRequest({
                    method: 'POST',
                    url: url,
                    data: data
                }, token);
            },

            put: function (url, data, token) {
                return makeRequest({
                    method: 'PUT',
                    url: url,
                    data: data
                }, token);
            },

            delete: function (url, token) {
                return makeRequest({
                    method: 'DELETE',
                    url: url
                }, token);
            },

            upload: function (url, data, token) {
                return makeRequest({
                    url: url,
                    data: data
                }, token, true);

            }

        };
    }

})();
