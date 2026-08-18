ALTER TABLE `player` 
	ADD `passed` TINYINT UNSIGNED NOT NULL DEFAULT '0',
	
	ADD `coin` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD `wood` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD `stone` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD `ore` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD `penalty` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD `vp` INT UNSIGNED NOT NULL DEFAULT '0',
	
	ADD `starting_order` INT UNSIGNED NOT NULL DEFAULT '0',
	
	ADD `goods` LONGTEXT,
	ADD `boards` LONGTEXT,
	ADD `artisan_shed` TEXT,
	ADD `ships` TEXT,
	ADD `emigrations` TEXT,
	ADD `extra` LONGTEXT
;

CREATE TABLE IF NOT EXISTS `player_saved` (
	`player_id` int(11) unsigned NOT NULL,
	`coin` INT UNSIGNED NOT NULL DEFAULT '0',
	`wood` INT UNSIGNED NOT NULL DEFAULT '0',
	`stone` INT UNSIGNED NOT NULL DEFAULT '0',
	`ore` INT UNSIGNED NOT NULL DEFAULT '0',
	`penalty` INT UNSIGNED NOT NULL DEFAULT '0',
    `vp` INT UNSIGNED NOT NULL DEFAULT '0',
	
	`player_score` INT NOT NULL DEFAULT '0',
	`player_score_aux` INT NOT NULL DEFAULT '0',
	
	`goods` LONGTEXT,
	`boards` LONGTEXT,
	`artisan_shed` TEXT,
	`ships` TEXT,
	`emigrations` TEXT,
	`extra` LONGTEXT,
	UNIQUE KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Occupations table
-- id, deck (A, B, C), starting, ability, location (hand, played, discard, deck), player_id
CREATE TABLE IF NOT EXISTS `occupation` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`deck` enum('a', 'b', 'c') DEFAULT 'a' NOT NULL,
	`starter` tinyint(1),
	`ability` int(11) unsigned,
	`points` int(11),
	`location` enum('hand', 'played', 'discard', 'deck') DEFAULT 'deck' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	`used` tinyint(1) DEFAULT 0 NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `occupation_saved` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`deck` enum('a', 'b', 'c') DEFAULT 'a' NOT NULL,
	`starter` tinyint(1),
	`ability` int(11) unsigned,
	`points` int(11),
	`location` enum('hand', 'played', 'discard', 'deck') DEFAULT 'deck' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	`used` tinyint(1) DEFAULT 0 NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Weapons table
-- id, type, location (hand, discard, deck), player_id
CREATE TABLE IF NOT EXISTS `weapon` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`type` enum('bow', 'snare', 'spear', 'sword') DEFAULT 'bow' NOT NULL,
	`location` enum('hand', 'discard', 'deck') DEFAULT 'deck' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `weapon_saved` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`type` enum('bow', 'snare', 'spear', 'sword') DEFAULT 'bow' NOT NULL,
	`location` enum('hand', 'discard', 'deck') DEFAULT 'deck' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Meeples table (meeples)
-- id, player_id, location, action_id, slot
CREATE TABLE IF NOT EXISTS `meeple` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`player_id` int(11) unsigned DEFAULT NULL,
	`location` enum('action', 'feast', 'thing', 'bench') DEFAULT 'feast' NOT NULL,
	`action_id` int(11) unsigned DEFAULT NULL,
	`slot` int(11) unsigned DEFAULT 0 NOT NULL,
	`colour` int(11) unsigned DEFAULT 0 NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `meeple_saved` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`player_id` int(11) unsigned DEFAULT NULL,
	`location` enum('action', 'feast', 'thing', 'bench') DEFAULT 'feast' NOT NULL,
	`action_id` int(11) unsigned DEFAULT NULL,
	`slot` int(11) unsigned DEFAULT 0 NOT NULL,
	`colour` int(11) unsigned DEFAULT 0 NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `command_queue` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`command` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `string_globals` (
	`key` VARCHAR(30) NOT NULL UNIQUE,
	`value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `string_globals_saved` (
	`key` VARCHAR(30) NOT NULL UNIQUE,
	`value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `available_islands` (
	`code` VARCHAR(1),
	`name` VARCHAR(30) NOT NULL,
	`coins` int(11) unsigned DEFAULT 0 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `available_islands_saved` (
	`code` VARCHAR(1),
	`name` VARCHAR(30) NOT NULL,
	`coins` int(11) unsigned DEFAULT 0 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forge` (
	`name` VARCHAR(30) NOT NULL,
	`cost` int(11) unsigned DEFAULT 0 NOT NULL,
	`strength` int(11) unsigned DEFAULT 0 NOT NULL,
	`tongs` tinyint(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forge_saved` (
	`name` VARCHAR(30) NOT NULL,
	`cost` int(11) unsigned DEFAULT 0 NOT NULL,
	`strength` int(11) unsigned DEFAULT 0 NOT NULL,
	`tongs` tinyint(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mountain_strips` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`code` int(11) unsigned NOT NULL,
	`resources` VARCHAR(100) NOT NULL,
	`location` enum('deck', 'discard', 'available') DEFAULT 'deck' NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `mountain_strips_saved` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`code` int(11) unsigned NOT NULL,
	`resources` VARCHAR(100) NOT NULL,
	`location` enum('deck', 'discard', 'available') DEFAULT 'deck' NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `asset_diffs` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`player_id` int(11) unsigned NOT NULL,
	`asset` VARCHAR(256) NOT NULL,
	`amount` int(11) NOT NULL,
	`source` VARCHAR(256) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;