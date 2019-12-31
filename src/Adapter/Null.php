<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter;

class Null extends NullFill
{
    /**
     * {@inheritdoc}
     */
    public function __construct($count = 0)
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\NullFill',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($count);
    }
}
