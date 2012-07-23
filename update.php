<?php

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


$db->querySafe("ALTER TABLE `cms_file` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_file_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_group` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_iframe` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_iframe_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_include` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_include_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_item_edit_permission` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_item_permission` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_language` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_link` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_link_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_menuitem` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_menuitem_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_modules` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_module_instances` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_module_settings` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_page` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_page_translation` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_plugins` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_plugin_admins` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_plugin_settings` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_settings` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_user` ENGINE = InnoDB", true);
$db->querySafe("ALTER TABLE `cms_user_group` ENGINE = InnoDB", true);



/**
 * File
 */
$db->querySafe("ALTER TABLE `cms_file_translation` ADD INDEX ( `lang_id` )", true);

$db->querySafe("ALTER TABLE `cms_file_translation` ADD FOREIGN KEY ( `f_id` ) REFERENCES `cms_file` (
`f_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_file_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);






/**
 * Iframe
 */
$db->querySafe("ALTER TABLE `cms_iframe_translation` ADD INDEX ( `lang_id` )", true);

$db->querySafe("ALTER TABLE `cms_iframe_translation` ADD FOREIGN KEY ( `ifr_id` ) REFERENCES `cms_iframe` (
`ifr_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_iframe_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

/**
 * Include
 */
$db->querySafe("ALTER TABLE `cms_include_translation` ADD INDEX ( `lang_id` )", true);

$db->querySafe("ALTER TABLE `cms_include_translation` ADD FOREIGN KEY ( `inc_id` ) REFERENCES `cms_include` (
`inc_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_include_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

/**
 * Link
 */
$db->querySafe("ALTER TABLE `cms_link_translation` ADD INDEX ( `lang_id` )", true);

$db->querySafe("ALTER TABLE `cms_link_translation` ADD FOREIGN KEY ( `l_id` ) REFERENCES `cms_link` (
`l_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_link_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

/**
 * Menu
 */
$db->querySafe("ALTER TABLE `cms_menuitem_translation` ADD INDEX ( `lang_id` )", true);
$db->querySafe("ALTER TABLE `cms_menuitem` ADD INDEX ( `mi_mod_instance` )", true);

$db->querySafe("ALTER TABLE `cms_menuitem` ADD `template_id` INT NULL DEFAULT NULL", true);

$db->querySafe("ALTER TABLE `cms_menuitem` ADD FOREIGN KEY ( `mi_mod_instance` ) REFERENCES `cms_module_instances` (
`instance`
) ON DELETE SET NULL ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_menuitem_translation` ADD FOREIGN KEY ( `mi_id` ) REFERENCES `cms_menuitem` (
`mi_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_menuitem_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

/**
 * Module
 */
$db->querySafe("ALTER TABLE `cms_module_instances` ADD INDEX ( `module` )", true);

$db->querySafe("ALTER TABLE `cms_module_instances` ADD FOREIGN KEY ( `module` ) REFERENCES `cms_modules` (
`m_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_module_settings` ADD FOREIGN KEY ( `module` ) REFERENCES `cms_modules` (
`m_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

/**
 * Page
 */
$db->querySafe("ALTER TABLE `cms_page_translation` CHANGE `text` `text` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL", true);

$db->querySafe("ALTER TABLE `cms_page` ADD `locked` INT( 15 ) NULL DEFAULT NULL ,
ADD `locked_by` INT( 11 ) NULL DEFAULT NULL ", true);

$db->querySafe("ALTER TABLE `log_site` CHANGE `referer` `referer` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL", true);
$db->querySafe("ALTER TABLE `log_site` CHANGE `date_time` `date_time` DATE NOT NULL");

$db->querySafe("ALTER TABLE `cms_page` ADD INDEX ( `locked_by` )", true);

$db->querySafe("ALTER TABLE `cms_page` ADD FOREIGN KEY ( `locked_by` ) REFERENCES `cms_user` (
`u_id`
) ON DELETE SET NULL ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_page_translation` ADD INDEX ( `lang_id` )", true);

$db->querySafe("ALTER TABLE `cms_page_translation` ADD FOREIGN KEY ( `p_id` ) REFERENCES `cms_page` (
`p_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);

$db->querySafe("ALTER TABLE `cms_page_translation` ADD FOREIGN KEY ( `lang_id` ) REFERENCES `cms_language` (
`lang_id`
) ON DELETE CASCADE ON UPDATE CASCADE", true);



/**
 * Settings
 */
$db->querySafe("ALTER TABLE `cms_settings` CHANGE `description` `description` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
CHANGE `subject` `subject` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
$db->querySafe("ALTER TABLE `cms_settings` CHANGE `keywords` `keywords` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ", true);
$db->querySafe("ALTER TABLE `cms_settings` ADD `ie_warning` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `disable_rightclick`", true);
$db->querySafe("ALTER TABLE `cms_settings` ADD `offline` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `ie_warning`", true);

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_whitelist` (
`ip` BIGINT NOT NULL ,
PRIMARY KEY ( `ip` )
) ENGINE = InnoDB;");

$db->querySafe("INSERT INTO `cms_whitelist`
            VALUES (1411379055)");

/**
 * Logging
 */
$db->querySafe("ALTER TABLE `log_site` CHANGE `referer` `referer` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
$db->querySafe("ALTER TABLE `log_site` CHANGE `date_time` `date_time` DATE NOT NULL");
$db->querySafe("ALTER TABLE `log_logins` CHANGE `ip` `ip` INT( 11 ) NULL; ");


/**
 * Templates
 */
$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;", true);

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template_css` (
  `template_id` int(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`template_id`,`filename`),
  KEY `fk_cms_template_css_cms_template1` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", true);

$db->querySafe("CREATE TABLE IF NOT EXISTS `cms_template_js` (
  `template_id` int(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`template_id`,`filename`),
  KEY `fk_cms_template_js_cms_template1` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", true);

$db->querySafe("ALTER TABLE `cms_template_css`
  ADD CONSTRAINT `cms_template_css_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `cms_template` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE;", true);

$db->querySafe("ALTER TABLE `cms_template_js`
  ADD CONSTRAINT `cms_template_js_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `cms_template` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE;", true);



/**
 * User
 */
$db->querySafe("INSERT INTO `cms_user` (    `u_name` ,
                                            `u_fullname` ,
                                            `u_email` ,
                                            `u_password` ,
                                            `u_active` ,
                                            `u_admin` ,
                                            `u_verified`)
       VALUES ('HHW', 'HHW Admin', 'cms@huizinga.nl', '', '1', '1', '1')", true);
$db->querySafe("UPDATE `cms_user` SET `u_id` = '0' WHERE `cms_user`.`u_name` ='HHW' AND `cms_user`.`u_fullname` = 'HHW Admin';");

$db->querySafe("ALTER TABLE `cms_user` ORDER BY `u_id`");

$db->querySafe("ALTER TABLE `cms_settings` ADD `fck_max_image_size` INT NOT NULL DEFAULT '800'");

$db->querySafe("ALTER TABLE `log_site` CHANGE `ip` `ip` INT( 11 ) NULL");


?>