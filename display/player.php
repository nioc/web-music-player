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
        <div ng-controller="PlayerController">
            <h1 class="current-title" ng-bind="playlist.tracks[playlist.currentTrack].title"></h1>
            <div class="cover">
                <img class="cover" src="/display/files/images/default_cover.png" alt="cover"/>
            </div>
            <nav class="controls">
                <button class="button-icon control prev" ng-click="player.previous()"><i class="fa fa-fast-backward" title="Previous"></i></button>
                <button class="button-icon control play" ng-click="player.play()" ng-hide="player.isPlaying" title="Play"><i class="fa fa-play"></i></button>
                <button class="button-icon control pause" ng-click="player.pause()" ng-show="player.isPlaying" title="Pause"><i class="fa fa-pause"></i></button>
                <button class="button-icon control next" ng-click="player.next()"><i class="fa fa-fast-forward" title="Next"></i></button>
            </nav>
            <ul class="grid">
                <li class="grid-row" ng-repeat="track in playlist.tracks" ng-class="{current: playlist.currentTrack == $index}">
                    <span class="grid-col" ng-click="player.play($index)" ng-bind="track.title"></span>
                    <span class="grid-col" ng-click="player.play($index)" ng-bind="track.artist.label"></span>
                    <span class="grid-col" ng-click="player.play($index)" ng-bind="track.album.label"></span>
                    <span class="grid-col"><button class="button-icon remove" ng-click="playlist.remove(track)"><i class="fa fa-trash"></i></button></span>
                </li>
            </ul>
            <section class="library">
                <h1>Music Library</h1>
                <ul class="grid">
                    <li class="grid-header">
                        <span class="grid-col">Title</span>
                        <span class="grid-col">Artist</span>
                        <span class="grid-col">Album</span>
                    </li>
                    <li class="grid-row">
                        <span class="grid-col"><input ng-model="library.search.title"  placeholder="Title"  ng-change="library.search.query()"></span>
                        <span class="grid-col"><input ng-model="library.search.artist" placeholder="Artist" ng-change="library.search.query()"></span>
                        <span class="grid-col"><input ng-model="library.search.album"  placeholder="Album"  ng-change="library.search.query()"></span>
                    </li>
                    <li class="grid-row clickable" ng-repeat="track in library.tracks | orderBy:library.order" ng-click="playlist.add(track)">
                        <span class="grid-col" ng-bind="::track.title"></span>
                        <span class="grid-col" ng-bind="::track.artist.label"></span>
                        <span class="grid-col" ng-bind="::track.album.label"></span>
                    </li>
                </ul>
            </section>
        </div>
        <script src="/display/files/vendor/angularjs/angular.min.js"></script>
        <script src="/display/files/vendor/angularjs/angular-resource.min.js"></script>
        <script src="/display/files/wmp.js"></script>
    </body>
</html>
