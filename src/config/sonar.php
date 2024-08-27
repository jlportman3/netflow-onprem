<?php

return [
    "url" => env("SONAR_URL", "https://noenvsetup.local"),
    "token" => env("SONAR_TOKEN", "NO TOKEN PROVIDED"),

    "netflow" => [
        "max_life" => env("NFDUMP_MAXLIFE", "7d"),
        "max_size" => env("NFDUMP_MAXSIZE", "100G"),
    ],
];
