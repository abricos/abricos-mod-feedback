<?php
/**
 * @package Abricos
 * @subpackage Feedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'classes/structure.php';
require_once 'dbquery.php';

class FeedbackModuleManager extends Ab_ModuleManager {

    /**
     * @var FeedbackModuleManager
     */
    public static $instance = null;

    private $_feedbackManager = null;

    public function __construct($module) {
        parent::__construct($module);

        FeedbackModuleManager::$instance = $this;
    }

    public function IsAdminRole() {
        return $this->IsRoleEnable(FeedbackAction::ADMIN);
    }

    public function IsWriteRole() {
        if ($this->IsAdminRole()) {
            return true;
        }
        return $this->IsRoleEnable(FeedbackAction::WRITE);
    }

    public function IsViewRole() {
        if ($this->IsWriteRole()) {
            return true;
        }
        return $this->IsRoleEnable(FeedbackAction::VIEW);
    }

    /**
     * @return FeedbackManager
     */
    public function GetFeedbackManager() {
        if (empty($this->_feedbackManager)) {
            require_once 'classes/feedback.php';
            $this->_feedbackManager = new FeedbackManager($this);
        }
        return $this->_feedbackManager;
    }

    public function TreatResult($res) {
        $ret = new stdClass();
        $ret->err = 0;

        if (is_integer($res)) {
            $ret->err = $res;
        } else if (is_object($res)) {
            $ret = $res;
        }
        if (isset($ret->err)){
            $ret->err = intval($ret->err);
        }

        return $ret;
    }

    public function AJAX($d) {
        $ret = $this->GetFeedbackManager()->AJAX($d);

        if (empty($ret)) {
            $ret = new stdClass();
            $ret->err = 500;
        }

        return $ret;
    }

    public function Bos_MenuData() {
        if (!$this->IsAdminRole()) {
            return null;
        }
        $lng = $this->module->GetI18n();
        return array(
            array(
                "name" => "feedback",
                "title" => $lng['bosmenu']['feedback'],
                "icon" => "/modules/feedback/images/icon.gif",
                "url" => "feedback/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }
}

?>