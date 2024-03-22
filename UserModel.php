<?php

namespace alexshonia\phpmvc;

use alexshonia\phpmvc\db\DbModel;


abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}