<?php namespace Phpcmf\Member;

/**
 * http://www.xunruicms.com
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/


// 网站表单操作类 基于 Ftable
class Form extends \Phpcmf\Table
{
    public $form;
    protected $_is_post;
    protected $_is_edit;
    protected $_is_delete;

    protected $is_verify;

    // 上级公共类
    public function __construct() {
        parent::__construct();
        $this->_Extend_Init();
    }


    public function _get_auth_value($name, $member) {

        if (!$this->form['setting'][$name]) {
            return 0;
        }

        if (!$member) {
            $auth = [0];
        } else {
            $auth = $member['groupid'];
            if (!$auth) {
                $auth = [0]; // 没有用户组的视为游客
            }
        }

        $value = [];
        foreach ($auth as $k) {
            if (isset($this->form['setting'][$name][$k])) {
                $value[] = (int)$this->form['setting'][$name][$k];
            }
        }

        return $value ? max($value) : 0;
    }

    // 继承类初始化
    protected function _Extend_Init() {
        // 判断表单是否操作
        $cache = \Phpcmf\Service::L('cache')->get('form-'.SITE_ID);
        $this->form = $cache[\Phpcmf\Service::L('Router')->class];
        if (!$this->form) {
            $this->_admin_msg(0, dr_lang('网站表单【%s】不存在', \Phpcmf\Service::L('Router')->class));
        } elseif (!$this->form['setting']['is_member']) {
            $this->_msg(0, dr_lang('网站表单【%s】没有开启管理内容功能', \Phpcmf\Service::L('Router')->class));
        } elseif (isset($this->form['setting']['web']) && $this->form['setting']['web']) {
            $this->_msg(0, dr_lang('无权限访问表单'));
            return;
        }
        // 支持附表存储
        $this->is_data = 1;
        // 模板前缀(避免混淆)
        $this->tpl_prefix = 'form_';
        // 单独模板命名
        $this->tpl_name = $this->form['table'];
        // 表单显示名称
        $this->name = dr_lang('网站表单（%s）', $this->form['name']);
        // 初始化数据表
        $this->_init([
            'table' => SITE_ID.'_form_'.$this->form['table'],
            'field' => $this->form['field'],
            'date_field' => 'inputtime',
            'show_field' => 'title',
            'list_field' => $this->form['setting']['list_field'],
            'order_by' => 'displayorder DESC,inputtime DESC',
            'where_list' => 'uid='.$this->uid,
        ]);
        $this->edit_where = $this->delete_where = 'uid='.$this->uid;

        // 是否有验证码
        $this->is_verify = $this->_get_auth_value('post_verify', $this->member);
        $this->is_post_code = $this->_get_auth_value('post_code', $this->member);

        // 无权限发布表单
        $this->_is_post = $this->_get_auth_value('post_add', $this->member);
        // 修改权限
        $this->_is_edit = $this->_is_post;
        // 删除权限
        $this->_is_delete = $this->_is_post;

        \Phpcmf\Service::V()->assign([
            'field' => $this->init['field'],
            'form_list' => $cache,
            'form_name' => $this->form['name'],
            'form_table' => $this->form['table'],
            'is_delete' => $this->_is_delete,
            'is_post' => $this->_is_post,
            'is_edit' => $this->_is_edit,
            'is_post_code' => $this->is_post_code,
        ]);
    }

    // 添加表单内容
    protected function _Member_Add() {
        list($tpl) = $this->_Post(0);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 修改表单内容
    protected function _Member_Edit() {
        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        list($tpl, $data) = $this->_Post($id);
        if (!$data) {
            $this->_msg(0, dr_lang('数据不存在: '.$id));
        }
        \Phpcmf\Service::V()->display($tpl);
    }

    // 查看表单列表
    protected function _Member_List() {
        list($tpl) = $this->_List();
        return \Phpcmf\Service::V()->display($tpl);
    }

    // 删除表单内容
    protected function _Member_Del() {
        $this->_Del(
            \Phpcmf\Service::L('input')->get_post_ids(),
            null,
            function ($rows) {
                // 对应删除提醒
                foreach ($rows as $t) {
                    \Phpcmf\Service::M('member')->delete_admin_notice('form/'.$this->form['table'].'_verify/edit:id/'.$t['id'], SITE_ID);
                    \Phpcmf\Service::M('member')->delete_admin_notice('form/'.$this->form['table'].'/edit:id/'.$t['id'], SITE_ID);
                    \Phpcmf\Service::L('cache')->clear('from_'.$this->form['table'].'_show_id_'.$t['id']);
                }

            },
            \Phpcmf\Service::M()->dbprefix($this->init['table'])
        );
    }

    // 后台批量保存排序值
    protected function _Member_Order() {
        $this->_Display_Order(
            intval(\Phpcmf\Service::L('input')->get('id')),
            intval(\Phpcmf\Service::L('input')->get('value'))
        );
    }

    /**
     * 获取内容
     * $id      内容id,新增为0
     * */
    protected function _Data($id = 0) {

        $data = parent::_Data($id);
        if ($data && $data['uid'] != $this->uid) {
            return [];
        }

        return $data;
    }

    // 格式化保存数据 保存之前
    protected function _Format_Data($id, $data, $old) {

        // 新增数据
        if (!$old) {
            /*
            if ($this->uid) {
                // 判断日发布量
                $day_post = \Phpcmf\Service::M('member_auth')->form_auth($this->form['id'], 'day_post', $this->member);
                if ($day_post && \Phpcmf\Service::M()->db
                        ->table($this->init['table'])
                        ->where('uid', $this->uid)
                        ->where('DATEDIFF(from_unixtime(inputtime),now())=0')
                        ->countAllResults() >= $day_post) {
                    $this->_json(0, dr_lang('每天发布数量不能超过%s个', $day_post));
                }
                // 判断发布总量
                $total_post = \Phpcmf\Service::M('member_auth')->form_auth($this->form['id'], 'total_post', $this->member);
                if ($total_post && \Phpcmf\Service::M()->db
                        ->table($this->init['table'])
                        ->where('uid', $this->uid)
                        ->countAllResults() >= $total_post) {
                    $this->_json(0, dr_lang('发布数量不能超过%s个', $total_post));
                }
            }*/

			// 审核状态
            $data[1]['status'] = $this->_get_auth_value('post_verify', $this->member) ? 0 : 1;

            // 默认数据
            $data[0]['uid'] = $data[1]['uid'] = (int)$this->member['uid'];
            $data[1]['inputip'] = \Phpcmf\Service::L('input')->ip_info();
            $data[1]['inputtime'] = SYS_TIME;
            $data[1]['tableid'] = $data[1]['displayorder'] = 0;
        } else {
			// 修改时
			// 审核状态
            $data[1]['status'] = $this->_get_auth_value('post_verify', $this->member) ? 0 : 1;
		}


        return $data;
    }

    /**
     * 保存内容
     * $id      内容id,新增为0
     * $data    提交内容数组,留空为自动获取
     * $func    格式化提交的数据
     * */
    protected function _Save($id = 0, $data = [], $old = [], $func = null, $func2 = null) {

        return parent::_Save($id, $data, $old,
            function ($id, $data, $old) {
                // 挂钩点
                if (!$old) {
                    // 首次 发布
                    \Phpcmf\Hooks::trigger('form_post_before', dr_array2array($data[1], $data[0]));
                }
                return dr_return_data(1, 'ok', $data);
            },
            function ($id, $data, $old) {
                if (!$old) {
                    // 首次 发布
                    // 提醒通知
                    if (isset($this->form['setting']['notice']['use']) && $this->form['setting']['notice']['use']) {
                        if (isset($this->form['setting']['notice']['username']) && $this->form['setting']['notice']['username']) {
                            $arr = explode(',', $this->form['setting']['notice']['username']);
                            $var = dr_array2array($data[1], $data[0]);
                            $fields = $this->form['field'];
                            $fields['inputtime'] = ['fieldtype' => 'Date'];
                            $var = \Phpcmf\Service::L('Field')->format_value($fields, $var, 1);
                            foreach ($arr as $autor) {
                                $user = dr_member_username_info($autor);
                                if (!$user) {
                                    log_message('error', '网站表单【'.$this->form['name'].'】已开启通知提醒，但通知人['.$autor.']有误');
                                } else {
                                    \Phpcmf\Service::L('Notice')->send_notice_user('form_'.$this->form['table'].'_post', $user['id'], $var, $this->form['setting']['notice']);
                                }
                            }
                        } else {
                            log_message('error', '网站表单【'.$this->form['name'].'】已开启通知提醒，但未设置通知人');
                        }
                    }
                    // 挂钩点
                    \Phpcmf\Hooks::trigger('form_post_after', dr_array2array($data[1], $data[0]));
                }
                if (!$data[1]['status']) {
                    // 审核
                    \Phpcmf\Service::M('member')->admin_notice(SITE_ID, 'content', $this->member, dr_lang('%s提交审核', $this->form['name']), 'form/'.$this->form['table'].'_verify/edit:id/'.$data[1]['id']);
                    $data['url'] = $this->form['setting']['rt_url'];
                    return dr_return_data($data[1]['id'], dr_lang($this->form['setting']['rt_text2'] ? $this->form['setting']['rt_text2'] : '操作成功，等待管理员审核'), $data);
                }
                return dr_return_data($data[1]['id'], dr_lang($this->form['setting']['rt_text'] ? $this->form['setting']['rt_text'] : '操作成功'), $data);
            }
        );
    }

}
