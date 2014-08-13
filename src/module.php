<?php 
/**
 * @package Abricos 
 * @subpackage Feedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Модуль обратной связи
 * @package Abricos 
 * @subpackage Feedback
 */
class FeedbackModule extends Ab_Module {
	
	/**
	 * @var FeedbackModule
	 */
	public static $instance = null;
	
	private $_manager = null;

	/**
	 * Конструктор 
	 */
	public function FeedbackModule(){
		$this->version = "0.2.5.1";
		$this->name = "feedback";
		$this->takelink = "feedback";
		
		$this->permission = new FeedbackPermission($this);
		
		FeedbackModule::$instance = $this;
	}
	
	/**
	 * @return FeedbackModuleManager
	 */
	public function GetManager(){
		if (is_null($this->_manager)){
			require_once 'includes/manager.php';
			$this->_manager = new FeedbackModuleManager($this);
		}
		return $this->_manager;
	}

    public function Bos_IsMenu(){
        return true;
    }
}

class FeedbackAction {
	const VIEW	= 10;
	const WRITE	= 30;
	const ADMIN	= 50;
}

class FeedbackPermission extends Ab_UserPermission {

	public function FeedbackPermission(FeedbackModule $module){

		$defRoles = array(
			new Ab_UserRole(FeedbackAction::VIEW, Ab_UserGroup::GUEST),
			new Ab_UserRole(FeedbackAction::VIEW, Ab_UserGroup::REGISTERED),
			new Ab_UserRole(FeedbackAction::VIEW, Ab_UserGroup::ADMIN),

			new Ab_UserRole(FeedbackAction::WRITE, Ab_UserGroup::GUEST),
			new Ab_UserRole(FeedbackAction::WRITE, Ab_UserGroup::REGISTERED),
			new Ab_UserRole(FeedbackAction::WRITE, Ab_UserGroup::ADMIN),

			new Ab_UserRole(FeedbackAction::ADMIN, Ab_UserGroup::ADMIN),
		);

		parent::__construct($module, $defRoles);
	}

	public function GetRoles(){
		return array(
			FeedbackAction::VIEW => $this->CheckAction(FeedbackAction::VIEW),
			FeedbackAction::WRITE => $this->CheckAction(FeedbackAction::WRITE),
			FeedbackAction::ADMIN => $this->CheckAction(FeedbackAction::ADMIN)
		);
	}
}

Abricos::ModuleRegister(new FeedbackModule());

?>