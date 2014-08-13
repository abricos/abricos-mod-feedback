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
	
	public static function FeedbackList(Ab_Database $db){
		$sql = "
			SELECT
				messageid as id,
				userid,
				fio,
				phone,
				email,
				message,
				dateline,
				status,
				overfields
			FROM ".$db->prefix."fb_message
			ORDER BY dateline DESC
		";
		return $db->query_read($sql);
	}
	
	public static function Feedback(Ab_Database $db, $feedbackId){
		$sql = "
			SELECT
                messageid as id,
				userid,
				fio,
				phone,
				email,
				message,
				dateline,
				status,
				overfields
			FROM ".$db->prefix."fb_message
			WHERE messageid=".bkint($feedbackId)."
			LIMIT 1
		";
		return $db->query_first($sql);
	}

    public static function ReplyList(Ab_Database $db, $feedbackId){
        $sql = "
			SELECT
				replyid as id,
				userid,
				body as message,
				dateline
			FROM ".$db->prefix."fb_reply
			WHERE messageid=".bkint($feedbackId)."
			ORDER BY dateline DESC
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
	
	public static function MessageAppend(Ab_Database $db, $globalid, $userid, $fio, $phone, $email, $message, $overFields){
		$sql = "
			INSERT INTO ".$db->prefix."fb_message
			(globalmessageid, userid, fio, phone, email, message, overfields, dateline) VALUES
			(
				'".bkstr($globalid)."',
				".bkint($userid).",
				'".bkstr($fio)."',
				'".bkstr($phone)."',
				'".bkstr($email)."',
				'".bkstr($message)."',
				'".bkstr($overFields)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}
}

?>