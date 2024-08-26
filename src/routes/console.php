<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command("sonar:post:data-usage")
    ->everyMinute();
