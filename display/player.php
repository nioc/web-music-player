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
            <h2 class="current-title" ng-bind="playlist.tracks[playlist.currentTrack].title"></h2>
            <div class="cover">
                <img class="cover" src="/display/files/images/default_cover.png" alt="cover"/>
            </div>
            <nav class="controls">
                <input type="range" id="seek" class="seek" value="0" max="{{player.duration}}" ng-model="player.currentTime" ng-change="player.seek()"/>
                <button class="button-icon control prev" ng-click="player.previous()"><i class="fa fa-fast-backward" title="Previous"></i></button>
                <button class="button-icon control play" ng-click="player.play()" ng-hide="player.isPlaying" title="Play"><i class="fa fa-play"></i></button>
                <button class="button-icon control pause" ng-click="player.pause()" ng-show="player.isPlaying" title="Pause"><i class="fa fa-pause"></i></button>
                <button class="button-icon control next" ng-click="player.next()"><i class="fa fa-fast-forward" title="Next"></i></button>
            </nav>
            <ul class="grid" ng-cloak ng-sortable="playlistSort">
                <li class="grid-header">
                    <span class="grid-cell"></span>
                    <span class="grid-cell">Title</span>
                    <span class="grid-cell">Artist</span>
                    <span class="grid-cell">Album</span>
                    <span class="grid-cell"></span>
                </li>
                <li class="grid-row track" ng-repeat="track in playlist.tracks" ng-class="{current: playlist.currentTrack == $index}">
                    <span class="grid-cell track-handle"><i class="fa fa-ellipsis-v sortable-v" title="Drag and drop to reorder tracks"></i></span>
                    <span class="grid-cell clickable" ng-click="player.play($index)" ng-bind="track.title"></span>
                    <span class="grid-cell clickable" ng-click="player.play($index)" ng-bind="track.artist.label"></span>
                    <span class="grid-cell clickable" ng-click="player.play($index)" ng-bind="track.album.label"></span>
                    <span class="grid-cell"><button class="button-icon remove" ng-click="playlist.remove(track)"><i class="fa fa-trash"></i></button></span>
                </li>
                <li class="grid-row" ng-if="playlist.tracks.length === 0">
                    <span class="grid-cell">No track in playlist, add some from library</span>
                </li>
            </ul>
            <section class="library">
                <h2 ng-click="library.toggleDisplay()" class="clickable"><i class="fa fa-archive"></i>Music Library</h2>
                <ul class="grid" ng-show="library.display" ng-cloak>
                    <li class="grid-header">
                        <span class="grid-cell">
                            <span>Title</span><button class="button-icon" ng-click="library.search.displayFilter.title = !library.search.displayFilter.title"><i class="fa fa-search"></i></button>
                            <input ng-show="library.search.displayFilter.title" ng-model="library.search.title" placeholder="Title" ng-change="library.search.query()" ng-model-options="{ debounce: 1000 }">
                        </span>
                        <span class="grid-cell">
                            <span>Artist</span><button class="button-icon" ng-click="library.search.displayFilter.artist = !library.search.displayFilter.artist"><i class="fa fa-search"></i></button>
                            <input ng-show="library.search.displayFilter.artist" ng-model="library.search.artist" placeholder="Artist" ng-change="library.search.query()" ng-model-options="{ debounce: 1000 }">
                        </span>
                        <span class="grid-cell">
                            <span>Album</span><button class="button-icon" ng-click="library.search.displayFilter.album = !library.search.displayFilter.album"><i class="fa fa-search"></i></button>
                            <input ng-show="library.search.displayFilter.album" ng-model="library.search.album" placeholder="Album" ng-change="library.search.query()" ng-model-options="{ debounce: 1000 }">
                        </span>
                    </li>
                    <li class="grid-row clickable" ng-repeat="track in library.tracks | orderBy:library.order" ng-click="playlist.add(track)">
                        <span class="grid-cell" ng-bind="::track.title"></span>
                        <span class="grid-cell" ng-bind="::track.artist.label"></span>
                        <span class="grid-cell" ng-bind="::track.album.label"></span>
                    </li>
                    <li class="grid-row" ng-if="library.tracks.length === 0">
                        <span class="grid-cell">No results found</span>
                    </li>
                </ul>
            </section>
        </div>
        <script src="/display/files/vendor/angularjs/angular.min.js"></script>
        <script src="/display/files/vendor/angularjs/angular-resource.min.js"></script>
        <script src="/display/files/vendor/Sortable/Sortable.js"></script>
        <script src="/display/files/vendor/Sortable/ng-sortable.js"></script>
        <script src="/display/files/wmp.js"></script>
        <script src="/display/files/playlistItem.factory.js"></script>
        <script src="/display/files/audio.factory.js"></script>
        <script src="/display/files/folder.factory.js"></script>
        <script src="/display/files/library.factory.js"></script>
        <script src="/display/files/user.factory.js"></script>
        <script src="/display/files/authInterceptor.factory.js"></script>
    </body>
</html>
