<?php

declare(strict_types=1);

namespace spaceonfire\Common\Data;

interface FieldInterface extends \Stringable
{
    /**
     * @param mixed $element
     * @return mixed|null
     */
    public function extract($element);
}
