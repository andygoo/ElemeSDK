<?php

/**
 * 饿了么蜂鸟配送api封装类
 * Author: shinn_lancelot
 * Mail: 945226793@qq.com
 */
namespace hillpy\ElemeSDK;

class Eleme
{
    private $appId;
    private $secretKey;

    const saltMin = 1000;
    const saltMax = 9999;
    const API_HOST = 'https://open-anubis.ele.me';
    const ACCESS_TOKEN_PATH = '/anubis-webapi/get_access_token?';


    public function __construct($appId, $secretKey)
    {
        $this->appId = $appId;
        $this->secretKey = $secretKey;

    }

    public function getAccessToken()
    {
        $paramArr = array(
            'app_id'=>$this->appId,
            'salt'=>mt_rand(Eleme::saltMin, Eleme::saltMax),
        );
        $url = Eleme::API_HOST . Eleme::ACCESS_TOKEN_PATH . http_build_query($paramArr);
        $res = $this->checkResult(json_decode(Common::http_request($url), true));
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
