<!doctype html>
<html ng-app="wmpApp">
    <head>
        <meta charset="utf-8">
        <title>Web Music Player</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <link type="text/css" href="/display/files/vendor/normalize.css/normalize.css" rel="stylesheet"/>
        <link type="text/css" href="/display/files/vendor/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet"/>
        <link type="text/css" href="/display/files/wmp.css" rel="stylesheet"/>
    </head>
    <body>
        <div ng-controller="catalogCtrl">
            <ul class="tree">
                <li ng-repeat="folder in catalog.folders" ng-include="'folder.html'"></li>
            </ul>
            <span ng-bind="catalog.result"></span>
        </div>
    </body>
    <script src="/display/files/vendor/angularjs/angular.min.js"></script>
    <script src="/display/files/vendor/angularjs/angular-resource.min.js"></script>
    <script src="/display/files/wmp.js"></script>
    <script type="text/ng-template" id="folder.html">
    <button class="button-icon" title="Expand folder" ng-click="catalog.expandFolder(folder)"><i style="width:18px;" class="fa folder-icn" ng-class="{'fa-folder-open': folder.show, 'fa-folder': !folder.show}"></i>{{::folder.path}}</button>
    <button class="button-icon" title="Add/sync folder to library" ng-click="catalog.addFolder(folder)"><i style="width:18px;" class="fa fa-plus-circle"></i></button></span>
    <ul class="tree" ng-show="folder.show">
        <li ng-repeat="file in folder.files"><i style="width:18px;" class="fa fa-music music-file-icn"></i>{{::file}}</li>
        <li ng-repeat="folder in folder.subfolders" ng-include="'folder.html'"></li>
    </ul>
    </script>
</html>
