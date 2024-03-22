<?php

namespace alexshonia\phpmvc\middlewares;

abstract class BaseMiddleware
{
    abstract public function execute();
}