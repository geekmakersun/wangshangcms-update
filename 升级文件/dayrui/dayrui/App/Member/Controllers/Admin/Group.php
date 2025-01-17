<?php namespace Phpcmf\Controllers\Admin;


// 用户组
class Group extends \Phpcmf\Table
{

    private $type;

    public function __construct()
    {
        parent::__construct();
        // 支持附表存储
        $this->is_data = 0;
        // 表单验证配置
        $this->form_rule = [
            'name' => [
                'name' => '名称',
                'rule' => [
                    'empty' => dr_lang('名称不能为空')
                ],
                'filter' => [],
                'length' => '200'
            ],
        ];
        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '用户组管理' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-users'],
                    '添加组' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/add', 'fa fa-plus'],
                    '修改' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                    '等级制度' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/level_index', 'fa fa-list-ol'],
                    '添加等级' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/level_add', 'fa fa-plus'],
                    '修改等级' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/level_edit', 'fa fa-edit'],
                    'help' => [515]
                ]
            ),
        ]);
    }

    private function _init_group() {
        $this->type = 1;
        // 表单显示名称
        $this->name = dr_lang('用户组');
        $this->tpl_prefix = 'member_group_';
        // 初始化数据表
        $this->_init([
            'table' => 'member_group',
            'sys_field' => [],
            'order_by' => 'displayorder asc,id asc',
            'list_field' => [],
        ]);
    }

    private function _init_level($gid) {

        if (!$gid) {
            $this->_admin_msg(0, dr_lang('用户组id参数不能为空'));
        }

        $this->type = 0;
        // 表单显示名称
        $this->name = dr_lang('用户组等级');
        $this->tpl_prefix = 'member_level_';

        $this->my_field = [
            'stars' => [
                'ismain' => 1,
                'name' => '图标',
                'fieldtype' => 'File',
                'fieldname' => 'stars',
                'setting' => ['option' => ['ext' => 'jpg,gif,png,jpeg', 'size' => 10, 'input' => 1]]
            ]
        ];
        // 初始化数据表
        $this->_init([
            'table' => 'member_level',
            'field' => $this->my_field,
            'sys_field' => [],
            'order_by' => 'displayorder asc,id asc',
            'list_field' => [],
            'where_list' => 'gid='.$gid,
        ]);
        $group = \Phpcmf\Service::M()->table('member_group')->get($gid);
        $group['setting'] = dr_string2array($group['setting']);

        if ($group['setting']['level']['auto']) {
            // 自动模式，只有消费额和经验值
            if ($group['setting']['level']['unit']) {
               $dwz = dr_lang('消费额');
            } else {
                $dwz = SITE_EXPERIENCE;
            }
        } else {
            // 手动模式，只有人民币和积分
            if ($group['unit']) {
                $dwz = SITE_SCORE;
            } else {
                $dwz = dr_lang('元');
            }
        }

        \Phpcmf\Service::V()->assign([
            'dwz' => $dwz,
            'group' => $group
        ]);
    }

    // 管理
    public function index() {
        $this->_init_group();
        list($tpl, $data) = $this->_List(null, -1);
        if ($data['list']) {
            foreach ($data['list'] as $i => $t) {
                $data['list'][$i]['level'] = \Phpcmf\Service::M()->table('member_level')->where('gid', $t['id'])->order_by('`value` asc')->getAll();
            }
            \Phpcmf\Service::V()->assign('list', $data['list']);
        }
        \Phpcmf\Service::V()->display($tpl);
    }

    // 添加
    public function add() {
        $this->_init_group();
        list($tpl) = $this->_Post(0);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 修改
    public function edit() {
        $this->_init_group();
        $gid = intval(\Phpcmf\Service::L('input')->get('id'));
        list($tpl) = $this->_Post($gid);
        $page = intval(\Phpcmf\Service::L('input')->get('page'));
        $is_level = \Phpcmf\Service::M()->table('member_level')->where('gid', $gid)->getRow();
        \Phpcmf\Service::V()->assign([
            'page' => $page,
            'form' => dr_form_hidden(['page' => $page]),
            'is_level' => $is_level,
        ]);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 排序
    public function order_edit() {
        $this->_init_group();
        $this->_Display_Order(
            intval(\Phpcmf\Service::L('input')->get('id')),
            intval(\Phpcmf\Service::L('input')->get('value')),
            function ($r) {
                \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
            }
        );
    }

    // 允许注册
    public function register_edit() {
        $this->_init_group();
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $data = $this->_Data($id);
        if (!$data) {
            $this->_json(0, dr_lang('数据#%s不存在', $id));
        }
        if (!$data['register']) {
            $level = \Phpcmf\Service::M()->table('member_level')->where('gid', $id)->order_by('displayorder ASC,id ASC')->getRow();
            if ($level && !$data['setting']['level']['auto'] && $level['value'] > 0) {
                $this->_json(0, dr_lang('收费的用户组，不能开启允许注册开关'), $data);
            } elseif ($data['price'] > 0) {
                $this->_json(0, dr_lang('收费的用户组，不能开启允许注册开关'), $data);
            }
        }

        $value = $data['register'] ? 0 : 1;
        \Phpcmf\Service::M()->table('member_group')->save($id, 'register', $value);
        \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
        $this->_json(1, dr_lang('操作成功'), ['value' => $value]);
    }

    // 允许申请
    public function apply_edit() {
        $this->_init_group();
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $data = $this->_Data($id);
        if (!$data) {
            $this->_json(0, dr_lang('数据#%s不存在', $id));
        }
        $value = $data['apply'] ? 0 : 1;
        \Phpcmf\Service::M()->table('member_group')->save($id, 'apply', $value);
        \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
        exit($this->_json(1, dr_lang('操作成功'), ['value' => $value]));
    }

    // 删除
    public function del() {
        $this->_init_group();
        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        $this->_Del(
            $ids,
            null,
            function ($rows) {
                \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
            },
            \Phpcmf\Service::M()->dbprefix($this->init['table'])
        );
    }

    // 等级管理
    public function level_index() {
        $gid = intval($_GET['gid']);
        $this->_init_level($gid);;
        list($tpl) = $this->_List([], -1);
        \Phpcmf\Service::V()->assign([
            'gid' => $gid,
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '用户组管理' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-users'],
                    '修改' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                    '等级制度' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/level_index', 'fa fa-list-ol'],
                    '添加等级' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/level_add{gid='.intval($_GET['gid']).'}', 'fa fa-plus'],
                ]
            ),
        ]);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 添加等级
    public function level_add() {
        $gid = intval($_GET['gid']);
        $this->_init_level($gid);
        list($tpl) = $this->_Post(0);
        \Phpcmf\Service::V()->assign([
            'gid' => $gid,
            'form' => dr_form_hidden(['gid' => $gid]),
            'apply' => 1,
        ]);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 修改等级
    public function level_edit() {
        $gid = intval($_GET['gid']);
        $this->_init_level($gid);
        list($tpl, $data) = $this->_Post(intval(\Phpcmf\Service::L('input')->get('id')));
        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(['gid' => intval($data['gid'])]),
        ]);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 等级排序
    public function level_order_edit() {
        $this->_init_level(intval($_GET['gid']));
        $this->_Display_Order(
            intval(\Phpcmf\Service::L('input')->get('id')),
            intval(\Phpcmf\Service::L('input')->get('value')),
            function ($r) {
                \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
            }
        );
    }

    // 允许申请等级
    public function apply_level_edit() {
        $gid = intval($_GET['gid']);
        $this->_init_level($gid);
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $data = $this->_Data($id);
        if (!$data) {
            $this->_json(0, dr_lang('数据#%s不存在', $id));
        }
        $value = $data['apply'] ? 0 : 1;
        \Phpcmf\Service::M()->table('member_level')->save($id, 'apply', $value);
        \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
        exit($this->_json(1, dr_lang('操作成功'), ['value' => $value]));
    }

    // 删除等级
    public function level_del() {
        $gid = intval($_GET['gid']);
        $this->_init_level($gid);
        $this->_Del(
            \Phpcmf\Service::L('input')->get_post_ids(),
            null,
            function ($r) {
                \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
            },
            \Phpcmf\Service::M()->dbprefix($this->init['table'])
        );
    }

    // 保存
    protected function _Save($id = 0, $data = [], $old = [],  $func = null, $after = null) {
        return parent::_Save($id, $data, $old, function($id, $post, $old){
            $data = \Phpcmf\Service::L('input')->post('data');
            $setting = \Phpcmf\Service::L('input')->post('setting');
            $data['setting'] = dr_array2string($setting);
            if ($this->type) {
                // 用户组
                $level = $id ? \Phpcmf\Service::M()->table('member_level')->where('gid', $id)->order_by('displayorder ASC,id ASC')->getRow() : [];
                if ($level && !$setting['level']['auto'] && $level['value'] > 0 && $data['register']) {
                    return dr_return_data(0, dr_lang('收费的用户组，不能开启允许注册开关'), $data);
                } elseif ($data['price'] > 0 && $data['register']) {
                    return dr_return_data(0, dr_lang('收费的用户组，不能开启允许注册开关'), $data);
                }
                $data['price'] = floatval($data['price']);
                $data['days'] = intval($data['days']);
            } else {
                // 等级
                $data['gid'] = (int)\Phpcmf\Service::L('input')->post('gid');
                if (!$data['gid']) {
                    return dr_return_data(0, dr_lang('所属用户组id不存在'), $data);
                }
                $data['stars'] = $post[1]['stars'];
                $data['value'] = intval($data['value']);
            }
            !$id && $data['displayorder'] = 0;
            return dr_return_data(1, null, $data);
        }, function ($id, $data, $old) {
            \Phpcmf\Service::M('cache')->sync_cache('member'); // 自动更新缓存
        });
    }

    /**
     * 获取内容
     * $id      内容id,新增为0
     * */
    protected function _Data($id = 0) {
        $data = parent::_Data($id);
        $data['setting'] = dr_string2array($data['setting']);
        return $data;
    }


}
