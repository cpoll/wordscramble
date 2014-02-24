
DROP TABLE profile CASCADE;
CREATE TABLE profile(
	-- Authentication
	email varchar(50),
	password char(128) NOT NULL,
	
	-- User info
	displayname varchar(20) UNIQUE NOT NULL,
	fname varchar(20) NOT NULL,
	lname varchar(20) NOT NULL,
	birthday date NOT NULL,
	avatar integer NOT NULL,
	registered timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(email)
);

DROP TABLE gamestats;
CREATE TABLE gamestats(
	email varchar(50) REFERENCES profile(email) ON DELETE CASCADE,
	score integer NOT NULL,
	submitted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(email, submitted)
);

DROP TABLE dictionary;
CREATE TABLE dictionary(
	word varchar(50) PRIMARY KEY
);
