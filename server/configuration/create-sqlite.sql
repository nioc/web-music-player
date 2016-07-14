CREATE TABLE `user` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, -- 'Identifier'
  `login` TEXT DEFAULT NULL UNIQUE, -- 'Login'
  `name` TEXT DEFAULT NULL, -- 'Full name'
  `email` TEXT DEFAULT NULL, -- 'Email'
  `password` TEXT DEFAULT NULL, -- 'Crypted password '
  `status` BOOLEAN NOT NULL DEFAULT '0' -- 'Status 0/1'
);

CREATE TABLE `scope` (
  `userId` INTEGER NOT NULL, -- 'User identifier'
  `scope` TEXT NOT NULL, -- 'Scope'
  PRIMARY KEY (`userId`,`scope`),
  FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `artist` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, -- 'Identifier'
  `name` TEXT DEFAULT NULL, -- 'Artist name'
  `mbid` TEXT DEFAULT NULL, -- 'MusicBrainz artist identifier'
  `summary` TEXT, -- 'Biography of the artist'
  `country` TEXT DEFAULT NULL -- 'Country where the group come from'
);

CREATE TABLE `album` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, -- 'Identifier'
  `name` TEXT DEFAULT NULL, -- 'Name'
  `mbid` TEXT DEFAULT NULL, -- 'MusicBrainz release identifier'
  `artist` INTEGER NOT NULL, -- 'Album artist'
  `year` INTEGER DEFAULT NULL, -- 'Year when the album was released'
  `disk` INTEGER DEFAULT NULL, -- 'Disk number'
  `country` TEXT DEFAULT NULL, -- 'Country where the album is released'
  `mbidGroup` TEXT DEFAULT NULL, -- 'MusicBrainz release group identifier'
  FOREIGN KEY (`artist`) REFERENCES `artist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `track` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, -- 'Identifier'
  `file` TEXT DEFAULT NULL, -- 'Filename where the file is stored'
  `album` INTEGER NOT NULL DEFAULT '0', -- 'Album identifier'
  `year` INTEGER DEFAULT NULL, -- 'Year'
  `artist` INTEGER NOT NULL DEFAULT '0', -- 'Artist'
  `title` TEXT DEFAULT NULL, -- 'Title'
  `bitrate` INTEGER DEFAULT NULL, -- 'Bitrate'
  `rate` INTEGER DEFAULT NULL, -- 'Main rating'
  `mode` TEXT DEFAULT NULL, -- 'Encoding bitrate mode ('abr','vbr','cbr')'
  `size` INTEGER DEFAULT NULL, -- 'File size'
  `time` INTEGER DEFAULT NULL, -- 'Time in seconds'
  `track` INTEGER DEFAULT NULL, -- 'Track number'
  `mbid` TEXT DEFAULT NULL, -- 'MusicBrainz identifier'
  `updateTime` INTEGER DEFAULT '0', -- 'Last modification timestamp'
  `additionTime` INTEGER DEFAULT '0', -- 'Adding timestamp'
  `composer` TEXT DEFAULT NULL, -- 'Composer'
  FOREIGN KEY (`album`) REFERENCES `album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`artist`) REFERENCES `artist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `playlist` (
  `userId` INTEGER NOT NULL, -- 'Owner'
  `sequence` INTEGER NOT NULL DEFAULT '0', -- 'Sequence for ordering tracks'
  `id` INTEGER NOT NULL, -- 'Track identifier'
  PRIMARY KEY (`userId`,`sequence`),
  FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id`) REFERENCES `track` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `cover` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, -- 'Identifier'
  `albumId` INTEGER NULL, -- 'Album'
  `status` BOOLEAN DEFAULT '0', -- 'Image status (only one active)'
  `width` INTEGER DEFAULT '0', -- 'Image width in pixel'
  `height` INTEGER DEFAULT '0', -- 'Image height in pixel'
  `mime` TEXT DEFAULT NULL, -- 'MIME type (ex: image/jpeg)'
  `image` BLOB, -- 'Image stream'
  FOREIGN KEY (`albumId`) REFERENCES `album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `user` (`id`, `login`, `name`, `email`, `password`, `status`) VALUES (1, 'admin', 'Admin', NULL, 'ee10c315eba2c75b403ea99136f5b48d', 1);
INSERT INTO `scope` (`userId`, `scope`) VALUES (1, 'admin'), (1, 'user');

