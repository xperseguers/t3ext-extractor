#
# Table structure for table 'sys_file_metadata'
#
CREATE TABLE sys_file_metadata (
  copyright text,
  camera_make varchar(255) DEFAULT '' NOT NULL,
  camera_model varchar(255) DEFAULT '' NOT NULL,
  camera_lens varchar(255) DEFAULT '' NOT NULL,
  shutter_speed varchar(20) DEFAULT '' NOT NULL,
  focal_length float unsigned DEFAULT '0' NOT NULL,
  exposure_bias varchar(20) DEFAULT '' NOT NULL,
  white_balance_mode varchar(255) DEFAULT '' NOT NULL,
  iso_speed int(11) unsigned DEFAULT '0' NOT NULL,
  aperture float unsigned DEFAULT '0' NOT NULL,
  flash int(4) DEFAULT '0' NOT NULL
);
