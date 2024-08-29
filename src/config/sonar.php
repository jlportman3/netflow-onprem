<?php

return [
    "url" => rtrim(env("SONAR_URL", "https://noenvsetup.local"), "/"),
    "token" => env("SONAR_TOKEN", "NO TOKEN PROVIDED"),
    "version" => env("NETFLOW_ONPREM_VERSION", "0.0.1"),

    "netflow" => [
        "max_life" => env("NFDUMP_MAXLIFE", "7d"),
        "max_size" => env("NFDUMP_MAXSIZE", "100G"),
    ],
];
