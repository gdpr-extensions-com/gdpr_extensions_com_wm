CREATE TABLE tx_gdprextensionscomwm_domain_model_apiconnect (
	uid int(11) DEFAULT '0' NOT NULL,
	apiconnect_id int(11) NOT NULL DEFAULT '0',
	apiconnect_title varchar(255) NOT NULL DEFAULT '',
	apiconnect_html TEXT NOT NULL DEFAULT '',
	apiconnect_css TEXT NOT NULL DEFAULT '',
	apiconnect_js TEXT NOT NULL DEFAULT '',
	valid_from int(11) NOT NULL DEFAULT '0',
	valid_to int(11) NOT NULL DEFAULT '0',
	create_time varchar(255) NOT NULL DEFAULT '',
	root_pid int(11) NOT NULL DEFAULT '0',
);


#
# Table structure for table 'tx_gdprextensionscomwm_stats'
#
CREATE TABLE tx_gdprextensionscomwm_stats (
	uid int(11) DEFAULT '0' NOT NULL,
	pid int(11) DEFAULT '0' NOT NULL,
	apiconnect_id int(11) NOT NULL DEFAULT '0',
	tstamp int(11) NOT NULL DEFAULT '0',
);

CREATE TABLE pages (
	multi_locations int(11) unsigned DEFAULT '0' NOT NULL,
);

CREATE TABLE multilocations (
	multi_location_gpno varchar(255) NOT NULL DEFAULT '',
	name_of_location varchar(255) NOT NULL DEFAULT '',
	multi_kitchenplaner_license varchar(255) NOT NULL DEFAULT '',
	dashboard_api_key varchar(255) NOT NULL DEFAULT '',
	location_page_id varchar(255) NOT NULL DEFAULT '',
	pages int(11) unsigned DEFAULT '0',
);
