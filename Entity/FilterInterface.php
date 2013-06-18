<?php

namespace Bangpound\Twitter\StreamingBundle\Entity;

interface FilterInterface
{
    public function setIsActive($isActive);
    public function getIsActive();
}