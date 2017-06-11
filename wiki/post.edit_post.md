# post.edit_post

帖子的编辑-单个帖子的编辑操作

## 接口调用请求说明

接口URL：http://dev.wuanlife.com:800/post/edit_post

请求方式：POST

参数说明：

|参数    |类型  |是否必须    |默认值    |范围             |说明|
|:--|:--|:--|:--|:--|:--|
|token|字符串|必须|-|-|jwt字符串，包含下列两个参数|
|user_id    |整型   |必须    |           |最小：1     |用户id，包含于token|
|post_id    |整型   |必须         |      |最小：1     |帖子id，包含于token|
|p_title|字符串 | 必须 | |最小：1 最大 ：60|帖子标题|
|p_text| 字符串 | 必须| |最小：1 最大 ：5000| 帖子内容|

## 返回说明

|字段    |        类型   |      说明|
|:--|:--|:--|
|code    |            整型      |  操作码，1表示编辑成功，0表示编辑失败|
|post_id|整型|帖子id,此处只返回帖子id,由此跳转到帖子详情|
|msg|字符型|提示信息|

## 示例

回复帖子

http://dev.wuanlife.com:800/post/edit_post

    JSON:
    {
	"ret": 200,
	"data": {
		"code": 1,
		"post_id": "45"
	},
	"msg": "编辑成功"
    }