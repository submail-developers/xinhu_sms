<?php
class smsbaoChajian extends Chajian{

	private	$sendurl	=	'https://api.mysubmail.com/message/send/';

	/**
	*	短信模版写这里的
	*/
	protected function initChajian()
	{

		$mobian['defyzm'] 	= '欢迎使用，您的短信验证码为：#code#，为保障系统安全，请勿将验证码和办公系统网址提供给他人，祝您工作愉快。';
		$mobian['defsucc'] 	= '您提交单据(#modename#,单号:#sericnum#)已全部处理完成，可登录系统查看详情。';
		$mobian['default'] 	= '您有单据(#modename#,单号:#sericnum#)需要处理，请登录系统及时去处理。';
		$mobian['birthday'] = '尊敬的#name#，今天是#dt#，农历#dtnong#，是您的生日，我们在这里祝您生日快乐。';
		$mobian['defnum'] 	= '您有#applyname#的(#modename#)单据需要您处理，详情：#url#';
		$mobian['defurls'] 	= '您有单据(#modename#,单号:#sericnum#)需要处理，请及时去处理，详情：#url#。';
		$mobian['gongsms'] 		= '您收到一条“#title#”的通知，详情：#url#';
		$mobian['meetapply'] 	= '#optname#发起会议“#title#”在#hyname#，时间#startdt#至#enddt#';
		$mobian['meetcancel'] 	= '#optname#取消会议“#title#”，时间#startdt#至#enddt#，请悉知。';
		$mobian['meettodo'] 	= '会议“#title#”将在#fenz#分钟后的#time#开始请做好准备，在会议室“#hyname#”';

		//上面加你的模版
		$this->mobianarr 	= $mobian;
	}

	/**
	*	批量发送短信
	*	$mobiles 接收人手机号多个,分开
	*	$qianm 签名
	*	$tplid 模版编号，在上面initChajian()数组中查找
	*	$cans 模版中的参数数组
	*	例子：c('mysms')->send('15800000000,15800000001','信呼', 'default', array('modename'=>'模块名','sericnum'=>'单号')); 这例子是不需要自己调用，只要短信设置下切换为“我的短信服务”就可以了
	*/
	public function send($mobiles, $qianm, $tplid, $cans=array())
	{
		//要发送短信的内容
        $text	= arrvalue($this->mobianarr, $tplid);
		if(isempt($text))return returnerror('模版'.$tplid.'不存在');
		foreach($cans as $k=>$v)$text = str_replace('#'.$k.'#', $v, $text);
		if(isempt($qianm))return returnerror('没有设置短信签名');

		$text = '【'.$qianm.'】'.$text.''; //发的短信
        $where = array('num'=>'sms_bnum');
		$where1 = array('num'=>'sms_bpwd');

        $user = DB::table('option')->getone($where,'value')['value']; //短信平台帐号
		$pass = DB::table('option')->getone($where1,'value')['value'];
		
		$send_str['to'] = $mobiles;
		$send_str['content'] = $text;
		$send_str['appid'] = $user;
		$send_str['signature'] = $pass;
		$result = $this->post_data($this->sendurl,$send_str);

		if($result['status'] == 'success' )return returnerror('发送成功');
		if($result['status'] !== 'success' )return returnerror('发送失败');

	}

	protected	function post_data($url, $data)
	{
		$query = http_build_query($data);
		$options['http'] = array(
			'timeout' => 60,
			'method' => 'POST',
			'header' => 'Content-type:application/x-www-form-urlencoded',
			'content' => $query
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$output = trim($result, "\xEF\xBB\xBF");
		return json_decode($output, true);
	}
}
