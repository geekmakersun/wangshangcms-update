<?php namespace Phpcmf\Field;

/**
 * https://www.wsw88.cn
 * 网商CMS
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

class Ueditor extends \Phpcmf\Library\A_Field {

    protected $rid; // 附件入库标记字符

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        //$this->use_xss = 1; // 强制xss验证
        $this->fieldtype = ['MEDIUMTEXT' => ''];
        $this->defaulttype = 'MEDIUMTEXT';
        $this->rid = md5(FC_NOW_URL.\Phpcmf\Service::L('input')->get_user_agent().\Phpcmf\Service::L('input')->ip_address().\Phpcmf\Service::C()->uid);
    }

    /**
     * 字段相关属性参数
     *
     * @param   array   $value  值
     * @return  string
     */
    public function option($option) {

        $option['ym'] = isset($option['ym']) ? $option['ym'] : 1;
        $option['mode'] = isset($option['mode']) ? $option['mode'] : 1;
        $option['page'] = isset($option['page']) ? $option['page'] : 0;
        $option['tool'] = isset($option['tool']) ? $option['tool'] : '\'bold\', \'italic\', \'underline\'';
        $option['mode2'] = isset($option['mode2']) ? $option['mode2'] : $option['mode'];
        $option['tool2'] = isset($option['tool2']) ? $option['tool2'] : $option['tool'];
        $option['mode3'] = isset($option['mode3']) ? $option['mode3'] : $option['mode'];
        $option['tool3'] = isset($option['tool3']) ? $option['tool3'] : $option['tool'];
        $option['duiqi'] = isset($option['duiqi']) ? $option['duiqi'] : 0;
        $option['value'] = isset($option['value']) ? $option['value'] : '';
        $option['width'] = isset($option['width']) ? $option['width'] : '100%';
        $option['height'] = isset($option['height']) ? $option['height'] : 300;
        $option['fieldtype'] = isset($option['fieldtype']) ? $option['fieldtype'] : '';
        $option['autofloat'] = isset($option['autofloat']) ? $option['autofloat'] : 0;
        $option['autoheight'] = isset($option['autoheight']) ? $option['autoheight'] : 0;
        $option['fieldlength'] = isset($option['fieldlength']) ? $option['fieldlength'] : '';
        $option['simpleupload'] = isset($option['simpleupload']) ? $option['simpleupload'] : 0;
        $option['watermark'] = isset($option['watermark']) ? $option['watermark'] : '';
        $option['enter'] = isset($option['enter']) ? $option['enter'] : '';
        $option['show_bottom_boot'] = isset($option['show_bottom_boot']) ? $option['show_bottom_boot'] : '';

        $wm = \Phpcmf\Service::C()->get_cache('site', SITE_ID, 'watermark', 'ueditor') ? '<div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('图片水印').'</label>
                    <div class="col-md-9">
                        <div class="form-control-static">
                            '.dr_lang('系统强制开启水印').'
                        </div>
                    </div>
                </div>' : '<div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('图片水印').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][watermark]" '.($option['watermark'] == 1 ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                             &nbsp; &nbsp;
                             <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][watermark]" '.($option['watermark'] == 0 ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('上传的图片会加上水印图').'</span>
                    </div>
                </div>';

        return [$this->_search_field().'
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('回车换行符号').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][enter]" '.($option['enter'] ? 'checked' : '').' > '.dr_lang('br标签').' <span></span></label>
                            &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input  type="radio" value="0" name="data[setting][option][enter]" '.(!$option['enter'] ? 'checked' : '').' > '.dr_lang('p标签').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('选择回车换行的符号，默认是p标签换行').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('下载远程图片').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][down_img]" '.($option['down_img'] == 1 ? 'checked' : '').' > '.dr_lang('自动').' <span></span></label>
                            &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input  type="radio" value="0" name="data[setting][option][down_img]" '.($option['down_img'] == 0 ? 'checked' : '').' > '.dr_lang('手动').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('自动模式下每一次编辑内容时都会下载图片；手动模式可以在编辑器下放工具栏中控制“是否下载”').'</span>
                    </div>
                </div>
				'.$wm.
            '
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('底部工具栏').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" onclick="$(\'#sdmrx\').show()" value="1" name="data[setting][option][show_bottom_boot]" '.($option['show_bottom_boot'] == 1 ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                             &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input type="radio" onclick="$(\'#sdmrx\').hide()" value="0" name="data[setting][option][show_bottom_boot]" '.($option['show_bottom_boot'] == 0 ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('编辑器底部工具栏，有截取字符选择、提取缩略图、下载远程图等控制按钮').'</span>
                    </div>
                </div>
                <div class="form-group" id="sdmrx" '.(!$option['show_bottom_boot'] ? 'style="display:none"' : '').'>
                    <label class="col-md-1 control-label"> &nbsp; &nbsp;</label>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label class="col-md-2 control-label">'.dr_lang("提取描述").'</label>
                            <div class="col-md-9">
                                <input type="checkbox" name="data[setting][option][tool_select_2]" value="1" '.($option['tool_select_2'] ? 'checked' : '').' data-on-text="'.dr_lang("默认选中").'" data-off-text="'.dr_lang("默认不选").'" data-on-color="success" data-off-color="danger" class="make-switch" data-size="small">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">'.dr_lang("提取缩略图").'</label>
                            <div class="col-md-9">
                                <input type="checkbox" name="data[setting][option][tool_select_1]" value="1" '.($option['tool_select_1'] ? 'checked' : '').' data-on-text="'.dr_lang("默认选中").'" data-off-text="'.dr_lang("默认不选").'" data-on-color="success" data-off-color="danger" class="make-switch" data-size="small">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">'.dr_lang("下载远程图").'</label>
                            <div class="col-md-9">
                                <input type="checkbox" name="data[setting][option][tool_select_3]" value="1" '.($option['tool_select_3'] ? 'checked' : '').' data-on-text="'.dr_lang("默认选中").'" data-off-text="'.dr_lang("默认不选").'" data-on-color="success" data-off-color="danger" class="make-switch" data-size="small">                             
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">'.dr_lang("去除站外链接").'</label>
                            <div class="col-md-9">
                                <input type="checkbox" name="data[setting][option][tool_select_4]" value="1" '.($option['tool_select_4'] ? 'checked' : '').' data-on-text="'.dr_lang("默认选中").'" data-off-text="'.dr_lang("默认不选").'" data-on-color="success" data-off-color="danger" class="make-switch" data-size="small">                             
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('固定编辑器图标栏').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][autofloat]" '.($option['autofloat'] == 1 ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio"  value="0" name="data[setting][option][autofloat]" '.($option['autofloat'] == 0 ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('当开启时，在完整模式下，编辑器图标栏会固定在页面，不会随浏览器滚动').'</span>
                    </div>
                </div>
                <div class="form-group hide">
                    <label class="col-md-2 control-label">'.dr_lang('过滤style属性').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][remove_style]" '.(!$option['remove_style'] ? 'checked' : '').' > '.dr_lang('全部过滤').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio"  value="1" name="data[setting][option][remove_style]" '.($option['remove_style'] ==1 ? 'checked' : '').' > '.dr_lang('后台过滤').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio"  value="2" name="data[setting][option][remove_style]" '.($option['remove_style'] ==2 ? 'checked' : '').' > '.dr_lang('前端过滤').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('过滤编辑器里面的多余的style属性参数').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('将div标签转换为p标签').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][div2p]" '.(!$option['div2p'] ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][div2p]" '.($option['div2p'] ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('将编辑器的div标签强制转换为p标签').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('图片控件显示对齐快捷操作').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][duiqi]" '.($option['duiqi'] ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][duiqi]" '.(!$option['duiqi'] ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('鼠标移动到图片控件时，开启状态将显示浮动快捷框，关闭状态就直接弹出编辑图片窗口').'</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('自动伸长高度').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][autoheight]" '.($option['autoheight'] == 1 ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][autoheight]" '.($option['autoheight'] == 0 ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						
						<span class="help-block">'.dr_lang('编辑器会自动增加高度').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('分页标签').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][page]" '.($option['page'] ? 'checked' : '').' > '.dr_lang('开启').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="0" name="data[setting][option][page]" '.(!$option['page'] ? 'checked' : '').' > '.dr_lang('关闭').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('文章内容的分页功能').'</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('后台编辑器模式').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][mode]" '.($option['mode'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms1\').hide()"> '.dr_lang('完整').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="2" name="data[setting][option][mode]" '.($option['mode'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms1\').hide()"> '.dr_lang('精简').' <span></span></label>
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="3" name="data[setting][option][mode]" '.($option['mode'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms1\').show()"> '.dr_lang('自定义').' <span></span></label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="bjqms1" '.($option['mode'] < 3 ? 'style="display:none"' : '').'>
                    <label class="col-md-2 control-label">'.dr_lang('工具栏').'</label>
                    <div class="col-md-9">
                    <textarea name="data[setting][option][tool]" style="height:90px;" class="form-control">'.$option['tool'].'</textarea>
                    <span class="help-block">'.dr_lang('必须严格按照Ueditor工具栏格式\'source\', \'|\', \'undo\', \'redo\'').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('源码工具栏').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][ym]" '.($option['ym'] ? 'checked' : '').' > '.dr_lang('前端显示').' <span></span></label>
                            &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input  type="radio" value="0" name="data[setting][option][ym]" '.(!$option['ym'] ? 'checked' : '').' > '.dr_lang('前端不显示').' <span></span></label>
                        </div>
						<span class="help-block">'.dr_lang('源码HTML工具栏是否在前端可显示').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('前台编辑器模式').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][mode2]" '.($option['mode2'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms2\').hide()"> '.dr_lang('完整').' <span></span></label>
                             &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="2" name="data[setting][option][mode2]" '.($option['mode2'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms2\').hide()"> '.dr_lang('精简').' <span></span></label>
                             &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="3" name="data[setting][option][mode2]" '.($option['mode2'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms2\').show()"> '.dr_lang('自定义').' <span></span></label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="bjqms2" '.($option['mode2'] < 3 ? 'style="display:none"' : '').'>
                    <label class="col-md-2 control-label">'.dr_lang('工具栏').'</label>
                    <div class="col-md-9">
                    <textarea name="data[setting][option][tool2]" style="height:90px;" class="form-control">'.$option['tool2'].'</textarea>
                    <span class="help-block">'.dr_lang('必须严格按照Ueditor工具栏格式\'source\', \'|\', \'undo\', \'redo\'').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('移动端编辑器模式').'</label>
                    <div class="col-md-9">
                        <div class="mt-radio-inline">
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="1" name="data[setting][option][mode3]" '.($option['mode3'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms3\').hide()"> '.dr_lang('完整').' <span></span></label>
                             &nbsp; &nbsp;
                             <label class="mt-radio mt-radio-outline"><input type="radio" value="2" name="data[setting][option][mode3]" '.($option['mode3'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms3\').hide()"> '.dr_lang('精简').' <span></span></label>
                             &nbsp; &nbsp;
                            <label class="mt-radio mt-radio-outline"><input type="radio" value="3" name="data[setting][option][mode3]" '.($option['mode3'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms3\').show()"> '.dr_lang('自定义').' <span></span></label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="bjqms3" '.($option['mode3'] < 3 ? 'style="display:none"' : '').'>
                    <label class="col-md-2 control-label">'.dr_lang('工具栏').'</label>
                    <div class="col-md-9">
                    <textarea name="data[setting][option][tool3]" style="height:90px;" class="form-control">'.$option['tool3'].'</textarea>
                    <span class="help-block">'.dr_lang('必须严格按照Ueditor工具栏格式\'source\', \'|\', \'undo\', \'redo\'').'</span>
                    </div>
                </div>
                '.$this->attachment($option, 0).'
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('图片补加后缀字符').' </label>
                    <div class="col-md-9">
                        <label><input type="text" class="form-control" value="'.$option['image_endstr'].'" name="data[setting][option][image_endstr]"></label>
                        <span class="help-block">'.dr_lang('上传图片后自动为图片补加指定的后缀字符串').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('默认存储值').'</label>
                    <div class="col-md-9">
					<textarea id="field_default_value" style="width: 90%;height: 100px;" class="form-control" name="data[setting][option][value]">'.$option['value'].'</textarea>
					<p><label>'.$this->member_field_select().'</label>
					<span class="help-block">'.dr_lang('用于字段为空时显示该填充值，并不会去主动变更数据库中的实际值；可以设置会员表字段，表示用当前登录会员信息来填充这个值').'</span></p>
                    </div>
                </div>
                '.$this->field_type($option['fieldtype'], $option['fieldlength']),

            '<div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('控件宽度').'</label>
                    <div class="col-md-9">
                        <label><input type="text" class="form-control" name="data[setting][option][width]" value="'.$option['width'].'"></label>
                        <span class="help-block">'.dr_lang('[整数]表示固定宽度；[整数%]表示百分比').'</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">'.dr_lang('控件高度').'</label>
                    <div class="col-md-9">
                        <label><input type="text" class="form-control" name="data[setting][option][height]" value="'.$option['height'].'"></label>
                        <label>px</label>
                    </div>
                </div>'
        ];
    }

    /**
     * 字段入库值
     */
    public function insert_value($field) {

        //$table = [];
        $value = (string)\Phpcmf\Service::L('Field')->post[$field['fieldname']];

        // 第一张作为缩略图
        $slt = isset($_POST['data']['thumb']) && isset($_POST['is_auto_thumb_'.$field['fieldname']]) && !$_POST['data']['thumb'] && $_POST['is_auto_thumb_'.$field['fieldname']];

        // 是否下载图片
        $yct = $field['setting']['option']['down_img'] || (isset($_POST['is_auto_down_img_'.$field['fieldname']]) && $_POST['is_auto_down_img_'.$field['fieldname']]);

        // 下载远程图片
        if ($yct || $slt) {
            $temp = preg_replace('/<pre(.*)<\/pre>/siU', '', $value);
            $temp = preg_replace('/<code(.*)<\/code>/siU', '', $temp);
            if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+)\\2/i", $temp, $imgs)) {
                $reps = array_unique($imgs[3]);
                usort($reps, function ($a, $b) {
                    return dr_strlen($b) - dr_strlen($a);
                });
                foreach ($reps as $img) {
                    if (strpos($img, '/api/ueditor/') !== false) {
                        continue;
                    }
                    $ext = $this->_get_image_ext($img);
                    if (!$ext) {
                        continue;
                    }
                    // 下载图片
                    if ($yct && strpos($img, 'http') === 0) {
                        if (dr_is_app('mfile') && \Phpcmf\Service::M('mfile', 'mfile')->check_upload(\Phpcmf\Service::C()->uid)) {
                            //用户存储空间已满
                        } else {
                            // 正常下载
                            // 判断域名白名单
                            $arr = parse_url($img);
                            $domain = $arr['host'];
                            if ($domain) {
                                $sites = \Phpcmf\Service::R(WRITEPATH.'config/domain_site.php');
                                if (isset($sites[$domain])) {
                                    // 过滤站点域名
                                } elseif (strpos(SYS_UPLOAD_URL, $domain) !== false) {
                                    // 过滤附件白名单
                                } else {
                                    $zj = 0;
                                    $remote = \Phpcmf\Service::C()->get_cache('attachment');
                                    if ($remote) {
                                        foreach ($remote as $t) {
                                            if (strpos($t['url'], $domain) !== false) {
                                                $zj = 1;
                                                break;
                                            }
                                        }
                                    }
                                    if ($zj == 0) {
                                        // 可以下载文件
                                        // 下载远程文件
                                        $rt = \Phpcmf\Service::L('upload')->down_file([
                                            'url' => html_entity_decode((string)$img),
                                            'timeout' => 5,
                                            'watermark' => \Phpcmf\Service::C()->get_cache('site', SITE_ID, 'watermark', 'ueditor') || $field['setting']['option']['watermark'] ? 1 : 0,
                                            'attachment' => \Phpcmf\Service::M('Attachment')->get_attach_info(intval($field['setting']['option']['attachment']), $field['setting']['option']['image_reduce']),
                                            'file_ext' => $ext,
                                        ]);
                                        if ($rt['code']) {
                                            $att = \Phpcmf\Service::M('Attachment')->save_data($rt['data'], 'ueditor:'.$this->rid);
                                            if ($att['code']) {
                                                // 归档成功
                                                $value = str_replace($img, $rt['data']['url'], $value);
                                                $img = $att['code'];
                                                // 标记附件
                                                \Phpcmf\Service::M('Attachment')->save_ueditor_aid($this->rid, $att['code']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // 缩略图
                    if ($img && $slt) {
                        $_field = \Phpcmf\Service::L('form')->fields;
                        if (isset($_field['thumb']) && $_field['thumb']['fieldtype'] == 'File' && !\Phpcmf\Service::L('Field')->data[$_field['thumb']['ismain']]['thumb']) {
                            if (!is_numeric($img)) {
                                // 下载缩略图
                                // 判断域名白名单
                                $arr = parse_url($img);
                                $domain = $arr['host'];
                                if ($domain) {
                                    $file = dr_catcher_data($img, 8);
                                    if (!$file) {
                                        CI_DEBUG && log_message('debug', '服务器无法下载图片：'.$img);
                                    } else {
                                        // 尝试找一找附件库
                                        $att = \Phpcmf\Service::M()->table('attachment')->like('related', 'ueditor')->where('filemd5', md5($file))->getRow();
                                        if ($att) {
                                            $img = $att['id'];
                                        } else {
                                            // 下载归档
                                            $rt = \Phpcmf\Service::L('upload')->down_file([
                                                'url' => html_entity_decode((string)$img),
                                                'timeout' => 5,
                                                'watermark' => \Phpcmf\Service::C()->get_cache('site', SITE_ID, 'watermark', 'ueditor') || $field['setting']['option']['watermark'] ? 1 : 0,
                                                'attachment' => \Phpcmf\Service::M('Attachment')->get_attach_info(intval($field['setting']['option']['attachment']), $field['setting']['option']['image_reduce']),
                                                'file_ext' => $ext,
                                                'file_content' => $file,
                                            ]);
                                            if ($rt['code']) {
                                                $att = \Phpcmf\Service::M('Attachment')->save_data($rt['data'], 'ueditor:'.$this->rid);
                                                if ($att['code']) {
                                                    // 归档成功
                                                    $value = str_replace($img, $rt['data']['url'], $value);
                                                    $img = $att['code'];
                                                    // 标记附件
                                                    \Phpcmf\Service::M('Attachment')->save_ueditor_aid($this->rid, $att['code']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            \Phpcmf\Service::L('Field')->data[$_field['thumb']['ismain']]['thumb'] = $_POST['data']['thumb'] = $img;
                        }
                    }
                }
            }

        }

        // 去除站外链接
        if (isset($_POST['is_remove_a_'.$field['fieldname']]) && $_POST['is_remove_a_'.$field['fieldname']]
            && preg_match_all("/<a (.*)href=(.+)>(.*)<\/a>/Ui", $value, $arrs)) {
            $sites = \Phpcmf\Service::R(WRITEPATH.'config/domain_site.php');
            foreach ($arrs[2] as $i => $a) {
                if (strpos($a, ' ') !== false) {
                    list($a) = explode(' ', $a);
                }
                $a = trim($a, '"');
                $a = trim($a, '\'');
                $arr = parse_url($a);
                if ($arr && $arr['host'] && !isset($sites[$arr['host']])) {
                    // 去除a标签
                    $value = str_replace($arrs[0][$i], $arrs[3][$i], $value);
                }
            }
        }

        /*
        // 默认过滤style标签
        if (!isset($field['setting']['option']['remove_style']) || !$field['setting']['option']['remove_style']
            || (IS_ADMIN && $field['setting']['option']['remove_style'] == 1)
            || (IS_MEMBER && $field['setting']['option']['remove_style'] == 2)
        ) {
            $value = preg_replace('/<div style=".*?"/iU', '', $value);
        }dr_rp($value, '<p><br></p>', '')
        */

        // 提取描述信息
        if (isset($_POST['data']['description']) && isset($_POST['is_auto_description_'.$field['fieldname']])
            && $_POST['is_auto_description_'.$field['fieldname']]) {
            \Phpcmf\Service::L('Field')->data[1]['description'] = $_POST['data']['description'] = dr_get_description($value);
        }

        // 替换分页
        $value = str_replace('_ueditor_page_break_tag_', '<hr class="pagebreak">', $value);
        $value = str_replace(' style=""', '', $value);

        // 入库操作
        if (isset($_GET['is_verify_iframe']) && $_GET['is_verify_iframe']) {
            // 来自批量审核内容
            \Phpcmf\Service::L('Field')->data[$field['ismain']][$field['fieldname']] = ($value);
        } else {
            \Phpcmf\Service::L('Field')->data[$field['ismain']][$field['fieldname']] = htmlspecialchars($value);
        }
    }

    // 获取远程附件扩展名
    protected function _get_image_ext($url) {

        if (strlen($url) > 300) {
            return '';
        }

        $arr = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
        $ext = str_replace('.', '', trim(strtolower(strrchr($url, '.')), '.'));
        if ($ext && in_array($ext, $arr)) {
            return $ext; // 满足扩展名
        } elseif ($ext && strlen($ext) < 4) {
            //CI_DEBUG && log_message('error', '此路径不是远程图片：'.$url);
            return ''; // 表示不是图片扩展名了
        }

        foreach ($arr as $t) {
            if (stripos($url, $t) !== false) {
                return $t;
            }
        }

        $rt = getimagesize($url);
        if ($rt && $rt['mime']) {
            foreach ($arr as $t) {
                if (stripos($rt['mime'], $t) !== false) {
                    return $t;
                }
            }
        }

        CI_DEBUG && log_message('debug', '服务器无法获取远程图片的扩展名：'.dr_safe_replace($url));

        return '';
    }

    /**
     * 获取附件id
     */
    public function get_attach_id($value) {
        return \Phpcmf\Service::M('Attachment')->get_ueditor_aid($this->rid);
    }

    /**
     * 附件处理
     */
    public function attach($data, $_data) {
        return [\Phpcmf\Service::M('Attachment')->get_ueditor_aid($this->rid, true), NULL];
    }

    /**
     * 字段输出
     *
     * @param   array   $value  数据库值
     * @return  string
     */
    public function output($value) {
        return dr_ueditor_html($value, isset(\Phpcmf\Service::L('Field')->data['title']) ? \Phpcmf\Service::L('Field')->data['title'] : '');
    }

    /**
     * 字段显示
     *
     * @return  string
     */
    public function show($field, $value = null) {
        $html = '
        <div class="portlet  bordered light">
        <div class="portlet-body">
        <div class="scroller" style="width:'.(\Phpcmf\Service::IS_MOBILE_USER() ? '100%' : ($field['setting']['option']['width'] ? $field['setting']['option']['width'].(is_numeric($field['setting']['option']['width']) ? 'px' : '') : '100%')).';height:'.($field['setting']['option']['height'] ? $field['setting']['option']['height'] : '300').'px" data-always-visible="1" data-rail-visible="1">
        '.htmlspecialchars_decode((string)$value).'                
        </div>
        </div>
        </div>';
        return $this->input_format($field['fieldname'], $field['name'], $html);
    }

    /**
     * 字段表单输入
     *
     */
    public function input($field, $value = '') {

        // 字段禁止修改时就返回显示字符串
        if ($this->_not_edit($field, $value)) {
            return $this->show($field, $value);
        }

        // 字段存储名称
        $name = $field['fieldname'];

        // 字段显示名称
        $text = ($field['setting']['validate']['required'] ? '<span class="required" aria-required="true"> * </span>' : '').dr_lang($field['name']);

        if (isset($_GET['is_verify_iframe']) && $_GET['is_verify_iframe']) {
            // 来自批量审核内容
            $str = '<textarea class="form-control"  name="data['.$name.']" id="dr_'.$name.'">'.htmlspecialchars($value).'</textarea>';
            return $this->input_format($field['fieldname'], $text, $str);
        }

        // 表单宽度设置
        $width = \Phpcmf\Service::IS_MOBILE_USER() ? '100%' : ($field['setting']['option']['width'] ? $field['setting']['option']['width'] : '100%');

        // 表单高度设置
        $height = $field['setting']['option']['height'] ? $field['setting']['option']['height'] : '300';

        // 字段提示信息
        $tips = $field['setting']['validate']['tips'] ? '<span class="help-block" id="dr_'.$name.'_tips">'.$field['setting']['validate']['tips'].'</span>' : '';

        // 字段默认值
        $value = htmlspecialchars_decode((string)(dr_strlen($value) ? $value : $this->get_default_value($field['setting']['option']['value'])));

        // 输出
        $str = '';
        // 防止重复加载JS
        if (!$this->is_load_js($field['fieldtype'])) {
            $cf = \Phpcmf\Service::M('app')->get_config('Ueditor');
            $ak = (isset($cf['ak']) ? $cf['ak'] : '');
            $str.= '
            <script type="text/javascript"> var uedtiro_baidumap_ak = "'.$ak.'";</script>
            <script type="text/javascript" src="'.ROOT_URL.'api/ueditor/ueditor.config.js?v='.CMF_UPDATE_TIME.'"></script>
            <script type="text/javascript" src="'.ROOT_URL.'api/ueditor/ueditor.'.(IS_XRDEV ? 'all' : 'all.min').'.js?v='.CMF_UPDATE_TIME.'"></script>
            ';
            $this->set_load_js($field['fieldtype'], 1);
        }

        if (IS_DEV || IS_ADMIN) {
            $tool = "'fullscreen', 'source', '|', ";
        } elseif (isset($field['setting']['option']['ym']) && $field['setting']['option']['ym']) {
            $tool = "'source', '|', ";
        } else {
            $tool = '';
        }

        // 编辑器模式
        if (\Phpcmf\Service::IS_MOBILE_USER()) {
            $mode = $field['setting']['option']['mode3'] ? $field['setting']['option']['mode3'] : $field['setting']['option']['mode'];
            $field['setting']['option']['tool'] = $field['setting']['option']['tool3'] ? $field['setting']['option']['tool3'] : $field['setting']['option']['tool'];
        } elseif (IS_ADMIN) {
            $mode = $field['setting']['option']['mode'];
        } else {
            $mode = $field['setting']['option']['mode2'] ? $field['setting']['option']['mode2'] : $field['setting']['option']['mode'];
            $field['setting']['option']['tool'] = $field['setting']['option']['tool2'] ? $field['setting']['option']['tool2'] : $field['setting']['option']['tool'];
        }

        // 编辑器工具
        $pagebreak = (int)$field['setting']['option']['page'] ? ', \'pagebreak\'' : '';
        switch ($mode) {
            case 3: // 自定义
                $tool.= trim($field['setting']['option']['tool'], ',').$pagebreak;
                break;
            case 2: // 精简
                $tool.= "'undo', 'redo', '|',
                        'bold', 'italic', 'underline', 'strikethrough','|', 'pasteplain', 'forecolor', 'fontfamily', 'fontsize','|', 'link', 'insertimage'$pagebreak";
                break;
            default: // 完整模式

                $tool_code = \Phpcmf\Service::R(ROOTPATH.'api/ueditor/php/tool.php');
                if (!$tool_code) {
                    $tool.= "'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'insertimage', 'emotion', 'scrawl', 'insertvideo', 'attachment', 'map', 'insertframe', 'insertcode', 'template', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'print', 'preview', 'searchreplace', 'drafts'";
                } else {
                    $tool.= str_replace([PHP_EOL, chr(13), chr(10)], ' ', $tool_code);
                }
                $tool.= "$pagebreak";
                break;
        }
        $tool = str_replace(['"simpleupload",', ',"simpleupload"',"'simpleupload',", ",'simpleupload'"], '', $tool);
        if (!$field['setting']['option']['image_endstr']) {
            $field['setting']['option']['image_endstr'] = '';
        }
        $p2 = dr_authcode([
            'attachment' => $field['setting']['option']['attachment'],
            'image_reduce' => $field['setting']['option']['image_reduce'],
        ], 'ENCODE');
        $rp = IS_ADMIN ? WEB_DIR.SELF."?c=api&m=ueditor" : WEB_DIR."index.php?s=api&c=file&m=ueditor";
        $str.= "<script class=\"dr_ueditor\" name=\"data[$name]\" type=\"text/plain\" id=\"dr_$name\">$value</script>";
        $js = \Phpcmf\Service::L('js_packer');
        $str.= $js->pack("
        <script type=\"text/javascript\">
         function dr_is_auto_description_".$field['fieldname']."() {
            var v = $(\"#is_auto_description_".$field['fieldname']."\").is(\":checked\");
            if (v == true) {
                $(\"#dr_description\").prop(\"readonly\", true);
            } else {
                $(\"#dr_description\").prop(\"readonly\", false);
            }
        }
            $(function(){
                dr_is_auto_description_".$field['fieldname']."();
                var editor_{$name} = new baidu.editor.ui.Editor({
                    ismobile: ".(dr_is_mobile() ? 1 : 0).", 
                    UEDITOR_HOME_URL: \"/api/ueditor/\",
                    UEDITOR_ROOT_URL: \"".ROOT_URL."api/ueditor/\",
                    serverUrl:\"".$rp."&token=".dr_get_csrf_token()."&image_endstr=".trim((string)$field['setting']['option']['image_endstr'])."&image_reduce=".intval($field['setting']['option']['image_reduce'])."&attachment=".intval($field['setting']['option']['attachment'])."&is_wm=".$field['setting']['option']['watermark']."&rid=".$this->rid."&\",
                    lang: \"lang\",
                    langPath: \"".ROOT_URL."api/ueditor/\",
                    toolbars: [
                        [ $tool ]
                    ],
                    topOffset: '".(IS_ADMIN ? 40 : 0)."',
                    initialContent:\"\",
                    pageBreakTag:\"_ueditor_page_break_tag_\",
                    initialFrameWidth: \"".$width."\",
                    initialFrameHeight: \"{$height}\",
                    initialStyle:\"body{font-size:14px}\",
                    autoFloatEnabled:".($field['setting']['option']['autofloat'] ? 'true' : 'false').",
                    allowDivTransToP:".(!$field['setting']['option']['div2p'] ? 'true' : 'false').",
                    autoHeightEnabled:".($field['setting']['option']['autoheight'] ? 'true' : 'false').",
                    imageDqEnabled:".($field['setting']['option']['duiqi'] ? 'true' : 'false').",
                    charset:\"utf-8\",
                    ".($field['setting']['option']['enter'] ? 'enterTag : \'br\'' : '')."
                });
                editor_{$name}.render(\"dr_$name\");
                dr_post_addfunc(function(){
                    if(UE.getEditor(\"dr_$name\").queryCommandState('source')!=0){
                        UE.getEditor(\"dr_$name\").execCommand('source');
                    }
                })
            });
function dr_editor_down_img_".$field['fieldname']."(){
var index = layer.load(2, {
    shade: [0.3,'#fff'], //0.1透明度的白色背景
    time: 100000000
});
$.ajax({
    type: 'POST',
    url: '".dr_web_prefix('index.php?s=api&c=file&m=down_img&is_iframe=1&token='.dr_get_csrf_token().'&rid='.$this->rid.'&p=' . $p2."&is_wm=".$field['setting']['option']['watermark'])."',
    dataType: 'json',
    data: { value: UE.getEditor('dr_".$field['fieldname']."').getContent() },
    success: function (json) {
        layer.close(index);
        if (json.code == 0) {
            dr_cmf_tips(0, json.msg, json.data.time);
        } else {
            
            var width = '500px';
            var height = '70%';
        
            if (is_mobile_cms == 1) {
                width = '95%';
                height = '90%';
            }
        
            layer.open({
                type: 2,
                title: '',
                fix:true,
                scrollbar: false,
                maxmin: false,
                resize: true,
                shadeClose: true,
                shade: 0,
                area: [width, height],
                btn: [dr_lang('确定'), dr_lang('取消')],
                yes: function(index, layero){
                    // 延迟加载
                    var loading = layer.load(2, {
                        shade: [0.3,'#fff'], //0.1透明度的白色背景
                        time: 100000000
                    });
                    var body = layer.getChildFrame('body', index);
                    $.ajax({type: 'POST',dataType:'json', url: json.msg, data: $(body).find('#myform').serialize(),
                        success: function(json) {
                            layer.close(loading);
                            if (json.code) {
                                layer.close(index);
                                 UE.getEditor('dr_".$field['fieldname']."').setContent(json.data);
                                dr_cmf_tips(1, json.msg);
                            } else {
                                dr_cmf_tips(0, json.msg, json.data.time);
                            }
                            return false;
                        },
                        error: function(HttpRequest, ajaxOptions, thrownError) {
                            dr_ajax_alert_error(HttpRequest, this, thrownError);
                        }
                    });
                    return false;
                },
                success: function(layero, index){
                    // 主要用于后台权限验证
                    var body = layer.getChildFrame('body', index);
                    var json = $(body).html();
                    if (json.indexOf('\"code\":0') > 0 && json.length < 500){
                        var obj = JSON.parse(json);
                        layer.close(index);
                        dr_cmf_tips(0, obj.msg);
                    }
                },
                content: json.msg+'&is_iframe=1'
            });
            
            
        }
    },
    error: function(HttpRequest, ajaxOptions, thrownError) {
        dr_ajax_alert_error(HttpRequest, this, thrownError);
    }
});
            }
        </script>
        ", 0);


        if (isset($field['setting']['option']['show_bottom_boot']) && $field['setting']['option']['show_bottom_boot']) {
            $str.= '<div class="mt-checkbox-inline" style="margin-top: 10px;">';
            $str.= '     <label style="margin-bottom: 0;" class="mt-checkbox mt-checkbox-outline">
                  <input name="is_auto_thumb_'.$field['fieldname'].'" type="checkbox" '.($field['setting']['option']['tool_select_1'] ? 'checked' : '').' value="1"> '.dr_lang('提取第一个图片为缩略图').' <span></span>
                 </label>';
            $str.= '
                 <label style="margin-bottom: 0;" class="mt-checkbox mt-checkbox-outline">
                  <input id="is_auto_description_'.$field['fieldname'].'" onclick="dr_is_auto_description_'.$field['fieldname'].'()"  name="is_auto_description_'.$field['fieldname'].'" type="checkbox" '.($field['setting']['option']['tool_select_2'] ? 'checked' : '').' value="1"> '.dr_lang('提取内容作为描述信息').' <span></span>
                 </label>';
            $str.= '
                 <label style="margin-bottom: 0;" class="mt-checkbox mt-checkbox-outline">
                  <input name="is_remove_a_'.$field['fieldname'].'" type="checkbox" '.($field['setting']['option']['tool_select_4'] ? 'checked' : '').' value="1"> '.dr_lang('去除站外链接').' <span></span>
                 </label>';
            if (!$field['setting']['option']['down_img']) {
                $str.= '
                 <label style="margin-bottom: 0;" class="mt-checkbox mt-checkbox-outline">
                  <input name="is_auto_down_img_'.$field['fieldname'].'" type="checkbox" '.($field['setting']['option']['tool_select_3'] ? 'checked' : '').' value="1"> '.dr_lang('下载远程图片').' <span></span>
                 </label>';
            }
            $str.= '
                 <label style="margin-bottom: 0;" class="mt-checkbox mt-checkbox-outline">
                  <a class="btn blue btn-xs" onclick="dr_editor_down_img_'.$field['fieldname'].'()"> '.dr_lang('一键下载远程图片').' </a>
                 </label>';

            $str.= '</div>';
        }


        return $this->input_format($name, $text, $str.$tips);
    }
}