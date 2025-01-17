<?php namespace Phpcmf\Controllers\Admin;


class Setting_notice extends \Phpcmf\Common
{

    public function index() {

        $notice['member'] = [
            'name' => dr_lang('系统'),
            'value' => require APPPATH.'Config/Notice.php',
        ];

        if (is_file(MYPATH.'Config/Notice.php')) {
            $notice['my'] = [
                'name' => dr_lang('自定义'),
                'value' => require MYPATH.'Config/Notice.php',
            ];
        }

        $local = \Phpcmf\Service::Apps();
        foreach ($local as $dir => $path) {
            if (is_file($path.'install.lock')
                && is_file($path.'/Config/Notice.php')
                && is_file($path.'/Config/App.php')) {
                $app = require $path.'/Config/App.php';
                $cfg = require $path.'/Config/Notice.php';
                $app && $cfg && $notice[strtolower($dir)] = [
                    'name' => $app['name'],
                    'value' => $cfg
                ];
            }
        }

        foreach ($notice as $i => $t) {
            if ($t['value']) {
                foreach ($t['value'] as $ii => $v) {
                    $path = \Phpcmf\Service::L('html')->get_webpath(SITE_ID, 'site', '');
                    $notice[$i]['value'][$ii] = [
                        'name' => $v,
                    ];
                    $notice[$i]['value'][$ii]['file'] = [
                        'mobile' => is_file($path.'config/notice/mobile/'.$ii.'.html') or is_file(CONFIGPATH.'notice/mobile/'.$ii.'.html') ? 1 : 0,
                        'email' => is_file($path.'config/notice/email/'.$ii.'.html') or is_file(CONFIGPATH.'notice/email/'.$ii.'.html') ? 1 : 0,
                    ];
                    if (dr_is_app('notice')) {
                        $notice[$i]['value'][$ii]['file']['notice'] = $notice[$i]['value'][$ii]['file']['mobile'];
                    }
                    if (dr_is_app('weixin')) {
                        $notice[$i]['value'][$ii]['file']['weixin'] = is_file($path.'config/notice/weixin/'.$ii.'.html') or is_file(CONFIGPATH.'notice/weixin/'.$ii.'.html') ? 1 : 0;
                    }
                }
            }
        }

        $data = \Phpcmf\Service::M()->db->table('member_setting')->where('name', 'notice')->get()->getRowArray();

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '通知设置' => ['member/setting_notice/index', 'fa fa-volume-up'],
                    'help' => [194],
                ]
            ),
            'value' => dr_string2array($data['value']),
            'notice_config' => $notice,
        ]);
        \Phpcmf\Service::V()->display('member_setting_notice.html');
    }

    // 保存配置
    public function add() {

        if (IS_AJAX_POST) {
            \Phpcmf\Service::M()->db->table('member_setting')->replace([
                'name' => 'notice',
                'value' => dr_array2string(\Phpcmf\Service::L('input')->post('data'))
            ]);
            \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
            $this->_json(1, dr_lang('操作成功'));
        } else {
            $this->_json(0, dr_lang('异常请求'));
        }
    }

    private function _get_file($path, $ppath, $file, $name) {

        if (is_file($path.'config/notice/'.$name.'/'.$file.'.html')) {
            return htmlentities(file_get_contents($path.'config/notice/'.$name.'/'.$file.'.html'),ENT_COMPAT,'UTF-8');
        } elseif (is_file($ppath.'notice/'.$name.'/'.$file.'.html')) {
            return dr_lang('############由于web目录下没有此文件，因此加载根目录下的文件：'.$ppath.'notice/'.$name.'/'.$file.'.html'.'#################').PHP_EOL
                .htmlentities(file_get_contents($ppath.'notice/'.$name.'/'.$file.'.html'),ENT_COMPAT,'UTF-8');
        }

        return dr_lang('文件不存在，请手动创建此文件');
    }

    // 修改模板
    public function edit() {

        $file = dr_safe_filename($_GET['file']);
        $list = [];
        $ppath = CONFIGPATH;
        !defined('IS_SITES') && define('IS_SITES', 0);
        foreach ($this->site_info as $sid => $t) {
            if ($sid != SITE_ID) {
                continue;
            }
            $path = \Phpcmf\Service::L('html')->get_webpath($sid, 'site', '');
            $list[$sid] = [
                'name' => $t['SITE_NAME'],
                'data' => [
                    'mobile' => [
                        'name' => dr_lang('短信和消息'),
                        'code' => $this->_get_file($path, $ppath, $file, 'mobile'),
                        'file' => (CI_DEBUG && IS_SITES ? $path : '') . 'config/notice/mobile/'.$file.'.html',
                        'help' => 'javascript:dr_help(479);', //'http://help.phpcmf.net/479.html',
                        'height' => '100',
                    ],
                    'email' => [
                        'name' => dr_lang('邮件'),
                        'code' => $this->_get_file($path, $ppath, $file, 'email'),
                        'file' => (CI_DEBUG && IS_SITES ? $path : '') . 'config/notice/email/'.$file.'.html',
                        'help' => 'javascript:dr_help(480);', //'http://help.phpcmf.net/480.html',
                        'height' => '200',
                    ],
                    'weixin' => [
                        'name' => dr_lang('微信'),
                        'code' => $this->_get_file($path, $ppath, $file, 'weixin'),
                        'file' => (CI_DEBUG && IS_SITES ? $path : '') . 'config/notice/weixin/'.$file.'.html',
                        'help' => 'javascript:dr_help(481);', //'http://help.phpcmf.net/481.html',
                        'height' => '200',
                    ],
                ]
            ];

            if (!dr_is_app('weixin')) {
                unset($list[$sid]['data']['weixin']);
            }
        }

        if (IS_POST) {

            if (!IS_DEV) {
                $this->_json(0, dr_lang('禁止修改'));
            }

            $post = \Phpcmf\Service::L('input')->post('data', false);
            if (!$post) {
                $this->_json(0, dr_lang('内容没有填写完整'));
            }

            $ok = 0;
            foreach ($post as $sid => $t) {
                foreach ($t as $name => $value) {
                    if (isset($list[$sid]['data'][$name]) && $list[$sid]['data'][$name] && $value) {
                        $rt = file_put_contents($list[$sid]['data'][$name]['file'], $value);
                        if (!$rt) {
                            $this->_json(0, dr_lang('文件%s写入失败', $list[$sid]['data'][$name]['file']));
                        }
                        $ok++;
                    }
                }
            }

            $this->_json(1, dr_lang('共修改%s个文件', $ok));
        }

        \Phpcmf\Service::V()->assign([
            'list' => $list,
        ]);
        \Phpcmf\Service::V()->display('member_setting_notice_edit.html');exit;
    }

}
