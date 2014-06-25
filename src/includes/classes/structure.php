<?php

class Feedback extends AbricosItem {

    public $fio;
    public $phone;
    public $email;
    public $message;

    public function __construct($d) {
        parent::__construct($d);

        $this->fio = strval($d['fio']);
        $this->phone = strval($d['phone']);
        $this->email = strval($d['email']);
        $this->message = strval($d['message']);
    }

    public function ToAJAX() {
        $ret = parent::ToAJAX();
        $ret->fio = $this->fio;
        $ret->phone = $this->phone;
        $ret->email = $this->email;
        $ret->message = $this->message;

        return $ret;
    }
}

class FeedbackList extends AbricosList {
}

?>