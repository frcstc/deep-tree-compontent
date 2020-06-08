<?php
namespace Frcstc\Luatree\Entity;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Str;

class BaseEntity implements Jsonable,JsonSerializable
{
    private array $data = [];

    public function __construct($data = [])
    {
        $this->fillData($data);
    }

    public function fillData($data)
    {
        foreach ($data as $key => $value) {
            $this->data[Str::camel($key)] = $data[$key];
        }
    }

    public function &__get($name)
    {
        if (method_exists($this, "get{$name}")) {
            return $this->{"get{$name}"}();
        } else if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            $result = null;
            return $result;
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        if (method_exists($this, "get{$name}")) {
            return true;
        } else if (array_key_exists($name, $this->data)) {
            return true;
        } else {
            return isset($this->{$name});
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return json_encode($this->getData(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->getData();
    }
}