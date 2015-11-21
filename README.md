# oj-stats
Online Judge Stats

Required
---
* `cURL-HTTP-function` from [here](https://github.com/Xi-Plus/cURL-HTTP-function)

Features
---
### API
Fetch from `/api` via http GET/POST with following arguments.

#### Arguments
| Arguments | Description | Type |
|-----------|-------------------|---|
| field <br> `Essential` | View [here](README.md#method). | String |
| oj <br> `Essential` | Online judge name. View [here](README.md#supported-oj). | String |
| user <br> `Essential` | User ID list. | Array String |
| prob <br> `Optional` | Problem ID list. | Array String |
| validtime <br> `Optional` | Valid time (seconds). If last fetch time < now - validtime, it will be refreshed. Default is setting in `config/config.php`. | Integer |

#### Method
| field | Description | Usage |
|-----------|-------------------|---|
| ojinfo | Online judge infomation. Ex: Name, URL. | `field=ojinfo&oj={oj-name}` |
| userinfo | User infomation on online judge. | `field=userinfo&oj={oj-name}&user={user-array}&validtime={valid-time}` |
| userstat | User solved problem. | `field=userstat&oj={oj-name}&user={user-array}&prob={prob-array}&validtime={valid-time}` |

#### Supported OJ
| OJ id | OJ name | Link | User ID |
|-----------|-------------------|---|---|
| poj | PKU JudgeOnline | http://poj.org | Your login `User ID` |
| tioj | TIOJ Infor Online Judge | http://tioj.ck.tp.edu.tw | Your login `Username` |
| toj | TNFSH Online Judge | http://toj.tfcis.org | View [here](http://toj.tfcis.org/oj/chal/) to find your `ID` |
| zj | ZeroJudge | http://zerojudge.tw | Your login `Account` |
