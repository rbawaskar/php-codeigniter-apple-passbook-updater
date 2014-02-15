 CREATE TABLE `apple_passes` (
  `pass_id` int(11) NOT NULL,
  `serial_number` varchar(50) NOT NULL DEFAULT '',
  `authentication_token` varchar(100) NOT NULL DEFAULT '',
  `pass_type_id` varchar(100) NOT NULL DEFAULT '',
  `last_update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 


CREATE TABLE `apple_registrations` (
  `serial_number` varchar(100) NOT NULL DEFAULT '',
  `device_id` varchar(100) NOT NULL DEFAULT '',
  `push_token` varchar(100) DEFAULT '',
  `pass_type_id` varchar(100) DEFAULT NULL
);