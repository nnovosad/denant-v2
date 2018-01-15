<?php

class AnyWay_Tasks_WordpressMail extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    public $id = 'wordpress-mail';

    const ATTACHMENTS_SIZE_LIMIT = 10240000; // default postfix message size limit
    protected $to;
    protected $subject;
    protected $message;
    protected $attachments = array();

    public function __construct($options = array())
    {
        if (!isset($options['to']))
            throw new Exception("to not set");

        if (!isset($options['subject']))
            throw new Exception("subject not set");

        if (!isset($options['message']))
            throw new Exception("message not set");

        $this->to = $options['to'];
        $this->subject = $options['subject'];
        $this->message = $options['message'];

        if (isset($options['attachments']))
            $this->attachments = $options['attachments'];
    }

    public function getState()
    {
        return array(
            'to' => $this->to,
            'subject' => $this->subject,
            'message' => $this->message,
            'attachments' => $this->attachments
        );
    }

    public function runPartial($deadline, $hardDeadline)
    {
        $attachments = array();
        $total = 10240;
        foreach ($this->attachments as $attachment) {
            if ($total + @filesize($attachment) < static::ATTACHMENTS_SIZE_LIMIT) {
                $attachments[] = $attachment;
                $total += filesize($attachment);
            }
        }

        if (false === wp_mail(
                $this->to,
                $this->subject,
                $this->message,
                array('Content-Type: text/html; charset=UTF-8'),
                null) // used to be $attachments
        ) {
            $this->emit("warning", "Unable to send notification email");
        }
        return null;
    }
}