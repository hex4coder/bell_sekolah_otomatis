<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('bell', function () {
    return true;
});
