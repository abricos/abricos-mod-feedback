<?php
/**
 * @package Abricos
 * @subpackage Feedback
 * @copyright 2009-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class FeedbackManager
 *
 * @property FeedbackModuleManager $manager
 */
class Feedback extends AbricosApplication {

    protected function GetClasses(){
        return array(
            'Config' => 'FeedbackConfig',
            'Message' => 'FeedbackMessage',
            'MessageList' => 'FeedbackMessageList',
            'Reply' => 'FeedbackReply',
            'ReplyList' => 'FeedbackReplyList'
        );
    }

    protected function GetStructures(){
        return 'Message,Reply,ReplyList,Config';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "feedbackSend":
                return $this->FeedbackSendToJSON($d->feedback);
            case "messageList":
                return $this->MessageListToJSON();
            case "message":
                return $this->MessageToJSON($d->messageid);
            case "messageRemove":
                return $this->MessageRemoveToJSON($d->messageid);
            case "replySend":
                return $this->ReplySendToJSON($d->messageid, $d->reply);
            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);

        }
        return null;
    }

    public function FeedbackSendToJSON($sd){
        $res = $this->FeedbackSend($sd);
        return $this->ResultToJSON('feedbackSend', $res);
    }

    /**
     * Добавить сообщение от пользователя и отправить уведомление администратору сайта
     *
     * @param object $data данные сообщения
     * @return integer код ошибки
     */
    public function FeedbackSend($data){
        if (!$this->manager->IsWriteRole()){
            return 403;
        }

        $utmf = Abricos::TextParser(true);

        $utm = Abricos::TextParser();
        $utm->jevix->cfgSetAutoBrMode(true);

        $messageeml = $utm->JevixParser(nl2br($data->message));
        $message = $utm->JevixParser($data->message);
        $message = str_replace("<br/>", "", $message);

        $overFields = "";
        $overFieldsArray = array();
        foreach ($data as $key => $value){
            if ($key === "fio" || $key === "phone" ||
                $key === "email" || $key === "message" ||
                $key === "overfields"
            ){
                continue;
            }
            if (strlen($value) > 1000 || count($overFieldsArray) > 50){
                continue;
            }
            $newval = $utmf->Parser($value);
            if (empty($newval)){
                continue;
            }
            $overFieldsArray[$key] = $newval;
        }

        if (count($overFieldsArray) > 0){
            $overFields = json_encode($overFieldsArray);
        }

        $userid = Abricos::$user->id;

        if ($userid === 0 && empty($data->email)){
            // return 0;
        }

        $globalid = md5(TIMENOW + rand(0, 1000));

        $emails = FeedbackModule::$instance->GetPhrases()->Get('adm_emails');
        $arr = explode(',', $emails);

        $brick = Brick::$builder->LoadBrickS("feedback", "templates");
        $v = $brick->param->var;

        $subject = $v['adm_notify_subj'];
        $body = Brick::ReplaceVarByData($v['adm_notify'], array(
            "unm" => $data->fio,
            "phone" => $data->phone,
            "email" => $data->email,
            "text" => $messageeml
        ));

        if (count($arr) === 0 || (count($arr) === 1) && empty($arr[0])){
            array_push($arr, SystemModule::$instance->GetPhrases()->Get('admin_mail'));
        }

        foreach ($arr as $email){
            $email = trim($email);
            if (empty($email)){
                continue;
            }

            Abricos::Notify()->SendMail($email, $subject, $body);
        }

        $messageId = FeedbackQuery::MessageAppend(Brick::$db, $globalid, $userid, $data->fio, $data->phone, $data->email, $message, $overFields);

        $ret = new stdClass();
        $ret->messageid = $messageId;

        return $ret;
    }

    public function MessageListToJSON(){
        $res = $this->MessageList();
        return $this->ResultToJSON('messageList', $res);
    }

    /**
     * @return FeedbackMessageList
     */
    public function MessageList(){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }

        $list = $this->models->InstanceClass('MessageList');
        $rows = FeedbackQuery::MessageList($this->db);
        while (($d = $this->db->fetch_array($rows))){
            $list->Add($this->models->InstanceClass('Message', $d));
        }
        return $list;
    }

    public function MessageToJSON($messageid){
        $res = $this->Message($messageid);
        return $this->ResultToJSON('message', $res);
    }

    /**
     * @param $messageid
     * @return FeedbackMessage
     */
    public function Message($messageid){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }
        $d = FeedbackQuery::Feedback($this->db, $messageid);
        if (empty($d)){
            return 404;
        }

        /** @var FeedbackMessage $message */
        $message = $this->models->InstanceClass('Message', $d);

        $rows = FeedbackQuery::ReplyList($this->db, $messageid);
        while (($d = $this->db->fetch_array($rows))){
            $message->replyList->Add($this->models->InstanceClass('Reply', $d));
        }

        return $message;
    }

    public function ReplySendToJSON($messageid, $d){
        $res = $this->ReplySend($messageid, $d);
        return $this->ImplodeJSON(array(
            $this->MessageToJSON($messageid),
            $this->ResultToJSON('replySend', $res)
        ));
    }

    public function ReplySend($messageid, $sd){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }

        $message = $this->Message($messageid);

        if (is_integer($message)){
            return 404;
        }

        $body = nl2br($sd->message);

        Abricos::Notify()->SendMail($message->email, "Re: ".SystemModule::$instance->GetPhrases()->Get('site_name'), $body);

        $replyid = FeedbackQuery::Reply($this->db, $messageid, Abricos::$user->id, $body);

        $ret = new stdClass();
        $ret->replyid = $replyid;
        return $ret;
    }

    public function MessageRemoveToJSON($messageid){
        $res = $this->MessageRemove($messageid);
        return $this->ResultToJSON('messageRemove', $res);
    }

    public function MessageRemove($messageid){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }
        $message = $this->Message($messageid);
        if (empty($message)){
            return 404;
        }
        FeedbackQuery::MessageRemove(Abricos::$db, $messageid);

        $ret = new stdClass();
        $ret->messageid = $messageid;
        return $ret;
    }


    public function ConfigToJSON(){
        $res = $this->Config();
        return $this->ResultToJSON('config', $res);
    }

    public function Config(){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }

        $phrases = FeedbackModule::$instance->GetPhrases();

        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }

        return $this->models->InstanceClass('Config', $d);
    }

    public function ConfigSaveToJSON($sd){
        $this->ConfigSave($sd);
        return $this->ConfigToJSON();
    }

    public function ConfigSave($sd){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }
        $utmf = Abricos::TextParser(true);

        $phs = FeedbackModule::$instance->GetPhrases();
        $phs->Set("adm_emails", $utmf->Parser($sd->adm_emails));

        Abricos::$phrases->Save();
    }

}

?>