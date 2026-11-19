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
            // The demo editor's login is public; the admin password is not shown here.
            'EXTENSIONS' => [
                'backend' => [
                    'loginFootnote' => 'Demo login: user "editor", password "Demo-Editor-1". Run "ddev login" for the admin account.',
                ],
            ],
            'SYS' => [
                'trustedHostsPattern' => '.*.*',
                'devIPmask' => '*',
                'displayErrors' => 1,
            ],
        ]
    );
}

/**
 * Settings for the demo container (Dockerfile), as it runs at
 * demo.cms-provenance.org. Everything that differs per host comes from the
 * environment, see docker-compose.mittwald.yml.
 */
if (getenv('DEMO_CONTAINER') === 'true') {
    $siteHost = parse_url((string)getenv('DEMO_SITE_URL'), PHP_URL_HOST) ?: 'localhost';
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => getenv('DEMO_DB_NAME'),
                        'driver' => 'mysqli',
                        'host' => getenv('DEMO_DB_HOST'),
                        'password' => getenv('DEMO_DB_PASSWORD'),
                        'port' => getenv('DEMO_DB_PORT') ?: '3306',
                        'user' => getenv('DEMO_DB_USER'),
                    ],
                ],
            ],
            // The editor's login is shared, so nobody may reset its password.
            'BE' => [
                'passwordReset' => false,
            ],
            // The demo sends no mail.
            'MAIL' => [
                'transport' => 'null',
            ],
            'EXTENSIONS' => [
                'backend' => [
                    'loginFootnote' => 'Demo login: user "editor", password "Demo-Editor-1". The demo resets itself every full hour.',
                ],
            ],
            'SYS' => [
                'trustedHostsPattern' => preg_quote($siteHost, '/') . '(:\d+)?',
                // The mittwald ingress terminates TLS and connects from the
                // shared address space 100.64.0.0/10 (RFC 6598). It appends
                // its peer to X-Forwarded-For, so the last entry is the one
                // a client cannot choose. The setup renders the pages through
                // localhost as if it were the ingress (Build/demo-setup.sh);
                // for localhost the scheme comes from X-Forwarded-Proto.
                'reverseProxyIP' => (getenv('DEMO_REVERSE_PROXY_IP') ?: '100.64.0.0/10') . ',127.0.0.1',
                'reverseProxyHeaderMultiValue' => 'last',
                'reverseProxySSL' => getenv('DEMO_REVERSE_PROXY_IP') ?: '100.64.0.0/10',
                'displayErrors' => 0,
            ],
        ]
    );
}
