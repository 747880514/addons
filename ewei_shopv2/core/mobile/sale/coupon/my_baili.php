<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class My_baili_EweiShopV2Page extends MobileLoginPage
{
	//是否有新用户优惠券
	public function hasnewcoupon()
	{
		global $_W;
		global $_GPC;

		$openid = $_W['openid'];
		$count = 0;

		$count = pdo_fetchcolumn('select count(*) from '.tablename('ewei_shop_coupon_data').' where couponid = 1 AND openid = :openid',array(':openid'=>$openid));

		show_json(1, array('count' => $count));
	}
}