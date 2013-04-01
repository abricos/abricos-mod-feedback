<?php 
/**
 * @package Abricos 
 * @subpackage Feedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

class FeedbackManager extends Ab_ModuleManager {
	
	/**
	 * @var FeedbackManager
	 */
	public static $instance = null;
	
	public function __construct($module){
		parent::__construct($module);
	
		FeedbackManager::$instance = $this;
	}
	
	public function IsAdminRole(){
		return $this->IsRoleEnable(FeedbackAction::ADMIN);
	}
	
	public function IsWriteRole(){
		if ($this->IsAdminRole()){ return true; }
		return $this->IsRoleEnable(FeedbackAction::WRITE);
	}
	
	public function IsViewRole(){
		if ($this->IsWriteRole()){ return true; }
		return $this->IsRoleEnable(FeedbackAction::VIEW);
	}
	
	/**
	 * Добавить сообщение от пользователя и отправить уведомление администратору сайта
	 * 
	 * @static
	 * @param object $data данные сообщения 
	 * @return integer идентификатор нового сообщения
	 */
	public function MessageAppend($data){
		if (!$this->IsWriteRole()){ return; }
		
		$utmanager = Abricos::TextParser();

		$utmanager->jevix->cfgSetAutoBrMode(true);
		$messageeml = $utmanager->JevixParser(nl2br($data->message));
		$message = $utmanager->JevixParser($data->message);
		$message = str_replace("<br/>", "", $message);
		
		if (empty($message)){ return 0; }

		$userid = $this->userid;
		
		if ($userid == 0 && empty($data->email)){
			return 0;
		}
		
		$globalid = md5(TIMENOW);
		
		$emails = Brick::$builder->phrase->Get('feedback', 'adm_emails');
		$arr = explode(',', $emails);
		$subject = Brick::$builder->phrase->Get('feedback', 'adm_notify_subj');
		$body = nl2br(Brick::$builder->phrase->Get('feedback', 'adm_notify'));
		$body = sprintf($body, $data->fio, $data->phone, $data->email, $messageeml);
		
		foreach ($arr as $email){
			$email = trim($email);
			if (empty($email)){ continue; }
			
			Abricos::Notify()->SendMail($email, $subject, $body);
		}
		
		return FeedbackQuery::MessageAppend(Brick::$db, $globalid, $userid, $data->fio, $data->phone, $data->email, $message, $data->owner, $data->ownerparam);
	}
	
	/**
	 * Получить список сообщений из базы
	 * 
	 * @static
	 * @param integer $status статус сообщения, 0 - новое, 1 - сообщения на которые был дан ответ
	 * @param integer $page номер страницы
	 * @param integer $limit кол-во сообщений на страницу
	 * @return integer указатель на результат SQL запроса
	 */
	public function MessageList($status, $page, $limit){
		if (!$this->IsAdminRole()){ return null; }
		return FeedbackQuery::MessageList(Brick::$db, $status, $page, $limit);
	}
	
	/**
	 * Удалить сообщение из базы
	 * 
	 * @static
	 * @param integer $messageid идентификатор сообщения
	 */
	public function MessageRemove($messageid){
		if (!$this->IsAdminRole()){ return null; }
		FeedbackQuery::MessageRemove(Brick::$db, $messageid);
	}
	
	/**
	 * Ответить на сообщение, занеся ответ в базу и отправив email с ответом пользователю
	 * 
	 * @static
	 * @param object $data данные сообщения и текст ответа
	 */
	public function Reply($data){
		if (!$this->IsAdminRole()){ return null; }
				
		$messageid = $data->id;
		$userid = Abricos::$user->info['userid'];
		$body = nl2br($data->rp_body);

		Abricos::Notify()->SendMail($data->ml, "Re: ".Brick::$builder->phrase->Get('sys', 'site_name'), $body );
		
		FeedbackQuery::Reply(Brick::$db, $messageid, $userid, $body);
	}
}

?>