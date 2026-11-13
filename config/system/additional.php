<?php

/**
 * Settings for running the demo in DDEV.
 *
 * DDEV usually generates this file, but only once TYPO3 is installed. The
 * demo installs TYPO3 in the post-start hook (Build/demo-setup.sh), after
 * DDEV has looked for it, so the file is part of the repository instead.
 * Without it the trusted hosts check fails behind the DDEV router.
 */
if (getenv('IS_DDEV_PROJECT') === 'true') {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => 'db',
                        'driver' => 'mysqli',
                        'host' => 'db',
                        'password' => 'db',
                        'port' => '3306',
                        'user' => 'db',
                    ],
                ],
            ],
            'MAIL' => [
                'transport' => 'smtp',
                'transport_smtp_server' => 'localhost:1025',
            ],
            'SYS' => [
                'trustedHostsPattern' => '.*.*',
                'devIPmask' => '*',
                'displayErrors' => 1,
            ],
        ]
    );
}
