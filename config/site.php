<?php

return [
    // SMTP 发送邮件设置
    'smtp' => Array(
        // 是否开启注册邮件验证
        'enable' => '',
        'host' => '',
        'port' => '',
        'user' => '',
        'pass' => '',
        'name' => ''
    ),
	// 模板名称
	'template' => 'pigeon',
	// 站点名称（显示在标题和网页上）
	'sitename' => 'Jason',
	// 站点介绍（显示在网页上）
	'description' => '夏天夏天悄悄过去留下小秘密 ...',
	// 允许注册（true/false）
	'enable_registe' => true,
	// 开启安全模式（开启后将不会解析 Markdown 里的 HTML）
	'enable_safemode' => true,
	// 只对普通用户开启安全模式（设置为 false 则代表所有用户均无法使用 HTML）
	'enable_foruser' => true,
	// reCaptcha 谷歌验证码前端 Key，留空以禁用此功能
	'recaptcha_key' => '',
	// reCaptcha 的服务端 Key，如果上面留空了这里就是废的
	'recaptcha_key_post' => '',

    'setting_auto_refresh' => false
];