/*
 * main AngularJS code for wmp
 * version 1.0.0
 */
'use strict';
angular
.module('wmpApp', ['ngResource', 'ng-sortable'])
//declare player controller
.controller('PlayerController', ['$scope', 'PlaylistItem', 'Library', 'Audio', 'User', '$window', function($scope, PlaylistItem, Library, Audio, User, $window) {
    //check user profile
    $scope.user = User;
    if (!$scope.user.getProfile() || !Number.isInteger($scope.user.id)) {
        $window.location = '/sign';
        //redirect to sign in page
        return false;
    }
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
                    //get token and send it in query string
                    var token = $scope.user.getToken();
                    var queryParameter = '';
                    if (token) {
                        queryParameter = '?token=' + encodeURIComponent(token);
                    }
                    audio.src = $scope.playlist.tracks[$scope.playlist.currentTrack].file + queryParameter;
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
        tracks : PlaylistItem.query({userId:$scope.user.id}),
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
    //sort playlist
    $scope.playlistSort = {
        draggable: '.track',
        handle: '.track-handle',
        filter: '.grid-header',
        sort: true,
        animation: 1000,
        onUpdate(evt) {
            //apply local change
            if (evt.oldIndex < $scope.playlist.currentTrack && evt.newIndex >= $scope.playlist.currentTrack) {
                $scope.playlist.currentTrack--;
            } else if (evt.oldIndex > $scope.playlist.currentTrack && evt.newIndex <= $scope.playlist.currentTrack) {
                $scope.playlist.currentTrack++;
            } else if (evt.oldIndex === $scope.playlist.currentTrack) {
                $scope.playlist.currentTrack = evt.newIndex;
            }
            //update playlist on server
            if (evt.newIndex > evt.oldIndex) {
                evt.model.newSequence=$scope.playlist.tracks[evt.newIndex-1].sequence;
            } else if (evt.newIndex < evt.oldIndex) {
                evt.model.newSequence=$scope.playlist.tracks[evt.newIndex+1].sequence;
            }
            var playlistItem = new PlaylistItem(evt.model);
            PlaylistItem.update(playlistItem, function(data) {
                //success, apply display change
                $scope.playlist.tracks=data;
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
}]).
//declare catalog controller
controller('catalogCtrl', ['$scope', 'Library', 'Folder', function($scope, Library, Folder) {
    $scope.folder = '';
    $scope.catalog = {
        folders : Folder.query(),
        expandFolder(folder) {
            folder.show=!folder.show;
        },
        addFolder(folder) {
            if (folder.path !== '') {
                Library.save({'folder':folder.path}, function(data) {
                    //success, apply display change
                    //@TODO
                }, function(error) {
                    //error, alert user
                    alert(error.data.message);
                });
            }
        }
    };
}])
//declare filter converting duration in seconds into a datetime
.filter('duration', function() {
   return function(seconds) {
       return new Date(1970, 0, 1).setSeconds(seconds);
   };
});
