SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `channels` (
  `id` int(11) NOT NULL COMMENT 'Channel ID',
  `creator` varchar(50) NOT NULL COMMENT 'Channel Creator',
  `title` varchar(20) NOT NULL,
  `description` varchar(1000) NOT NULL DEFAULT 'A channel for 1337 h@xX0rz',
  `deleted` varchar(5) NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all of the channels.';

CREATE TABLE `chat` (
  `username` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(50) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `deleted` varchar(5) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all of the chat messages.';

CREATE TABLE `flags` (
  `id` int(11) NOT NULL,
  `flag` varchar(50) NOT NULL,
  `worth` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores the user''s flags discovered.';

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL COMMENT 'User ID',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time of Login Attempt',
  `ip` varchar(50) NOT NULL COMMENT 'IP Address of Request',
  `insecure_ip` varchar(50) NOT NULL COMMENT 'Spoofed IP Address of Request',
  `success` varchar(5) NOT NULL COMMENT 'Login Success?'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all of the login attempts.';

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'User ID',
  `username` varchar(25) NOT NULL COMMENT 'Username',
  `password` varchar(256) NOT NULL COMMENT 'Hashed Password',
  `name` int(25) NOT NULL COMMENT 'Username (ignore this column)',
  `email` varchar(255) NOT NULL COMMENT 'Email Address',
  `banned` varchar(5) NOT NULL COMMENT 'Banned?',
  `description` varchar(500) NOT NULL COMMENT 'User Description',
  `languages` varchar(100) NOT NULL COMMENT 'Known Languages',
  `location` varchar(100) NOT NULL COMMENT 'User Location',
  `rank` varchar(5) NOT NULL DEFAULT '0',
  `icon` varchar(248) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all of the core user data.';


ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `flags`
  ADD PRIMARY KEY (`id`,`flag`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Channel ID', AUTO_INCREMENT=4;
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=703;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User ID', AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
