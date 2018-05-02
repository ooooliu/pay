# pay
Alipay和WeChat的支付扩展包,后面会持续更新

laravel 扩展包请 [传送至这里](https://github.com/ooooliu/pay)

## 特点
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 运行环境
- PHP 5.6+
- composer

## 支持的支付网关

由于各支付网关参差不齐，所以抽象了两个方法 `driver()`，`gateway()`。

两个方法的作用如下：

`driver()` ： 确定支付平台，如 `alipay`,`wechat`;  

`gateway()`： 确定支付网关。通过此方法，确定支付平台下的支付网关。例如，支付宝下有 「电脑网站支付」，「手机网站支付」，「APP 支付」三种支付网关，通过传入 `web`,`wap`,`app` 确定。

### 1、支付宝

- 电脑支付
- 手机网站支付
- APP 支付

SDK 中对应的 driver 和 gateway 如下表所示：  

| driver | gateway |   描述      |
| :----: | :-----: | :-------:  |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
| alipay | app     | APP 支付    |
| alipay | scan    | 扫码支付     |
| alipay | transfer| 帐户转账(可用于平台用户提现)|
  
### 2、微信

- 公众号支付
- H5 支付
- APP 支付

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | wap     | H5 支付    |
| wechat | app     | APP 支付   |
| wechat | scan    | 扫码支付    |
| wechat | transfer| 企业付款    |

## 支持的方法

所有网关均支持以下方法

- pay(array $config_biz)  
说明：支付接口  
参数：数组类型，订单业务配置项，包含 订单号，订单金额等  
返回：mixed  详情请看「支付网关配置说明与返回值」一节。 

- refund(array|string $config_biz, $refund_amount = 0)  
说明：退款接口  
参数：`$config_biz` 为字符串类型仅对`支付宝支付`有效，此时代表订单号，第二个参数为退款金额。  
返回：mixed  退款成功，返回 服务器返回的数组；否则返回 false；  

- close(array|string $config_biz)  
说明：关闭订单接口  
参数：`$config_biz` 为字符串类型时代表订单号，如果为数组，则为关闭订单业务配置项，配置项内容请参考各个支付网关官方文档。  
返回：mixed  关闭订单成功，返回 服务器返回的数组；否则返回 false；  

- find(string $out_trade_no)  
说明：查找订单接口  
参数：`$out_trade_no` 为订单号。  
返回：mixed  查找订单成功，返回 服务器返回的数组；否则返回 false；  

- verify($data, $sign = null)  
说明：验证服务器返回消息是否合法  
参数：`$data` 为服务器接收到的原始内容，`$sign` 为签名信息，当其为空时，系统将自动转化 `$data` 为数组，然后取 `$data['sign']`。  
返回：mixed  验证成功，返回 服务器返回的数组；否则返回 false；

