#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_feuserfriends_view int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_feuserfriends_friends'
#
CREATE TABLE tx_feuserfriends_friends (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	user_from text,
	user_to text,
	invitation text,
	accept tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_feuserfriends_message'
#
CREATE TABLE tx_feuserfriends_message (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	user_from text,
	user_to text,
	message text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


