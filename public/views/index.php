<!doctype html>

<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Task Tracker</title>

        <link rel="stylesheet"
              href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
              integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
              crossorigin="anonymous">
        <link rel="stylesheet" href="/css/main.css">

        <script src="js/lib/sha256.min.js"></script>
        <script src="js/node_modules/angular/angular.js"></script>
        <script src="js/node_modules/angular-route/angular-route.js"></script>
        <script src="js/node_modules/angular-cookies/angular-cookies.js"></script>
        <script src="js/node_modules/ngstorage/ngStorage.min.js"></script>
        <script src="js/node_modules/ng-file-upload/dist/ng-file-upload-all.min.js"></script>

        <script src="js/app.js"></script>
        <script src="js/services/requestService.js"></script>
        <script src="js/services/userService.js"></script>
        <script src="js/services/messagesService.js"></script>
        <script src="js/services/taskService.js"></script>
        <script src="js/controllers/mainController.js"></script>
        <script src="js/controllers/tasksController.js"></script>
        <script src="js/controllers/profileController.js"></script>
        <script src="js/controllers/loginController.js"></script>
        <script src="js/controllers/signupController.js"></script>
        <script src="js/controllers/verificationController.js"></script>
        <script src="js/controllers/inviteController.js"></script>

    </head>

    <body class="container" ng-app="taskTrackerApp" ng-controller="mainController as mc">

        <h1>Task Tracker</h1>

        <div>
            <a ng-show="mc.showLink('')"        ng-href="#/"        ng-class="{active: mc.isLinkActive('')}">Tasks</a>
            <a ng-show="mc.showLink('profile')" ng-href="#/profile" ng-class="{active: mc.isLinkActive('profile')}">Edit Profile</a>
            <a ng-show="mc.showLink('invite')"  ng-href="#/invite" ng-class="{active: mc.isLinkActive('invite')}">Invite</a>
            <a ng-show="mc.showLink('login')"   ng-href="#/login"   ng-class="{active: mc.isLinkActive('login')}">Login</a>
            <a ng-show="mc.showLink('signup')"  ng-href="#/signup"  ng-class="{active: mc.isLinkActive('signup')}">Sign Up</a>
            <span ng-show="mc.isUserLoggedIn()" class="pull-right">
                <span class="logged-in-as">Logged in as: {{ mc.getLoggedInAs() }}</span>
                <a href="#" ng-click="mc.logout()">Logout</a>
            </span>
        </div>

        <div ng-view></div>

    </body>

</html>
