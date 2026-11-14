<?php

declare(strict_types=1);

use CmsProvenance\ProvenanceDemoSite\Middleware\MarkdownSuffixUrls;

return [
    'frontend' => [
        'cms-provenance/provenance-demo-site/markdown-suffix-urls' => [
            'target' => MarkdownSuffixUrls::class,
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['b13/ai-bots-love-markdown/request'],
        ],
    ],
];
