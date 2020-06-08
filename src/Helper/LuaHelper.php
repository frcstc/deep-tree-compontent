<?php
namespace src\Helper;
class LuaHelper
{
    /**
     * 获取所有子节点
     * @return string
     */
    public static function getChildList()
    {
        return <<<LUA
            local treeKey = KEYS[1]
            local fnodeId = ARGV[1]
            local function getTreeChild(currentnode, t, res)
            if currentnode == nil or t == nil  then
            return res
            end
            local nextNode = nil
            local nextType = nil
            if t == "userId" and (type(currentnode) == "number" or type(currentnode) == "string") then
            local treeNode = redis.call("HGET", treeKey, currentnode)
            if treeNode then
            local node = cjson.decode(treeNode)
            table.insert(res, treeNode)
            if node and node.childrenIds then
            nextNode = node.childrenIds
            nextType = "childrenIds"
            end
            end
            elseif t == "childrenIds" then
            nextNode = {}
            nextType = "childrenIds"
            local treeNode  = nil
            local node = nil
            local cnt = 0
            for _, val in ipairs(currentnode) do
            treeNode = redis.call("HGET", treeKey, tostring(val))
            if treeNode then
            node = cjson.decode(treeNode)
            table.insert(res, treeNode)
            if node and node.childrenIds then
            for _, val2 in ipairs(node.childrenIds) do
            table.insert(nextNode, val2)
            cnt = cnt + 1
            end
            end
            end
            end
            if cnt == 0 then
            nextNode = nil
            nextType = nil
            end
            end
            return getTreeChild(nextNode, nextType, res)
            end
            
            if treeKey and fnodeId then
            return getTreeChild(fnodeId, "userId", {})
            end
            return {}
        LUA;
    }

    /**
     * 获取所有子节点数量
     * @return string
     */
    public static function getChildCount()
    {
        return <<<LUA
            local treeKey = KEYS[1]
            local fnodeId  = ARGV[1]
            
            local function getTreeChildCnt(currentnode, t, res)
            if currentnode == nil or t == nil  then
            return res
            end
            
            local nextNode = nil
            local nextType = nil
            if t == "userId" and (type(currentnode) == "number" or type(currentnode) == "string") then
            local treeNode = redis.call("HGET", treeKey, currentnode)
            if treeNode then
            local node = cjson.decode(treeNode)
            res = res + 1
            if node and node.childrenIds then
            nextNode = node.childrenIds
            nextType = "childrenIds"
            end
            end
            elseif t == "childrenIds" then
            nextNode = {}
            nextType = "childrenIds"
            local treeNode  = nil
            local cnt = 0
            for _, val in ipairs(currentnode) do
            treeNode = redis.call("HGET", treeKey, tostring(val))
            if treeNode then
            local node = cjson.decode(treeNode)
            res = res + 1
            if node and node.childrenIds then
            for _, val2 in ipairs(node.childrenIds) do
              table.insert(nextNode, val2)
            cnt = cnt + 1
            end
            end
            end
            end
            if cnt == 0 then
            nextNode = nil
            nextType = nil
            end
            end
            return getTreeChildCnt(nextNode, nextType, res)
            end
            if treeKey and fnodeId then
            return getTreeChildCnt(fnodeId, "userId", 0)
            end
            return 0
            LUA;
    }

    /**
     * 获取上级
     * @return string
     */
    public static function getParentList()
    {
        return <<<LUA
            local treeKey = KEYS[1]
            local nodeId  = ARGV[1]
            
            local function getTreeParent(treeKey, res, nodeId)
            if nodeId == nil or not (type(nodeId) == "number" or type(nodeId) == "string") then
            return res
            end
            local treeNode = redis.call("HGET", treeKey, nodeId)
            local nextNodeId = nil
            if treeNode then
            local node = cjson.decode(treeNode)
            table.insert(res, treeNode)
            if node then
              nextNodeId = node.inviteUserId
            end
            end
            return getTreeParent(treeKey, res, nextNodeId)
            end
            
            
            if treeKey and nodeId then
            return getTreeParent(treeKey, {}, nodeId)
            end
            
            return {}
            LUA;

    }

    /**
     * 获取父级数目
     * @return string
     */
    public static function getParentCount()
    {
        return <<<LUA
            local treeKey = KEYS[1]
            local nodeId  = ARGV[1]
            
            local function getTreeParentCnt(treeKey, nodeId, res)
            if nodeId == nil or not (type(nodeId) == "number" or type(nodeId) == "string") then
            return res
            end
            local treeNode = redis.call("HGET", treeKey, nodeId)
            local nextNodeId = nil
            if treeNode then
            local node = cjson.decode(treeNode)
            res = res + 1
            if node then
            nextNodeId = node.inviteUserId
            end
            end
            return getTreeParentCnt(treeKey, nextNodeId, res)
            end
            
            if treeKey and nodeId then
            return getTreeParentCnt(treeKey, nodeId, 0)
            end
            
            return 0
        LUA;

    }

    /**
     * 获取第一个五级的所有上级
     * @return string
     */
    public static function getParentGroupByLevel()
    {
        return <<<LUA
            local treeKey = KEYS[1]
            local nodeId  = ARGV[1]
            
            local function getParentGroupByLevel(treeKey, res, nodeId, nodeLevel)
                if nodeId == nil or not (type(nodeId) == "number" or type(nodeId) == "string") or nodeLevel == 5 then
                    return res
                end
                local treeNode = redis.call("HGET", treeKey, nodeId)
                local nextNodeId = nil
                if treeNode 
                then
                    local node = cjson.decode(treeNode)
                    if node 
                    then
                        local nextNodeLevel = node.userLevelId
                        if nextNodeLevel > nodeLevel 
                        then
                            nodeLevel = nextNodeLevel
                            local thisTable = {
                                userId = node.userId,
                                userLevelId = node.userLevelId,
                                inviteUserId = node.inviteUserId
                            }
                            if type(node.inviteUserId) == "number" or type(node.inviteUserId) == "string"
                            then
                                local inviteNode = redis.call("HGET", treeKey, node.inviteUserId)
                                if inviteNode then
                                    thisTable["inviteUserLevelId"] = cjson.decode(inviteNode).userLevelId
                                end
                            end 
                            table.insert(res, cjson.encode(thisTable))
                        end
                      nextNodeId = node.inviteUserId
                    end
                end
                return getParentGroupByLevel(treeKey, res, nextNodeId, nodeLevel)
            end
            
            if treeKey and nodeId  then
                return getParentGroupByLevel(treeKey, {}, nodeId, 0)
            end
            
            return {}
            LUA;

    }
}