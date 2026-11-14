<?php

declare(strict_types=1);

namespace CmsProvenance\ProvenanceDemoSite\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Serves the Markdown version of a page at /<page>.md and of the start page at
 * /index.md. The route enhancer "PageTypeSuffix" in the site configuration
 * already generates these URLs; b13/ai-bots-love-markdown, however, detects a
 * Markdown request before routing and only by ?type=, the Accept header or a
 * "/<suffix>" path. This middleware turns the .md URL into ?type= for it.
 */
final class MarkdownSuffixUrls implements MiddlewareInterface
{
    private const SUFFIX = '.md';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        $path = $request->getUri()->getPath();
        if (!$site instanceof Site || !str_ends_with($path, self::SUFFIX) || str_ends_with($path, '/' . self::SUFFIX)) {
            return $handler->handle($request);
        }

        $pagePath = $this->pagePath($path);
        $typeNum = (int)$site->getSettings()->get('ai_bots_love_markdown.pageTypeTypeNum', 2026);
        $queryParams = ['type' => (string)$typeNum] + $request->getQueryParams();
        $uri = $request->getUri()->withPath($pagePath)->withQuery(http_build_query($queryParams));

        $request = $request->withUri($uri)->withQueryParams($queryParams);

        // The site resolver has already split the path; the page resolver reads its tail.
        $routing = $request->getAttribute('routing');
        if ($routing instanceof SiteRouteResult) {
            $tail = $this->pagePath($routing->getTail());
            $request = $request->withAttribute('routing', new SiteRouteResult($uri, $routing->getSite(), $routing->getLanguage(), $tail));
        }

        return $handler->handle($request);
    }

    /**
     * "cabinets.md" becomes "cabinets", "index.md" the start page.
     */
    private function pagePath(string $path): string
    {
        $pagePath = substr($path, 0, -strlen(self::SUFFIX));

        return in_array($pagePath, ['index', '/index'], true) ? substr($pagePath, 0, -5) : $pagePath;
    }
}
