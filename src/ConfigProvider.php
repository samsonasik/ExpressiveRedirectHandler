<?php

namespace ExpressiveRedirectHandler;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    Middleware\RedirectHandlerAction::class => Middleware\RedirectHandlerActionFactory::class,
                ],
            ],
        ];
    }
}