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

| Name     | Type   | Description                          |
|----------|--------|--------------------------------------|
| id       | string | Track identifier                     |
| title    | string | Title                                |
| file     | string | Source of the track                  |
| userId   | string | Owner (user) identifiant             |
| sequence | string | Track position in the playlist       |
| album    | object | Album object (identifier and label)  |
| artist   | object | Artist object (identifier and label) |

```` json
{
  "id": "123",
  "title": "Monkey gone to heaven",
  "file": "/stream/123",
  "userId": "1",
  "sequence": "6",
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
- 500 in case of error.

#### Remove a track from a user's playlist
````
DELETE /server/api/users/:userId/playlist/tracks/:sequence
````
The API returns:
- 204 if the track is successfully removed from user's playlist,
- 404 if the track was not in the user's playlist.

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

#### Get all tracks from the server
````
GET /server/api/library/tracks
````
The API returns:
- 200 with an array of tracks,
- 500 in case of error.