INSERT INTO `yzm_config` (`id`, `name`, `type`, `title`,`value`,`status`) VALUES ('', 'submail_appid', '1', '赛邮云Appid','0','1');
INSERT INTO `yzm_config` (`name`, `type`, `title`,`value`,`status`) VALUES ('', 'submail_signature', '1', '赛邮云Signature','0','1');
INSERT INTO `yzm_config` (`id`, `name`, `type`, `title`,`value`,`status`) VALUES ('', 'submail_sign', '1', '赛邮云短信签名','0','1');
INSERT INTO `yzm_config` (`id`, `name`, `type`, `title`,`value`,`status`) VALUES ('', 'member_mobile', '3', '新会员注册是否需要短信验证','0','1');
ALTER TABLE `yzm_member` ADD COLUMN `mobile`  char(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '手机号码' AFTER `loginnum`;
