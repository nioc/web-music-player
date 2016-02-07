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
