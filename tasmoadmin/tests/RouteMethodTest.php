<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RouteMethodTest extends TestCase
{
    #[DataProvider('postOnlyRoutes')]
    public function testLogoutAndLanguageRoutesAcceptOnlyPost(string $path): void
    {
        $routes = require __DIR__.'/../includes/routes.php';

        $postRequest = Request::create($path, 'POST');
        $context = new RequestContext();
        $context->fromRequest($postRequest);

        self::assertSame(
            'render_raw',
            new UrlMatcher($routes, $context)->match($postRequest->getPathInfo())['_controller']
        );

        $getRequest = Request::create($path);
        $context->fromRequest($getRequest);

        $this->expectException(MethodNotAllowedException::class);
        new UrlMatcher($routes, $context)->match($getRequest->getPathInfo());
    }

    public static function postOnlyRoutes(): array
    {
        return [
            'logout' => ['/logout'],
            'language' => ['/change_language/de'],
        ];
    }
}
