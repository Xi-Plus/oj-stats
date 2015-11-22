# oj-stats
Online Judge Stats

Required
---
* `cURL-HTTP-function` from [here](https://github.com/Xi-Plus/cURL-HTTP-function)

Features
---
### API
Fetch from `/api` via http GET/POST with following parameters.

#### Nodes
| [node](README.md#parameters) | Description | Parameters | Request method |
|---|---|---|---|
| ojinfo | Online judge infomation. | oj `Essential` <br> node `Essential` | `?oj={oj-name}`<br>`&node=ojinfo` |
| userinfo | User infomation on online judge. | oj `Essential` <br> node `Essential` <br> user `Essential` <br> validtime `Optional` | `?oj={oj-name}`<br>`&node=userinfo`<br>`&user={user-array}`<br>`&validtime={valid-time}` |
| userstat | User solved problem. | oj `Essential` <br> node `Essential` <br> user `Essential` <br> prob `Optional` <br> validtime `Optional` | `?oj={oj-name}`<br>`&node=userstat`<br>`&user={user-array}`<br>`&prob={prob-array}`<br>`&validtime={valid-time}` |

#### Parameters
| Parameter | Description | Type |
|---|---|---|
| oj <br> `Essential` | Online judge name. View [here](README.md#supported-oj). | String |
| node <br> `Essential` | View [here](README.md#nodes). | String |
| user <br> `Essential` | User ID list. | Array String |
| prob <br> `Optional` | Problem ID list. | Array String |
| validtime <br> `Optional` | Valid time (seconds). If last fetch time < now - validtime, it will be refreshed. Default in `config/config.php`. | Integer |

#### Supported OJ
| OJ id | OJ name | Link | User ID |
|-----------|-------------------|---|---|
| bzoj | 大视野在线测评 | http://www.lydsy.com/JudgeOnline | Your login `User ID` |
| cf | Codeforces | http://codeforces.com | Your login `Handle` |
| gj | Green Judge | http://www.tcgs.tc.edu.tw:1218 | Your login `Account` |
| hdu | HDU Online Judge | http://acm.hdu.edu.cn | Your login `Author ID` |
| poj | PKU JudgeOnline | http://poj.org | Your login `User ID` |
| tioj | TIOJ Infor Online Judge | http://tioj.ck.tp.edu.tw | Your login `Username` |
| toj | TNFSH Online Judge | http://toj.tfcis.org | View [here](http://toj.tfcis.org/oj/chal/) to find your `ID` |
| tzj | TNFSH Judge | http://judge.tnfsh.tn.edu.tw:8080 | Your login `Account` |
| uva | UVa Online Judge | https://uva.onlinejudge.org | Your login `Username` |
| zj | ZeroJudge | http://zerojudge.tw | Your login `Account` |
