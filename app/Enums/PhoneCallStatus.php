<?php

namespace App\Enums;

enum PhoneCallStatus: string
{
    case WaitingReceiver = 'waiting_receiver';
    case Canceled = 'canceled';
    case TalkStarted = 'talk_started';
    case Finished = 'finished';
}
