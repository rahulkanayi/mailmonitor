CREATE DATABASE IF NOT EXISTS mailmonitor
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mailmonitor;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_username_unique (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mail_stats (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    server_name VARCHAR(191) NOT NULL,
    total BIGINT UNSIGNED NOT NULL DEFAULT 0,
    qp BIGINT UNSIGNED NOT NULL DEFAULT 0,
    dirdel BIGINT UNSIGNED NOT NULL DEFAULT 0,
    pending BIGINT UNSIGNED NOT NULL DEFAULT 0,
    deferred BIGINT UNSIGNED NOT NULL DEFAULT 0,
    bounced BIGINT UNSIGNED NOT NULL DEFAULT 0,
    recorded_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY mail_stats_server_time_id (server_name, recorded_at, id),
    KEY mail_stats_recorded_at (recorded_at)
) ENGINE=InnoDB;
