/*
 * main AngularJS code for wmp
 * version 1.0.0
 */
'use strict';
var wmpApp = angular.module('wmpApp', ['ngResource']);

//declare player controller
wmpApp.controller('PlayerController', ['$scope', 'PlaylistItem', 'Library', 'Audio', function($scope, PlaylistItem, Library, Audio) {
    //create user profile
    //@TODO call profile after signin
    $scope.user = {id:'1'};
    //create player
    var audio = Audio;
    $scope.player = {
        isPlaying:false,
        isPaused:false,
        currentTime:0,
        duration:0,
        //declare function for playing current track in playlist
        play(trackIndex) {
            if ($scope.playlist.tracks.length > 0 && $scope.playlist.tracks.length > $scope.playlist.currentTrack) {
                if (this.isPaused && !angular.isDefined(trackIndex)) {
                    //resume the playing (only if there is no specific track asked)
                    audio.play();
                } else {
                    //load new track and play it
                    if (angular.isDefined(trackIndex)) {
                        $scope.playlist.currentTrack = trackIndex;
                    }
                    audio.src=$scope.playlist.tracks[$scope.playlist.currentTrack].file;
                    audio.play();
                    this.currentTime = 0;
                }
                this.isPlaying=true;
                this.isPaused=false;
            }
        },
        //declare function to pause the playing
        pause() {
            if (this.isPlaying) {
                audio.pause();
                this.isPaused=true;
                this.isPlaying=false;
            }
        },
        //declare function for playing previous track in playlist
        previous() {
            if (!$scope.playlist.tracks.length) {
                return;
            }
            this.isPaused=false;
            if ($scope.playlist.currentTrack > 0) {
                //go to previous track
                $scope.playlist.currentTrack--;
            } else {
                //go to the last track
                $scope.playlist.currentTrack = $scope.playlist.tracks.length - 1;
            }
            this.play();
        },
        //declare function for playing next track in playlist
        next() {
            if (!$scope.playlist.tracks.length) {
                //there is no track to play, stop the playing
                audio.pause();
                this.isPaused=true;
                this.isPlaying=false;
                return;
            }
            this.isPaused=false;
            this.isPlaying=true;
            if ($scope.playlist.tracks.length > ($scope.playlist.currentTrack + 1)) {
                //go to next track
                $scope.playlist.currentTrack++;
            } else {
                //come back to the first track
                $scope.playlist.currentTrack = 0;
            }
            this.play($scope.playlist.currentTrack);
        },
        //declare function for seeking in track
        seek() {
            audio.currentTime = this.currentTime;
        }
    };
    //automatic call to the next function when track is ended
    audio.onended=function() {
        $scope.$apply($scope.player.next());
    };
    //automatic update seeker
    audio.ontimeupdate=function() {
        $scope.$apply($scope.player.currentTime = this.currentTime);
    };
    //automatic update seeker max range
    audio.ondurationchange=function() {
        $scope.$apply($scope.player.duration = this.duration);
    };
    //get playlist tracks
    $scope.playlist = {
        tracks : PlaylistItem.query({userId:1}),
        currentTrack : 0,
        //declare function to add a track to the user playlist
        add(track) {
            var playlistItem = new PlaylistItem(track);
            playlistItem.userId=$scope.user.id;
            PlaylistItem.save(playlistItem, function(data) {
                    //success, apply display change
                    $scope.playlist.tracks.push(data);
                }, function(error) {
                    //error, alert user
                    alert(error.data.message);
                });
        },
        //declare function to remove a track from the user playlist
        remove(track) {
            track.$delete(function(){
                //success, apply display change
                var trackRemovedIndex = $scope.playlist.tracks.indexOf(track);
                var currentTrack = $scope.playlist.currentTrack;
                //remove track from the playlist
                $scope.playlist.tracks.splice(trackRemovedIndex, 1);
                //update currentTrack index
                if (currentTrack >= trackRemovedIndex) {
                    if (currentTrack >= 0) {
                        $scope.playlist.currentTrack--;
                    }
                    //go to next track if the removed track was playing
                    if ($scope.player.isPlaying && currentTrack === trackRemovedIndex) {
                        $scope.player.next();
                    }
                    if ($scope.playlist.currentTrack < 0) {
                        $scope.playlist.currentTrack = 0;
                    }
                }
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
    //get library
    $scope.library = {
        tracks : [],
        order : ['title','artist'],
        display : false,
        toggleDisplay() {
            this.display = !this.display;
            if (this.display && $scope.library.tracks.length === 0) {
                this.search.query();
            }
        },
        search : {
            artist : null,
            album : null,
            title : null,
            displayFilter : {
                artist : false,
                album : false,
                title : false
            },
            query() {
                $scope.library.tracks = Library.query({
                    title  : this.title,
                    album  : this.album,
                    artist : this.artist
                });
            }
        }
    };
}]);

//declare catalog controller
wmpApp.controller('catalogCtrl', ['$scope', 'Library', 'Folder', function($scope, Library, Folder) {
    $scope.folder = '';
    $scope.catalog = {
        folders : Folder.query(),
        expandFolder(folder) {
            folder.show=!folder.show;
        },
        addFolder(folder) {
            if (folder.path != '') {
                Library.save({'folder':folder.path}, function(data) {
                    //success, apply display change
                    //@TODO
                }, function(error) {
                    //error, alert user
                    alert(error.data.message);
                });
            }
        }
    }
}]);

//return a PlaylistItem object
wmpApp.factory('PlaylistItem', function($resource) {
    return $resource('/server/api/users/:userId/playlist/tracks/:sequence', {userId:'@userId', sequence:'@sequence'});
});

//return a Library object
wmpApp.factory('Library', function($resource) {
    return $resource('/server/api/library/tracks:id', null,
    {
      'save': { method:'POST', isArray:true }
    });
});

//return a Folder object
wmpApp.factory('Folder', function($resource) {
    return $resource('/server/api/library/folders:id');
});

// return an HTML audio object
wmpApp.factory('Audio', function($document) {
    return $document[0].createElement('audio');
});
