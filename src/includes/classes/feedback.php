<?php

class FeedbackManager {

    /**
     * @var FeedbackModuleManager
     */
    public $manager;

    /**
     * @var Ab_Database
     */
    public $db;

    public function __construct(FeedbackModuleManager $manager) {
        $this->manager = $manager;
        $this->db = $manager->db;
    }

    public function AJAX($d) {
        switch ($d->do) {
            case "feedbacksend":
                return $this->FeedbackSendToAJAX($d->savedata);
            case "feedbacklist":
                return $this->FeedbackListToAJAX();
            case "feedback":
                return $this->FeedbackToAJAX($d->feedbackid);
            case "replysend":
                return $this->ReplySendToAJAX($d->feedbackid, $d->savedata);
            case "config":
                return $this->ConfigToAJAX();
        }
        return null;
    }

    public function FeedbackSendToAJAX($sd) {
        $res = $this->FeedbackSend($sd);
        $ret = $this->manager->TreatResult($res);
        return $ret;
    }


    /**
     * Добавить сообщение от пользователя и отправить уведомление администратору сайта
     *
     * Код ошибки:
     *  1 - сообщение не должно быть пустым
     *
     * @static
     * @param object $data данные сообщения
     * @return integer код ошибки
     */
    public function FeedbackSend($data) {
        if (!$this->manager->IsWriteRole()) {
            return 403;
        }

        $utmf = Abricos::TextParser(true);

        $utm = Abricos::TextParser();
        $utm->jevix->cfgSetAutoBrMode(true);

        $messageeml = $utm->JevixParser(nl2br($data->message));
        $message = $utm->JevixParser($data->message);
        $message = str_replace("<br/>", "", $message);

        if (empty($message)) {
            return 1;
        }

        $overFields = "";
        $overFieldsArray = array();
        foreach ($data as $key => $value) {
            if ($key === "fio" || $key === "phone"
                || $key === "email" || $key === "message" || $key === "overfields"
            ) {
                continue;
            }
            if (strlen($value) > 1000 || count($overFieldsArray) > 50) {
                continue;
            }
            $newval = $utmf->Parser($value);
            if (empty($newval)) {
                continue;
            }
            $overFieldsArray[$key] = $newval;
        }

        if (count($overFieldsArray) > 0) {
            $overFields = json_encode($overFieldsArray);
        }

        $userid = $this->userid;

        if ($userid == 0 && empty($data->email)) {
            // return 0;
        }

        $globalid = md5(TIMENOW + rand(0, 1000));

        $emails = Brick::$builder->phrase->Get('feedback', 'adm_emails');
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

        if (count($arr) === 0 || (count($arr) === 1) && empty($arr[0])) {
            array_push($arr, Brick::$builder->phrase->Get('sys', 'admin_mail'));
        }

        foreach ($arr as $email) {
            $email = trim($email);
            if (empty($email)) {
                continue;
            }

            Abricos::Notify()->SendMail($email, $subject, $body);
        }

        $messageId = FeedbackQuery::MessageAppend(Brick::$db, $globalid, $userid, $data->fio, $data->phone, $data->email, $message, $overFields);

        $ret = new stdClass();
        $ret->messageid = $messageId;

        return $ret;
    }

    public function FeedbackListToAJAX($overResult = null) {
        $ret = !empty($overResult) ? $overResult : (new stdClass());
        $ret->err = 0;

        $result = $this->FeedbackList();
        if (is_integer($result)) {
            $ret->err = $result;
        } else {
            $ret->feedbacks = $result->ToAJAX();
        }

        return $ret;
    }

    /**
     * Получить список сообщений
     */
    public function FeedbackList() {
        if (!$this->manager->IsAdminRole()) {
            return 403;
        }

        $list = new FeedbackList();
        $rows = FeedbackQuery::FeedbackList($this->db);
        while (($d = $this->db->fetch_array($rows))) {
            $list->Add(new Feedback($d));
        }
        return $list;
    }

    public function FeedbackToAJAX($feedbackId, $overResult = null) {
        $ret = !empty($overResult) ? $overResult : (new stdClass());
        $ret->err = 0;

        $result = $this->Feedback($feedbackId);
        if (is_integer($result)) {
            $ret->err = $result;
        } else {
            $ret->feedback = $result->ToAJAX();
        }

        return $ret;
    }

    public function Feedback($feedbackId) {
        if (!$this->manager->IsAdminRole()) {
            return 403;
        }
        $row = FeedbackQuery::Feedback($this->db, $feedbackId);
        if (empty($row)) {
            return 404;
        }

        $feedback = new Feedback($row);

        $list = new FeedbackReplyList();
        $rows = FeedbackQuery::ReplyList($this->db, $feedbackId);
        while (($d = $this->db->fetch_array($rows))) {
            $list->Add(new FeedbackReply($d));
        }
        $feedback->replyList = $list;

        return $feedback;
    }

    public function ReplySendToAJAX($feedbackId, $sd) {
        $res = $this->ReplySend($feedbackId, $sd);
        $ret = $this->manager->TreatResult($res);
        return $ret;
    }

    public function ReplySend($feedbackId, $sd) {

        $feedback = $this->Feedback($feedbackId);

        if (is_integer($feedback)) {
            return $feedback;
        }

        $body = nl2br($sd->message);

        Abricos::Notify()->SendMail($feedback->email,
            "Re: ".Brick::$builder->phrase->Get('sys', 'site_name'),
            $body
        );

        FeedbackQuery::Reply($this->db, $feedbackId, Abricos::$user->id, $body);

        return $this->Feedback($feedbackId);
    }

    /**
     * Удалить сообщение из базы
     *
     * @static
     * @param integer $messageid идентификатор сообщения
     */
    public function MessageRemove($messageid) {
        if (!$this->manager->IsAdminRole()) {
            return null;
        }
        FeedbackQuery::MessageRemove(Brick::$db, $messageid);
    }


    public function ConfigToAJAX($overResult = null) {
        $ret = !empty($overResult) ? $overResult : (new stdClass());
        $ret->err = 0;

        $result = $this->Config();
        if (is_integer($result)) {
            $ret->err = $result;
        } else {
            $ret->config = $result->ToAJAX();
        }

        return $ret;
    }

    public function Config() {
        if (!$this->manager->IsAdminRole()) {
            return 403;
        }

        Brick::$builder->phrase->PreloadByModule("feedback");
        $rows = Brick::$builder->phrase->GetArray("feedback");

        $config = new FeedbackConfig([]);

        return $config;
    }

}

?>