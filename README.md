# component-creater

```
composer require frcstc/luatree
```

## 利用 redis  + lua实现多层级递归查询
### 使用方法：
```
$addNode1 = new AddNode([
  'userId' => 'test1', //用户标识
  'inviteUserId' => 'test2',//上级或邀请人标识
  'userLevelId' => 1, //用户等级
  'childrenIds' => [],// 直属下级
]);
$addNode2 = new AddNode([
  'userId' => 'test2', 
  'inviteUserId' => 'test3',
  'userLevelId' => 2,
  'childrenIds' => ['test1'],
]);
$addNode3 = new AddNode([
  'userId' => 'test3',
  'inviteUserId' => 'test4',
  'userLevelId' => 3,
  'childrenIds' => ['test2'],
]);

$addNode4 = new AddNode([
  'userId' => 'test4',
  'inviteUserId' => '',
  'userLevelId' => 4,
  'childrenIds' => ['test3'],
]);
//存入缓存
$hashKey = "userTestHash"; //redis hset的 key
$treeUtil = new TreeUtil();
$treeUtil->add($hashKey, $addNode1);
$treeUtil->add($hashKey, $addNode2);
$treeUtil->add($hashKey, $addNode3);
$treeUtil->add($hashKey, $addNode4);

// 获取所有父节点
$treeUtil->getParent($hashKey, 'test1');
// 获取所有子节点
$treeUtil->getChildrenList($hashKey, 'test4');
```

### 此方案 利用了redis hset 以及lua函数尾调用 来消除栈内存 达到理论上无限递归的可能，优点是可以支持较大数据量，缺点是 数据存储格式固定，可以继承treeUtil 进行扩展
