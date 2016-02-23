USE `wmp`;

CREATE TABLE `wmp`.`user` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifier',
  `login` varchar(128) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Login',
  `name` varchar(128) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Full name',
  `email` varchar(128) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Email',
  `password` varchar(64) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Crypted password ',
  `status` boolean NOT NULL DEFAULT '0' COMMENT 'Status 0/1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Users';

CREATE TABLE `wmp`.`scope` (
  `userId` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User identifier',
  `scope` varchar(32) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Scope',
  PRIMARY KEY (`userId`,`scope`),
  KEY `userId` (`userId`),
  CONSTRAINT `scope_fk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Users scope';

CREATE TABLE `wmp`.`artist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifier',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Artist name',
  `mbid` varchar(1369) CHARACTER SET utf8 DEFAULT NULL COMMENT 'MusicBrainz artist identifier',
  `summary` text CHARACTER SET utf8 COMMENT 'Biography of the artist',
  `country` varchar(64) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Country where the group come from',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Artists';

CREATE TABLE `wmp`.`album` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifier',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Name',
  `mbid` varchar(36) CHARACTER SET utf8 DEFAULT NULL COMMENT 'MusicBrainz release identifier',
  `artist` int(11) unsigned NOT NULL COMMENT 'Album artist',
  `year` int(4) unsigned DEFAULT NULL COMMENT 'Year when the album was released',
  `disk` smallint(5) unsigned DEFAULT NULL COMMENT 'Disk number',
  `country` varchar(64) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Country where the album is released',
  `mbidGroup` varchar(36) CHARACTER SET utf8 DEFAULT NULL COMMENT 'MusicBrainz release group identifier',
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `disk` (`disk`),
  FULLTEXT KEY `name` (`name`),
  CONSTRAINT `album_fk_1` FOREIGN KEY (`artist`) REFERENCES `artist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Albums';

CREATE TABLE `wmp`.`track` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifier',
  `file` varchar(1024) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Filename where the file is stored',
  `album` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Album identifier',
  `year` smallint(4) unsigned DEFAULT NULL COMMENT 'Year',
  `artist` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Artist',
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Title',
  `bitrate` mediumint(8) unsigned DEFAULT NULL COMMENT 'Bitrate',
  `rate` mediumint(8) unsigned DEFAULT NULL COMMENT 'Main rating',
  `mode` enum('abr','vbr','cbr') CHARACTER SET utf8 DEFAULT NULL COMMENT 'Encoding bitrate mode',
  `size` int(11) unsigned DEFAULT NULL COMMENT 'File size',
  `time` smallint(5) unsigned DEFAULT NULL COMMENT 'Time in seconds',
  `track` smallint(5) unsigned DEFAULT NULL COMMENT 'Track number',
  `mbid` varchar(36) CHARACTER SET utf8 DEFAULT NULL COMMENT 'MusicBrainz identifier',
  `updateTime` int(11) unsigned DEFAULT '0' COMMENT 'Last modification timestamp',
  `additionTime` int(11) unsigned DEFAULT '0' COMMENT 'Adding timestamp',
  `composer` varchar(256) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Composer',
  PRIMARY KEY (`id`),
  KEY `album` (`album`),
  KEY `artist` (`artist`),
  KEY `file` (`file`(255)),
  FULLTEXT KEY `title` (`title`),
  CONSTRAINT `track_fk_1` FOREIGN KEY (`album`) REFERENCES `album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `track_fk_2` FOREIGN KEY (`artist`) REFERENCES `artist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tracks';


CREATE TABLE `wmp`.`playlist` (
  `userId` smallint(6) unsigned NOT NULL COMMENT 'Owner',
  `sequence` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Sequence for ordering tracks',
  `id` int(11) unsigned NOT NULL COMMENT 'Track identifier',
  PRIMARY KEY (`userId`,`sequence`),
  KEY `userId` (`userId`),
  KEY `id` (`id`),
  CONSTRAINT `playlist_fk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `playlist_fk_2` FOREIGN KEY (`id`) REFERENCES `track` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'User\'s current playlist';

INSERT INTO `wmp`.`user` (`id`, `login`, `name`, `email`, `password`, `status`) VALUES (1, 'admin', 'Admin', NULL, md5('nimda'), 1);
INSERT INTO `wmp`.`scope` (`userId`, `scope`) VALUES (1, 'admin'), (1, 'user');

