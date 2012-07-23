<?php

ob_start();

/*****
	This file creates the database for the core CMS.
	Having it all in one file - seems easy at first, becomes a hell later.
	We need to refactor this. Incremental update scripts (one script per update) seems like a usefull idea.
	See http://martinfowler.com/articles/evodb.html
 *****/

define('DS',                DIRECTORY_SEPARATOR);
define('ROOT',              dirname(__FILE__) . DS);
define('SYSTEMPATH',        dirname(__FILE__) . DS . 'cms' . DS);
define('PANIC_EMAIL',       'cms@huizinga.nl');

// Include the CMS configuration file.
require_once ROOT . 'cms_config.php';
require_once SYSTEMPATH . 'sources/classes/registry.class.php';
require_once SYSTEMPATH . 'sources/classes/firephp.class.php';
Registry::set('firephp',    new FirePHP());
$fb = Registry::get('firephp');
require_once SYSTEMPATH . 'sources/classes/firephp/table.class.php';
require_once SYSTEMPATH . 'sources/classes/html2text.class.php';
require_once SYSTEMPATH . 'sources/classes/email.class.php';
require_once SYSTEMPATH . 'sources/classes/error.class.php';
require_once SYSTEMPATH . 'sources/classes/database.class.php';
Registry::set('error', new Error());
Registry::set('database', new Database());
$db = Registry::get('database');

//-----
//	Create the tables we need.
//-----


$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_file` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT,
  `f_target` enum('_blank','_parent','_self','_top') NOT NULL,
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_file_translation` (
  `f_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`f_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_group` (
  `g_id` int(10) NOT NULL AUTO_INCREMENT,
  `g_name` varchar(255) NOT NULL,
  `g_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`g_id`),
  UNIQUE KEY `g_name` (`g_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_iframe` (
  `ifr_id` int(10) NOT NULL AUTO_INCREMENT,
  `ifr_height` varchar(6) NOT NULL,
  `ifr_width` varchar(6) NOT NULL,
  `ifr_allowtransparency` tinyint(1) NOT NULL,
  PRIMARY KEY (`ifr_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_iframe_translation` (
  `ifr_id` int(10) NOT NULL,
  `lang_id` int(10) NOT NULL,
  `url` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ifr_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_include` (
  `inc_id` int(10) NOT NULL AUTO_INCREMENT,
  `inc_file` varchar(255) NOT NULL,
  PRIMARY KEY (`inc_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_item_edit_permission` (
  `item` int(10) NOT NULL,
  `type` enum('page','plugin','link','iframe','include','module','file') NOT NULL,
  `group` int(10) NOT NULL,
  `mod_instance` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item`,`type`,`group`,`mod_instance`),
  KEY `item` (`item`,`type`,`group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_item_permission` (
  `item` int(10) NOT NULL,
  `type` enum('page','link','iframe','include','module','file') NOT NULL,
  `group` int(10) NOT NULL,
  `mod_instance` int(11) NOT NULL,
  PRIMARY KEY (`item`,`type`,`group`,`mod_instance`),
  KEY `item` (`item`,`type`,`group`) 
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_language` (
  `lang_id` int(10) NOT NULL AUTO_INCREMENT,
  `lang_tag` char(2) NOT NULL,
  `lang_flag` varchar(255) NOT NULL,
  `lang_name` varchar(255) NOT NULL,
  `lang_searchengine_name` char(20) NOT NULL,
  `lang_locale` varchar(25) NOT NULL,
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `lang_tag` (`lang_tag`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_link` (
  `l_id` int(10) NOT NULL AUTO_INCREMENT,
  `l_target` enum('_blank','_parent','_self','_top') NOT NULL,
  PRIMARY KEY (`l_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_link_translation` (
  `l_id` int(10) NOT NULL,
  `lang_id` int(10) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`l_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_menuitem` (
  `mi_id` int(10) NOT NULL AUTO_INCREMENT,
  `mi_tag` varchar(255) NOT NULL,
  `mi_position` int(10) NOT NULL,
  `mi_parent` varchar(10) NOT NULL,
  `mi_active` tinyint(1) NOT NULL DEFAULT '1',
  `mi_type` enum('page','link','iframe','include','module','file') NOT NULL,
  `mi_item_id` int(10) NOT NULL,
  `mi_mod_instance` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`mi_id`),
  UNIQUE KEY `unique tag parent combo` (`mi_tag`,`mi_parent`),
  KEY `template_id` (`template_id`),
  KEY `mi_mod_instance` (`mi_mod_instance`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_menuitem_translation` (
  `mi_id` int(10) NOT NULL,
  `lang_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  PRIMARY KEY (`mi_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_modules` (
  `m_id` int(11) NOT NULL AUTO_INCREMENT,
  `m_path` varchar(255) NOT NULL,
  `m_name` varchar(255) NOT NULL,
  `m_has_admin` tinyint(1) NOT NULL,
  `m_is_active` tinyint(1) NOT NULL,
  `m_is_owned` tinyint(1) NOT NULL,
  `m_supports_multiple_instances` tinyint(1) NOT NULL,
  PRIMARY KEY (`m_path`),
  UNIQUE KEY `m_name` (`m_name`),
  UNIQUE KEY `m_id` (`m_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_module_instances` (
  `module` int(11) NOT NULL,
  `menuitem` int(11) NOT NULL,
  `instance` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`instance`),
  KEY `module` (`module`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_module_settings` (
  `module` int(11) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_page` (
  `p_id` int(10) NOT NULL AUTO_INCREMENT,
  `p_intrash` tinyint(1) NOT NULL DEFAULT '0',
  `locked` int(15) DEFAULT NULL,
  `locked_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`p_id`),
  KEY `locked_by` (`locked_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_page_translation` (
  `p_id` int(10) NOT NULL,
  `lang_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `text` text,
  PRIMARY KEY (`p_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_plugins` (
  `p_id` int(10) NOT NULL AUTO_INCREMENT,
  `p_code` varchar(255) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Boolean',
  `p_owned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Boolean',
  PRIMARY KEY (`p_id`),
  UNIQUE KEY `p_code` (`p_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_plugin_settings` (
  `plugin` int(10) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_settings` (
  `sitename` varchar(255) NOT NULL,
  `default_language` int(10) NOT NULL,
  `default_menuitem` int(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `googleanalytics` varchar(50) NOT NULL,
  `system-mail` varchar(255) NOT NULL,
  `disable_rightclick` tinyint(1) NOT NULL DEFAULT '0',
  `ie_warning` tinyint(1) NOT NULL DEFAULT '0',
  `offline` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `menu_style` enum('show-all-children','show-active-children') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_whitelist` (
`ip` BIGINT NOT NULL ,
PRIMARY KEY ( `ip` )
) ENGINE = InnoDB;");

$db->querySafe("INSERT INTO `cms_whitelist`
            VALUES (1411379055)");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template_css` (
  `template_id` int(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`template_id`,`filename`),
  KEY `fk_cms_template_css_cms_template1` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template_js` (
  `template_id` int(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`template_id`,`filename`),
  KEY `fk_cms_template_js_cms_template1` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_user` (
  `u_id` int(10) NOT NULL AUTO_INCREMENT,
  `u_name` varchar(255) NOT NULL,
  `u_fullname` varchar(255) NOT NULL,
  `u_email` varchar(255) NOT NULL,
  `u_password` char(40) NOT NULL,
  `u_active` tinyint(1) NOT NULL DEFAULT '1',
  `u_admin` tinyint(1) NOT NULL DEFAULT '0',
  `u_verified` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Hook for modules that want to add users, but have a check in between. All this says is, \"the user registration isn''t complete yet\", the module will need to keep a list of these users itself.',
  PRIMARY KEY (`u_id`),
  UNIQUE KEY `name` (`u_name`),
  UNIQUE KEY `fullname` (`u_fullname`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_user_group` (
  `u_id` int(10) NOT NULL,
  `g_id` int(10) NOT NULL,
  PRIMARY KEY (`u_id`,`g_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `log_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  `hostname` varchar(150) NOT NULL,
  `succesfull` tinyint(1) NOT NULL,
  `fail_reason` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `succesfull` (`succesfull`),
  KEY `user_id` (`user_id`),
  KEY `time` (`time`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `log_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` DATE NOT NULL,
  `ip` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `referer` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `rc_countries` (
  `code` char(2) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`code`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");


$db->querySafe("CREATE TABLE IF NOT EXISTS `mod_users_email_changes` (
  `userid` int(11) NOT NULL,
  `newaddress` varchar(255) NOT NULL,
  `validationkey` char(100) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `mod_users_reset_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `date` varchar(50) NOT NULL,
  `used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_id` (`u_id`),
  KEY `u_id_2` (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("CREATE TABLE IF NOT EXISTS `mod_users_signup_verification` (
  `u_id` int(11) NOT NULL,
  `key` char(100) NOT NULL,
  `date` int(11) NOT NULL,
  `step` int(11) NOT NULL,
  PRIMARY KEY (`u_id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

$db->querySafe("ALTER TABLE `cms_file_translation`
  ADD CONSTRAINT `cms_file_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_language` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cms_file_translation_ibfk_1` FOREIGN KEY (`f_id`) REFERENCES `cms_file` (`f_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_iframe_translation`
  ADD CONSTRAINT `cms_iframe_translation_ibfk_2` FOREIGN KEY (`ifr_id`) REFERENCES `cms_iframe` (`ifr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cms_iframe_translation_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `cms_language` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_link_translation`
  ADD CONSTRAINT `cms_link_translation_ibfk_2` FOREIGN KEY (`l_id`) REFERENCES `cms_link` (`l_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cms_link_translation_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `cms_language` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_menuitem`
  ADD CONSTRAINT `cms_menuitem_ibfk_2` FOREIGN KEY (`mi_mod_instance`) REFERENCES `cms_module_instances` (`instance`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `cms_menuitem_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `cms_template` (`template_id`) ON DELETE SET NULL ON UPDATE NO ACTION;");

$db->querySafe("ALTER TABLE `cms_menuitem_translation`
  ADD CONSTRAINT `cms_menuitem_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_language` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cms_menuitem_translation_ibfk_1` FOREIGN KEY (`mi_id`) REFERENCES `cms_menuitem` (`mi_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_module_instances`
  ADD CONSTRAINT `cms_module_instances_ibfk_1` FOREIGN KEY (`module`) REFERENCES `cms_modules` (`m_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_module_settings`
  ADD CONSTRAINT `cms_module_settings_ibfk_1` FOREIGN KEY (`module`) REFERENCES `cms_modules` (`m_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_page`
  ADD CONSTRAINT `cms_page_ibfk_1` FOREIGN KEY (`locked_by`) REFERENCES `cms_user` (`u_id`) ON DELETE SET NULL ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_page_translation`
  ADD CONSTRAINT `cms_page_translation_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `cms_page` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cms_page_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_language` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_template_css`
  ADD CONSTRAINT `cms_template_css_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `cms_template` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_template_js`
  ADD CONSTRAINT `cms_template_js_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `cms_template` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `mod_users_email_changes`
  ADD CONSTRAINT `mod_users_email_changes_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `cms_user` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `mod_users_reset_password`
  ADD CONSTRAINT `mod_users_reset_password_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `cms_user` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `mod_users_signup_verification`
  ADD CONSTRAINT `mod_users_signup_verification_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `cms_user` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$db->querySafe("ALTER TABLE `cms_settings` CHANGE `keywords` `keywords` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");

$db->querySafe("ALTER TABLE `cms_settings` ADD `website_offline` INT( 1 ) NOT NULL DEFAULT '0';");


//-----
//	Now that we have the tables, insert the default data.
//-----
$db->querySafe("
	INSERT INTO
			`cms_group`
	(
			`g_name`
		,	`g_admin`
	)
	VALUES
	(
			'Iedereen'
		,	0
	);
");

$db->querySafe("
	INSERT INTO
			`cms_language`
	(
		`lang_tag`,
		`lang_name`,
		`lang_searchengine_name`,
		`lang_flag`,
		`lang_locale`	
	)
	VALUES
	(
		'nl',
		'Nederlands',
		'Dutch',
		'/icons/flags/nl.png',
		'nl_NL'
	);
");

$db->querySafe("
	INSERT INTO
			`cms_page`
	(
		`p_intrash`
	)
	VALUES
	(
		0
	);
");

$db->querySafe("
	INSERT INTO
			`cms_page_translation`
	(
		`p_id`,
		`lang_id`,
		`name`,
		`text`
	)
	VALUES
	(
		1,
		1,
		'Huizinga CMS Voorbeeld Pagina',
		'<h1>Welkom op onze nieuwe website</h1><p>Vanaf heden maken wij gebruik van het <a href=\"http://www.huizinga.nl/nl/info/websites/cms/\">CMS van Huizinga Hosting & Webdesign</a>'
	);
");

$db->querySafe("
	INSERT INTO
			`cms_menuitem`
	(
		`mi_tag`,
		`mi_position`,
		`mi_parent`,
		`mi_active`,
		`mi_type`,
		`mi_item_id`
	)
	VALUES
	(
		'home',
		1,
		0,
		1,
		'page',
		1
	);
");


$db->querySafe("
	INSERT INTO
			`cms_menuitem_translation`
	(
		`mi_id`,
		`lang_id`,
		`name`,
		`description`,
		`keywords`
	)
	VALUES
	(
		1,
		1,
		'Home',
		'Huizinga CMS Voorbeeld Pagina',
		'huizinga, hosting, webdesign, webapplicatie'
	);
");

$db->querySafe("
	INSERT INTO
			`cms_item_permission`
	(
		`item`,
		`type`,
		`group`
	)
	VALUES
	(
		1,
		'page',
		1
	);
");

$db->querySafe("INSERT INTO `cms_user` (    `u_name` ,
                                            `u_fullname` ,
                                            `u_email` ,
                                            `u_password` ,
                                            `u_active` ,
                                            `u_admin` ,
                                            `u_verified`)
       VALUES ('HHW', 'HHW Admin', 'cms@huizinga.nl', '', '1', '1', '1')", true);
$db->querySafe("UPDATE `cms_user` SET `u_id` = '0' WHERE `cms_user`.`u_name` ='HHW' AND `cms_user`.`u_fullname` = 'HHW Admin';");


$db->querySafe("
	INSERT INTO
			`cms_user`
	(
		`u_name`,
		`u_fullname`,
		`u_email`,
		`u_password`,
		`u_active`,
		`u_admin`,
		`u_verified`
	)
	VALUES
	(
		'admin',
		'Administrator',
		'" . $argv[2] . "',
		'" . sha1( $argv[3] ) . "',
		1,
		1,
		1
	)
");

$db->querySafe("
	INSERT INTO
			`cms_user_group`
	(
		`u_id`,
		`g_id`
	)
	VALUES
	(
		1,
		1
	)
");

$db->querySafe("INSERT INTO
	`cms_settings`
	(
		`sitename`,
		`default_language`,
		`default_menuitem`,
		`description`,
		`subject`,
		`keywords`,
		`googleanalytics`,
		`system-mail`,
		`disable_rightclick`,
		`menu_style`
	)
	VALUES
	(
		'" . $argv[1] . "',
		1,
		1,
		'',
		'',
		'',
		'',
		'" . $argv[2] . "',
		0,
		'show-active-children'
	)
");

$db->querySafe("INSERT INTO `cms_template` (`title`, `identifier`, `default`) VALUES
('Standaard', 'default', 1);");

$db->querySafe("INSERT INTO `cms_template_css` (`template_id`, `filename`, `order`) VALUES
(1, 'style.css', 1);");

$db->querySafe("INSERT INTO `cms_template_js` (`template_id`, `filename`, `order`) VALUES
(1, 'jquerySafe-1.4.2.min.js', 1),
(1, 'messages.js', 2);");

$db->querySafe("ALTER TABLE `cms_settings` ADD `fck_max_image_size` INT NOT NULL DEFAULT '800'");

$db->querySafe("ALTER TABLE `log_site` CHANGE `ip` `ip` INT( 11 ) NULL");

/*****
 STOP:
 Do not put something below our querySafe. This one is so freaking long, I vote we keep it at the end, so that things remain somewhere near findable in this mess.
 *****/
$db->querySafe("INSERT INTO `rc_countries` (`code`, `name`) VALUES
('AD', 'Andorra'),
('AE', 'United Arab Emirates'),
('AF', 'Afghanistan'),
('AG', 'Antigua And Barbuda'),
('AI', 'Anguilla'),
('AL', 'Albania'),
('AM', 'Armenia'),
('AN', 'Netherlands Antilles'),
('AO', 'Angola'),
('AQ', 'Antarctica'),
('AR', 'Argentina'),
('AS', 'American Samoa'),
('AT', 'Austria'),
('AU', 'Australia'),
('AW', 'Aruba'),
('AX', 'ï¿½land Islands'),
('AZ', 'Azerbaijan'),
('BA', 'Bosnia And Herzegovina'),
('BB', 'Barbados'),
('BD', 'Bangladesh'),
('BE', 'Belgium'),
('BF', 'Burkina Faso'),
('BG', 'Bulgaria'),
('BH', 'Bahrain'),
('BI', 'Burundi'),
('BJ', 'Benin'),
('BM', 'Bermuda'),
('BN', 'Brunei Darussalam'),
('BO', 'Bolivia'),
('BR', 'Brazil'),
('BS', 'Bahamas'),
('BT', 'Bhutan'),
('BV', 'Bouvet Island'),
('BW', 'Botswana'),
('BY', 'Belarus'),
('BZ', 'Belize'),
('CA', 'Canada'),
('CC', 'Cocos (Keeling) Islands'),
('CD', 'Congo, The Dem. Rep. Of The'),
('CF', 'Central African Republic'),
('CG', 'Congo'),
('CH', 'Switzerland'),
('CK', 'Cook Islands'),
('CL', 'Chile'),
('CM', 'Cameroon'),
('CN', 'China'),
('CO', 'Colombia'),
('CR', 'Costa Rica'),
('CS', 'Serbia And Montenegro'),
('CU', 'Cuba'),
('CV', 'Cape Verde'),
('CX', 'Christmas Island'),
('CY', 'Cyprus'),
('CZ', 'Czech Republic'),
('DE', 'Germany'),
('DJ', 'Djibouti'),
('DK', 'Denmark'),
('DM', 'Dominica'),
('DO', 'Dominican Republic'),
('DZ', 'Algeria'),
('EC', 'Ecuador'),
('EE', 'Estonia'),
('EG', 'Egypt'),
('EH', 'Western Sahara'),
('ER', 'Eritrea'),
('ES', 'Spain'),
('ET', 'Ethiopia'),
('FI', 'Finland'),
('FJ', 'Fiji'),
('FK', 'Falkland Islands (Malvinas)'),
('FM', 'Micronesia'),
('FO', 'Faroe Islands'),
('FR', 'France'),
('GA', 'Gabon'),
('GB', 'United Kingdom'),
('GD', 'Grenada'),
('GE', 'Georgia'),
('GF', 'French Guiana'),
('GH', 'Ghana'),
('GI', 'Gibraltar'),
('GL', 'Greenland'),
('GM', 'Gambia'),
('GN', 'Guinea'),
('GP', 'Guadeloupe'),
('GQ', 'Equatorial Guinea'),
('GR', 'Greece'),
('GS', 'South Georgia'),
('GT', 'Guatemala'),
('GU', 'Guam'),
('GW', 'Guinea-Bissau'),
('GY', 'Guyana'),
('HK', 'Hong Kong'),
('HM', 'Heard Island'),
('HN', 'Honduras'),
('HR', 'Croatia'),
('HT', 'Haiti'),
('HU', 'Hungary'),
('ID', 'Indonesia'),
('IE', 'Ireland'),
('IL', 'Israel'),
('IN', 'India'),
('IO', 'British Indian Ocean Terr.'),
('IQ', 'Iraq'),
('IR', 'Iran, Islamic Republic Of'),
('IS', 'Iceland'),
('IT', 'Italy'),
('JM', 'Jamaica'),
('JO', 'Jordan'),
('JP', 'Japan'),
('KE', 'Kenya'),
('KG', 'Kyrgyzstan'),
('KH', 'Cambodia'),
('KI', 'Kiribati'),
('KM', 'Comoros'),
('KN', 'Saint Kitts And Nevis'),
('KR', 'Korea, Republic Of'),
('KW', 'Kuwait'),
('KY', 'Cayman Islands'),
('KZ', 'Kazakhstan'),
('LB', 'Lebanon'),
('LC', 'Saint Lucia'),
('LI', 'Liechtenstein'),
('LK', 'Sri Lanka'),
('LR', 'Liberia'),
('LS', 'Lesotho'),
('LT', 'Lithuania'),
('LU', 'Luxembourg'),
('LV', 'Latvia'),
('LY', 'Libyan Arab Jamahiriya'),
('MA', 'Morocco'),
('MC', 'Monaco'),
('MD', 'Moldova, Republic Of'),
('MG', 'Madagascar'),
('MH', 'Marshall Islands'),
('MK', 'Macedonia'),
('ML', 'Mali'),
('MM', 'Myanmar'),
('MN', 'Mongolia'),
('MO', 'Macao'),
('MP', 'Northern Mariana Islands'),
('MQ', 'Martinique'),
('MR', 'Mauritania'),
('MS', 'Montserrat'),
('MT', 'Malta'),
('MU', 'Mauritius'),
('MV', 'Maldives'),
('MW', 'Malawi'),
('MX', 'Mexico'),
('MY', 'Malaysia'),
('MZ', 'Mozambique'),
('NA', 'Namibia'),
('NC', 'New Caledonia'),
('NE', 'Niger'),
('NF', 'Norfolk Island'),
('NG', 'Nigeria'),
('NI', 'Nicaragua'),
('NL', 'Netherlands'),
('NO', 'Norway'),
('NP', 'Nepal'),
('NR', 'Nauru'),
('NU', 'Niue'),
('NZ', 'New Zealand'),
('OM', 'Oman'),
('PA', 'Panama'),
('PE', 'Peru'),
('PF', 'French Polynesia'),
('PG', 'Papua New Guinea'),
('PH', 'Philippines'),
('PK', 'Pakistan'),
('PL', 'Poland'),
('PM', 'Saint Pierre And Miquelon'),
('PN', 'Pitcairn'),
('PR', 'Puerto Rico'),
('PS', 'Palestinian Territory'),
('PT', 'Portugal'),
('PW', 'Palau'),
('PY', 'Paraguay'),
('QA', 'Qatar'),
('RE', 'Reunion'),
('RO', 'Romania'),
('RU', 'Russian Federation'),
('RW', 'Rwanda'),
('SA', 'Saudi Arabia'),
('SB', 'Solomon Islands'),
('SC', 'Seychelles'),
('SD', 'Sudan'),
('SE', 'Sweden'),
('SG', 'Singapore'),
('SH', 'Saint Helena'),
('SI', 'Slovenia'),
('SJ', 'Svalbard And Jan Mayen'),
('SK', 'Slovakia'),
('SL', 'Sierra Leone'),
('SM', 'San Marino'),
('SN', 'Senegal'),
('SO', 'Somalia'),
('SR', 'Suriname'),
('ST', 'Sao Tome And Principe'),
('SV', 'El Salvador'),
('SY', 'Syrian Arab Republic'),
('SZ', 'Swaziland'),
('TC', 'Turks And Caicos Islands'),
('TD', 'Chad'),
('TF', 'French Southern Territories'),
('TG', 'Togo'),
('TH', 'Thailand'),
('TJ', 'Tajikistan'),
('TK', 'Tokelau'),
('TL', 'Timor-Leste'),
('TM', 'Turkmenistan'),
('TN', 'Tunisia'),
('TO', 'Tonga'),
('TR', 'Turkey'),
('TT', 'Trinidad And Tobago'),
('TV', 'Tuvalu'),
('TW', 'Taiwan'),
('TZ', 'Tanzania, United Republic Of'),
('UA', 'Ukraine'),
('UG', 'Uganda'),
('UM', 'United States M.O. Islands'),
('US', 'United States'),
('UY', 'Uruguay'),
('UZ', 'Uzbekistan'),
('VA', 'Holy See'),
('VC', 'Saint Vincent And The Grenadines'),
('VE', 'Venezuela'),
('VG', 'Virgin Islands, British'),
('VI', 'Virgin Islands, U.S.'),
('VN', 'Viet Nam'),
('VU', 'Vanuatu'),
('WF', 'Wallis And Futuna'),
('WS', 'Samoa'),
('YE', 'Yemen'),
('YT', 'Mayotte'),
('ZA', 'South Africa'),
('ZM', 'Zambia'),
('ZW', 'Zimbabwe');");
?>