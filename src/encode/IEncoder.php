<?php

namespace neophapi\encode;

interface IEncoder
{
    public function encode(array $statements): string;
}
