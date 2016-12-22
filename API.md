# API list

Web Music Player provides and consumes some API.

## 1. API provided (and self consumed)

Each API requires an authentication token provided in the `Authorization` header with the bearer scheme :
````
Authorization: Bearer fyJhbGciOiJIUzI1NiIsIcpoCEC475rvrvrF.feknaaojapoZjHaopzfjapozfazf46fefzZFZCPHKGRaEGE6rvwJrherhergerpgo5fzfzfznbrptob3prt5brojmzvzemvzIiwiZW1haWwiOiJhZG1pbkBuaW9jLmV1Iiwic2NvcGcezZEPHL3tg0d12dezAWx=.ifFZFzfZFzf86GdDJ41b0gzegzegzegZEGZEGezgze4=
````

This token is received by posting /users/tokens with user credentials :
````
POST /server/api/users/tokens
{"login": "yourlogin", "password": "yourpassword"}
````
The response include the token in a JSON object:
````
{"token":"fyJhbGciOiJIUzI1NiIsIcpoCEC475rvrvrF.feknaaojapoZjHaopzfjapozfazf46fefzZFZCPHKGRaEGE6rvwJrherhergerpgo5fzfzfznbrptob3prt5brojmzvzemvzIiwiZW1haWwiOiJhZG1pbkBuaW9jLmV1Iiwic2NvcGcezZEPHL3tg0d12dezAWx=.ifFZFzfZFzf86GdDJ41b0gzegzegzegZEGZEGezgze4="}
````

In case of error, the following structure is returned in the body:

| Name      | Type    | Description                                           |
|-----------|---------|-------------------------------------------------------|
| code      | integer | The error code describing the problem                 |
| message   | string  | Message describing the error for humans understanding |

```` json
{
  "code":500,
  "message":"Database is unavailable"
}
````

### 1.1 Playlist

Handle a user's playlist (tracks to play).

"Playlist track" ressource is structured as the following:

| Name     | Type    | Description                          |
|----------|---------|--------------------------------------|
| id       | integer | Track identifier                     |
| title    | string  | Title                                |
| file     | string  | Source of the track                  |
| userId   | string  | Owner (user) identifiant             |
| sequence | integer | Track position in the playlist       |
| album    | object  | Album object (identifier and label)  |
| artist   | object  | Artist object (identifier and label) |

```` json
{
  "id": 123,
  "title": "Monkey gone to heaven",
  "file": "/stream/123",
  "userId": "1",
  "sequence": 6,
  "album": {
    "id": 27,
    "label": "Doolittle"
  },
  "artist": {
    "id": 5,
    "label": "Pixies"
  }
}
````

#### Get all tracks in a user's playlist
````
GET /server/api/users/:userId/playlist/tracks
````
The API returns:
- 200 with an array of the tracks included in user's playlist,
- 204 if there is no track in user's playlist,
- 400 if user identifier `userId` is missing
- 401 if authorization token is missing or invalid,
- 403 if the requester is not the playlist owner.

#### Add a track in a user's playlist
````
POST /server/api/users/:userId/playlist/tracks
````
The body request must include the track identifier in a json attribute (like this `{"id": 1}`).

The API returns:
- 201 if the track is successfully added to the user's playlist (body contains the tracks sequence),
- 400 if a mandatory parameter is missing (user identifier `userId`, track identifier),
- 401 if authorization token is missing or invalid,
- 403 if the requester is not the playlist owner,
- 500 in case of error.

#### Clear a user's playlist
````
DELETE /server/api/users/:userId/playlist/tracks
````
The API returns:
- 204 if the user's playlist is successfully cleared,
- 400 if a mandatory parameter is missing (user identifier `userId`),
- 401 if authorization token is missing or invalid,
- 403 if the requester is not the playlist owner,
- 500 in case of error.

#### Remove a track from a user's playlist
````
DELETE /server/api/users/:userId/playlist/tracks/:sequence
````
The API returns:
- 204 if the track is successfully removed from user's playlist,
- 400 if a mandatory parameter is missing (user identifier `userId`),
- 401 if authorization token is missing or invalid,
- 403 if the requester is not the playlist owner,
- 404 if the track was not in the user's playlist.

#### Reorder a track into a user's playlist
````
PUT /server/api/users/:userId/playlist/tracks/:sequence
````
The body request must include the new sequence in a json attribute (like this `{"newSequence": 1}`).

The API returns:
- 200 if user's playlist has been successfully reordered with all tracks in playlist for synchronizing with GUI,
- 204 if there is no track in user's playlist,
- 400 if a mandatory parameter is missing (user identifier `userId`, old `sequence` and new sequence of the track `newSequence`),
- 401 if authorization token is missing or invalid,
- 403 if the requester is not the playlist owner,
- 500 in case of error.

### 1.2 Tracks

Handle the server library (tracks).

"Tracks" ressource is structured as the following:

| Name   | Type    | Description                          |
|--------|---------|--------------------------------------|
| id     | integer | Identifier                           |
| title  | string  | Title                                |
| file   | string  | Source of the track                  |
| year   | integer | Year of the track (1)                |
| time   | integer | Track duration in seconds (1)        |
| track  | integer | Track number in album (1)            |
| album  | object  | Album object (identifier and label)  |
| artist | object  | Artist object (identifier and label) |

Notes:

1 : this information is not provided in GET without id (returning all the tracks).

```` json
{
  "id": 123,
  "title": "Monkey gone to heaven",
  "file": "/stream/123",
  "year": 1989,
  "time": 176,
  "track": 7,
  "album": {
    "id": 27,
    "label": "Doolittle"
  },
  "artist": {
    "id": 5,
    "label": "Pixies"
  }
}
````

#### Get all tracks from the library
````
GET /server/api/library/tracks
````
The API returns:
- 200 with an array of tracks,
- 204 if there is no track in library,
- 401 if authorization token is missing or invalid,
- 500 in case of error.

#### Get a track from the library
````
GET /server/api/library/tracks/:id
````
The API returns:
- 200 with the requested track,
- 401 if authorization token is missing or invalid,
- 404 if track is unknown.

#### Add tracks to the library from a server folder
````
POST /server/api/library/tracks
{"folder":"/home/user/music/PIXIES/Doolittle/"}
````
The API returns:
- 201 with an array of the added tracks,
- 400 if folder attribute is omitted,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 500 in case of error.

#### Edit track informations
````
PUT /server/api/library/tracks/:id
````
The body request must include track information in JSON (id property must be provided, editable properties are: year, title and track) :
```` json
{
    "id":8792,
    "year":1994,
    "title":"The man who sold the world",
    "track":4
}
````
The API returns:
- 200 with updated track,
- 400 if track identifier is omitted or if track in request body is not valid,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 404 if track is unknown,
- 500 in case of error.

### 1.3 Folders

Handle the server library folders and files.

"Folder" ressource is recursive and structured as the following:

| Name       | Type          | Description                      |
|------------|---------------|----------------------------------|
| path       | string        | Path of the current folder       |
| subfolders | array(Folder) | Subfolders in the current folder |
| files      | array(string) | Files in the current folder      |

```` json
[
   {
      "path":"/home/user/music/",
      "subfolders":[
         {
            "path":"/home/user/music/PIXIES/",
            "subfolders":[
               {
                  "path":"/home/user/music/PIXIES/Doolittle/",
                  "subfolders":[
                  ],
                  "files":[
                     "01 Debaser.mp3",
                     "02 Fame.mp3",
                     "03 Wave of mutilation.mp3",
                     "04 I bleed.mp3",
                     "05 Here comes your man.mp3",
                     "06 Dead.mp3",
                     "07 Monkey gone to heaven.mp3",
                     "08 Mr Grieves.mp3",
                     "09 Crackity Jones.mp3",
                     "10 La la love you.mp3",
                     "11 N° 13 baby.mp3",
                     "12 There goes my gun.mp3",
                     "13 Hey.mp3",
                     "14 Silver.mp3",
                     "15 Gounge away.mp3"
                  ]
               }
            ]
         }
      ]
   }
]
````

#### Get all files from the server
````
GET /server/api/library/folders
````
The API returns:
- 200 with an array of folders,
- 204 if current folder contains neither subfolders nor files,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library.

### 1.4 Users

Handle user profile.

"User" ressource is structured as the following:

| Name  | Type    | Description                              |
|-------|---------|------------------------------------------|
| sub   | integer | Identifier                               |
| login | string  | Login                                    |
| name  | string  | User's full name                         |
| email | string  | User's email address                     |
| scope | string  | List of scopes granted (space separated) |

```` json
{
    "sub": 1,
    "login": "john12",
    "name": "John Doe",
    "email": "john.doe@domain.com",
    "scope": "user admin"
}
````

#### Query all users
````
GET /server/api/users
````
The API returns:
- 200 with public profiles of all users,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage users,
- 500 if there is an error during querying.

#### Get user's profile
````
GET /server/api/users/:id
````
The API returns:
- 200 with user's public profile,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage users and is requesting another user profile,
- 404 if user is unknown.

#### Update user's profile
````
PUT /server/api/users/:id
````
The body request must include user public profile in JSON.

The API returns:
- 200 with updated user's public profile,
- 400 if user identifier is omitted or if user in request body is not valid,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage users and is updating another user profile,
- 404 if user is unknown,
- 500 if there is an error during update process.

#### Create user
````
POST /server/api/users/
````
The body request must include user public profile in JSON.

The API returns:
- 201 with created user's public profile,
- 400 if user identifier is omitted or if user in request body is not valid,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage users,
- 500 if there is an error during creation process.

### 1.5 Albums

Handle album informations.

"Album" ressource is structured as the following:

| Name      | Type    | Description                          |
|-----------|---------|--------------------------------------|
| id        | integer | Identifier                           |
| name      | string  | Album name                           |
| mbid      | string  | MusicBrainz release identifier       |
| artist    | object  | Artist object (identifier and label) |
| year      | integer | Year when the album was released     |
| disk      | integer | Disk number                          |
| country   | string  | Country where the album is released  |
| mbidGroup | string  | MusicBrainz release group identifier |
| tracks    | array   | Track included in album              |
| coverPath | string  | Path to album cover                  |

```` json
{
    "id": 2,
    "name": "Nevermind",
    "mbid": "b52a8f31-b5ab-34e9-92f4-f5b7110220f0",
    "artist":
    {
        "id": 1,
        "label": "Nirvana"
    },
    "year": 1991,
    "disk": null,
    "country": "XE",
    "mbidGroup": "5b11f4ce-a62d-471e-81fc-a69a8278c7da",
    "tracks":[
         {
            "id": 7314,
            "track": 1,
            "title": "Smells Like Teen Spirit",
            "time": 301,
            "artist":
            {
                "id": 1,
                "label": "Nirvana"
            }
        },
        {
            "id": 8775,
            "track": 2,
            "title": "In Bloom",
            "time": 254,
            "artist":
            {
                "id": 1,
                "label": "Nirvana"
            }
        }
    ],
    "coverPath": "/server/covers/2.jpeg"
}
````

#### Get album informations
````
GET /server/api/albums/:id
````
The API returns:
- 200 with album informations,
- 400 if album identifier is omitted,
- 401 if authorization token is missing or invalid,
- 404 if album identifier is unknown.

#### Update album informations
````
PUT /server/api/albums/:id
````
The body request must include album informations in JSON (id property must be provided, editable properties are: year, name) :
```` json
{
    "id": 2,
    "name": "Nevermind",
    "year": 1991
}
````
The API returns:
- 200 with updated album informations,
- 400 if album identifier is omitted or if album in request body is not valid,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 404 if album identifier is unknown,
- 500 if there is an error during update process.

#### Delete an album (and all its tracks)
````
DELETE /server/api/albums/:id
````
The API returns:
- 204 in case of success,
- 400 if album identifier is omitted,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 500 if there is an error during delete process.

### 1.6 Artists

Handle album informations.

"Artist" ressource is structured as the following:

| Name    | Type    | Description                        |
|---------|---------|------------------------------------|
| id      | integer | Identifier                         |
| name    | string  | Artist name                        |
| mbid    | string  | MusicBrainz artist identifier      |
| summary | string  | Biography of the artist            |
| country | string  | Country where the artist came from |
| tracks  | array   | Tracks by artist                   |

```` json
{
    "id": 1,
    "name": "Nirvana",
    "mbid": "5b11f4ce-a62d-471e-81fc-a69a8278c7da",
    "summary": null,
    "country": "US",
    "tracks":[
         {
            "id": 7314,
            "track": 1,
            "title": "Smells Like Teen Spirit",
            "time": 301,
            "album":
            {
                "id": 2,
                "label": "Nevermind"
            }
        },
        {
            "id": 8775,
            "track": 2,
            "title": "In Bloom",
            "time": 254,
            "album":
            {
                "id": 2,
                "label": "Nevermind"
            }
        }
    ]
}
````

#### Get artist informations
````
GET /server/api/artists/:id
````
The API returns:
- 200 with artist informations,
- 400 if artist identifier is omitted,
- 401 if authorization token is missing or invalid,
- 404 if artist identifier is unknown.

#### Update artist informations
````
PUT /server/api/artists/:id
````
The body request must include artist informations in JSON (id property must be provided, editable properties are: year, name) :
```` json
{
    "id": 2,
    "name": "Nevermind",
    "year": 1991
}
````
The API returns:
- 200 with updated artist informations,
- 400 if artist identifier is omitted or if artist in request body is not valid,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 404 if artist identifier is unknown,
- 500 if there is an error during update process.

#### Delete an artist (and all his albums and tracks)
````
DELETE /server/api/artists/:id
````
The API returns:
- 204 in case of success,
- 400 if artist identifier is omitted,
- 401 if authorization token is missing or invalid,
- 403 if requester is not granted to manage library,
- 500 if there is an error during delete process.
