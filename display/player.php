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
        <div ng-controller="playerCtrl" id="container">
            <h1>{{playlist.tracks[playlist.currentTrack].title}}</h1>
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
                    <span class="grid-col-4 track-title" ng-click="player.play($index)">{{track.title}}</span>
                    <span class="grid-col-4 artist-name" ng-click="player.play($index)">{{track.artist}}</span>
                    <span class="grid-col-4 album-title" ng-click="player.play($index)">{{track.album}}</span>
                    <span class="grid-col-4"><button class="button-icon remove" ng-click="playlist.remove(track)"><i class="fa fa-trash"></i></button></span>
                </li>
            </ul>
            <section class="library">
                <h1>Music Library</h1>
                <ul class="grid">
                    <li class="grid-row">
                        <span class="grid-header grid-col-3">Title</span>
                        <span class="grid-header grid-col-3">Artist</span>
                        <span class="grid-header grid-col-3">Album</span>
                    </li>
                    <li class="grid-row">
                        <span class="grid-col-3"><input ng-model="library.search.title" ng-change="library.search.query()" placeholder="Title"></span>
                        <span class="grid-col-3"><input ng-model="library.search.artist" placeholder="Artist"></span>
                        <span class="grid-col-3"><input ng-model="library.search.album" placeholder="Album"></span>
                    </li>
                    <li class="grid-row clickable" ng-repeat="track in library.tracks | orderBy:library.order" ng-click="playlist.add(track)">
                        <span class="grid-col-3">{{track.title}}</span>
                        <span class="grid-col-3">{{track.artist}}</span>
                        <span class="grid-col-3">{{track.album}}</span>
                    </li>
                </ul>
            </section>
        </div>
        <script src="/display/files/vendor/angularjs/angular.min.js"></script>
        <script src="/display/files/vendor/angularjs/angular-resource.min.js"></script>
        <script src="/display/files/wmp.js"></script>
    </body>
</html>