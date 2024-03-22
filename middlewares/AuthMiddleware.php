<?php

namespace alexshonia\phpmvc\middlewares;

use alexshonia\phpmvc\Application;
use alexshonia\phpmvc\exception\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $actions;

    public function __construct($actions = [])
    {
        $this->actions = $actions;
    }
    public function execute()
    {
        if (Application::isGuest()) {
            if (empty ($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }

    }
}