#Post.LockPost

锁定帖子接口

##接口调用请求说明

接口URL：http://dev.wuanlife.com:800/?service=Post.LockPost

请求方式：POST

参数说明：

|参数名字    |类型   |是否必须    |默认值    |范围        |说明|
|:--|:--|:--|:--|:--|:--|
|post_id    |字符串   |必须         |      |             |帖子id|

##返回说明：

|参数        |类型   |说明|
|:--|:--|:--|
|data            |整型   |操作码，1表示锁帖成功，0表示锁帖失败|

##示例：

将帖子id为1的帖子锁定

http://dev.wuanlife.com:800/?service=Post.LockPost&post_id=1

    JSON:
    {
    "ret": 200,
    "data": 1,
    "msg": ""
    }