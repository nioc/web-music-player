/*
 * main AngularJS code for wmp
 * version 1.0.0
 */
'use strict';
angular
.module('wmpApp', ['ngResource', 'ng-sortable'])
//declare player controller
.controller('PlayerController', ['$scope', 'PlaylistItem', 'Library', 'Audio', 'User', '$window', function($scope, PlaylistItem, Library, Audio, User, $window) {
    var playerCtr = this;
    //check user profile
    playerCtr.user = User;
    if (!playerCtr.user.getProfile() || !Number.isInteger(playerCtr.user.id)) {
        $window.location = '/sign';
        //redirect to sign in page
        return false;
    }
    //create player
    var audio = Audio;
    playerCtr.player = {
        isPlaying: false,
        isPaused: false,
        currentTime: 0,
        duration: 0,
        //declare function for playing current track in playlist
        play(trackIndex) {
            if (playerCtr.playlist.tracks.length > 0 && playerCtr.playlist.tracks.length > playerCtr.playlist.currentTrack) {
                if (this.isPaused && !angular.isDefined(trackIndex)) {
                    //resume the playing (only if there is no specific track asked)
                    audio.play();
                } else {
                    //load new track and play it
                    if (angular.isDefined(trackIndex)) {
                        playerCtr.playlist.currentTrack = trackIndex;
                    }
                    //get token and send it in query string
                    var token = playerCtr.user.getToken();
                    var queryParameter = '';
                    if (token) {
                        queryParameter = '?token=' + encodeURIComponent(token);
                    }
                    audio.src = playerCtr.playlist.tracks[playerCtr.playlist.currentTrack].file + queryParameter;
                    audio.play();
                    this.currentTime = 0;
                }
                this.isPlaying = true;
                this.isPaused = false;
            }
        },
        //declare function to pause the playing
        pause() {
            if (this.isPlaying) {
                audio.pause();
                this.isPaused = true;
                this.isPlaying = false;
            }
        },
        //declare function for playing previous track in playlist
        previous() {
            if (!playerCtr.playlist.tracks.length) {
                return;
            }
            this.isPaused=false;
            if (playerCtr.playlist.currentTrack > 0) {
                //go to previous track
                playerCtr.playlist.currentTrack--;
            } else {
                //go to the last track
                playerCtr.playlist.currentTrack = playerCtr.playlist.tracks.length - 1;
            }
            this.play();
        },
        //declare function for playing next track in playlist
        next() {
            if (!playerCtr.playlist.tracks.length) {
                //there is no track to play, stop the playing
                audio.pause();
                this.isPaused = true;
                this.isPlaying = false;
                return;
            }
            this.isPaused = false;
            this.isPlaying = true;
            if (playerCtr.playlist.tracks.length > (playerCtr.playlist.currentTrack + 1)) {
                //go to next track
                playerCtr.playlist.currentTrack++;
            } else {
                //come back to the first track
                playerCtr.playlist.currentTrack = 0;
            }
            this.play(playerCtr.playlist.currentTrack);
        },
        //declare function for seeking in track
        seek() {
            audio.currentTime = this.currentTime;
        }
    };
    //automatic call to the next function when track is ended
    audio.onended = function() {
        $scope.$apply(playerCtr.player.next());
    };
    //automatic update seeker
    audio.ontimeupdate = function() {
        $scope.$apply(playerCtr.player.currentTime = this.currentTime);
    };
    //automatic update seeker max range
    audio.ondurationchange = function() {
        $scope.$apply(playerCtr.player.duration = this.duration);
    };
    //get playlist tracks
    playerCtr.playlist = {
        tracks: PlaylistItem.query({ userId: playerCtr.user.id }),
        currentTrack : 0,
        //declare function to add a track to the user playlist
        add(track) {
            var playlistItem = new PlaylistItem(track);
            playlistItem.userId = playerCtr.user.id;
            PlaylistItem.save(playlistItem, function(data) {
                    //success, apply display change
                playerCtr.playlist.tracks.push(data);
                }, function(error) {
                    //error, alert user
                    alert(error.data.message);
                });
        },
        //declare function to remove a track from the user playlist
        remove(track) {
            track.$delete(function() {
                //success, apply display change
                var trackRemovedIndex = playerCtr.playlist.tracks.indexOf(track);
                var currentTrack = playerCtr.playlist.currentTrack;
                //remove track from the playlist
                playerCtr.playlist.tracks.splice(trackRemovedIndex, 1);
                //update currentTrack index
                if (currentTrack >= trackRemovedIndex) {
                    if (currentTrack >= 0) {
                        playerCtr.playlist.currentTrack--;
                    }
                    //go to next track if the removed track was playing
                    if (playerCtr.player.isPlaying && currentTrack === trackRemovedIndex) {
                        playerCtr.player.next();
                    }
                    if (playerCtr.playlist.currentTrack < 0) {
                        playerCtr.playlist.currentTrack = 0;
                    }
                }
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
    //get library
    playerCtr.library = {
        tracks: [],
        order: ['title','artist'],
        display: false,
        toggleDisplay() {
            this.display = !this.display;
            if (this.display && this.tracks.length === 0) {
                this.search.query();
            }
        },
        search: {
            artist: null,
            album: null,
            title: null,
            displayFilter: {
                artist: false,
                album: false,
                title: false
            },
            query() {
                playerCtr.library.tracks = Library.query({
                    title: this.title,
                    album: this.album,
                    artist: this.artist
                });
            }
        }
    };
    //sort playlist
    playerCtr.playlistSort = {
        draggable: '.track',
        handle: '.track-handle',
        filter: '.grid-header',
        sort: true,
        animation: 1000,
        onUpdate(evt) {
            //apply local change
            if (evt.oldIndex < playerCtr.playlist.currentTrack && evt.newIndex >= playerCtr.playlist.currentTrack) {
                playerCtr.playlist.currentTrack--;
            } else if (evt.oldIndex > playerCtr.playlist.currentTrack && evt.newIndex <= playerCtr.playlist.currentTrack) {
                playerCtr.playlist.currentTrack++;
            } else if (evt.oldIndex === playerCtr.playlist.currentTrack) {
                playerCtr.playlist.currentTrack = evt.newIndex;
            }
            //update playlist on server
            if (evt.newIndex > evt.oldIndex) {
                evt.model.newSequence = playerCtr.playlist.tracks[evt.newIndex-1].sequence;
            } else if (evt.newIndex < evt.oldIndex) {
                evt.model.newSequence = playerCtr.playlist.tracks[evt.newIndex+1].sequence;
            }
            var playlistItem = new PlaylistItem(evt.model);
            PlaylistItem.update(playlistItem, function(data) {
                //success, apply display change
                playerCtr.playlist.tracks = data;
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
}]).
//declare menu controller
controller('MenuController', ['User', '$window', function(User, $window) {
    var menu = this;
    menu.visible = false;
    menu.items = [];
    var existingItems = [
       { require: 'user', label: 'Library', icon: 'fa-archive', link: '/library' },
       { require: 'admin', label: 'Catalog', icon: 'fa-folder-open', link: '/catalog' },
       { require: 'user', label: 'Profile', icon: 'fa-user', link: '/profile' },
       { require: 'admin', label: 'Admin', icon: 'fa-sliders', link: '/admin' },
       { require: 'user', label: 'Sign out', icon: 'fa-sign-out', link: '/sign-out' },
       { require: 'user', label: 'Find an issue ?', icon: 'fa-bug', link: 'https://github.com/nioc/web-music-player/issues/new' },
       { require: 'user', label: 'Contribute', icon: 'fa-code-fork', link: 'https://github.com/nioc/web-music-player#contributing' }
   ];
    menu.toggle = function() {
        this.visible = !this.visible;
        if (this.visible) {
            document.querySelector('.player').style.display = 'none';
        } else {
            document.querySelector('.player').style.display = null;
        }
    };
    //check user profile
    var user = User;
    if (!user.getProfile() || !Number.isInteger(user.id)) {
        $window.location = '/sign';
        //no valid token found, redirect to sign in page
        return false;
    }
    //add links according to user scope
    angular.forEach(existingItems, function(item) {
        if (user.scope.indexOf(item.require) !== -1) {
            menu.items.push(item);
        }
    });
}]).
//declare catalog controller
controller('CatalogController', ['Library', 'Folder', function(Library, Folder) {
    var catalog = this;
    catalog.folders = Folder.query();
    catalog.expandFolder = function (folder) {
        folder.show=!folder.show;
    };
    catalog.addFolder = function (folder) {
        if (folder.path !== '') {
            Library.save({'folder':folder.path}, function(data) {
                //success, apply display change
                //@TODO
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
}])
//declare filter converting duration in seconds into a datetime
.filter('duration', function() {
    return function(seconds) {
        return new Date(1970, 0, 1).setSeconds(seconds);
    };
});
