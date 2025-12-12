<?php

namespace App\Traits;

use NotificationChannels\WebPush\HasPushSubscriptions as WebPushHasPushSubscriptions;

trait HasPushSubscriptions
{
    use WebPushHasPushSubscriptions;
}
