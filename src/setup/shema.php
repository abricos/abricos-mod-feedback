<?php
/**
 * @package Abricos
 * @subpackage Feedback
 * @copyright 2009-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current; 
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isInstall()){
	
	// таблица сообщений
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."fb_message (
		  messageid int(10) unsigned NOT NULL auto_increment,
		  userid int(10) unsigned NOT NULL default '0' COMMENT 'идентификатор пользователя, 0-гость',
		  globalmessageid varchar(32) NOT NULL default '' COMMENT 'глобальный идентификатор сообщения',
		  fio varchar(250) NOT NULL default '' COMMENT 'Контактное лицо',
		  phone varchar(250) NOT NULL default '' COMMENT 'Телефон',
		  email varchar(250) NOT NULL default '' COMMENT 'E-mail',
		  message TEXT NOT NULL COMMENT 'Сообщение',
		  overfields TEXT NOT NULL COMMENT 'Over Fields in JSON',
		  status int(1) unsigned NOT NULL default 0 COMMENT 'Статус: 0-поступившее, 1-был дан ответ',
		  dateline int(10) unsigned NOT NULL default '0',
		  deldate int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (messageid)
		 )".$charset
	);

	// ответы администрации сайта
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."fb_reply (
		  replyid int(10) unsigned NOT NULL auto_increment,
		  userid int(10) unsigned NOT NULL default '0' COMMENT 'идентификатор пользователя' ,
		  messageid int(10) unsigned NOT NULL default '0' COMMENT 'идентификатор сообщения' ,
		  body TEXT NOT NULL,
		  dateline int(10) unsigned NOT NULL default '0',
		  deldate int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (replyid)
		 )".$charset
	);
}

if ($updateManager->isUpdate('0.2.3')){
	Abricos::GetModule('feedback')->permission->Install();
}

if ($updateManager->isUpdate('0.2.5.1') && !$updateManager->isInstall()){
    $db->query_write("
        ALTER TABLE ".$pfx."fb_message
        DROP owner,
        DROP ownerparam,
        ADD overfields TEXT NOT NULL COMMENT 'Over Fields in JSON'
    ");

    $db->query_write("DROP TABLE IF EXISTS ".$pfx."fb_admin");
}

?>