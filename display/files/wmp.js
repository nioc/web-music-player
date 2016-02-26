/*
 * main AngularJS code for wmp
 * version 1.0.0
 */
'use strict';
angular
//declare module and dependencies
.module('wmpApp', ['ngResource', 'ngRoute', 'ng-sortable', 'angular-loading-bar', 'ngAnimate'])
//declare configuration
.config(config)
//declare playlist service
.service('Playlist', ['LocalUser', 'PlaylistItem', Playlist])
//declare player controller
.controller('PlayerController', ['$scope', 'Playlist', 'PlaylistItem', 'Audio', 'LocalUser', '$window', PlayerController])
//declare menu controller
.controller('MenuController', ['LocalUser', '$window', '$scope', MenuController])
//declare library controller
.controller('LibraryController', ['Library', 'Playlist', LibraryController])
//declare catalog controller
.controller('CatalogController', ['Library', 'Folder', '$q', CatalogController])
//declare sign-out controller
.controller('SignOutController', ['LocalUser', '$window', SignOutController])
//declare profile controller
.controller('UserController', ['LocalUser', 'User', '$routeParams', UserController])
//declare users management controller
.controller('UsersController', ['User', UsersController])
//declare album controller
.controller('AlbumController', ['$routeParams', '$location', 'Playlist', 'Album', AlbumController])
//declare artist controller
.controller('ArtistController', ['$routeParams', '$location', 'Playlist', 'Artist', ArtistController])
//declare filter converting duration in seconds into a datetime
.filter('duration', duration);
//playlist function
function Playlist(LocalUser, PlaylistItem) {
    var playlist = this;
    //get tracks
    playlist.tracks = PlaylistItem.query({userId: LocalUser.id});
    //initialize current track
    playlist.currentTrack = 0;
    //declare function for add track in playlist
    playlist.add = add;
    //function to add a track to the user playlist
    function add(track) {
        var playlistItem = new PlaylistItem(track);
        playlistItem.userId = LocalUser.id;
        PlaylistItem.save(playlistItem, function(data) {
            //success, add to playlist
            playlist.tracks.push(data);
        }, function(error) {
            //error, alert user
            alert(error.data.message);
        });
    }
}
//PlayerController function
function PlayerController($scope, Playlist, PlaylistItem, Audio, LocalUser, $window) {
    var player = this;
    //check user profile
    player.user = LocalUser;
    if (!player.user.getProfile() || !Number.isInteger(player.user.id)) {
        $window.location = '/sign';
        //redirect to sign in page
        return false;
    }
    //create player
    var audio = Audio;
    player.isPlaying = false;
    player.isPaused = false;
    player.currentTime = 0;
    player.duration = 0;
    player.coverPath = '/display/files/images/default_cover.png';
    //declare functions for controlling player
    player.play = play;
    player.pause = pause;
    player.previous = previous;
    player.next = next;
    player.seek = seek;
    //automatic handlers
    audio.onended = onEnded;
    audio.ontimeupdate = onTimeUpdate;
    audio.ondurationchange = onDurationChange;
    //link playlist to Playlist service
    player.playlist = Playlist;
    //add to PLaylist service the removing track function
    player.playlist.remove = remove;
    //sort playlist
    player.playlistSort = {
        draggable: '.track',
        handle: '.track-handle',
        filter: '.grid-header',
        sort: true,
        animation: 1000,
        onUpdate(evt) {
            //apply local change
            if (evt.oldIndex < player.playlist.currentTrack && evt.newIndex >= player.playlist.currentTrack) {
                player.playlist.currentTrack--;
            } else if (evt.oldIndex > player.playlist.currentTrack && evt.newIndex <= player.playlist.currentTrack) {
                player.playlist.currentTrack++;
            } else if (evt.oldIndex === player.playlist.currentTrack) {
                player.playlist.currentTrack = evt.newIndex;
            }
            //update playlist on server
            if (evt.newIndex > evt.oldIndex) {
                evt.model.newSequence = player.playlist.tracks[evt.newIndex - 1].sequence;
            } else if (evt.newIndex < evt.oldIndex) {
                evt.model.newSequence = player.playlist.tracks[evt.newIndex + 1].sequence;
            }
            var playlistItem = new PlaylistItem(evt.model);
            PlaylistItem.update(playlistItem, function(data) {
                //success, apply display change
                player.playlist.tracks = data;
            }, function(error) {
                //error, alert user
                alert(error.data.message);
            });
        }
    };
    //function for playing current track in playlist
    function play(trackIndex) {
        if (this.playlist.tracks.length > 0 && this.playlist.tracks.length > this.playlist.currentTrack) {
            if (this.isPaused && !angular.isDefined(trackIndex)) {
                //resume the playing (only if there is no specific track asked)
                audio.play();
            } else {
                //load new track and play it
                if (angular.isDefined(trackIndex)) {
                    this.playlist.currentTrack = trackIndex;
                }
                //get token and send it in query string
                var token = this.user.getToken();
                var queryParameter = '';
                if (token) {
                    queryParameter = '?token=' + encodeURIComponent(token);
                }
                audio.src = this.playlist.tracks[this.playlist.currentTrack].file + queryParameter;
                audio.play();
                if (this.playlist.tracks[this.playlist.currentTrack].album.coverPath) {
                    player.coverPath = this.playlist.tracks[this.playlist.currentTrack].album.coverPath;
                } else {
                    player.coverPath = '/display/files/images/default_cover.png';
                }
                this.currentTime = 0;
            }
            this.isPlaying = true;
            this.isPaused = false;
        }
    }
    //function to pause the playing
    function pause() {
        if (this.isPlaying) {
            audio.pause();
            this.isPaused = true;
            this.isPlaying = false;
        }
    }
    //function for playing previous track in playlist
    function previous() {
        if (!this.playlist.tracks.length) {
            return;
        }
        this.isPaused = false;
        if (this.playlist.currentTrack > 0) {
            //go to previous track
            this.playlist.currentTrack--;
        } else {
            //go to the last track
            this.playlist.currentTrack = this.playlist.tracks.length - 1;
        }
        this.play();
    }
    //function for playing next track in playlist
    function next() {
        if (!this.playlist.tracks.length) {
            //there is no track to play, stop the playing
            audio.pause();
            this.isPaused = true;
            this.isPlaying = false;
            return;
        }
        this.isPaused = false;
        this.isPlaying = true;
        if (this.playlist.tracks.length > (this.playlist.currentTrack + 1)) {
            //go to next track
            this.playlist.currentTrack++;
        } else {
            //come back to the first track
            this.playlist.currentTrack = 0;
        }
        this.play(this.playlist.currentTrack);
    }
    //function for seeking in track
    function seek() {
        audio.currentTime = this.currentTime;
    }
    //function to remove a track from the user playlist
    function remove(track) {
        track.$delete(function() {
            //success, apply display change
            var trackRemovedIndex = player.playlist.tracks.indexOf(track);
            var currentTrack = player.playlist.currentTrack;
            //remove track from the playlist
            player.playlist.tracks.splice(trackRemovedIndex, 1);
            //update currentTrack index
            if (currentTrack >= trackRemovedIndex) {
                if (currentTrack >= 0) {
                    player.playlist.currentTrack--;
                }
                //go to next track if the removed track was playing
                if (player.isPlaying && currentTrack === trackRemovedIndex) {
                    player.next();
                }
                if (player.playlist.currentTrack < 0) {
                    player.playlist.currentTrack = 0;
                }
            }
        }, function(error) {
            //error, alert user
            alert(error.data.message);
        });
    }
    //automatic call to the next function when track is ended
    function onEnded() {
        $scope.$apply(player.next());
    }
    //automatic update seeker
    function onTimeUpdate() {
        $scope.$apply(player.currentTime = this.currentTime);
    }
    //automatic update seeker max range
    function onDurationChange() {
        $scope.$apply(player.duration = this.duration);
    }
}
//LibraryController function
function LibraryController(Library, Playlist) {
    var librarys = this;
    //get library
    librarys.tracks = [];
    librarys.order = ['title', 'album', 'artist'];
    librarys.tracksFiltered = [];
    librarys.pagesCount = 1;
    librarys.currentPage = 1;
    librarys.itemsPerPage = 50;
    librarys.setPage = setPage;
    librarys.search = {
        artist: null,
        album: null,
        title: null,
        displayFilter: {
            artist: false,
            album: false,
            title: false
        },
        query() {
            librarys.tracks = Library.query({
                title: this.title,
                album: this.album,
                artist: this.artist
            }, function() {
                //update pagination system when query ends
                librarys.currentPage = 1;
                updateFilteredItems();
            });
        }
    };
    //add link to Playlist service ("add track to playlist" function)
    librarys.add = Playlist.add;
    librarys.search.query();
    //declare function for setting page number
    function setPage(currentPage) {
        librarys.currentPage = currentPage;
        updateFilteredItems();
    }
    //declare function for update pagination system
    function updateFilteredItems() {
        var begin = ((librarys.currentPage - 1) * librarys.itemsPerPage);
        var end = begin + librarys.itemsPerPage;
        librarys.tracksFiltered = librarys.tracks.slice(begin, end);
        librarys.pagesCount = Math.ceil(librarys.tracks.length / librarys.itemsPerPage);
    }
}
//MenuController function
function MenuController(LocalUser, $window, $scope) {
    var menu = this;
    menu.visible = false;
    menu.items = [];
    var existingItems = [
        {require: 'user', label: 'Player', icon: 'fa-headphones', link: '#/player'},
        {require: 'user', label: 'Library', icon: 'fa-archive', link: '#/library'},
        {require: 'admin', label: 'Catalog', icon: 'fa-folder-open', link: '#/catalog'},
        {require: 'user', label: 'Profile', icon: 'fa-user', link: '#/profile'},
        {require: 'admin', label: 'Users management', icon: 'fa-users', link: '#/users'},
        {require: 'admin', label: 'Admin', icon: 'fa-sliders', link: '#/admin'},
        {require: 'user', label: 'Sign out', icon: 'fa-sign-out', link: '#/sign-out'},
        {require: 'user', label: 'Find an issue ?', icon: 'fa-bug', link: 'https://github.com/nioc/web-music-player/issues/new'},
        {require: 'user', label: 'Contribute', icon: 'fa-code-fork', link: 'https://github.com/nioc/web-music-player#contributing'}
   ];
    menu.currentPage = existingItems[0];
    menu.toggle = toggle;
    //check user profile
    var user = LocalUser;
    if (!user.getProfile() || !Number.isInteger(user.id)) {
        $window.location = '/sign';
        //no valid token found, redirect to sign in page
        return false;
    }
    //add links according to user scope
    var scope = user.scope.split(' ');
    angular.forEach(existingItems, function(item) {
        if (scope.indexOf(item.require) !== -1) {
            item.isCurrentPage = isCurrentPage;
            item.setCurrentPage = setCurrentPage;
            menu.items.push(item);
        }
    });
    //location listener
    $scope.$on('$locationChangeSuccess', locationChangeSuccess);
    //toggle menu display
    function toggle() {
        this.visible = !this.visible;
    }
    //highlight current page
    function isCurrentPage() {
        return this.link === $window.location.hash;
    }
    //store the next page and hide menu
    function setCurrentPage() {
        menu.currentPage = this;
        menu.toggle();
    }
    function locationChangeSuccess(event) {
        //browser location detected, check if menu is synchronized
        if (menu.currentPage.link !== $window.location.hash) {
            //try to found the active item
            var i = 0;
            var itemFound = false;
            while (i < existingItems.length && !itemFound) {
                if (existingItems[i].link === $window.location.hash) {
                    //active item found, update menu.currentPage
                    itemFound = true;
                    menu.currentPage = existingItems[i];
                }
                i++;
            }
            if (!itemFound) {
                //active item not found, apply default values
                menu.currentPage = {require: 'user', label: 'WMP', icon: 'fa-headphones', link: $window.location.hash};
            }
        }
    }
}
//CatalogController function
function CatalogController(Library, Folder, $q) {
    var catalog = this;
    catalog.result = '';
    catalog.isProcessing = false;
    catalog.progress = 0;
    catalog.folders = Folder.query();
    catalog.expandFolder = function(folder) {
        folder.show = !folder.show;
    };
    catalog.addFolder = function(folder) {
        catalog.result = 'Processing, please wait.';
        //retrieve subfolders with files
        var folderPathsWithFiles = new Array();
        var filesCounter = 0;
        var filesProcessed = 0;
        catalog.isProcessing = true;
        //declare recursive function for retreiving folders with files
        function handleFolder(folder) {
            if (folder.files.length > 0) {
                //there are files under this subfolder, add it to the array and update files counter
                folderPathsWithFiles.push(folder.path);
                filesCounter += folder.files.length;
            }
            for (var i = 0; i < folder.subfolders.length; i++) {
                handleFolder(folder.subfolders[i]);
            }
        }
        //call recursive function
        handleFolder(folder);
        //sequential calls API for each folder
        var previous = $q.when(null);
        for (var i = 0; i < folderPathsWithFiles.length; i++) {
            (function(i) {
                previous = previous.then(function() {
                    return Library.save({'folder': folderPathsWithFiles[i]}, function(data) {
                        //success, display progression
                        filesProcessed += data.length;
                        catalog.progress = parseInt(100 * filesProcessed / filesCounter);
                        catalog.result = 'Processing, please wait (' + catalog.progress + '%)';
                    }).$promise;
                });
            }(i));
        }
        previous.then(function() {
            //success, remove popin and display end message
            catalog.result = 'Tracks processing is done';
            catalog.isProcessing = false;
        }, function(error) {
            //error, remove popin and display error message
            catalog.result = 'Tracks processing encounter an error: ' + error.data.message + ' on ' + error.config.data.folder;
            catalog.isProcessing = false;
        });
    };
}
//SignOutController function
function SignOutController(LocalUser, $window) {
    LocalUser.deleteToken();
    $window.location = '/sign';
}
//ProfileController function
function UserController(LocalUser, User, $routeParams) {
    var profile = this;
    profile.result = {text: '', class: ''};
    profile.submit = submit;
    if ($routeParams && $routeParams.id) {
        if (Number.isInteger(parseInt($routeParams.id))) {
            //edit existing user, get his profile from url id parameter
            profile.user = User.get({id: $routeParams.id});
            profile.title = 'Edit user';
            profile.scopeEditable = true;
        } else {
            //add user form
            profile.user = new User();
            profile.submit = addUser;
            profile.title = 'Create user';
            profile.scopeEditable = true;
        }
    } else {
        //edit current user, get his local profile
        LocalUser.getProfile();
        profile.user = User.get({id: LocalUser.id});
        profile.title = 'Edit your profile';
        profile.scopeEditable = false;
    }
    //function for creating user profile
    function addUser() {
        function successCallback() {
            profile.result.text = 'Profile successfully created';
            profile.result.class = 'form-valid';
        }
        function errorCallback(response) {
            profile.result.text = 'Error, profile not created';
            if (response.data && response.data.message) {
                profile.result.text = response.data.message;
            }
            profile.result.class = 'form-error';
        }
        profile.user.$save(successCallback, errorCallback);
    }
    //function for saving user profile modifications
    function submit() {
        function successCallback() {
            profile.result.text = 'Profile successfully updated';
            profile.result.class = 'form-valid';
        }
        function errorCallback(response) {
            profile.result.text = 'Error, profile not updated';
            if (response.data && response.data.message) {
                profile.result.text = response.data.message;
            }
            profile.result.class = 'form-error';
        }
        profile.user.$update(successCallback, errorCallback);
    }
}
//UsersController function
function UsersController(User) {
    var usersManagement = this;
    usersManagement.users = User.query();
}
//AlbumController function
function AlbumController($routeParams, $location, Playlist, Album) {
    var album = this;
    album.album = Album.get({id: $routeParams.id});
    album.remove = remove;
    //add link to Playlist service ("add track to playlist" function)
    album.add = Playlist.add;
    function remove() {
        if (confirm('This will delete "' + album.album.name + '" album from the library, are you sure?')) {
            album.album.$delete(function() {$location.path('/library').replace();}, function(error) {alert(error.data.message);});
        }
    }
}
//ArtistController function
function ArtistController($routeParams, $location, Playlist, Artist) {
    var artist = this;
    artist.artist = Artist.get({id: $routeParams.id});
    artist.remove = remove;
    //add link to Playlist service ("add track to playlist" function)
    artist.add = Playlist.add;
    function remove() {
        if (confirm('This will delete "' + artist.artist.name + '" artist from the library and all his tracks, are you sure?')) {
            artist.artist.$delete(function() {$location.path('/library').replace();}, function(error) {alert(error.data.message);});
        }
    }
}
//duration filter function
function duration() {
    return function(seconds) {
        return new Date(1970, 0, 1).setSeconds(seconds);
    };
}
//Configuration function
function config($routeProvider, cfpLoadingBarProvider) {
    $routeProvider
    .when('/player', {
    })
    .when('/library', {
        templateUrl: '/library',
        controller: 'LibraryController',
        controllerAs: 'library'
    })
    .when('/albums/:id', {
        templateUrl: '/albums',
        controller: 'AlbumController',
        controllerAs: 'album'
    })
    .when('/artists/:id', {
        templateUrl: '/artists',
        controller: 'ArtistController',
        controllerAs: 'artist'
    })
    .when('/catalog', {
        templateUrl: '/catalog',
        controller: 'CatalogController',
        controllerAs: 'catalog'
    })
    .when('/profile', {
        templateUrl: '/profile',
        controller: 'UserController',
        controllerAs: 'profile'
    })
    .when('/users', {
        templateUrl: '/users',
        controller: 'UsersController',
        controllerAs: 'usersManagement'
    })
    .when('/users/:id', {
        templateUrl: '/profile',
        controller: 'UserController',
        controllerAs: 'profile'
    })
    .when('/sign-out', {
        templateUrl: '/sign-out',
        controller: 'SignOutController'
    })
    .otherwise({
        redirectTo: '/player'
    });
    cfpLoadingBarProvider.spinnerTemplate = '<div class="spinner"><i class="fa fa-refresh fa-spin"></i></div>';
    cfpLoadingBarProvider.loadingBarTemplate = '<div class="loading-bar"><div class="bar"><div class="peg"></div></div></div>';
}
