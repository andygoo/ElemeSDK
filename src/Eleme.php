<?php

/**
 * 饿了么蜂鸟配送api封装类
 * Author: shinn_lancelot
 * Mail: 945226793@qq.com
 */
namespace hillpy\ElemeSDK;

include "Common.php";

class Eleme
{
    private $appId = '';
    private $secretKey = '';
    private $salt = '';
    private $accessToken = '';
    private $commonParamArr = array();
    private $apiHost = '';
    public $accessTokenData = array();

    const saltMin = 1000;
    const saltMax = 9999;
    const API_HOST_DEBUG = 'https://exam-anubis.ele.me';
    const API_HOST = 'https://open-anubis.ele.me';
    const ACCESS_TOKEN_PATH = '/anubis-webapi/get_access_token?';
    const ORDER_PATH = '/anubis-webapi/v2/order';
    const ORDER_CANCEL_PATH = '/anubis-webapi/v2/order/cancel';
    const ORDER_QUERY_PATH = '/anubis-webapi/v2/order/query';
    const ORDER_COMPLAINT_PATH = '/anubis-webapi/v2/order/complaint';
    const ORDER_CARRIER_PATH = '/anubis-webapi/v2/order/carrier';
    const CHAIN_STORE_PATH = '/anubis-webapi/v2/chain_store';
    const CHAIN_STORE_QUERY_PATH = '/anubis-webapi/v2/chain_store/query';
    const CHAIN_STORE_UPDATE_PATH = '/anubis-webapi/v2/chain_store/update';
    const CHAIN_STORE_DELIVERY_QUERY_PATH = '/anubis-webapi/v2/chain_store/update';

    /**
     * 初始化部分变量
     * Eleme constructor.
     * @param array $paramArr
     */
    public function __construct($paramArr = array())
    {
        $this->apiHost = $paramArr['debug'] === true ? Eleme::API_HOST_DEBUG : Eleme::API_HOST;
        $this->appId = $paramArr['appId'];
        $this->secretKey = $paramArr['secretKey'];
        $this->salt = mt_rand(Eleme::saltMin, Eleme::saltMax);
        $this->commonParamArr = array(
            'app_id'=>$this->appId,
            'salt'=>$this->salt,
        );

        if ($paramArr['accessToken']) {
            $this->accessToken = $paramArr['accessToken'];
        } else {
            $this->accessTokenData = $this->getAccessTokenData();
            $this->accessTokenData && $this->accessToken = $this->accessTokenData['access_token'];
        }
    }

    /**
     * accessToken数据数组获取
     * @return mixed|string
     */
    public function getAccessTokenData()
    {
        $paramArr = $this->commonParamArr;
        $paramArr['signature'] = $this->getSignature();
        $url = $this->apiHost . Eleme::ACCESS_TOKEN_PATH . http_build_query($paramArr);
        $res = $this->checkResult(json_decode(Common::httpRequest($url), true));
        if ($res['code'] == 200) {
            return $res['data'];
        } else {
            return '';
        }
    }

    /**
     * 获取签名
     * @return string
     */
    public function getSignature()
    {
        $paramArr = array(
            'app_id'=>$this->appId,
            'salt'=>$this->salt,
            'secret_key'=>$this->secretKey,
        );
        return md5(urlencode(http_build_query($paramArr)));
    }

    /**
     * 获取商务签名
     * @param string $data
     * @return string
     */
    public function getBusinessSignature($data = '')
    {
        $paramArr = array(
            'app_id'=>$this->appId,
            'access_token'=>$this->accessToken,
            'data'=>urlencode($data),
            'salt'=>$this->salt,
        );
        return md5((http_build_query($paramArr)));
    }

    /**
     * 添加订单（下单）
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "partner_remark": "商户备注信息",
            "partner_order_code": "12345678",
            "notify_url": "http://123.100.120.22:8090",
            "order_type": 1,
            "chain_store_code": "A001",
            "transport_info": {
                "transport_name": "XXX烤鸭店",
                "transport_address": "上海市普陀区近铁城市广场5楼",
                "transport_longitude": 120.00000,
                "transport_latitude": 30.11111,
                "position_source": 1,
                "transport_tel": "13901232231",
                "transport_remark": "备注"
            },
            "order_add_time": 1452570728594,
            "order_total_amount": 50.00,
            "order_actual_amount": 48.00,
            "order_weight": 3.5,
            "order_remark": "用户备注",
            "is_invoiced": 1,
            "invoice": "xxx有限公司",
            "order_payment_status": 1,
            "order_payment_method": 1,
            "is_agent_payment": 0,
            "require_payment_pay": 50.00,
            "goods_count": 4,
            "require_receive_time": 1452570728594,
            "serial_number": "5678",
            "receiver_info": {
                "receiver_name": "李明",
                "receiver_primary_phone": "13900000000",
                "receiver_second_phone": "13911111111",
                "receiver_address": "上海市近铁广场",
                "receiver_longitude": 130.0,
                "receiver_latitude": 30.0,
                "position_source": 1
            },
            "items_json": [
            {
                "item_id": "fresh0001",
                "item_name": "苹果",
                "item_quantity": 5,
                "item_price": 10.00,
                "item_actual_price": 9.50,
                "item_size": 1,
                "item_remark": "苹果，轻放",
                "is_need_package": 1,
                "is_agent_purchase": 0,
                "agent_purchase_price": 10.00
            },
            {
                "item_id": "fresh0002",
                "item_name": "香蕉",
                "item_quantity": 1,
                "item_price": 20.00,
                "item_actual_price": 19.00,
                "item_size": 2,
                "item_remark": "香蕉，轻放",
                "is_need_package": 1,
                "is_agent_purchase": 0,
                "agent_purchase_price": 10.00
            }]
        },
     * @return array
     */
    public function addOrder($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::ORDER_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 取消订单
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "partner_order_code": "BG32141",
            "order_cancel_reason_code": 2,
            "order_cancel_code": 1,
            "order_cancel_description": "货品不新鲜",
            "order_cancel_time": 1452570728594
        },
     * @return array
     */
    public function cancelOrder($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::ORDER_CANCEL_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 查询订单
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "partner_order_code": "1383837732"
        }
     * @return array
     */
    public function queryOrder($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::ORDER_QUERY_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 投诉订单
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "partner_order_code": "BG32141",
            "order_complaint_code": 150,
            "order_complaint_desc": "未保持餐品完整",
            "order_complaint_time": 1452570728594
        },
     * @return array
     */
    public function complaintOrder($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::ORDER_COMPLAINT_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 获取骑手位置
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "partner_order_code": "BG32141",
        },
     * @return array
     */
    public function getOrderCarrier($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::ORDER_CARRIER_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 添加门店信息
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "chain_store_code": "A001",
            "chain_store_name": "门店一",
            "contact_phone": "13611581190",
            "address": "上海市",
            "position_source": 3,
            "longitude": "109.690773",
            "latitude": "19.91243",
            "service_code": "1"
        },
     * @return array
     */
    public function addChainStore($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::CHAIN_STORE_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * 查询门店信息
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
        "chain_store_code": ["A001","A002"]
        }
     * @return array
     */
    public function queryChainStore($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::CHAIN_STORE_QUERY_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "chain_store_code": "A001",
            "chain_store_name": "门店一",
            "contact_phone": "13611581190",
            "address": "上海市",
            "position_source": 3,
            "longitude": "109.690773",
            "latitude": "19.91243",
            "service_code": "1"
        }
     * @return array
     */
    public function updateChainStore($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::CHAIN_STORE_UPDATE_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    /**
     * @param array $extendParamArr
     * $extendParamArr['data']的json形式：{
            "chain_store_code": "A001",
            "position_source": 3,
            "receiver_longitude": "109.690773",
            "receiver_latitude": "19.91243"
        }
     * @return array
     */
    public function queryChainStoreDelivery($extendParamArr = array())
    {
        $paramArr = array_merge($extendParamArr, $this->commonParamArr);
        $paramArr['signature'] = $this->getBusinessSignature(Common::jsonEncode($paramArr['data'], 'JSON_UNESCAPED_UNICODE'));
        $url = $this->apiHost . Eleme::CHAIN_STORE_DELIVERY_QUERY_PATH;
        $res = $this->checkResult(json_decode(Common::httpRequest($url, Common::jsonEncode($paramArr)), true));
        return $res;
    }

    public function checkResult($res = array())
    {
        if (!isset($res['code']) || count($res) <= 0 || strtolower($res['msg']) == 'success') {
            return $res;
        }

        switch ($res['code']) {
            /**
             * errorCode
             */
            case 40000:
                $res['msg'] = '请求失败';
                break;
            case 40001:
                $res['msg'] = 'appid不存在';
                break;
            case 40002:
                $res['msg'] = '验证签名失败';
                break;
            case 40004:
                $res['msg'] = 'token不正确或token已失效';
                break;
            case 50010:
                $res['msg'] = '缺失必填项';
                break;
            case 50011:
                $res['msg'] = '订单号重复提交';
                break;
            case 50012:
                $res['msg'] = '订单预计送达时间小于当前时间';
                break;
            case 50018:
                $res['msg'] = '查询订单错误';
                break;
            case 50019:
                $res['msg'] = '查询运单错误';
                break;
            case 50025:
                $res['msg'] = '订单暂未生成';
                break;
            case 50026:
                $res['msg'] = '运单暂未生成';
                break;
            case 50037:
                $res['msg'] = '订单不存在';
                break;
            case 50040:
                $res['msg'] = '字段值过长';
                break;
            case 50041:
                $res['msg'] = '字段值不符合规则';
                break;
            case 50042:
                $res['msg'] = '无此服务类型';
                break;
            case 50101:
                $res['msg'] = '商户取消订单失败';
                break;
            case 50102:
                $res['msg'] = '当前订单状态不允许取消';
                break;
            case 50110:
                $res['msg'] = '未购买服务或服务已下线';
                break;
            case 500060:
                $res['msg'] = '订单配送距离太远了超过阈值';
                break;
            case 500070:
                $res['msg'] = '没有运力覆盖';
                break;
            case 500080:
                $res['msg'] = '没有绑定微仓';
                break;
            case 500090:
                $res['msg'] = '用户绑定的微仓和运力覆盖范围不匹配';
                break;
            case 500100:
                $res['msg'] = '订单超重';
                break;
            case 50015:
                $res['msg'] = '预计送达时间过长';
                break;
            case 500103:
                $res['msg'] = '添加门店信息失败';
                break;
            case 500104:
                $res['msg'] = '经纬度不合法';
                break;
            case 500105:
                $res['msg'] = '该门店已认证通过，不能重复创建';
                break;
            case 500106:
                $res['msg'] = '该门店在认证中，请核查';
                break;
            case 500113:
                $res['msg'] = '门店编码存在,请使用其他编码';
                break;
            /**
             * otherCode
             */
            case 1:
                $res['msg'] = '系统已接单';
                break;
            case 20:
                $res['msg'] = '已分配骑手';
                break;
            case 80:
                $res['msg'] = '骑手已到店';
                break;
            case 2:
                $res['msg'] = '配送中';
                break;
            case 3:
                $res['msg'] = '已送达';
                break;
            case 5:
                $res['msg'] = '异常';
                break;
            case 4:
                $res['msg'] = '已取消';
                break;
        }

        return $res;
    }
}
