<?php
namespace Kernel\Core\Service;

interface CollectionInterface extends \IteratorAggregate
{
    public function toArray(): array;
}