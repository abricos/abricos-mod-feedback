<?php
/**
 * @package Abricos
 * @subpackage Feedback
 * @copyright 2009-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class FeedbackModuleManager
 */
class FeedbackModuleManager extends Ab_ModuleManager {

    /**
     * @var FeedbackModuleManager
     */
    public static $instance = null;

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

    public function AJAX($d) {
        return $this->GetApp()->AJAX($d);
    }

    public function Bos_MenuData() {
        if (!$this->IsAdminRole()) {
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "feedback",
                "title" => $i18n->Translate('bosmenu.feedback'),
                "icon" => "/modules/feedback/images/icon.gif",
                "url" => "feedback/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }

    public function Bos_SummaryData(){
        if (!$this->IsAdminRole()){
            return;
        }

        $i18n = $this->module->I18n();
        return array(
            array(
                "module" => "feedback",
                "component" => "summary",
                "widget" => "SummaryWidget",
                "title" => $i18n->Translate('bosmenu.feedback'),
            )
        );
    }

}
