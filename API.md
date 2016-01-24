# API list

Web Music Player provides and consumes some API.

## 1. API provided (and self consumed)

In case of error, the following structure is returned in the body:

| Attribute | Type    | Explanation                                           |
|-----------|---------|-------------------------------------------------------|
| code      | integer | The error code describing the problem                 |
| message   | string  | Message describing the error for humans understanding |

````
{"code":500,"message":"Database is unavailable"}
````

### 1.1 Playlist

Handle a user's playlist (tracks to play).

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

#### Get all tracks from the server
````
GET /server/api/library/tracks
````
The API returns:
- 200 with an array of tracks,
- 500 in case of error.