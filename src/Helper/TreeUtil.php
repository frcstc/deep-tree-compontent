<?php


namespace src\Helper;


use Hyperf\Utils\ApplicationContext;
use src\Entity\AddNode;

class TreeUtil
{
    public function add(string $hashKey, AddNode $addNode)
    {
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $redis->hSet($hashKey, $addNode->userId, json_encode($addNode));
    }

    /**
     * 获取父节点
     * @param string $hashKey
     * @param string $userId
     * @return mixed|string|null
     */
    public function getParent(string $hashKey, string $userId)
    {
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $list =  $redis->eval(LuaHelper::getParentList(), [$hashKey, $userId], 1);
        $error = $redis->getLastError();
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
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $list =  $redis->eval(LuaHelper::getParentGroupByLevel(), [$hashKey, $userId], 1);
        $error = $redis->getLastError();
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
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $list =  $redis->eval(LuaHelper::getChildList(), [$hashKey, $userId], 1);
        $error = $redis->getLastError();
        if (isset($error) && empty($list)) {
            return $error;
        }
        return $list;
    }
}