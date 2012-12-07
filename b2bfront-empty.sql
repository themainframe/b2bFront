# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.25)
# Database: b2bfront
# Generation Time: 2012-08-20 03:39:33 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table bf_admin_downloads
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_downloads`;

CREATE TABLE `bf_admin_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `path` text,
  `timestamp` int(16) DEFAULT '0',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admin_drafts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_drafts`;

CREATE TABLE `bf_admin_drafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text,
  `content` text,
  `timestamp` int(16) DEFAULT '0',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admin_inventory_browse_filters
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_inventory_browse_filters`;

CREATE TABLE `bf_admin_inventory_browse_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `sql_where` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admin_inventory_browse_views
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_inventory_browse_views`;

CREATE TABLE `bf_admin_inventory_browse_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `query_string` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admin_notifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_notifications`;

CREATE TABLE `bf_admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `content` text,
  `icon_url` text,
  `popup_needed` int(1) DEFAULT '1',
  `logged` int(1) DEFAULT '0',
  `delete_on_view` int(1) DEFAULT '0',
  `timestamp` int(16) DEFAULT '0',
  `email_required` int(1) DEFAULT '0',
  `relevance` text,
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rel` (`relevance`(64),`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admin_profiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_profiles`;

CREATE TABLE `bf_admin_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `can_account` int(1) DEFAULT '1',
  `can_categories` int(1) DEFAULT '1',
  `can_items` int(1) DEFAULT '1',
  `can_orders` int(1) DEFAULT '1',
  `can_website` int(1) DEFAULT '1',
  `can_system` int(1) DEFAULT '1',
  `can_login` int(1) DEFAULT '1',
  `can_stats` int(1) DEFAULT '1',
  `can_chat` int(1) DEFAULT '1',
  `can_data` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_admin_profiles` WRITE;
/*!40000 ALTER TABLE `bf_admin_profiles` DISABLE KEYS */;

INSERT INTO `bf_admin_profiles` (`id`, `name`, `can_account`, `can_categories`, `can_items`, `can_orders`, `can_website`, `can_system`, `can_login`, `can_stats`, `can_chat`, `can_data`)
VALUES
	(1,'Administrators',1,1,1,1,1,1,1,1,1,1);

/*!40000 ALTER TABLE `bf_admin_profiles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_admin_question_referrals
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admin_question_referrals`;

CREATE TABLE `bf_admin_question_referrals` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT '-1',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_admins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_admins`;

CREATE TABLE `bf_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `password` tinytext,
  `full_name` text,
  `description` tinytext,
  `email` text,
  `mobile_number` text,
  `inventory_default_view` text,
  `last_login_timestamp` int(16) DEFAULT '0',
  `last_activity_timestamp` int(16) DEFAULT '0',
  `online` int(1) DEFAULT '0',
  `supervisor` int(1) DEFAULT '0',
  `call_answer_count` int(11) DEFAULT '0',
  `notification_new_order` int(1) NOT NULL DEFAULT '0',
  `notification_note_added` int(1) NOT NULL DEFAULT '0',
  `notification_request_for_account` int(1) NOT NULL DEFAULT '0',
  `notification_new_question` int(1) NOT NULL DEFAULT '0',
  `notification_target_met` int(1) NOT NULL DEFAULT '0',
  `notification_target_missed` int(1) NOT NULL DEFAULT '0',
  `notification_system_event` int(1) NOT NULL DEFAULT '0',
  `notification_new_data_jobs` int(1) NOT NULL DEFAULT '0',
  `notification_dealer_login` int(1) DEFAULT '1',
  `notification_dealer_logout` int(1) DEFAULT '1',
  `profile_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='notification_ Columns:  1: ACP Only, 2: 1+Email, 3: 2+SMS';

LOCK TABLES `bf_admins` WRITE;
/*!40000 ALTER TABLE `bf_admins` DISABLE KEYS */;

INSERT INTO `bf_admins` (`id`, `name`, `password`, `full_name`, `description`, `email`, `mobile_number`, `inventory_default_view`, `last_login_timestamp`, `last_activity_timestamp`, `online`, `supervisor`, `call_answer_count`, `notification_new_order`, `notification_note_added`, `notification_request_for_account`, `notification_new_question`, `notification_target_met`, `notification_target_missed`, `notification_system_event`, `notification_new_data_jobs`, `notification_dealer_login`, `notification_dealer_logout`, `profile_id`)
VALUES
	(1,'root','63a9f0ea7bb98050796b649e85481845','Damien Walsh','Web Developer','m4infr4me@gmail.com','07545193588','f_term=C32&f_in=sku&f_filter=-1&f_category=-2&inventory_pg=0&inventory_lpp=0&inventory_order_d=&inventory_order=0&x_show_parents=1&f_classification=-2&f_label=-1&f_brand=-2',1345433041,1345433914,0,1,1,2,2,2,2,2,2,2,2,1,1,1);

/*!40000 ALTER TABLE `bf_admins` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_api_keys
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_api_keys`;

CREATE TABLE `bf_api_keys` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_article_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_article_categories`;

CREATE TABLE `bf_article_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `designation` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_article_categories` WRITE;
/*!40000 ALTER TABLE `bf_article_categories` DISABLE KEYS */;

INSERT INTO `bf_article_categories` (`id`, `name`, `designation`)
VALUES
	(1,'Trash','-trash-');

/*!40000 ALTER TABLE `bf_article_categories` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_articles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_articles`;

CREATE TABLE `bf_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `content` text,
  `meta_content` text,
  `timestamp` int(16) DEFAULT '0',
  `expiry_timestamp` int(16) DEFAULT '0',
  `article_category_id` int(11) DEFAULT '-1',
  `type` text NOT NULL COMMENT 'ART_TEXT,ART_ITEM,ART_IMAGE,ART_ITEM_COLLECTION,ART_CATEGORY,ART_CLASSIFICATION',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_brands
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_brands`;

CREATE TABLE `bf_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `image_path` text,
  `primary_classification_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_categories`;

CREATE TABLE `bf_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `image_id` int(11) DEFAULT '-1',
  `visible` int(1) DEFAULT '1',
  `parent_child_display_mode` text,
  `category_group_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_category_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_category_groups`;

CREATE TABLE `bf_category_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_cctv
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_cctv`;

CREATE TABLE `bf_cctv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` text,
  `session_id` text,
  `timestamp` int(16) DEFAULT NULL,
  `user_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_chat
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_chat`;

CREATE TABLE `bf_chat` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text,
  `timestamp` int(16) DEFAULT '0',
  `read` int(1) DEFAULT '0',
  `direction` int(1) DEFAULT '0' COMMENT '0 - A=>U   1 - U => A',
  `meta` text COMMENT 'Metadata',
  `user_id` int(11) DEFAULT '-1',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_classification_attributes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_classification_attributes`;

CREATE TABLE `bf_classification_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `classification_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_classifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_classifications`;

CREATE TABLE `bf_classifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `stock_low_threshold` int(11) DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_config`;

CREATE TABLE `bf_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `value` text,
  `default` text,
  `nice_name` text,
  `description` text,
  `type` text,
  `admin_editable` int(1) DEFAULT '1',
  `domain_id` int(11) DEFAULT '-1',
  `choice_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_config` WRITE;
/*!40000 ALTER TABLE `bf_config` DISABLE KEYS */;

INSERT INTO `bf_config` (`id`, `name`, `value`, `default`, `nice_name`, `description`, `type`, `admin_editable`, `domain_id`, `choice_id`)
VALUES
	(1,'com.b2bfront.acp.tips','1','1','Enable tips','Enable visual hints and tips in the Admin panel.','boolean',1,1,-1),
	(2,'com.b2bfront.mail.from','b2bFront','b2bFront','From Name','When sending mail, this From: name will be used.','text',1,2,-1),
	(3,'com.b2bfront.restorepoints.ttl','7','7','Maximum age','The number of days a Restore Point will be stored for after it is created.','integer',1,3,-1),
	(4,'com.b2bfront.restorepoints.max-diskspace','512','512','Maximum disk usage','The amount of disk space allowed for Restore Point storage in MB (megabytes).','integer',1,3,-1),
	(5,'com.b2bfront.restorepoints.auto','1','1','Automatic creation','Enable the automatic creation of Restore Points.','boolean',1,3,-1),
	(6,'com.b2bfront.mail.honour-opt-outs','1','1','Honour mail opt-outs','Honour opt-out flags for mass mailings on dealer accounts.<br />\nStrongly recommended for legal reasons.','boolean',1,2,-1),
	(29,'com.b2bfront.acp.max-draft-age','7','7','Maximum draft age','The number of days a draft will be stored for after it\'s last modification.','integer',1,1,-1),
	(30,'com.b2bfront.rackspace.api-key','API Key','API Key','API Key','The Subscription API Key that authenticates b2bFront with The Rackspace Cloud.','text',1,5,-1),
	(31,'com.b2bfront.rackspace.username','Username','Username','Username','The Subscription Username to use when connecting with The Rackspace Cloud.','text',1,5,-1),
	(35,'com.b2bfront.images.thumbnail-type','jpg','jpg','Thumbnail type','The type of file to save thumbnails as.  Either jpg, png or gif.','text',1,6,-1),
	(33,'com.b2bfront.rackspace.container-url','Container URL','Container URL','Container URL','The URL of the container into which files will be uploaded.','text',1,5,-1),
	(32,'com.b2bfront.rackspace.container','Container Name','Container Name','Container Name','The name of the container into which files will be uploaded.','text',1,5,-1),
	(34,'com.b2bfront.images.temp','/temp/','/temp/','Temporary directory','The location relative to the root directory into which images are held on a temporary basis.','text',1,6,-1),
	(36,'com.b2bfront.security.url-session-ids','1','1','URL-passed session IDs','Allow Session IDs to be passed in the Query String.','boolean',1,7,-1),
	(37,'com.b2bfront.site.url','http://www.coyote-sports.com/','http://www.coyote-sports.com/','Website URL','The full URL of the website with a trailing forward slash. ','text',1,8,-1),
	(38,'com.b2bfront.sms.username','Username','Username','Mediaburst API Username','The username of the Mediaburst SMS API account to log in with.','text',1,10,-1),
	(39,'com.b2bfront.sms.password','Password','Password','Mediaburst API Password','The password of the Mediaburst SMS API to log in with.','text',1,10,-1),
	(40,'com.b2bfront.sms.origin','b2bFront','b2bFront','SMS Origin','An origin name between 1 and 11 characters in length used for SMS messages.<br />\nThis is where SMS messages will appear to be sent from.','text',1,10,-1),
	(41,'com.b2bfront.rss.enable','1','1','Enable RSS','Enable RSS Syndication.','boolean',1,9,-1),
	(42,'com.b2bfront.sms.enable','1','1','Enable SMS','Enable the sending of Short Message Service (SMS) messages','boolean',1,10,-1),
	(43,'com.b2bfront.images.hard','/store/image/','/store/image/','Hard directory','The location relative to the root directory in which images are stored long term.','text',1,6,-1),
	(44,'com.b2bfront.brands.max-logo-proportion','20','20','Max logo proportion error','The maximum allowed percentage away from a true square brand logo image.','integer',1,11,-1),
	(45,'com.b2bfront.brands.show','1','1','Visible','Enable the display of brand names and logos in the website.','boolean',1,11,-1),
	(46,'com.b2bfront.brands.max-logo-size','100','100','Maximum logo size','The maximum width/height of logos in pixels.','integer',1,11,-1),
	(50,'com.b2bfront.security.cron-staff-username','cron','cron','Cron staff username','The username of the staff user that automated scripts run as.','text',1,7,-1),
	(49,'com.b2bfront.security.cron-staff-password','password','password','Cron staff password','The password of the staff user that automated scripts run as.','text',1,7,-1),
	(48,'com.b2bfront.security.cron-token','Choose a secure string','Choose a secure string','Cron token','A token that Cron/Scheduled Tasks must pass to the software to run.','text',1,7,-1),
	(51,'com.b2bfront.site.smart-tags','0','0','Smart view tags','Enable auto-commented template tags in the MVC system.<br />\nThis can improve page appearance while testing new models.','boolean',1,8,-1),
	(52,'com.b2bfront.site.double-precision','2','2','Double number precision','The number of decimal places to use when displaying double precision numbers.<br />\nThis may be overridden in places.','integer',1,8,-1),
	(53,'com.b2bfront.site.reveal-subviews','0','0','Reveal Subviews','Reveal subviews using green borders.<br />\nUseful for debugging views under development.','boolean',1,8,-1),
	(54,'com.b2bfront.site.title','Your Company','Your Company','Website Title','The title of your website.  Generally your company name.','text',1,8,-1),
	(55,'com.b2bfront.files.store','/store/etc/','/store/etc/','File Store Directory','The directory (relative to the root directory) into which uploaded files are saved.<br />\nOld files will be cleaned automatically.','text',1,12,-1),
	(57,'com.b2bfront.site.profiling','0','0','Profiling','Profile the performance of the website.<br />\nShould be disabled in a production environment.','boolean',1,8,-1),
	(58,'com.b2bfront.memcache.port','11211','11211','Port','The port number on which the Memcache service is offered.','integer',1,13,-1),
	(59,'com.b2bfront.memcache.host','localhost','localhost','Host','The hostname at which the Memcache service runs.','text',1,13,-1),
	(60,'com.b2bfront.site.default-image','/share/image/ui-missing.jpg','/share/image/ui-missing.jpg','Default Image','The image to display when no suitable image exists','text',1,8,-1),
	(62,'com.b2bfront.site.iframe-descriptions','0','0','IFrame Descriptions','Contain item descriptions within an IFrame element.','boolean',1,8,-1),
	(63,'com.b2bfront.site.skin','default','default','Skin','The skin to use when displaying the website.','text',1,8,-1),
	(64,'com.b2bfront.site.allowed-description-html','li,ul,b,i,u,br,p','li,ul,b,i,u,br,p','Allowed HTML tags in Descriptions','The HTML tags types allowed in item descriptions.<br />\nComma-separated, no &lt; or &gt; marks required.','text',1,8,-1),
	(65,'com.b2bfront.site.default-main-menu','1','1','Main Menu','The menu to provide to the skin as the \"main\" menu.','choice',1,8,2),
	(66,'com.b2bfront.security.default-profile','1','1','Default \"Logged-Out\" Dealer Profile','The profile to use for users that are not logged in.<br />Eg. members of the public.','choice',1,7,1),
	(67,'com.b2bfront.images.auto-rackspace','0','0','Automatically upload','Auto-upload new images to Rackspace Cloud.  Experimental.','boolean',1,6,-1),
	(68,'com.b2bfront.statistics.frequency','1','1','Statistical Period Frequency','The time at which a new statistical period starts.','choice',1,14,3),
	(69,'com.b2bfront.security.require-authentication','0','0','Force Log In','Force <em>all</em> users to log in.<br />\nThe website will be locked down to non-dealers.','boolean',1,7,-1),
	(91,'com.b2bfront.ordering.order-id-prefix','PN','PN','Order ID Prefix','A short string prepended to the start of all order IDs.','text',1,16,-1),
	(90,'com.b2bfront.site.locale','1','1','Website Locale','The locale used throughout the website and ACP.','choice',1,8,4),
	(89,'com.b2bfront.sms.no-late-messages','1','1','Prevent Late Messages','Prevent the SMS subsystem from sending messages between 12 AM and 6 AM.<br />\nMessages will be held until an acceptable time.','boolean',1,10,-1),
	(86,'com.b2bfront.crm.feedback-prompt','1','1','Prompt for Web Feedback','Ask users for feedback after completing the order process.','boolean',1,15,-1),
	(87,'com.b2bfront.crm.feedback-email','webmaster@example.com','webmaster@example.com','Web Feedback Email','The address to which users will send website feedback information.','text',1,15,-1),
	(92,'com.b2bfront.crm.reveal-staff-names','0','0','Show Staff Names','Show real staff names on the website.','boolean',1,15,-1),
	(93,'com.b2bfront.mail.from-address','b2bfront@localhost','b2bfront@localhost','From Address','When sending mail, this From: address will be used.','text',1,2,-1),
	(94,'com.b2bfront.mail.default-template','default','default','Default Mail Template','The name of the template to use when sending automated mail to dealers.','text',1,2,-1),
	(98,'com.b2bfront.mail.templates-directory','/extensions/mail_templates/','/extensions/mail_templates/','Mail Templates Directory','The location in which mail template bundles are stored.','text',1,2,-1),
	(99,'com.b2bfront.acp.dashboard-public-activity','1','1','Show Public Activity on Dashboard','Show public activity (I.e. activity not by logged-in dealers) on the ACP dashboard.','boolean',1,1,-1),
	(100,'com.b2bfront.plugins.location','/extensions/plugins/','/extensions/plugins/','Location','The location from which plugins are loaded.','text',1,17,-1),
	(101,'com.b2bfront.plugins.disabled-plugins','HelloWorld','HelloWorld','Disabled Plugins','A comma-separated list of disabled plugins.','text',1,17,-1),
	(97,'com.b2bfront.plugins.enable','1','1','Enable','Enable the plugins subsystem.','boolean',1,17,-1),
	(103,'com.b2bfront.acp.update-children-default','1','1','Default Child Update Policy','The default state of checkboxes to update child items while modifying a parent.','boolean',1,1,-1),
	(104,'com.b2bfront.acp.inventory-tags-visible','1','1','Show Tag Icons in Inventory','Show Item Tag icons in the inventory list view.','boolean',1,1,-1),
	(105,'com.b2bfront.acp.inventory-labels-visible','1','1','Show Label Colours in Inventory','Show Item Label colours in the inventory list view.','boolean',1,1,-1),
	(106,'com.b2bfront.crm.allow-sms-notify','1','1','Allow SMS Stock Notifications','Allow dealers to request SMS stock availability notifications.','boolean',1,15,-1),
	(107,'com.b2bfront.mail.default-admin-template','default','default','Default Internal Mail Template','The name of the template to use when sending automated mail to staff.','text',1,2,-1),
	(108,'com.b2bfront.mail.from-auto-address','b2bfront@localhost','b2bfront@localhost','Auto From Address','When sending automated mail to staff, this From: address will be used.','text',1,2,-1),
	(109,'com.b2bfront.mail.from-auto','b2bFront','b2bFront','Auto From Name','When sending automated mail to staff, this From: name will be used.','text',1,2,-1),
	(110,'com.b2bfront.acp.dashboard-motd-fortune','1','1','Use Fortune for Dashboard MOTD','Use output from the <tt>fortune</tt> UNIX program instead of Top Tips<br />\nfor the dashboard Message Of The Day.','boolean',1,1,-1),
	(111,'com.b2bfront.site.tidy-description-html','1','1','Auto-Tidy Description HTML','Use the <tt>tidy</tt> program to clean up bad item description HTML.','boolean',1,8,-1),
	(112,'com.b2bfront.site.description-html-utf8','1','1','Force UTF-8 Encoded Item Descriptions','Changes the encoding of all Item Descriptions to UTF-8.<br />\nFixes some issues with characters that render incorrectly.','boolean',1,8,-1),
	(113,'com.b2bfront.acp.floating-data-headers','1','1','Floating Data Headers','Make data headers follow your browser as you scroll down a page.<br />\nMay cause some browser incompatibility issues.','boolean',1,1,-1),
	(114,'com.b2bfront.notifications.daily-digest','0','0','Daily Digest','Send an email digest of each Staff User\'s notifications to them at the end of each day.','boolean',1,4,-1),
	(115,'com.b2bfront.brands.constrain-logo-proportions','1','1','Constrain Brand Logo Sizes','Prevent staff from uploading odd-sized brand images.','boolean',1,11,-1),
	(116,'com.b2bfront.site.news-article-category','1','1','News Article Source Category','The Article Category considered to contain articles relating to site news.','choice',1,8,5),
	(117,'com.b2bfront.item-tags.backgrounds','1','1','Backgrounds','Allow Item Tags to have background colours in list views.','boolean',1,18,-1),
	(118,'com.b2bfront.item-tags.font-effects','1','1','Font Effects','Allow Item Tags to have special font effects in list views.','boolean',1,18,-1),
	(119,'com.b2bfront.item-tags.icons','1','1','Icons','Allow Item Tags to have icons in list views.','boolean',1,18,-1),
	(120,'com.b2bfront.item-tags.show-as-categories','1','1','Show As Categories','Show Item Tags as categories on the home page.','boolean',1,18,-1),
	(121,'com.b2bfront.item-tags.featured','1','1','Featured Item Tag','The Item Tag considered to represent Featured items.','choice',1,18,6),
	(122,'com.b2bfront.site.ticker-article-category','1','1','Ticker Article Category','The Article Category considered to contain articles to use as items in the homepage ticker.','choice',1,8,5),
	(123,'com.b2bfront.site.ticker','0','0','Enable Homepage News Ticker','Enable the display of a news ticker/banner on the homepage.','boolean',1,8,-1),
	(124,'com.b2bfront.crm.facebook','0','0','Display Facebook Link','Indicate to views that they should display a Facebook link.','boolean',1,15,-1),
	(125,'com.b2bfront.ordering.print-template','default','default','Invoice Printing Template','The template to use when printing orders.<br />\n<span class=\"grey\">Named directories in /extensions/invoice_print_templates/</span>','text',1,16,-1),
	(126,'com.b2bfront.crm.support-email','sales@example.com','sales@example.com','Web Support Email','The address to which users will send requests for general website support.','text',1,15,-1),
	(128,'com.b2bfront.crm.purchase-history-length','30','30','Purchase History Length','The length of the buying history in days considered when sending \"Back In Stock\" reminders.<br />\nThis directive does not apply to dealer-requested stock availability notifications.','integer',1,15,-1),
	(129,'com.b2bfront.site.max-category-image-size','100','100','Max Category Image Size','Set the maximum size of category images on the homepage.','integer',1,8,-1),
	(130,'com.b2bfront.site.pcr-list-display-mode','0','0','Group Child Items in Category Views','Display child items as a single row in category list views.','boolean',1,8,-1),
	(131,'com.b2bfront.crm.user-provided-mobile-numbers','0','0','User Provided Mobile Phone Numbers','Allow users to provide their own mobile phone numbers via the website.','boolean',1,15,-1),
	(132,'com.b2bfront.ordering.order-email-template','orders','orders','Order Email Template','The name of the Email Template to use when sending order notifications to Admins.','text',1,16,-1),
	(133,'com.b2bfront.site.top-lines-count','50','50','Top Lines Count','The number of product lines to show in the \"Top Lines\" view.','integer',1,8,-1),
	(137,'com.b2bfront.site.twitter-account','b2bfront','b2bfront','Home Twitter Account','A Twitter account to download tweets and display if supported.','text',1,8,-1),
	(136,'com.b2bfront.acp.sounds','1','1','Enable Chat Message Sounds','Make a sound when a new chat message arrives and the chat window is closed.','boolean',1,1,-1);

/*!40000 ALTER TABLE `bf_config` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_config_choices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_config_choices`;

CREATE TABLE `bf_config_choices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` text,
  `column_name` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_config_choices` WRITE;
/*!40000 ALTER TABLE `bf_config_choices` DISABLE KEYS */;

INSERT INTO `bf_config_choices` (`id`, `table_name`, `column_name`)
VALUES
	(1,'bf_user_profiles','name'),
	(2,'bf_website_menus','description'),
	(3,'bf_statistics_periods','name'),
	(4,'bf_locales','name'),
	(5,'bf_article_categories','name'),
	(6,'bf_item_tags','name');

/*!40000 ALTER TABLE `bf_config_choices` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_config_domains
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_config_domains`;

CREATE TABLE `bf_config_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `title` text,
  `description` text,
  `icon_path` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_config_domains` WRITE;
/*!40000 ALTER TABLE `bf_config_domains` DISABLE KEYS */;

INSERT INTO `bf_config_domains` (`id`, `name`, `title`, `description`, `icon_path`)
VALUES
	(1,'com.b2bfront.acp','Admin Control Panel Settings','These settings control the behaviour of the ACP.','/acp/static/icon/wrench.png'),
	(2,'com.b2bfront.mail','Mail Settings','These settings change the way mail is dispatched.','/acp/static/icon/mail.png'),
	(3,'com.b2bfront.restorepoints','Automatic Backup Settings','These settings change the behaviour of the Backup system.','/acp/static/icon/lifebuoy.png'),
	(4,'com.b2bfront.notifications','Notification Settings','These settings change the way Staff are notified about events.','/acp/static/icon/information.png'),
	(5,'com.b2bfront.rackspace','Rackspace Cloud Settings','These settings control how the software connects to The Rackspace Cloud Web Services API','/acp/static/icon/network-clouds.png'),
	(6,'com.b2bfront.images','Image Settings','These settings control the way the software handles images.','/acp/static/icon/picture.png'),
	(7,'com.b2bfront.security','Security Settings','These settings control the security implemented by the software.','/acp/static/icon/key.png'),
	(8,'com.b2bfront.site','Website Settings','These settings control how the website is served.','/acp/static/icon/sitemap.png'),
	(9,'com.b2bfront.rss','Syndication Settings','These settings control how Really Simple Syndication (RSS) is served.','/acp/static/icon/feed.png'),
	(10,'com.b2bfront.sms','Text Message Settings','These settings control how the software connects to the Mediaburst SMS API.','/acp/static/icon/mobile-phone-cast.png'),
	(11,'com.b2bfront.brands','Brands Settings','These settings control how branding is managed.','/acp/static/icon/reg-trademark.png'),
	(12,'com.b2bfront.files','File Store Settings','These settings control how the software stores files that are uploaded.','/acp/static/icon/drive.png'),
	(13,'com.b2bfront.memcache','Memcache Settings','These settings control the operation of the Memcache module.','/acp/static/icon/memcached.png'),
	(14,'com.b2bfront.statistics','Statistics Settings','These settings control the behaviour of statistics recording.','/acp/static/icon/chart-up-color.png'),
	(15,'com.b2bfront.crm','CRM Settings','These settings control Customer Relationship Management aspects of the website.','/acp/static/icon/balloon.png'),
	(16,'com.b2bfront.ordering','Ordering Settings','These settings control the behaviour of the orders system.','/acp/static/icon/money-coin.png'),
	(17,'com.b2bfront.plugins','Plugin Settings','These settings control how third-party plugins are loaded their functionality.','/acp/static/icon/plug.png'),
	(18,'com.b2bfront.item-tags','Item Tag Settings','These settings control the behaviour of Item Tags.','/acp/static/icon/tags.png');

/*!40000 ALTER TABLE `bf_config_domains` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_data_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_data_jobs`;

CREATE TABLE `bf_data_jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text,
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_data_jobs_ignore
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_data_jobs_ignore`;

CREATE TABLE `bf_data_jobs_ignore` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_data_views
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_data_views`;

CREATE TABLE `bf_data_views` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `context` text,
  `order_key` int(11) DEFAULT NULL,
  `order_direction` int(1) DEFAULT NULL COMMENT '0 = ascending, 1 = descending',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_downloads
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_downloads`;

CREATE TABLE `bf_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` text,
  `name` text,
  `timestamp` int(16) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `mime_type` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_events
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_events`;

CREATE TABLE `bf_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(1) DEFAULT NULL,
  `title` text,
  `contents` tinytext,
  `attention_required` int(1) DEFAULT '1',
  `timestamp` int(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_events` WRITE;
/*!40000 ALTER TABLE `bf_events` DISABLE KEYS */;

INSERT INTO `bf_events` (`id`, `level`, `title`, `contents`, `attention_required`, `timestamp`)
VALUES
	(1,3,'ACP Security','There was an attempt to log in to the ACP with: admin<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br /',1,1345432869),
	(2,3,'ACP Security','There was an attempt to log in to the ACP with: admin<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br /',1,1345432884),
	(3,3,'ACP Security','There was an attempt to log in to the ACP with: admin<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br /',1,1345432887),
	(4,3,'ACP Security','There was an attempt to log in to the ACP with: admin<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br /',1,1345432894),
	(5,3,'ACP Security','There was an attempt to log in to the ACP with: admin<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br /',1,1345432896),
	(6,3,'ACP Security','There was an attempt to log in to the ACP with: <br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br />\n<br',1,1345432898),
	(7,3,'ACP Security','There was an attempt to log in to the ACP with: <br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br />\n<br',1,1345432936),
	(8,3,'ACP Security','There was an attempt to log in to the ACP with: <br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br />\n<br',1,1345432939),
	(9,3,'ACP Security','There was an attempt to log in to the ACP with: root<br />The attempt did not authenticate successfully.<br /><br />The ACP is secure.<br /><br />The attempt was made from: ::1<br />\n<br />\nServing URI: /b2bfront/acp/?login=true<br />\nRemote IP: ::1<br />',1,1345433006);

/*!40000 ALTER TABLE `bf_events` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_file_ttls
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_file_ttls`;

CREATE TABLE `bf_file_ttls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text,
  `expiry_timestamp` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_image_thumbnail_sizes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_image_thumbnail_sizes`;

CREATE TABLE `bf_image_thumbnail_sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `width` int(6) DEFAULT '640',
  `height` int(6) DEFAULT '480',
  `name` text,
  `suffix` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_images`;

CREATE TABLE `bf_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `size_x` int(11) DEFAULT '1',
  `size_y` int(11) DEFAULT '1',
  `size_bytes` int(11) DEFAULT '0',
  `timestamp` int(16) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_attribute_applications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_attribute_applications`;

CREATE TABLE `bf_item_attribute_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` text,
  `classification_attribute_id` int(11) DEFAULT '-1',
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_images`;

CREATE TABLE `bf_item_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) DEFAULT '1',
  `item_id` int(11) DEFAULT '-1',
  `image_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_label_applications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_label_applications`;

CREATE TABLE `bf_item_label_applications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_label_id` int(11) DEFAULT '-1',
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_label_id` (`item_label_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_labels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_labels`;

CREATE TABLE `bf_item_labels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `colour` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_item_labels` WRITE;
/*!40000 ALTER TABLE `bf_item_labels` DISABLE KEYS */;

INSERT INTO `bf_item_labels` (`id`, `name`, `colour`)
VALUES
	(2,'End of Line','red');

/*!40000 ALTER TABLE `bf_item_labels` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_item_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_links`;

CREATE TABLE `bf_item_links` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` text,
  `item_id` int(11) DEFAULT '-1',
  `item_id_target` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_notes`;

CREATE TABLE `bf_item_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` tinytext,
  `item_id` int(12) DEFAULT '-1',
  `checked` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_tag_applications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_tag_applications`;

CREATE TABLE `bf_item_tag_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT '-1',
  `item_tag_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`,`item_tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_tags`;

CREATE TABLE `bf_item_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `font_list_colour` text,
  `font_list_bold` int(1) DEFAULT '0',
  `font_list_italic` int(1) DEFAULT '0',
  `font_list_small_caps` int(1) DEFAULT '0',
  `icon_path` text,
  `masthead` int(1) DEFAULT '0',
  `masthead_image_path` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_item_units
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_item_units`;

CREATE TABLE `bf_item_units` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `quantity` int(11) DEFAULT '0',
  `item_id` int(11) DEFAULT '-1',
  `location_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_items`;

CREATE TABLE `bf_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` text CHARACTER SET latin1,
  `name` text CHARACTER SET latin1,
  `trade_price` float(10,2) DEFAULT '0.00',
  `pro_net_price` float(10,2) DEFAULT '0.00',
  `pro_net_qty` int(11) DEFAULT '1',
  `wholesale_price` float(10,2) DEFAULT '0.00',
  `rrp_price` float(10,2) DEFAULT '0.00',
  `cost_price` float(10,2) DEFAULT '0.00',
  `stock_free` int(11) DEFAULT '0',
  `stock_held` int(11) DEFAULT '0',
  `stock_date` int(16) DEFAULT '0',
  `barcode` text CHARACTER SET latin1,
  `description` text CHARACTER SET latin1,
  `visible` int(1) DEFAULT '1',
  `stop_on_zero_stock` int(1) DEFAULT '0',
  `notify_on_low_stock` int(1) DEFAULT '1',
  `notify_on_zero_stock` int(1) DEFAULT '1',
  `keywords` text CHARACTER SET latin1,
  `brand_id` int(11) DEFAULT '-1',
  `category_id` int(11) DEFAULT '-1',
  `classification_id` int(11) DEFAULT '-1',
  `subcategory_id` int(11) DEFAULT '-1',
  `parent_item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`(20))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table bf_locales
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_locales`;

CREATE TABLE `bf_locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `currency_name` text,
  `currency_html_entity` text,
  `icon_path` text,
  `currency_xr` float(6,5) DEFAULT '1.00000',
  `language_code` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_locales` WRITE;
/*!40000 ALTER TABLE `bf_locales` DISABLE KEYS */;

INSERT INTO `bf_locales` (`id`, `name`, `currency_name`, `currency_html_entity`, `icon_path`, `currency_xr`, `language_code`)
VALUES
	(1,'United Kingdom','Pounds','&pound;','/share/icon/locales/gb.png',1.00000,'enGB'),
	(2,'United States','US Dollar','&#36;','/share/icon/locales/us.png',1.00000,'enUS'),
	(3,'Europe','Euro','&euro;','/share/icon/locales/eu.png',1.00000,'enGB');

/*!40000 ALTER TABLE `bf_locales` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_locations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_locations`;

CREATE TABLE `bf_locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_matrix
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_matrix`;

CREATE TABLE `bf_matrix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` float(6,5) DEFAULT '1.00000',
  `band_id` int(11) DEFAULT '-1',
  `category_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_order_lines
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_order_lines`;

CREATE TABLE `bf_order_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(6) DEFAULT '1',
  `invoice_price_each` float(10,2) DEFAULT '0.00',
  `item_id` int(11) DEFAULT '-1',
  `order_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_order_notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_order_notes`;

CREATE TABLE `bf_order_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_name` text,
  `content` text,
  `timestamp` int(16) DEFAULT '0',
  `staff_only` int(1) DEFAULT '0',
  `author_is_staff` int(1) DEFAULT '0',
  `order_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_orders`;

CREATE TABLE `bf_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(16) DEFAULT '0',
  `processed` int(1) DEFAULT '0',
  `processed_timestamp` int(16) DEFAULT '0',
  `held` int(1) DEFAULT '0',
  `owner_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_outlet_snapshots
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_outlet_snapshots`;

CREATE TABLE `bf_outlet_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `timestamp` int(16) DEFAULT NULL,
  `rise` int(1) DEFAULT '1',
  `outlet_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_outlets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_outlets`;

CREATE TABLE `bf_outlets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `url` text,
  `xml_node_id` int(11) DEFAULT '0',
  `state_ok` int(1) DEFAULT '1',
  `modification_timestamp` int(16) DEFAULT '0',
  `notify_percentage_rrp` int(1) DEFAULT '0',
  `notify_value_rrp` int(1) DEFAULT '0',
  `notify_threshold` float(10,2) DEFAULT '0.00',
  `notify_triggered` int(1) DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '-1',
  `item_id` int(11) NOT NULL DEFAULT '-1',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_page_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_page_permissions`;

CREATE TABLE `bf_page_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) DEFAULT '-1',
  `page_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_pages`;

CREATE TABLE `bf_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `content` text,
  `public` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_parent_item_attribute_applications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_parent_item_attribute_applications`;

CREATE TABLE `bf_parent_item_attribute_applications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `value` text,
  `classification_attribute_id` int(11) DEFAULT '-1',
  `parent_item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_parent_item_variation_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_parent_item_variation_data`;

CREATE TABLE `bf_parent_item_variation_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` text,
  `item_id` int(11) DEFAULT '-1',
  `parent_item_variation_id` int(11) DEFAULT '-1',
  `parent_item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_parent_item_variations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_parent_item_variations`;

CREATE TABLE `bf_parent_item_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `parent_item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_parent_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_parent_items`;

CREATE TABLE `bf_parent_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` tinytext,
  `name` text,
  `trade_price` float(10,2) DEFAULT '0.00',
  `pro_net_price` float(10,2) DEFAULT '0.00',
  `pro_net_qty` int(11) DEFAULT '1',
  `wholesale_price` float(10,2) DEFAULT '0.00',
  `rrp_price` float(10,2) DEFAULT '0.00',
  `cost_price` float(10,2) DEFAULT '0.00',
  `description` text,
  `keywords` text,
  `brand_id` int(11) DEFAULT '-1',
  `category_id` int(11) DEFAULT '-1',
  `classification_id` int(11) DEFAULT '-1',
  `subcategory_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_question_answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_question_answers`;

CREATE TABLE `bf_question_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `timestamp` int(16) DEFAULT NULL,
  `question_id` int(11) DEFAULT '-1',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_questions`;

CREATE TABLE `bf_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(16) DEFAULT '0',
  `title` text,
  `content` text,
  `answered` int(1) DEFAULT '0',
  `user_id` int(11) DEFAULT '-1',
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_restore_points
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_restore_points`;

CREATE TABLE `bf_restore_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(16) DEFAULT '0',
  `creation_reason` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_scheduled_import_results
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_scheduled_import_results`;

CREATE TABLE `bf_scheduled_import_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` text,
  `type` text,
  `reason` text,
  `scheduled_import_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_scheduled_imports
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_scheduled_imports`;

CREATE TABLE `bf_scheduled_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `timestamp` int(16) NOT NULL DEFAULT '0',
  `notification_sms` int(1) DEFAULT '0',
  `notification_email` int(1) DEFAULT '0',
  `completed` int(1) DEFAULT '0',
  `create_new_skus` int(1) DEFAULT '0',
  `path` text,
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_sms_queue
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_sms_queue`;

CREATE TABLE `bf_sms_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `msisdn` text,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_statistic_snapshot_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_statistic_snapshot_data`;

CREATE TABLE `bf_statistic_snapshot_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` float DEFAULT '0',
  `snapshot_id` int(11) DEFAULT '-1',
  `statistic_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_statistic_snapshots
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_statistic_snapshots`;

CREATE TABLE `bf_statistic_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_statistics
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_statistics`;

CREATE TABLE `bf_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aftermarket` int(1) DEFAULT '0',
  `name` text,
  `description` text,
  `value` float DEFAULT '0',
  `domain_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_statistics` WRITE;
/*!40000 ALTER TABLE `bf_statistics` DISABLE KEYS */;

INSERT INTO `bf_statistics` (`id`, `aftermarket`, `name`, `description`, `value`, `domain_id`)
VALUES
	(1,0,'com.b2bfront.stats.website.hits','Hits',52565,3),
	(3,0,'com.b2bfront.stats.website.unique-visits','Unique Visits',29323,3),
	(4,0,'com.b2bfront.stats.users.file-downloads','File Downloads',0,1),
	(5,0,'com.b2bfront.stats.website.item-views','Item Views',7239,3),
	(6,0,'com.b2bfront.stats.users.bad-logins','Failed Dealer Logins',396,1),
	(8,0,'com.b2bfront.stats.admins.logins','Staff Logins',191,2),
	(9,0,'com.b2bfront.stats.users.account-requests','Requests for Accounts',0,1),
	(10,0,'com.b2bfront.stats.users.questions','Questions Submitted',7,1),
	(11,0,'com.b2bfront.stats.admins.data-imports','Data Imports Performed',12,2),
	(13,0,'com.b2bfront.stats.users.searches','Searches Performed',3747,1),
	(16,0,'com.b2bfront.stats.system.errors','Errors',44,6),
	(18,0,'com.b2bfront.stats.users.orders-submitted','Orders Submitted',174,1),
	(19,0,'com.b2bfront.stats.system.data-sent','Outgoing Bandwidth (KB)',915156,6),
	(20,0,'com.b2bfront.stats.users.baskets-cleared','Cleared Baskets',4,1),
	(24,0,'com.b2bfront.stats.financial.total-gross','Total Gross Order Value',49792.7,8),
	(23,0,'com.b2bfront.stats.financial.total-net','Total Net Order Value',49792.7,8),
	(28,0,'com.b2bfront.stats.admins.data-jobs-attended','Data Jobs Attended',0,2),
	(29,0,'com.b2bfront.stats.users.pages-viewed','Pages Viewed',189,1),
	(40,0,'com.b2bfront.stats.users.brand-clickthroughs','Brand Clickthroughs',29,1),
	(41,0,'com.b2bfront.stats.system.sms-messages','SMS Messages Sent',5,6),
	(42,0,'com.b2bfront.stats.users.tickets-printed','Tickets Printed',9,1),
	(43,0,'com.b2bfront.stats.users.logins','Logins',1975,1),
	(44,0,'com.b2bfront.stats.users.logouts','Logouts',200,1);

/*!40000 ALTER TABLE `bf_statistics` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_statistics_domains
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_statistics_domains`;

CREATE TABLE `bf_statistics_domains` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `title` text,
  `description` text,
  `icon_path` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_statistics_domains` WRITE;
/*!40000 ALTER TABLE `bf_statistics_domains` DISABLE KEYS */;

INSERT INTO `bf_statistics_domains` (`id`, `name`, `title`, `description`, `icon_path`)
VALUES
	(1,'com.b2bfront.stats.users','User Actions','Actions performed by dealers and public users','/acp/static/icon/users.png'),
	(2,'com.b2bfront.stats.admins','Staff Actions','Actions performed by staff','/acp/static/icon/user-business.png'),
	(3,'com.b2bfront.stats.website','Website Events','Website-related events','/acp/static/icon/sitemap.png'),
	(5,'com.b2bfront.stats.custom','Custom','Custom statistics','/acp/static/icon/counter.png'),
	(6,'com.b2bfront.stats.system','System','System statistics','/acp/static/icon/gear.png'),
	(8,'com.b2bfront.stats.financial','Financial','Financial statistics','/acp/static/icon/currency-pound.png'),
	(9,'com.b2bfront.stats.pages','Pages','Page statistics','/acp/static/icon/document-copy.png');

/*!40000 ALTER TABLE `bf_statistics_domains` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_statistics_periods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_statistics_periods`;

CREATE TABLE `bf_statistics_periods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `minute` int(11) DEFAULT NULL,
  `hour` int(11) DEFAULT NULL,
  `weekday` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `monthday` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_statistics_periods` WRITE;
/*!40000 ALTER TABLE `bf_statistics_periods` DISABLE KEYS */;

INSERT INTO `bf_statistics_periods` (`id`, `name`, `minute`, `hour`, `weekday`, `month`, `monthday`)
VALUES
	(1,'12AM, Monday, Every Week',55,23,1,-1,-1),
	(2,'12AM, First Day of Every Month',55,23,-1,-1,1),
	(3,'12AM, Every Day',55,23,-1,-1,-1),
	(4,'12AM, First Day, Every Year',55,23,-1,1,1),
	(5,'Every 5 Minutes - Do Not Use',-1,-1,-1,-1,-1);

/*!40000 ALTER TABLE `bf_statistics_periods` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_stock_replenishments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_stock_replenishments`;

CREATE TABLE `bf_stock_replenishments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(16) DEFAULT '0',
  `notification_sent` int(1) DEFAULT '0',
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_subcategories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_subcategories`;

CREATE TABLE `bf_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `category_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_targets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_targets`;

CREATE TABLE `bf_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text,
  `value` float DEFAULT '10',
  `notify_on_hit` int(1) DEFAULT '0',
  `statistic_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_targets` WRITE;
/*!40000 ALTER TABLE `bf_targets` DISABLE KEYS */;

INSERT INTO `bf_targets` (`id`, `description`, `value`, `notify_on_hit`, `statistic_id`)
VALUES
	(1,'2000 Hits',2000,0,3);

/*!40000 ALTER TABLE `bf_targets` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_trackbacks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_trackbacks`;

CREATE TABLE `bf_trackbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `hit_count` int(11) DEFAULT '0',
  `last_hit_timestamp` int(16) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_action_logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_action_logs`;

CREATE TABLE `bf_user_action_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` text,
  `session_id` text,
  `timestamp` int(16) DEFAULT NULL,
  `user_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_bands
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_bands`;

CREATE TABLE `bf_user_bands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_cart_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_cart_items`;

CREATE TABLE `bf_user_cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(11) DEFAULT '1',
  `item_id` int(11) DEFAULT '-1',
  `user_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_favourites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_favourites`;

CREATE TABLE `bf_user_favourites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '-1',
  `item_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_password_resets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_password_resets`;

CREATE TABLE `bf_user_password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_password` text,
  `token` text,
  `timestamp` int(16) DEFAULT '0',
  `user_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_prices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_prices`;

CREATE TABLE `bf_user_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trade_price` float(10,2) NOT NULL DEFAULT '0.00',
  `pro_net_price` float(10,2) NOT NULL DEFAULT '0.00',
  `user_id` int(11) NOT NULL DEFAULT '-1',
  `item_id` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_profiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_profiles`;

CREATE TABLE `bf_user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `can_see_prices` int(1) DEFAULT '1',
  `can_see_rrp` int(1) DEFAULT '1',
  `can_wholesale` int(1) DEFAULT '1',
  `can_pro_rate` int(1) DEFAULT '0',
  `can_order` int(1) DEFAULT '1',
  `can_question` int(1) DEFAULT '1',
  `can_see_stock` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_user_stock_notifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_user_stock_notifications`;

CREATE TABLE `bf_user_stock_notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '-1',
  `item_id` int(11) DEFAULT '-1',
  `type` text COMMENT 'sms|email',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_item` (`item_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_users`;

CREATE TABLE `bf_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  `description` text,
  `account_code` tinytext,
  `password` tinytext,
  `email` tinytext,
  `include_in_bulk_mailings` int(1) DEFAULT '1',
  `requires_review` int(1) DEFAULT '1',
  `address_building` text,
  `address_street` text,
  `address_city` text,
  `address_postcode` text,
  `phone_mobile` text,
  `phone_landline` text,
  `url` text,
  `slogan` text,
  `points` int(11) DEFAULT '0',
  `in_directory` int(11) DEFAULT '0',
  `profile_id` int(11) DEFAULT '-1',
  `locale_id` int(11) DEFAULT '-1',
  `band_id` int(11) DEFAULT '-1',
  `admin_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bf_website_menu_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_website_menu_items`;

CREATE TABLE `bf_website_menu_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `priority` int(11) DEFAULT '1',
  `name` text,
  `url` text,
  `menu_id` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_website_menu_items` WRITE;
/*!40000 ALTER TABLE `bf_website_menu_items` DISABLE KEYS */;

INSERT INTO `bf_website_menu_items` (`id`, `priority`, `name`, `url`, `menu_id`)
VALUES
	(1,1,'Home','./?option=',1),
	(2,2,'Search','./?option=search',1),
	(3,3,'Basket','./?option=basket',1),
	(4,4,'Contact','./?option=page&id=1',1),
	(5,5,'News','./?option=news',1),
	(6,6,'Downloads','./?option=downloads',1),
	(7,7,'New Items','./?option=featured',1),
	(8,8,'Brands','./?option=brands',1),
	(9,9,'Visit','./?option=page&id=2',1),
	(10,10,'Account','./?option=account',1);

/*!40000 ALTER TABLE `bf_website_menu_items` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bf_website_menus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bf_website_menus`;

CREATE TABLE `bf_website_menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `bf_website_menus` WRITE;
/*!40000 ALTER TABLE `bf_website_menus` DISABLE KEYS */;

INSERT INTO `bf_website_menus` (`id`, `name`, `description`)
VALUES
	(1,'tab_bar','Main Tab Bar');

/*!40000 ALTER TABLE `bf_website_menus` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
