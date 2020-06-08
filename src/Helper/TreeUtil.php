<?php


namespace Frcstc\Luatree\Helper;


use Hyperf\Di\Annotation\Inject;
use Frcstc\Luatree\Entity\AddNode;
use Redis;

class TreeUtil
{
    /**
     * @Inject()
     * @var Redis
     */
    protected Redis $redis;

    public function add(string $hashKey, AddNode $addNode)
    {
        $this->redis->hSet($hashKey, $addNode->userId, json_encode($addNode));
    }

    /**
     * 获取父节点
     * @param string $hashKey
     * @param string $userId
     * @return mixed|string|null
     */
    public function getParent(string $hashKey, string $userId)
    {

        $list =  $this->redis->eval(LuaHelper::getParentList(), [$hashKey, $userId], 1);
        $error = $this->redis->getLastError();
        if (isset($error) && empty($list)) {
            return $error;
        }
        return $list;
    }

    /**
     * 获取父节点的不同等级的第一个节点
     * @param string $hashKey
     * @param string $userId
     * @return mixed|string|null
     */
    public function getParentGroupByLevel(string $hashKey, string $userId)
    {
        $list =  $this->redis->eval(LuaHelper::getParentGroupByLevel(), [$hashKey, $userId], 1);
        $error = $this->redis->getLastError();
        if (isset($error) && empty($list)) {
            return $error;
        }
        return $list;
    }

    /**
     * 获取所有的子节点
     * @param string $hashKey
     * @param string $userId
     * @return mixed|string|null
     */
    public function getChildrenList(string $hashKey, string $userId)
    {
        $list =  $this->redis->eval(LuaHelper::getChildList(), [$hashKey, $userId], 1);
        $error = $this->redis->getLastError();
        if (isset($error) && empty($list)) {
            return $error;
        }
        return $list;
    }
}