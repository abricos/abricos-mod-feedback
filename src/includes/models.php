<?php
/**
 * @package Abricos
 * @subpackage Feedback
 * @copyright 2009-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class FeedbackMessage
 *
 * @property string $fio
 * @property string $phone
 * @property string $email
 * @property string $message
 * @property string $overFields
 * @property int $dateline
 * @property FeedbackReplyList $replyList
 */
class FeedbackMessage extends AbricosModel {
    protected $_structModule = 'feedback';
    protected $_structName = 'Message';
}

/**
 * Class FeedbackMessageList
 */
class FeedbackMessageList extends AbricosModelList {
}

/**
 * Class FeedbackReply
 *
 * @property string $message
 * @property int $dateline
 */
class FeedbackReply extends AbricosModel {
    protected $_structModule = 'feedback';
    protected $_structName = 'Reply';
}

class FeedbackReplyList extends AbricosModelList {
}

/**
 * Class FeedbackConfig
 *
 * @property string $adm_emails
 */
class FeedbackConfig extends AbricosModel {
    protected $_structModule = 'feedback';
    protected $_structName = 'Config';
}
