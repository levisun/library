<?php
/**
 * 支付成功后随机红包
 *
 */
class Respond extends Build
{

    public function sendBonus($status, $order_sn, $bonus_type_id)
    {
        if (!$status) {
            return false;
        }

        // 查询订单ID与用户ID
        $result =
        $this->model
        ->table('order_info')
        ->field('order_id, user_id')
        ->where('order_sn=\'' . $order_sn . '\'')
        ->find();

        // 安订单ID查询goods_coupons中是否有此订单商品数据
        // goods_coupons中有数据 是优惠券订单
        // goods_coupons中无数据 不是优惠券订单
        $join = ' INNER JOIN ' . $this->model->getTable('goods_coupons gc') . ' ON(og.goods_id=gc.goods_id)';
        $join .= ' INNER JOIN ' . $this->model->getTable('goods g') . ' ON(og.goods_id=g.goods_id)';
        $map = 'og.order_id=' . $result['order_id'];
        $goods =
        $this->model
        ->table('order_goods og')
        ->field('gc.id, og.goods_id, g.goods_name')
        ->join($join)
        ->where($map)
        ->all();

        // 是优惠券订单
        // 随机红包 （2元与1元红包）
        if (!empty($goods)) {
            $this->import('Bonus');
            $Bonus = new Bonus;

            // 随机红包类型
            // 19 2元红包 18 1元红包
            $rand = rand(1, 10) > 8 ? 19: 18;

            $Bonus->sendBonus($result['user_id'], $rand);

            $coupons = array(
                'user_id' => $order['user_id'],
                'goods'   => $goods,
                );

            return $coupons;
        }

        return false;
    }
}