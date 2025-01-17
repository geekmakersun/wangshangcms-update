<?php namespace Phpcmf\Controllers;

// 账号信息
class Account extends \Phpcmf\Common {

    /**
     * 修改资料
     */
    public function index() {

        // 初始化自定义字段类
        \Phpcmf\Service::L('field')->app(APP_DIR);

        // 获取该组可用字段
        $field = [];
        if ($this->member_cache['field'] && $this->member['groupid']) {
            $fieldid = [];
            foreach ($this->member['groupid'] as $gid) {
                $this->member_cache['group'][$gid]['field']
                && $fieldid = dr_array2array($fieldid, $this->member_cache['group'][$gid]['field']);
            }
            if ($fieldid) {
                foreach ($this->member_cache['field'] as $fname => $t) {
                    in_array($fname, $fieldid) && $field[$fname] = $t;
                }
            }
        }

        // 是否允许更新姓名
        $is_update_name = $this->member_cache['config']['edit_name'] || !$this->member['name'];

        if (IS_POST) {
            $post = \Phpcmf\Service::L('input')->post('data');
            \Phpcmf\Hooks::trigger('member_edit_before', $post);
            list($data, $return, $attach) = \Phpcmf\Service::L('form')->id($this->uid)->validation($post, null, $field, $this->member);
            // 输出错误
            if ($return) {
                $this->_json(0, $return['error'], ['field' => $return['name']]);
            }
            if ($is_update_name) {
                if (!$post['name']) {
                    $this->_json(0, dr_lang('%s没有填写', MEMBER_CNAME), ['field' => 'name']);
                }
                $post['name'] = dr_strcut($post['name'], intval($this->member_cache['register']['cutname']), '');
                if ($this->member_cache['config']['unique_name']
                    && \Phpcmf\Service::M()->db->table('member')->where('id<>'. $this->uid)->where('name', $post['name'])->countAllResults()) {
                    $this->_json(0, dr_lang('%s已经存在', MEMBER_CNAME), ['field' => 'name']);
                }
                if ($this->member_cache['config']['edit_verify']) {
                    // 审核
                } else {
                    \Phpcmf\Service::M()->table('member')->update($this->uid, [
                        'name' => dr_strcut(dr_safe_replace($post['name']), 20, ''),
                    ]);
                }
            }
            $data[1]['is_complete'] = 1;
            if ($this->member_cache['config']['edit_verify']) {
                // 审核
            } else {
                \Phpcmf\Service::M()->table('member_data')->update($this->uid, $data[1]);
            }
            // 附件归档
            SYS_ATTACHMENT_DB && $attach && \Phpcmf\Service::M('Attachment')->handle(
                $this->member['id'],
                \Phpcmf\Service::M()->dbprefix('member').'-'.$this->uid,
                $attach
            );
            if ($this->member_cache['config']['edit_verify']) {
                // 审核
                $id = \Phpcmf\Service::M('verify', 'member')->save_info($this->uid, $is_update_name ? $post['name'] : '', $data[1]);
                // 提醒
                \Phpcmf\Service::M('member')->admin_notice(0, 'member', $this->member, dr_lang('用户[%s]资料审核', $this->member['username']), 'member/edit_verify/edit:id/'.$id);
                $this->_json(1, dr_lang('提交成功，等待管理员审核'), IS_API_HTTP ? \Phpcmf\Service::M('member')->get_member($this->uid) : []);
            } else {
                \Phpcmf\Hooks::trigger('member_edit_after', $data[1]);
                \Phpcmf\Service::M('member')->clear_cache($this->uid);
                $this->_json(1, dr_lang('保存成功'), IS_API_HTTP ? \Phpcmf\Service::M('member')->get_member($this->uid) : []);
            }
        }

        $verify = [];
        if ($this->member_cache['config']['edit_verify']) {
            // 审核
            $verify = \Phpcmf\Service::M('verify', 'member')->info($this->uid);
            if ($verify) {
                $this->member = dr_array22array($this->member, $verify['info']);
                if ($verify['name'] && $is_update_name) {
                    $this->member['name'] = $verify['name'];
                }
            }
        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'field' => $field,
            'member' => $this->member,
            'myfield' => \Phpcmf\Service::L('field')->toform($this->uid, $field, $this->member),
            'edit_verify' => $verify,
            'is_update_name' => $is_update_name,
        ]);
        \Phpcmf\Service::V()->display('account_index.html');
    }

    /**
     * 头像上传
     */
    public function avatar() {

        if (IS_POST) {
            $content = trim($_POST['file']);
            // 普通文件上传
            if (isset($_FILES['file'])) {
                if (isset($_FILES["file"]["tmp_name"]) && $_FILES["file"]["tmp_name"]) {
                    $content = \Phpcmf\Service::L('file')->base64_image($_FILES["file"]["tmp_name"]);
                }
            }
            if (!$content) {
                $this->_json(0, dr_lang('上传文件失败'));
            }
            list($cache_path) = dr_avatar_path();
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/i', $content, $result)) {
                $content = base64_decode(str_replace($result[1], '', $content));
                if (strlen($content) > 30000000) {
                    $this->_json(0, dr_lang('图片太大了'));
                }
                // 头像上传成功之前
                \Phpcmf\Hooks::trigger('upload_avatar_before', [
                    'member' => $this->member,
                    'base64_image' => $content,
                ]);
                $name = $this->uid;
                $dir = dr_avatar_dir($this->uid);
                if ($this->member_cache['config']['avatar_verify']) {
                    // 审核
                    $name.= '_verify';
                }
                $rt = \Phpcmf\Service::L('upload')->base64_image([
                    'content' => $content,
                    'ext' => 'jpg',
                    'save_name' => $name,
                    'save_file' => $cache_path.$dir.$name.'.jpg',
                ]);
                if (!$rt['code']) {
                    $this->_json(0, $rt['msg']);
                }
                if (is_file($cache_path.$this->uid.'.jpg')) {
                    // 移动老版本目录
                    if (copy($cache_path.$this->uid.'.jpg', $cache_path.$dir.$this->uid.'.jpg')) {
                        unlink($cache_path.$this->uid.'.jpg');
                    }
                }
                if ($this->member_cache['config']['avatar_verify']) {
                    // 审核
                    $id = \Phpcmf\Service::M('verify', 'member')->save_avatar($this->uid);
                    // 提醒
                    \Phpcmf\Service::M('member')->admin_notice(0, 'member', $this->member, dr_lang('用户[%s]头像审核', $this->member['username']), 'member/avatar_verify/edit:id/'.$id);
                    $this->_json(1, dr_lang('上传成功，等待管理员审核'), []);
                } else {
                    // 头像上传成功之后
                    \Phpcmf\Hooks::trigger('upload_avatar_after', [
                        'member' => $this->member,
                        'base64_image' => $content,
                    ]);
                    // 头像认证成功
                    if (!$this->member['is_avatar']) {
                        \Phpcmf\Service::M('member')->do_avatar($this->member);
                    }
                    \Phpcmf\Service::M('member')->clear_cache($this->uid);
                    $this->_json(1, dr_lang('上传成功'), IS_API_HTTP ? \Phpcmf\Service::M('member')->get_member($this->uid) : []);
                }
            } else {
                $this->_json(0, dr_lang('头像内容不规范'));
            }
        }

        $verify = [];
        $avatar_url = '';
        if ($this->member_cache['config']['avatar_verify']) {
            // 审核
            $verify = \Phpcmf\Service::M('verify', 'member')->avatar($this->uid);
            if ($verify) {
                $avatar_url = dr_member_verify_avatar_url($this->uid);
            }
        }
        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(['file' => '']),
            'avatar_url' => $avatar_url ? $avatar_url : dr_avatar($this->uid),
            'edit_verify' => $verify,
        ]);
        \Phpcmf\Service::V()->display('account_avatar.html');
    }

    /**
     * 修改密码
     */
    public function password() {

        if ($this->member['salt'] == 'login_sms') {
            $this->member['password'] = '';
        }

        if (IS_POST) {
            $post = \Phpcmf\Service::L('input')->post('data');
            $password = dr_safe_password($post['password']);
            if ((empty($post['password2']) || empty($post['password3']))) {
                $this->_json(0, dr_lang('密码不能为空'), ['field' => 'password2']);
            } elseif ($post['password3'] != $post['password2']) {
                $this->_json(0, dr_lang('两次密码不一致'), ['field' => 'password3']);
            } elseif ($this->member['password'] && md5(md5($password).$this->member['salt'].md5($password)) != $this->member['password']) {
                $this->_json(0, dr_lang('原密码不正确'), ['field' => 'password']);
            } elseif ($this->member['password'] && md5(md5($post['password2']).$this->member['salt'].md5($post['password2'])) == $this->member['password']) {
                $this->_json(0, dr_lang('原密码不能与新密码相同'), ['field' => 'password2']);
            }
            $rt = \Phpcmf\Service::L('Form')->check_password($post['password2'], $this->member['username']);
            if (!$rt['code']) {
                $this->_json(0, $rt['msg'], ['field' => 'password2']);
            }
            // 修改密码
            \Phpcmf\Service::M('member')->edit_password($this->member, $post['password2']);
            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'member' => $this->member,
        ]);
        \Phpcmf\Service::V()->display('account_password.html');
    }

    /**
     * 手机修改
     */
    public function mobile() {

        // 是否允许更新手机号码
        $is_update = $this->member_cache['config']['edit_mobile'] || !$this->member['phone'];

        // 是否需要认证手机号码 // $this->member_cache['config']['mobile'] &&
        $is_mobile = !$this->member['is_mobile'] ;

        // 账号已经录入了手机，且没有进行手机认证时，强制不更新，先认证
        //$is_mobile && $this->member['phone'] && $is_update = 0;

        if (IS_POST) {
            $post = \Phpcmf\Service::L('input')->post('data');
			$cache = \Phpcmf\Service::L('cache')->check_auth_data('member-mobile-code-'.$this->uid, 300);
            if (!$this->member['randcode']) {
                $this->_json(0, dr_lang('手机验证码已过期'));
            } elseif ($post['code'] != $this->member['randcode']) {
                $this->_json(0, dr_lang('手机验证码不正确') . (IS_DEV ? '(你输入是：'.$post['code'].'，正确是：'.$this->member['randcode'].')' : ''));
            } elseif (!$cache) {
                $this->_json(0, dr_lang('手机验证码储存过期'));
            }

            $value = dr_safe_replace($post['phone']);
            if ($is_update && $value) {
                // 更新手机号
                if (!\Phpcmf\Service::L('Form')->check_phone($value)) {
                    $this->_json(0, dr_lang('手机号码格式不正确'));
                } elseif (\Phpcmf\Service::M()->db->table('member')->where('id<>'.$this->member['id'])->where('phone', $value)->countAllResults()) {
                    $this->_json(0, dr_lang('手机号码已经注册'));
                } elseif ($cache != $value) {
                    // caceh存储的是手机号码，验证手机号码是否匹配
                    $this->_json(0, dr_lang('手机号码不匹配（%s）', substr($cache, 0, 3).'****'.substr($cache, -4)) . (IS_DEV ? '(你输入是：'.$value.'，正确是：'.$cache.')' : ''));
                }
                $this->member['phone_old'] = $this->member['phone'];
                $this->member['phone'] = $value;
                \Phpcmf\Hooks::trigger('member_edit_phone_after', $this->member);
                \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['id'])->update(['phone' => $value]);
            } else {
                // 不变更手机时
                if ($cache != $this->member['phone']) {
                    // caceh存储的是手机号码，验证手机号码是否匹配
                    $this->_json(0, dr_lang('手机号码不匹配（%s）', substr($this->member['phone'], 0, 3).'****'.substr($this->member['phone'], -4)) . (IS_DEV ? '(正确是：'.$this->member['phone'].')' : ''));
                }
            }

            // 认证号码
            !$this->member['is_mobile'] && \Phpcmf\Service::M()->db->table('member_data')->where('id', $this->member['id'])->update(['is_mobile' => 1]);

            \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['id'])->update(['randcode' => 0]);

            \Phpcmf\Service::M('member')->clear_cache($this->uid);

            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'api_url' => \Phpcmf\Service::L('Router')->member_url('account/mobile_code'),
            'is_update' => $is_update,
            'is_mobile' => $is_mobile,
        ]);
        \Phpcmf\Service::V()->display('account_mobile.html');
    }

    /**
     * 邮箱修改
     */
    public function email() {

        // 是否允许更新
        $is_update = $this->member_cache['config']['edit_email'] || !$this->member['email'];

        // 是否需要认证 $this->member_cache['config']['email'] &&
        $is_email = !$this->member['is_email'] ;

        // 账号已经录入了手机，且没有进行手机认证时，强制不更新，先认证
        //$is_email && $this->member['phone'] && $is_update = 0;

        if (IS_POST) {
            $post = \Phpcmf\Service::L('input')->post('data');
            $cache = \Phpcmf\Service::L('cache')->check_auth_data('member-email-code-'.$this->uid, 300);
            if (!$this->member['randcode']) {
                $this->_json(0, dr_lang('邮箱验证码已过期'));
            } elseif ($post['code'] != $this->member['randcode']) {
                $this->_json(0, dr_lang('邮箱验证码不正确') . (IS_DEV ? '(你输入是：'.$post['code'].'，正确是：'.$this->member['randcode'].')' : ''));
            } elseif (!$cache) {
                $this->_json(0, dr_lang('邮箱验证码储存过期'));
            }

            $value = dr_safe_replace($post['email']);
            if ($is_update && $value) {
                // 更新
                if (!\Phpcmf\Service::L('Form')->check_email($value)) {
                    $this->_json(0, dr_lang('邮箱格式不正确'));
                } elseif (\Phpcmf\Service::M()->db->table('member')->where('id<>'.$this->member['id'])->where('email', $value)->countAllResults()) {
                    $this->_json(0, dr_lang('邮箱地址已经注册'));
                } elseif ($cache != $value) {
                    // caceh存储的是，验证号码是否匹配
                    $this->_json(0, dr_lang('邮件地址不匹配（%s）', substr($cache, 0, 3).'****'.substr($cache, -4)) . (IS_DEV ? '(你输入是：'.$value.'，正确是：'.$cache.')' : ''));
                }
                $this->member['email_old'] = $this->member['email'];
                $this->member['email'] = $value;
                \Phpcmf\Hooks::trigger('member_edit_email_after', $this->member);
                \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['id'])->update(['email' => $value]);
            } else {
                // 不变更时
                if ($cache != $this->member['email']) {
                    // caceh存储的是手机号码，验证手机号码是否匹配
                    $this->_json(0, dr_lang('邮箱地址不匹配（%s）', substr($this->member['email'], 0, 3).'****'.substr($this->member['email'], -4)) . (IS_DEV ? '(正确是：'.$this->member['phone'].')' : ''));
                }
            }

            // 认证
            !$this->member['is_email'] && \Phpcmf\Service::M()->db->table('member_data')->where('id', $this->member['id'])->update(['is_email' => 1]);

            \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['id'])->update(['randcode' => 0]);

            \Phpcmf\Service::M('member')->clear_cache($this->uid);

            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'api_url' => \Phpcmf\Service::L('Router')->member_url('account/email_code'),
            'is_update' => $is_update,
            'is_email' => $is_email,
        ]);
        \Phpcmf\Service::V()->display('account_email.html');
    }

    /**
     * 短信验证码
     */
    public function mobile_code() {

        $value = '';
        // 是否允许更新手机号码
        if ($this->member_cache['config']['edit_mobile'] || !$this->member['phone'] || !$this->member['is_mobile']) {
            $value = dr_safe_replace(\Phpcmf\Service::L('input')->get('value'));
        }

        // 是否需要认证手机号码 && $this->member_cache['config']['mobile']
        if (!$value && $this->member['phone'] && !$this->member['is_mobile']) {
            $value = $this->member['phone'];
        }

        // 已经认证通过的
        if (!$value && $this->member['is_mobile']) {
            $this->_json(0, dr_lang('您已经通过认证了'));
        }

        // 验证操作间隔
        $name = 'member-mobile-code-'.$this->uid;
		if (\Phpcmf\Service::L('cache')->check_auth_data($name, defined('SYS_CACHE_SMS') && SYS_CACHE_SMS ? SYS_CACHE_SMS : 60)) {
			$this->_json(0, dr_lang('已经发送稍后再试'));
		} elseif (!\Phpcmf\Service::L('Form')->check_phone($value)) {
			$this->_json(0, dr_lang('手机号码格式不正确'));
		}

        $this->member['randcode'] = \Phpcmf\Service::L('Form')->get_rand_value();
        \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['uid'])->update(['randcode' => $this->member['randcode']]);

        $rt = \Phpcmf\Service::M('member')->sendsms_code($value, $this->member['randcode']);
        if (!$rt['code']) {
			$this->_json(0, IS_DEV ? $rt['msg'] : dr_lang('发送失败'));
		}

		\Phpcmf\Service::L('cache')->set_auth_data($name, $value);
		
        $this->_json(1, dr_lang('验证码发送成功'));
    }

    /**
     * 邮件验证码
     */
    public function email_code() {

        $value = '';
        // 是否允许更新手机号码
        if ($this->member_cache['config']['edit_email'] || !$this->member['email'] || !$this->member['is_email']) {
            $value = dr_safe_replace(\Phpcmf\Service::L('input')->get('value'));
        }

        // 是否需要认证手机号码$this->member_cache['config']['email'] &&
        if (!$value && $this->member['email'] && !$this->member['is_email']) {
            $value = $this->member['email'];
        }

        // 已经认证通过的
        if (!$value && $this->member['is_email']) {
            $this->_json(0, dr_lang('您已经通过认证了'));
        }

        // 验证操作间隔
        $name = 'member-email-code-'.$this->uid;
		if (\Phpcmf\Service::L('cache')->check_auth_data($name, 300)) {
			$this->_json(0, dr_lang('已经发送稍后再试'));
		} elseif (!\Phpcmf\Service::L('Form')->check_email($value)) {
			$this->_json(0, dr_lang('邮箱地址格式不正确'));
		}

        $this->member['randcode'] = \Phpcmf\Service::L('Form')->get_rand_value();
        \Phpcmf\Service::M()->db->table('member')->where('id', $this->member['uid'])->update(['randcode' => $this->member['randcode']]);

        $rt = \Phpcmf\Service::M('member')->sendmail($value, dr_lang('邮件验证'), 'member_email_code.html', $this->member);
        if (!$rt['code']) {
			$this->_json(0, IS_DEV ? $rt['msg'] : dr_lang('发送失败'));
		}

		\Phpcmf\Service::L('cache')->set_auth_data($name, $value);

        $this->_json(1, dr_lang('验证码发送成功'));
    }

    /**
     * 登录记录
     */
    public function login() {
        \Phpcmf\Service::V()->display('account_login.html');
    }

    /**
     * 解除绑定
     */
    public function oauth_delete() {

        $name = dr_safe_replace(\Phpcmf\Service::L('input')->get('name'));
        \Phpcmf\Service::M()->db->table('member_oauth')->where('uid', $this->uid)->where('oauth', $name)->delete();
        if (dr_is_app('weixin')) {
            \Phpcmf\Service::M()->db->table('weixin_user')->where('uid', $this->uid)->delete();
        }

        \Phpcmf\Service::M('member')->clear_cache($this->uid);

        $this->_json(1, dr_lang('操作成功'), ['url' => \Phpcmf\Service::L('Router')->member_url('account/oauth')]);
    }

    /**
     * 快捷登录
     */
    public function oauth() {

        $name = [];
        $oauth = dr_oauth_list();
        if (dr_is_app('weixin')) {
            $oauth['wechat'] = [];
        }
        foreach ($oauth as $value => $t) {
            if (!isset($this->member_cache['oauth'][$value]['id'])
                || !$this->member_cache['oauth'][$value]['id']) {
                continue;
            }
            $name[] = $value;
        }

        \Phpcmf\Service::V()->assign([
            'list' => \Phpcmf\Service::M('member')->oauth($this->uid),
            'oauth' => $name,
        ]);
        \Phpcmf\Service::V()->display('account_oauth.html');
    }
}
