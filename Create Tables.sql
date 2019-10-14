CREATE TABLE Users
( userID INT(11) NOT NULL AUTO_INCREMENT,
  userDisplayName VARCHAR(16) NOT NULL,
  PRIMARY KEY (userID)
);

CREATE TABLE Galleries
( galleryID INT(11) NOT NULL AUTO_INCREMENT,
userID INT(11) NOT NULL,
galleryTitle varchar(255),
PRIMARY KEY (galleryID),
UNIQUE KEY gallery_key (galleryID, userID)
);

CREATE TABLE Albums
(albumID INT(11) NOT NULL AUTO_INCREMENT,
galleryID INT(11) NOT NULL,
albumTitle varchar(255),
PRIMARY KEY (albumID),
UNIQUE KEY album_key (albumID, galleryID)
);

CREATE TABLE Photos
( photoID INT(11) NOT NULL AUTO_INCREMENT,
userID INT(11) NOT NULL,
photoTitle varchar(255),
photoFileName varchar(75)  NOT NULL,
PRIMARY KEY (photoID),
UNIQUE KEY photo_key (photoID, userID)
);

CREATE TABLE PhotosJoin
( photoID INT(11) NOT NULL AUTO_INCREMENT,
  refID INT(11) NOT NULL,
  refType enum('gallery','album') ,
  UNIQUE KEY photo_key (photoID, refID, refType)
);