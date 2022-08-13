CREATE TABLE IF NOT EXISTS `Users` (
    `id` INT NOT NULL AUTO_INCREMENT
    ,`fname` VARCHAR(60) NOT NULL
    ,`lname` VARCHAR(60) NOT NULL
    ,`email` VARCHAR(100) NOT NULL
    ,`username` VARCHAR(60) NOT NULL
    ,`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,`password` VARCHAR(255) NOT NULL
    ,PRIMARY KEY(`id`)
    ,UNIQUE (`email`)
    ,UNIQUE (`username`)
)
