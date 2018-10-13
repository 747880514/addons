<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Setagentid_EweiShopV2Page extends MobileLoginPage
{
	protected $member;

	public function __construct()
	{
		global $_W;
		global $_GPC;
		parent::__construct();
		$this->member = m('member')->getInfo($_W['openid']);
	}

	//设置邀请会员
	public function main()
	{
		global $_W;
		global $_GPC;

		$mid = intval($_W['mid']);
		$code = strval($_GPC['code']);

		if($mid <= 0)
		{
			show_json(0, array('msg'=>'Error 会员信息已过期，请重新登录！'));
		}
		if(empty($code))
		{
			show_json(0, array('msg'=>'Error 手机号/邀请码不能为空'));
		}

		//获取推荐人信息
		$agent = m("member")->getMobileMember($code);	//通过手机号查询
		$agentid = $agent['id'];

		//手机号未查询到，通过邀请码查询
		if(!$agentid)
		{
			$agent = pdo_fetch('SELECT id,isagent FROM' . tablename('ewei_shop_member') . 'WHERE invitation_code=:code LIMIT 1', array(':code' => $code));
			$agentid = $agent['id'];
		}

		//邀请码，手机号都搜索不到的情况下，返回邀请人不存在
		if(!$agentid)
		{
			show_json(0, array('msg'=>'Error 邀请人不存在。'));
		}

		//邀请码不是分销商
		if($agent['isagent'] == 0)
		{
			show_json(0, array('msg'=>'Error 当前邀请码非分销商手机号/邀请码。'));
		}

		//邀请人不能是自己
		if($agentid == $mid)
		{
			show_json(0, array('msg'=>'Error 邀请人不能是自己'));
		}

		//保存推荐人信息
		$setagentiddata['agentid'] = $agentid;
		$res = pdo_update('ewei_shop_member', $setagentiddata, array('id' => $mid));

		//查询
		$hasagentid = pdo_fetchcolumn('select agentid from ' . tablename('ewei_shop_member') . ' where id=:mid ', array(':id' => $mid));

		if($hasagentid >= 0)
		{
			show_json(1, array('mid' => $mid, 'agentid' => $agentid, 'phone'=>$phone, 'msg'=>'验证成功，恭喜正式成为花蒜会员！'));
		}

		show_json(0, array('msg'=>'Error 网路哦错误，请稍后重试！'));
	}

	//获取邀请码
	public function getinvitationcode()
	{
		global $_W;
		global $_GPC;

		if($_W['mid'])
		{
			//如果是店主
			// $invitationcode = pdo_fetch('SELECT a.invitation_code,a.openid,a.agentlevel,b.nickname,b.mobile FROM' . tablename('ewei_shop_member') . ' a , '.tablename('ewei_shop_member').' b WHERE a.id=:id AND a.agentid = b.id LIMIT 1', array(':id' => $_W['mid']));

			$invitationcode = pdo_fetch('SELECT a.invitation_code,a.openid,a.agentlevel,a.agentid FROM' . tablename('ewei_shop_member') . ' a WHERE a.id=:id LIMIT 1', array(':id' => $_W['mid']));

			if($invitationcode['agentid'] > 0)
			{
				$invitationcode2 = pdo_fetch('SELECT b.nickname,b.mobile FROM' . tablename('ewei_shop_member') . ' b WHERE b.id=:id LIMIT 1', array(':id' => $invitationcode['agentid']));

				$invitationcode['nickname'] = $invitationcode2['nickname'];
				$invitationcode['mobile'] = $invitationcode2['mobile'];
			}
			if($invitationcode['agentid'] == 0)
			{
				$invitationcode['nickname'] = '总店';
			}

			//获取会员等级
			$level = p("commission")->getLevel($invitationcode["openid"]);
			if( empty($level) )
			{
				$invitationcode["levelname"] = "蒜头";
			}
			else
			{
				$invitationcode["levelname"] = $level["levelname"];
			}

			$other = $invitationcode;
			$invitationcode = $invitationcode['invitation_code'];


			//未获取到邀请码，设置邀请码
			if(empty($invitationcode))
			{
				$invitationcode = $this->setinvitationcode();
			}

			show_json(1, array('mid' => $_W['mid'], 'invitation_code' => $invitationcode, 'other'=>$other));
		}
	}


	//设置邀请码
	public function setinvitationcode()
	{
		global $_W;
		global $_GPC;

		if($_W['mid'])
		{
			$m_data['invitation_code'] = str_pad($_W['mid'], 6, "0", STR_PAD_LEFT);
			pdo_update('ewei_shop_member', $m_data, array('id' => $_W['mid']));

			return $m_data['invitation_code'];
		}
	}

}

?>
