<?php
namespace Kernel\Core\Service;

use Kernel\Core\Exception;

/**
 * Class Collection
 * @package Kernel\Core\Service
 */
class Collection implements CollectionInterface
{
    /**
     * make collect by data
     *
     * @param $data
     * @param null $callback
     * @return static
     * @throws Exception
     */
    public static function make($data, $callback = null): self
    {
        if ($callback !== null && ! is_callable($callback)) {
            throw new Exception('callback must be callable');
        }
        if (! is_iterable($data)) {
            throw new Exception('data must be iterable');
        }
        $collection = new self();
        foreach ($data as $key => $item) {
            if ($callback) {
                $collection->put($key, $callback($item, $key));
            } else {
                $collection->put($key, $item);
            }
        }

        return $collection;
    }

    /**
     * make collection by date with key
     *
     * @param $data
     * @param $callback
     * @return static
     * @throws Exception
     */
    public static function makeWithKey($data, $callback): self
    {
        if ($callback !== null && ! is_callable($callback)) {
            throw new Exception('callback must be callable');
        }
        if (! is_iterable($data)) {
            throw new Exception('data must be iterable');
        }
        $collection = new self();
        foreach ($data as $key => $item) {
            [$key, $value] = $callback($item, $key);
            $collection->put($key, $value);
        }

        return $collection;
    }

    /**
     * collection
     *
     * @var array|mixed
     */
    protected array $items = [];

    /**
     * Collection constructor.
     * @param mixed ...$items
     */
    public function __construct(...$items)
    {
        $this->items = $items;
    }

    /**
     * extract from collection by callback
     *
     * @param $callback
     * @return $this
     */
    public function extract(callable $callback): self
    {
        $collection = new self();
        foreach ($this->getIterator() as $item) {
            $collection->add($callback($item));
        }

        return $collection;
    }

    /**
     * extract from collection by callback with key
     *
     * @param callable $callback
     * @return $this
     */
    public function extractWithKey(callable $callback): self
    {
        $collection = new self();
        foreach ($this->getIterator() as $item) {
            [$key, $value] = $callback($item);
            $collection->put($key, $value);
        }

        return $collection;
    }

    /**
     * to string
     *
     * @param null $separator
     * @return string
     */
    public function toString($separator = null): string
    {
        return implode($separator, $this->items);
    }

    /**
     * sort collection
     *
     * @param $callback
     * @return $this
     */
    public function sort($callback): self
    {
        uasort($this->items, $callback);

        return $this;
    }

    /**
     * sum collection
     *
     * @return int
     */
    public function sum(): int
    {
        return array_sum($this->items);
    }

    /**
     * get keys
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->items);
    }

    /**
     * filter by key
     *
     * @param array $ids
     * @return $this
     */
    public function filterByKey(array $ids): self
    {
        $collection = new self();
        foreach ($this->getIterator() as $key => $item) {
            if (in_array($key, $ids, true)) {
                $collection->put($key, $item);
            }
        }

        return $collection;
    }

    /**
     * filter by value
     *
     * @param $value
     * @return $this
     */
    public function filterByValue($value): self
    {
        $collection = new self();
        foreach ($this->getIterator() as $key => $item) {
            if ($item === $value) {
                $collection->put($key, $item);
            }
        }

        return $collection;
    }

    /**
     * shuffle collection
     */
    public function shuffle(): void
    {
        shuffle($this->items);
    }

    /**
     * slice collection
     *
     * @param $offset
     * @param null $length
     * @return $this
     * @throws Exception
     */
    public function slice($offset, $length = null): self
    {
        return self::make(array_slice($this->items, $offset, $length, true));
    }

    /**
     * get size of collection
     *
     * @return int
     */
    public function getSize(): int
    {
        return count($this->items);
    }

    /**
     * add to collection
     *
     * @param $value
     */
    public function add($value): void
    {
        $this->items[] = $value;
    }

    /**
     * extract end item of collection
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * put to collection by key
     *
     * @param $key
     * @param $value
     */
    public function put($key, $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * append to collection
     *
     * @param $key
     * @param $value
     * @param string|null $secondKey
     */
    public function append($key, $value, string $secondKey = null): void
    {
        if (! isset($this->items[$key])) {
            $this->items[$key] = new self();
        }
        if ($secondKey === null) {
            $this->items[$key]->add($value);
        } else {
            $this->items[$key]->put($secondKey, $value);
        }
    }

    /**
     * remove from collection
     *
     * @param $key
     */
    public function remove($key): void
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
        }
    }

    /**
     * get data from collection by key
     *
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        if (! $this->has($key)) {
            throw new Exception("key [$key] is not set");
        }

        return $this->items[$key];
    }

    /**
     * destroy collection
     */
    public function destroy(): void
    {
        $this->items = [];
    }

    /**
     * is empty collection
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * find value in collection
     *
     * @param $value
     *
     * @return bool
     */
    public function find($value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * filter collection by callback
     *
     * @param $callback
     */
    public function filter(callable $callback): void
    {
        $this->items = array_filter($this->items, $callback);
    }

    /**
     * increment value in collection
     *
     * @param $key
     * @param int $count
     * @param bool $strict
     * @return int|null
     * @throws Exception
     */
    public function increment($key, $count = 1, $strict = true): ?int
    {
        // строгая обработка инкремента с проверкой наличия ключа
        if ($strict) {
            if ($this->has($key)) {
                $value = $this->get($key);
                $result = $value + $count;
                $this->put($key, $result);
                return $result;
            }
            return null;
        }

        // мягкая обработка
        if (! $this->has($key)) {
            $result = $count;
        } else {
            $value = $this->get($key);
            $result = $value + $count;
        }
        $this->put($key, $result);

        return $result;
    }

    /**
     * modify collection to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $res = [];
        foreach ($this->getIterator() as $key => $value) {
            if ($value instanceof CollectionInterface) {
                $value = $value->toArray();
            }
            $res[$key] = $value;
        }

        return $res;
    }

    /**
     * modify collection to object
     *
     * @return object
     */
    public function toObject(): object
    {
        return (object) $this->toArray();
    }

    /**
     * extract first value from collection
     *
     * @return array
     */
    public function extractFirst(): array
    {
        $key = array_key_first($this->items);
        return [$key, $this->items[$key]];
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}