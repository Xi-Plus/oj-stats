# oj-stats
Online Judge Stats

Required
---
* Nothing here.

Features
---
### API
Fetch from `/api` via http GET/POST with following parameters.

#### Nodes
| [node](README.md#parameters) | Description | Parameters | Request method |
|---|---|---|---|
| ojinfo | Online judge infomation. | oj `Essential` <br> node `Essential` <br> field `Optional` | `?oj={oj-name}`<br>`&node=ojinfo`<br>`&field={field-array}` |
| userinfo | User infomation on online judge. | oj `Essential` <br> node `Essential` <br> user `Essential` <br> field `Optional` <br> validtime `Optional` | `?oj={oj-name}`<br>`&node=userinfo`<br>`&user={user-array}`<br>`&field={field-array}`<br>`&validtime={valid-time}` |
| userstat | User submitted problem. | oj `Essential` <br> node `Essential` <br> user `Essential` <br> prob `Optional` <br> field `Optional` <br> validtime `Optional` | `?oj={oj-name}`<br>`&node=userstat`<br>`&user={user-array}`<br>`&prob={prob-array}`<br>`&field={field-array}`<br>`&validtime={valid-time}` |
| probinfo | Problem infomation on online judge. | oj `Essential` <br> node `Essential` <br> prob `Essential` <br> field `Optional` | `?oj={oj-name}`<br>`&node=probinfo`<br>`&prob={prob-array}`<br>`&field={field-array}` |

#### Parameters
| Parameter | Description | Type |
|---|---|---|
| oj | Online judge name. View [here](README.md#supported-oj). | String |
| node | View [here](README.md#nodes). | String |
| user | User ID list. | Array String |
| prob | Problem ID list. | Array String |
| field | Return fields choice. Defalut it all fields. Additional:`link` | Array String |
| validtime | Valid time (seconds). If last fetch time < now - validtime, it will be refreshed. Default in `config/config.php`. | Integer |

#### Supported OJ
| OJ id | OJ name | Link | User ID | Remark |
|---|---|---|---|---|
| bzoj | 大视野在线测评 | http://www.lydsy.com/JudgeOnline | Your login `User ID` ||
| cf | Codeforces | http://codeforces.com | Your login `Handle` | Not supported Field:`userstat->link` |
| gj | Green Judge | http://www.tcgs.tc.edu.tw:1218 | Your login `Account` ||
| hdu | HDU Online Judge | http://acm.hdu.edu.cn | Your login `Author ID` ||
| lightoj | Jan's LightOJ | http://lightoj.com | Your login `User ID` | Not supported Node:`userstat` |
| nthuoj | NTHU Online Judge | http://acm.cs.nthu.edu.tw | Your login `Username` ||
| poj | PKU JudgeOnline | http://poj.org | Your login `User ID` ||
| tioj | TIOJ Infor Online Judge | http://tioj.ck.tp.edu.tw | Your login `Username` ||
| toj | TNFSH Online Judge | http://toj.tfcis.org | View [here](http://toj.tfcis.org/oj/chal/) to find your `ID` ||
| tzj | TNFSH Judge | http://judge.tnfsh.tn.edu.tw:8080 | Your login `Account` ||
| uva | UVa Online Judge | https://uva.onlinejudge.org | Your login `Username` | Not supported Field:`userstat->link` |
| zj | ZeroJudge | http://zerojudge.tw | Your login `Account` ||
