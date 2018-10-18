<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 18.10.2018
 * Time: 23:38
 */

namespace Kernel\Core\Model\Mongo;

use MongoDB\Collection;
use MongoDB\Model;

/**
 * builder mongo
 *
 * Class Builder
 * @package Kernel\Core\Model\Mongo
 */
class Builder
{
    /**
     * collection mongo
     *
     * @var Collection
     */
    private $collection;

    /**
     * Builder constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->setCollection($collection);
    }

    /**
     * get collection
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * reduction to array
     *
     * @param Model\BSONDocument|Model\BSONArray $document
     * @return array
     */
    public function toArray($document): array
    {
        $data = $document->getArrayCopy();
        if (\is_array($data)) {
            foreach ($data as &$item) {
                if ($item instanceof Model\BSONDocument || $item instanceof Model\BSONArray) {
                    $item = $this->toArray($item);
                }
            }
        }
        return $data;
    }

    /**
     * set collection mongo
     *
     * @param Collection $collection
     */
    private function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

}