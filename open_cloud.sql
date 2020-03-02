START TRANSACTION;

DELIMITER $$
CREATE PROCEDURE `selectInsertPublicLink` (IN `file__id_in` INT, IN `user__id_in` INT, IN `link_in` VARCHAR(255))  BEGIN
    IF NOT EXISTS
        (
        SELECT
            `id`
        FROM
            `public_links`
        WHERE
            `public_links`.`file__id` = file__id_in AND `public_links`.`link` = link_in
        LIMIT 1
    ) THEN
INSERT INTO `public_links`(`id`, `link`, `file__id`)
VALUES(NULL, link_in, file__id_in) ; 
END IF ;
END$$

DELIMITER ;

CREATE TABLE `extensions` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'The mime type of the file, if the browser provided this information. An example would be "image/gif". ',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `extensions`;


CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `upload_date` datetime NOT NULL,
  `delete_date` datetime NOT NULL,
  `hash__name` text NOT NULL,
  `hash__file` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `real_name` text NOT NULL,
  `extension__id` int(11) NOT NULL DEFAULT 0,
  `status__id` int(11) NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Bytes',
  `parent_folder__id` int(11) NOT NULL DEFAULT 1,
  `type` int(11) NOT NULL DEFAULT 1 COMMENT '1 - file; 2 - folder'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `files`;

CREATE TABLE `public_links` (
  `id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `file__id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `public_links`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `users`;


ALTER TABLE `extensions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `public_links`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `extensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `public_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;