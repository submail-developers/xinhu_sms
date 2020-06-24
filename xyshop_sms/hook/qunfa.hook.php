<?php
//发送邮件
function qunfa_email($user, $info, $qunfa_id = 0)
{
    global $db, $cache_setting;
    pe_lead('public/class/mail/mail.class.php');
    $mail = new PHPMailer();                                   //创建PHPMailer实例
    $mail->IsSMTP();                                           //设置SMTP模式
    $mail->IsHTML(true);
    $mail->Host = $cache_setting['email_smtp']; //SMTP服务器地址
    $mail->Port = $cache_setting['email_port']; //SMTP服务器端口
    $mail->SMTPAuth = true;                                 //SMTP认证
    $mail->SMTPSecure = $cache_setting['email_ssl'] ? 'ssl' : '';
    $mail->Username = $cache_setting['email_name']; //认证用户名
    $mail->Password = $cache_setting['email_pw'];   //认证密码
    $mail->Subject = $info['qunfa_name'];                     //邮件标题
    $mail->Body = $info['qunfa_text'];
    $mail->CharSet = 'utf-8';                                  // 这里指定字符集！
    $mail->Encoding = 'base64';
    $mail->SetFrom($cache_setting['email_name'], $cache_setting['email_nname']);        //设置发件人
    if (stripos($user, '@') !== false) {
        $user_arr = explode(',', $user);
        foreach ($user_arr as $k => $v) $user_list[$k]['user_email'] = $v;
    } else {
        $user_list = $db->pe_selectall('user', " and `user_email` != '' and `user_id` in({$user})");
    }
    foreach ($user_list as $v) {
        if (preg_match("/^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[a-z]{2,3}$/", $v['user_email'])) {
            $mail->AddAddress($v['user_email']);  //添加收件人
            $result = $mail->Send();
            $mail->ClearAddresses();
            //更新或插入发送日志
            $sql_set['noticelog_stime'] = time();
            if ($result['result']) {
                $sql_set['noticelog_state'] = 'success';
            } else {
                $sql_set['noticelog_state'] = 'fail';
                $sql_set['noticelog_error'] = $result['show'];
            }
            if ($qunfa_id) {
                $db->pe_update('noticelog', array('noticelog_id' => $qunfa_id), pe_dbhold($sql_set));
            } else {
                $sql_set['noticelog_type'] = 'email';
                $sql_set['noticelog_user'] = $v['user_email'];
                $sql_set['noticelog_name'] = $info['qunfa_name'];
                $sql_set['noticelog_text'] = $info['qunfa_text'];
                $sql_set['noticelog_atime'] = time();
                $db->pe_insert('noticelog', pe_dbhold($sql_set));
            }
        }
    }
    if ($result['result']) $result['show'] = '发送成功';
    return $result;
}

//发送短信
function qunfa_sms($phone, $text, $templateid = null, $qunfa_id = 0)
{
    global $db, $cache_setting;
    if ($templateid == null) {
        $content = $text;
    } else {
        $sms_param = trim($text, "}");
        $sms_param = trim($sms_param, "{");
        $tpl = $templateid;
        $arr = explode(',', $sms_param);
        foreach ($arr as $v) {
            $key = explode(':', $v);
            $str = remove_quote($key[1]);
            $tpl = str_replace('${' . $key[0] . '}', $str, $tpl);
        }
        $content = '【' . $cache_setting['sms_sign'] . '】' . $tpl;
    }

    $data['appid'] = $cache_setting['sms_key']; //短信平台帐号
    $data['signature'] = $cache_setting['sms_secret']; //短信平台密码
    $data['content']    =   $content;
    $data['to'] =   $phone;
    $query = http_build_query($data);
    $options['http'] = array(
        'timeout' => 60,
        'method' => 'POST',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'content' => $query
    );
    $context = stream_context_create($options);
    $result = file_get_contents("https://api.mysubmail.com/message/send/", false, $context);
    $output = trim($result, "\xEF\xBB\xBF");
    $result = json_decode($output, true);
    if ($result['status'] == 'success') {
        return array('result' => true, 'show' => '发送成功');
    } else {
        return array('result' => false, 'show' => '发送失败');
    }
}

?>
