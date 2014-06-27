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

        $utm = Abricos::TextParser();
        $utm->jevix->cfgSetAutoBrMode(true);

        $messageeml = $utm->JevixParser(nl2br($data->message));
        $message = $utm->JevixParser($data->message);
        $message = str_replace("<br/>", "", $message);

        if (empty($message)) {
            return 1;
        }

        $userid = $this->userid;

        if ($userid == 0 && empty($data->email)) {
            // return 0;
        }

        $globalid = md5(TIMENOW);

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

        foreach ($arr as $email) {
            $email = trim($email);
            if (empty($email)) {
                continue;
            }

            Abricos::Notify()->SendMail($email, $subject, $body);
        }

        $messageId = FeedbackQuery::MessageAppend(Brick::$db, $globalid, $userid, $data->fio, $data->phone, $data->email, $message, $data->owner, $data->ownerparam);

        $ret = new stdClass();
        $ret->messageid = $messageId;

        return $ret;
    }

    public function FeedbackListToAJAX($overResult = null){
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

    public function FeedbackToAJAX($feedbackId, $overResult){
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

    public function Feedback($feedbackId){
        if (!$this->manager->IsAdminRole()){
            return 403;
        }
        $row = FeedbackQuery::Feedback($this->db, $feedbackId);
        if (empty($row)){
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

    /**
     * Ответить на сообщение, занеся ответ в базу и отправив email с ответом пользователю
     *
     * @static
     * @param object $data данные сообщения и текст ответа
     */
    public function Reply($data) {
        if (!$this->manager->IsAdminRole()) {
            return null;
        }

        $messageid = $data->id;
        $userid = Abricos::$user->info['userid'];
        $body = nl2br($data->rp_body);

        Abricos::Notify()->SendMail($data->ml, "Re: ".Brick::$builder->phrase->Get('sys', 'site_name'), $body);

        FeedbackQuery::Reply(Brick::$db, $messageid, $userid, $body);
    }
}

?>