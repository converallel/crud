<?php

namespace Crud;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\SecurityHeadersMiddleware;
use Crud\Middleware\LoggingMiddleware;

/**
 * Plugin for Crud
 */
class Plugin extends BasePlugin
{
    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middleware)
    {
        $securityHeaders = new SecurityHeadersMiddleware();
        $securityHeaders
            ->setCrossDomainPolicy()
            ->setReferrerPolicy()
            ->setXFrameOptions()
            ->setXssProtection()
            ->noOpen()
            ->noSniff();

        $middleware->prepend($securityHeaders);
        $middleware->prepend(new LoggingMiddleware());
        $middleware->prepend(new BodyParserMiddleware(['xml' => true]));

        return $middleware;
    }

    public function bootstrap(PluginApplicationInterface $app)
    {
        $app->addPlugin('Migrations');
        parent::bootstrap($app);
    }
}
