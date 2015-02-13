DROP TABLE IF EXISTS videoQueue;
DROP TABLE IF EXISTS profileQueue;
DROP TABLE IF EXISTS video;
DROP TABLE IF EXISTS queue;
DROP TABLE IF EXISTS profile;

CREATE TABLE profile (
	profileId INT UNSIGNED NOT NULL AUTO_INCREMENT,
	email VARCHAR(100) UNIQUE NOT NULL,
	imagePath VARCHAR(255),
	bowtieUserId INT UNSIGNED,
	PRIMARY KEY(profileId)
);

CREATE TABLE queue (
	queueId INT UNSIGNED NOT NULL AUTO_INCREMENT,
	creationDate DATETIME NOT NULL,
	PRIMARY KEY(queueId)
);

CREATE TABLE video (
	videoId INT UNSIGNED NOT NULL AUTO_INCREMENT,
	videoComment TEXT,
	PRIMARY KEY(videoId)
);

CREATE TABLE profileQueue (
	profileId INT UNSIGNED NOT NULL,
	queueId INT UNSIGNED NOT NULL,
	profileQueueName VARCHAR(100),
	INDEX(profileId),
	INDEX(queueId),
	FOREIGN KEY(profileId) REFERENCES profile(profileId),
	FOREIGN KEY(queueId) REFERENCES queue(queueId),
	PRIMARY KEY(profileId, queueId)
);

CREATE TABLE videoQueue (
	videoId INT UNSIGNED NOT NULL,
	queueId INT UNSIGNED NOT NULL,
	videoQueueNumber INT UNSIGNED NOT NULL,
	INDEX(videoId),
	INDEX(queueId),
	FOREIGN KEY(videoId) REFERENCES video(videoId),
	FOREIGN KEY(queueId) REFERENCES queue(queueId),
	PRIMARY KEY(videoId, queueId)
);
