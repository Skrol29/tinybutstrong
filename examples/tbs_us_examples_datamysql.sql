DROP TABLE IF EXISTS t_tbs_exemples;
CREATE TABLE t_tbs_exemples (
  res_id int(11) NOT NULL auto_increment,
  res_name varchar(50) NOT NULL default '',
  res_score int(11) NOT NULL default '0',
  res_date datetime NOT NULL default '0000-00-00 00:00:00',
  res_team varchar(20) NOT NULL default '',
  PRIMARY KEY  (res_id),
  KEY res_team (res_team)
) TYPE=MyISAM;

INSERT INTO t_tbs_exemples VALUES (1, 'michael', 255, '0000-00-00 00:00:00', 'Eagles');
INSERT INTO t_tbs_exemples VALUES (2, 'bigone', 103, '2002-11-24 23:50:00', 'Goonies');
INSERT INTO t_tbs_exemples VALUES (3, 'TheBest', 155, '2002-11-24 23:50:24', 'MIB');
INSERT INTO t_tbs_exemples VALUES (4, 'LittleNemo', 203, '2002-08-10 12:50:45', 'Eagles');
INSERT INTO t_tbs_exemples VALUES (5, 'Rolls', 159, '2002-11-24 23:51:19', 'MIB');
INSERT INTO t_tbs_exemples VALUES (6, 'The very best', 301, '2002-11-24 23:52:22', 'MIB');
INSERT INTO t_tbs_exemples VALUES (7, 'Rocky Horror', 178, '2002-11-24 23:52:41', 'Goonies');
