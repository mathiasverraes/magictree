<?php

namespace Verraes\MagicTree;

use JsonSerializable;

interface Node extends JsonSerializable
{
    public function toArray();

    public function toAscii($indent = 0);

    public function jsonSerialize();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return bool
     */
    public function has();

}