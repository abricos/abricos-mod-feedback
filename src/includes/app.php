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
class FeedbackApp extends AbricosApplication {

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

        $lstFields = "";

        $notifyBrick = Brick::$builder->LoadBrickS("feedback", "adminNotify");
        $v = &$notifyBrick->param->var;

        $utmf = Abricos::TextParser(true);

        $defFields = array(
            'fio',
            'phone',
            'email'
        );

        foreach ($defFields as $key){
            $data->$key = isset($data->$key) ? $utmf->Parser($data->$key) : "";
            if (!empty($data->$key)){
                $lstFields .= Brick::ReplaceVarByData($v[$key.'Field'], array(
                    "value" => $data->$key
                ));
            }
        }

        $utm = Abricos::TextParser();
        $utm->jevix->cfgSetAutoBrMode(true);

        $data->message = isset($data->message) ? trim($data->message) : "";
        if (!empty($data->message)){
            $lstFields .= Brick::ReplaceVarByData($v['messageField'], array(
                "value" => $utm->JevixParser($data->message)
            ));
        }

        $overFields = "";
        $overFieldsArray = array();
        foreach ($data as $key => $value){
            if ($key === "fio" || $key === "phone" ||
                $key === "email" || $key === "message" ||
                $key === "overfields"
            ){
                continue;
            }
            if (strlen($value) > 1000 || count($overFieldsArray) > 30){
                continue;
            }

            $newval = $utmf->Parser($value);
            if (empty($newval)){
                continue;
            }

            $overFieldsArray[$key] = $newval;

            $lstFields .= Brick::ReplaceVarByData($v['overField'], array(
                "key" => $key,
                "value" => $newval
            ));
        }

        if (count($overFieldsArray) > 0){
            $overFields = json_encode($overFieldsArray);
        }

        /** @var NotifyApp $notifyApp */
        $notifyApp = Abricos::GetApp('notify');

        $arr = explode(',', $this->Config()->adm_emails);
        foreach ($arr as $email){
            $email = trim($email);
            if (empty($email)){
                continue;
            }

            $body = Brick::ReplaceVarByData($notifyBrick->content, array(
                "host" => Ab_URI::fetch_host(),
                "email" => $email,
                "sitename" => SystemModule::$instance->GetPhrases()->Get('site_name'),
                "table" => Brick::ReplaceVarByData($v['table'], array(
                    "rows" => $lstFields
                )),
            ));

            $mail = $notifyApp->MailByFields($email, $v['subject'], $body);
            $mail->toName = '';
            $mail->toEmail = $email;

            $notifyApp->MailSend($mail);
        }

        $messageid = FeedbackQuery::MessageAppend(
            Brick::$db, $mail->globalid, Abricos::$user->id,
            $data->fio, $data->phone, $data->email, $data->message, $overFields
        );

        $ret = new stdClass();
        $ret->messageid = $messageid;

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

    /**
     * @return FeedbackConfig
     */
    public function Config(){
        $phrases = FeedbackModule::$instance->GetPhrases();


        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }

        /** @var FeedbackConfig $config */
        $config = $this->models->InstanceClass('Config', $d);

        $arr = explode(',', $config->adm_emails);

        if (count($arr) === 0 || (count($arr) === 1) && empty($arr[0])){
            $config->adm_emails = SystemModule::$instance->GetPhrases()->Get('admin_mail');
        }

        return $config;
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