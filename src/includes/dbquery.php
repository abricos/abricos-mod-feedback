<?php 
/**
 * @package Abricos 
 * @subpackage Feedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

class FeedbackQuery {
	
	/**
	 * Новое сообщение 
	 * @var integer
	 */
	const MSG_NEW = 0;
	/**
	 * Сообщение на которое был дан ответ администратором сайта 
	 * @var integer
	 */
	const MSG_REPLY = 1;
	
	/**
	 * Добавить ответ на сообщение пользователя и изменить статус этого сообщения на отвеченное
	 * 
	 * @static
	 * @param Ab_Database $db менеджер базы данных
	 * @param integer $messageid идентификатор сообщения
	 * @param integer $userid идентификатор пользователя который дает ответ на сообщение
	 * @param string $body текст ответа
	 */
	public static function Reply(Ab_Database $db, $messageid, $userid, $body){
		$sql = "
			INSERT INTO ".$db->prefix."fb_reply
			(userid, messageid, body, dateline) VALUES (
				".bkint($userid).",
				".bkint($messageid).",
				'".bkstr($body)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		
		$sql = "
			UPDATE ".$db->prefix."fb_message
			SET status=".FeedbackQuery::MSG_REPLY."
			WHERE messageid=".bkint($messageid)."
		";
		$db->query_write($sql);
	}
	
	public static function MessageList(Ab_Database $db, $status, $page, $limit){
		$sql = "
			SELECT
				messageid as id,
				userid as uid,
				fio,
				phone as phn,
				email as ml,
				message as msg,
				dateline as dl,
				status as st,
				owner as own,
				ownerparam as ownprm
			FROM ".$db->prefix."fb_message
			WHERE status=".bkint($status)."
			ORDER BY dl DESC
		";
		return $db->query_read($sql);
	}
	
	public static function Message(Ab_Database $db, $messageid){
		$sql = "
			SELECT
				a.messageid as id,
				a.*
			FROM ".$db->prefix."fb_message a
			WHERE a.messageid=".bkint($messageid)."
			LIMIT 1
		";
		return $db->query_read($sql);
	}
	
	public static function MessageRemove(Ab_Database $db, $messageid){
		$sql = "
			DELETE FROM ".$db->prefix."fb_message
			WHERE messageid=".bkint($messageid)."
		";
		$db->query_write($sql);
	}
	
	public static function MessageAppend(Ab_Database $db, $globalid, $userid, $fio, $phone, $email, $message, $owner, $ownerparam){
		$sql = "
			INSERT INTO ".$db->prefix."fb_message
			(globalmessageid, userid, fio, phone, email, message, owner, ownerparam, dateline) VALUES
			(
				'".bkstr($globalid)."',
				".bkint($userid).",
				'".bkstr($fio)."',
				'".bkstr($phone)."',
				'".bkstr($email)."',
				'".bkstr($message)."',
				'".bkstr($owner)."',
				'".bkstr($ownerparam)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}
}

?>