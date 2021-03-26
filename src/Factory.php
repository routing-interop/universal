<?php

namespace Interop\Routing;

use AltoRouter;
use Aura\Router\Matcher;
use Aura\Router\RouterContainer;
use FastRoute\Dispatcher;
use Interop\Routing\Alto\AltoDispatcher;
use Interop\Routing\Aura\AuraDispatcher;
use Interop\Routing\FastRoute\FastRoute;
use Interop\Routing\Route\RouteCollection;
use Interop\Routing\Symfony\DispatcherSymfony;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

use function FastRoute\simpleDispatcher;

/**
 * Universal Dispatcher factory
 *
 * Create an Interop Dispatcher instance from any kind of existing routing
 * argument (event from nothing):
 */
final class Factory
{
    /**
     * @param mixed|null $arg
     * @return DispatcherInterface
     */
    public function create($arg = null, RouteCollection $collection = null): DispatcherInterface
    {
        if (null === $arg) {
            $arg = $this->locateRoutingLibrary();
        }

        switch (true) {
            case $arg instanceof AltoRouter:          $dispatcher = new AltoDispatcher($arg); break;
            case $arg instanceof Matcher:             $dispatcher = new AuraDispatcher($arg); break;
            case $arg instanceof Dispatcher:          $dispatcher = new FastRoute($arg); break;
            case $arg instanceof UrlMatcherInterface: $dispatcher = new DispatcherSymfony($arg); break;
            default:                                  throw new RoutingNotFound;
        }

        if (null !== $collection) {
            $dispatcher->addRoutes($collection);
        }

        return $dispatcher;
    }

    private function locateRoutingLibrary()
    {
        if (class_exists(UrlMatcherInterface::class)) {
            return new UrlMatcher(new SymfonyRouteCollection, RequestContext::fromUri($this->getCurrentURI()));
        }

        if (class_exists(Dispatcher::class)) {
            return simpleDispatcher(function(){});
        }

        if (class_exists(Matcher::class)) {
            $container = new RouterContainer;

            return $container->getMatcher();
        }

        if (class_exists(AltoRouter::class)) {
            return new AltoRouter;
        }

        throw new RoutingNotFound;
    }

    private function getCurrentURI(): string
    {
        return
            (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://")
            . $_SERVER['HTTP_HOST']
            . $_SERVER['REQUEST_URI']
        ;
    }
}
