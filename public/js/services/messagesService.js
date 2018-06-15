(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .factory('Messages', [ messagesService ]);

    function messagesService()
    {
        return {

            clear: function(messageQ) {
                messageQ.length = 0;
            },

            addErrorResponse: function(errorResponse, messageQ) {
                if (errorResponse.type === 'validation_failed') {
                    for (var name in errorResponse.data) {
                        if (!errorResponse.data.hasOwnProperty(name)) {
                            continue;
                        }
                        for (var i = 0; i < errorResponse.data[name].length; ++i) {
                            messageQ.push({ message: errorResponse.data[name][i], type: 'danger' });
                        }
                    }
                } else {
                    messageQ.push({ message: errorResponse.message, type: 'danger' });
                }
            },

            addError: function(message, messageQ) {
                messageQ.push({ message: message, type: 'danger' });
            },

            addWarning: function(message, messageQ) {
                messageQ.push({ message: message, type: 'warning' });
            },

            addSuccess: function(message, messageQ) {
                messageQ.push({ message: message, type: 'success' });
            },

        };
    }

})();
