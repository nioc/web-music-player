# API list

Web Music Player provides and consumes some API.

## 1. API provided (and self consumed)

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
| id       | string  | Track identifier                     |
| title    | string  | Title                                |
| file     | string  | Source of the track                  |
| userId   | string  | Owner (user) identifiant             |
| sequence | integer | Track position in the playlist       |
| album    | object  | Album object (identifier and label)  |
| artist   | object  | Artist object (identifier and label) |

```` json
{
  "id": "123",
  "title": "Monkey gone to heaven",
  "file": "/stream/123",
  "userId": "1",
  "sequence": 6,
  "album": {
    "id": "27",
    "label": "Doolittle"
  },
  "artist": {
    "id": "5",
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
- 404 if the user is not known.

#### Add a track in a user's playlist
````
POST /server/api/users/:userId/playlist/tracks
````
The body request must include the track identifier in a json attribute (like this `{"id":"1"}`).
The API returns:
- 201 if the track is successfully added to the user's playlist (body contains the tracks sequence),
- 400 if a mandatory parameter is missing (user identifier, track identifier),
- 500 in case of error.

#### Remove a track from a user's playlist
````
DELETE /server/api/users/:userId/playlist/tracks/:sequence
````
The API returns:
- 204 if the track is successfully removed from user's playlist,
- 400 if a mandatory parameter is missing (user identifier, sequence of the track),
- 404 if the track was not in the user's playlist.

#### Reorder a track into a user's playlist
````
PUT /server/api/users/:userId/playlist/tracks/:sequence
````
The body request must include the new sequence in a json attribute (like this `{"newSequence":1}`).
The API returns:
- 200 if user's playlist has been successfully reordered with all tracks in playlist for synchronizing with GUI,
- 204 if there is no track in user's playlist,
- 400 if a mandatory parameter is missing (user identifier, old and new sequence of the track),
- 500 in case of error.

### 1.2 Tracks

Handle the server library (tracks).

"Tracks" ressource is structured as the following:

| Name   | Type   | Description                          |
|--------|--------|--------------------------------------|
| id     | string | Identifier                           |
| title  | string | Title                                |
| file   | string | Source of the track                  |
| album  | object | Album object (identifier and label)  |
| artist | object | Artist object (identifier and label) |

```` json
{
  "id": "123",
  "title": "Monkey gone to heaven",
  "file": "/stream/123",
  "album": {
    "id": "27",
    "label": "Doolittle"
  },
  "artist": {
    "id": "5",
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
- 500 in case of error.

#### Add tracks to the library from a server folder
````
POST /server/api/library/tracks
{"folder":"/home/user/music/PIXIES/Doolittle/"}
````
The API returns:
- 201 with an array of the added tracks,
- 400 if folder attribute is omitted,
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
- 204 if current folder contains neither subfolders nor files.

### 1.4 Users

Handle user profile.

"User" ressource is structured as the following:

| Name  | Type          | Description            |
|-------|---------------|------------------------|
| sub   | integer       | Identifier             |
| login | string        | Login                  |
| name  | string        | User's full name       |
| email | string        | User's email address   |
| scope | array(string) | List of scopes granted |

```` json
{
    "sub": 1,
    "login": "john12",
    "name": "John Doe",
    "email": "john.doe@domain.com",
    "scope":
    [
        "user"
    ]
}
````

#### Get user's profile
````
GET /server/api/users/:id
````
The API returns:
- 200 with user's profile
- 400 if user identifier is omitted,
- 404 if user is unknown.

#### Update user's profile
````
PUT /server/api/users/:id
````
The body request must include user in JSON.

The API returns:
- 200 with updated user's profile,
- 400 if user identifier is omitted or if user in request body is not valid,
- 404 if user is unknown,
- 500 if there is an error during update process.

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
    "mbidGroup": "5b11f4ce-a62d-471e-81fc-a69a8278c7da"
}
````

#### Get album informations
````
GET /server/api/albums/:id
````
The API returns:
- 200 with album information
- 400 if album identifier is omitted,
- 404 if album identifier is unknown.

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

```` json
{
    "id": 1,
    "name": "Nirvana",
    "mbid": "5b11f4ce-a62d-471e-81fc-a69a8278c7da",
    "summary": null,
    "country": "US"
}
````

#### Get artist informations
````
GET /server/api/artists/:id
````
The API returns:
- 200 with artist informations
- 400 if artist identifier is omitted,
- 404 if artist identifier is unknown.
