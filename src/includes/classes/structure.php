<?php

class Feedback extends AbricosItem {

    public $fio;
    public $phone;
    public $email;
    public $message;
    public $overFields;
    public $dateline;

    /**
     * @var FeedbackReplyList
     */
    public $replyList = null;

    public function __construct($d) {
        parent::__construct($d);

        $this->fio = strval($d['fio']);
        $this->phone = strval($d['phone']);
        $this->email = strval($d['email']);
        $this->message = strval($d['message']);
        $this->overFields = strval($d['overfields']);
        $this->dateline = intval($d['dateline']);
    }

    public function ToAJAX() {
        $ret = parent::ToAJAX();
        $ret->fio = $this->fio;
        $ret->phone = $this->phone;
        $ret->email = $this->email;
        $ret->message = $this->message;
        $ret->overfields = $this->overFields;
        $ret->dateline = $this->dateline;

        if (!empty($this->replyList)){
            $ret->replies = $this->replyList->ToAJAX();
        }

        return $ret;
    }
}

class FeedbackList extends AbricosList {
}

class FeedbackReply extends AbricosItem {

    public $message;
    public $dateline;

    public function __construct($d) {
        parent::__construct($d);

        $this->message = strval($d['message']);
        $this->dateline = intval($d['dateline']);
    }

    public function ToAJAX() {
        $ret = parent::ToAJAX();
        $ret->message = $this->message;
        $ret->dateline = $this->dateline;

        return $ret;
    }
}

class FeedbackReplyList extends AbricosList {
}


?>