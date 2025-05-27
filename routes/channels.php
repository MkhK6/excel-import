<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('import-progress.{progressKey}', function ($user, $progressKey) {
    return true;
});
