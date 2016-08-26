<?php

namespace Clumsy\CMS\Notifications;

use Illuminate\Notifications\Messages\MailMessage as BaseMailMessage;

class MailMessage extends BaseMailMessage
{
    /**
     * The view for the message.
     *
     * @var string
     */
    public $view = 'clumsy::notifications.email';
}
