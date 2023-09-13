<?php

namespace IfSo\Addons\SelectionForm;

require_once( IFSO_PLUGIN_SERVICES_BASE_DIR . 'groups-service/groups-service.class.php' );

use IfSo\PublicFace\Services\GroupsService\GroupsService as GroupsService;

class SelectionForm{
    function __construct(){
        add_action('ifso_extra_extended_shortcodes',[$this,'add_ifso_form_shortcodes']);
        add_action('wp_ajax_nopriv_ifso_select_form_handle',[$this,'handle_ajax_submission']);
        add_action('wp_ajax_ifso_select_form_handle',[$this,'handle_ajax_submission']);
        add_action('init',[$this,'handle_pageload_submission']);
        add_action('wp_enqueue_scripts',[$this,'add_js']);
        add_action('wp_ajax_nopriv_ifso_select_form_value',[$this,'ajax_print_selected_form_value']);
        add_action('wp_ajax_ifso_select_form_main_value',[$this,'ajax_print_selected_form_value']);
        add_action('wp_ajax_render_ifso_self_selection_forms',[$this,'handle_form_request']);
        add_action('wp_ajax_nopriv_render_ifso_self_selection_forms',[$this,'handle_form_request']);

        $this->groups_service = GroupsService::get_instance();
    }

    public function add_js(){
        $admin_url = admin_url('admin-ajax.php');
        $script = <<<SCR
        jQuery(document).ready(function () {
            var $ = jQuery;
            init_forms();
            replaceLoadLaterSelectionForms();
            document.addEventListener("ifso_ajax_triggers_loaded",init_forms);
            document.addEventListener("ifso_ajax_conditions_loaded",init_forms);
            function init_forms(){
                $('.ifso_selection_form').each(function(index,el){
                    if(el.getAttribute('ajax')!== null && el.getAttribute('self_selection_ready') === null){
                        $(el).on('submit',function(e){
                            e.preventDefault();
                            var form = $(this);
                            $.ajax({
                                type:'POST',
                                url:'{$admin_url}',
                                data: form.serialize(),
                            }).then(function(){
                                var rdr_to = getSelectedRedirectFromForm(el);
                                if(rdr_to!==null)
                                    location.href = rdr_to;
                            });
                        });
                        el.setAttribute('self_selection_ready',true);
                    }
                    if(el.getAttribute('ajax')===null && getSelectedRedirectFromForm(el)!==null){
						$(el).on('submit',function(e){
							el.action = getSelectedRedirectFromForm(el);	
						});
					}
                });	
            };
            
            function getSelectedRedirectFromForm(el){
                var rdr_to = null;
                if(el.querySelector('.if-so-add-to-grp-options')!==null)
                   rdr_to = el.querySelector('.if-so-add-to-grp-options').options[el.querySelector('.if-so-add-to-grp-options').selectedIndex].getAttribute('rdr_url')  
                if(el.querySelector('.if-so-add-to-grp-radio-options input[checked]')!==null)
                    rdr_to = el.querySelector('.if-so-add-to-grp-radio-options input[checked]').getAttribute('rdr_url');
                if(rdr_to===null && el.getAttribute('rdrto')!==null)
                    rdr_to = el.getAttribute('rdrto');
                return rdr_to;
            }	
						
            function replaceLoadLaterSelectionForms(){
                var elements = $('IfSoSelfSelection');
                var toReplace = lookForLoadLaterSelectionForms(elements);
                if (Object.keys(toReplace).length>0){
                    $.post('{$admin_url}', {selection_forms:JSON.stringify(toReplace),'action':'render_ifso_self_selection_forms',nonce:nonce}, function(ret) {
                        if(ret && ret!== null){
                            try{
                                var data = (typeof(ret) === 'object') ? ret :  JSON.parse(ret);
                                $.each(data, function(id,val){
                                    elements[id].outerHTML = val;
                                });
                                init_forms();
                            }
                            catch(e){
                                console.error('Error fetching if-so self-selection forms!');
                                console.error(e);
                            }
                        }
                    });
                }
            }
            
            function lookForLoadLaterSelectionForms(tags){
                var ret = [];
                tags.each(function(index,el){
                    var data = el.querySelector('data').innerHTML;
                    ret.push(data);
                });
                return ret;
            }
        });
SCR;

        wp_add_inline_script('if-so',$script,'after');
    }

    public function add_ifso_form_shortcodes(){
        add_shortcode('ifso_group_selection',function($atts){
            return $this->render_ifso_form($atts,'group');
        });

        add_shortcode('ifso_geo_selection',function($atts){
            return $this->render_ifso_form($atts,'geo-old');
        });

        add_shortcode('ifso_geo_override',function ($atts){
            return $this->render_ifso_form($atts,'geo');
        });

        add_shortcode('ifso_selection',function($atts){
            return $this->render_ifso_form($atts,'generic');
        });
    }

    private function render_ifso_form($atts,$formType){
        if(!empty($atts['ajax-render']) && strtolower($atts['ajax-render'])==='yes' && !is_admin() )
            return $this->create_self_selection_ajax_tag($atts,$formType);
        $allOptions = '';
        $submit = $this->make_submit($atts);
        $submit_type = (!empty($atts['ajax']) && ($atts['ajax'] === 'true' || $atts['ajax'] === 'yes') ) ? 'ajax' : '';
        $extra_classes = (!empty($atts['classname'])) ? $atts['classname'] : '';
        $nonce_field = wp_nonce_field( "ifso-groups-nonce",'_wpnonce',true,false);
        $formClass = "ifso_selection_form ifso_{$formType}_selection_form {$extra_classes}";
        $extra_fields = '';
        $redirect_to = (!empty($atts['redirect'])) ? $atts['redirect'] : null;
        $redirect_attr = (null===$redirect_to) ? '' : (($submit_type!=='ajax') ?  "action='{$redirect_to}'" : "rdrto='{$redirect_to}'");

        if(isset($atts['options'])){
            if($atts['options']==='all-countries' || $atts['options']==='all-us-states'){
                if($atts['options']==='all-countries'){
                    $allCountries = array('AF'=>'Afghanistan','AX'=>'Aland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua And Barbuda','AR'=>'Argentina','AM'=>'Armenia','AW'=>'Aruba','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia','BA'=>'Bosnia And Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Congo, Democratic Republic','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>'Cote D\'Ivoire','HR'=>'Croatia','CU'=>'Cuba','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island & Mcdonald Islands','VA'=>'Holy See (Vatican City State)','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran, Islamic Republic Of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle Of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'Kenya','KI'=>'Kiribati','KR'=>'Korea','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>'Lao People\'s Democratic Republic','LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libyan Arab Jamahiriya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia, Federated States Of','MD'=>'Moldova','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','AN'=>'Netherlands Antilles','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestinian Territory, Occupied','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Reunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','BL'=>'Saint Barthelemy','SH'=>'Saint Helena','KN'=>'Saint Kitts And Nevis','LC'=>'Saint Lucia','MF'=>'Saint Martin','PM'=>'Saint Pierre And Miquelon','VC'=>'Saint Vincent And Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome And Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia And Sandwich Isl.','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard And Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan','TJ'=>'Tajikistan','TZ'=>'Tanzania','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad And Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks And Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela','VN'=>'Viet Nam','VG'=>'Virgin Islands, British','VI'=>'Virgin Islands, U.S.','WF'=>'Wallis And Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe',);
                    $optionsArr = array_keys($allCountries);
                    $labelsArr = array_values($allCountries);
                }
                if($atts['options']==='all-us-states'){
                    $optionsArr = array('Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','District of Columbia','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming',);
                    $labelsArr = $optionsArr;
                }
            }
            else{
                $optionsArr = array_map('trim',explode(',',$atts['options']));
                $labelsArr = isset($atts['labels']) ? array_map('trim',explode(',',$atts['labels'])) : $optionsArr;
            }
            $optRedirectsArr = isset($atts['redirects']) ? array_map('trim',explode(',',$atts['redirects'])) : [];

            if(!empty($optionsArr) && $formType==='geo'){
                $options = $optionsArr;
                $labels = $labelsArr;
                $supports_emoji = !empty($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome')===false;  //Not supported in desktop chrome
                $extra_data = !empty($atts['extra-data']) && json_decode(urldecode($atts['extra-data'])) ? json_decode(urldecode($atts['extra-data']),true) : [];
                foreach($labels as $index=>$label){
                    if(!empty($atts['geo-type']) && $atts['geo-type']==='countryCode' && !empty($atts['show-flags']) && strtolower($atts['show-flags'])==='yes' && !empty($options[$index]) && method_exists('\IfSo\Addons\Geolocation\GeolocationExtension','get_ifso_country_flag_emoji') && $supports_emoji){
                        $flag_emoji= \IfSo\Addons\Geolocation\GeolocationExtension::get_instance()->get_ifso_country_flag_emoji($options[$index],false);
                        $label = "{$flag_emoji} " . $label;
                    }
                    $labelsArr[$index]= $label;
                }
                foreach($options as $index=>$option){
                    $newOpt = ['val'=>$option];
                    if(!empty($atts['geo-type']) && $atts['geo-type']==='countryCode' && !empty($labels[$index]))
                        $newOpt['extra_fields']['countryName'] = $labels[$index];
                    if(!empty($extra_data[$index]['fields']))
                        $newOpt['extra_fields'] = $extra_data[$index]['fields'];
                    $optionsArr[$index]= json_encode($newOpt);
                }
            }

            if(is_array($optionsArr)){
                $allOptions = json_encode($optionsArr);
                $mainOpts = $this->make_main_select($atts,$optionsArr,$labelsArr,$optRedirectsArr,$submit,$formType);
            }
        }

        if($formType==='group'){
            $extra_fields .= "<input type='text' hidden style='display:none!important;' name='ifso-selection-options-allOptions' value='{$allOptions}'>";
        }
        if($formType==='geo-old' || $formType ==='generic'){
            if(!empty($atts['cookie_name'])){
                $cookie_name_field = "<input type='hidden' name='ifso-selection-cookie-name' value='{$atts['cookie_name']}'>";
                $extra_fields .= $cookie_name_field;
            }
        }
        if($formType==='geo'){
            $geo_type = !empty($atts['geo-type']) ? $atts['geo-type'] : '';
            $geo_type_field = "<input type='hidden' name='ifso-geo-override-geo-type' value='{$geo_type}'>";
            $extra_fields .= $geo_type_field;
        }

        if(!isset($mainOpts)) return false;

        $html = "<form class='{$formClass}' {$submit_type} {$redirect_attr} method='POST'>
                        {$mainOpts}
                        <input type='hidden' name='ifso-selection-type' value='{$formType}'>
                        <input type='hidden' name='action' value='ifso_select_form_handle'>
                        {$extra_fields}
                        {$nonce_field}
                        {$submit['button']}
                    </form>";

        return $html;
    }

    private function make_submit($atts){
        $button_text = (isset($atts['button']) && !empty($atts['button'])) ? $atts['button'] : 'Submit' ;

        $submit = [
            'button' => "<button type='submit'>{$button_text}</button>",
            //'onchange'=> "onchange='this.form.submit();'"
            'onchange'=> "onchange='jQuery(this.form).submit();'"
        ];

        if(isset($atts['button']) && $atts['button'] !== 'false')
            $submit['onchange'] = '';
        else
            $submit['button'] = '';

        return $submit;
    }

    private function make_main_select($atts,$optionsArr,$optionsLabelsArr,$rdrsArr,$submit,$formType){
        $ret = '';
        $options = '';
        $default_select_text = (isset($atts['default-option'])) ? $atts['default-option'] : 'Select below';
        $check_selected = function ($opt) use ($atts,$formType){
            switch ($formType){
                case 'group':
                    if($this->groups_service->is_user_in_group($opt))
                        return true;
                    break;
                case 'geo':
                    if(class_exists('\IfSo\Addons\Geolocation\GeolocationExtension') && !empty($atts['geo-type'])){
                        if(json_decode($opt)!==null){
                            $decoded_opt = json_decode($opt,true);
                            $opt = !empty($decoded_opt['val']) ? $decoded_opt['val'] : '';
                        }
                        $geo_type = $atts['geo-type'];
                        $cname = \IfSo\Addons\Geolocation\GeolocationExtension::get_instance()->geo_override_cookie_name;
                        $override_data = !empty($_COOKIE[$cname]) && json_decode(stripslashes($_COOKIE[$cname])) ? json_decode(stripslashes($_COOKIE[$cname]),true) : null;
                        if(isset($override_data[$geo_type]) && $override_data[$geo_type] === $opt)
                            return true;
                        if(!empty($atts['autodetect-location']) && $atts['autodetect-location']==='yes'){
                            $geoData = \IfSo\Services\GeolocationService\GeolocationService::get_instance()->get_user_location();
                            if(!empty($geoData) && !empty($geoData->get($geo_type)) && $geoData->get($geo_type)===$opt)
                                return true;
                        }

                    }
                    break;
                case 'geo-old':
                case 'generic':
                    if(!empty($atts['cookie_name']) && isset($_COOKIE[$atts['cookie_name']]) && $opt === $_COOKIE[$atts['cookie_name']])
                        return true;
                    break;
            }
            return false;
        };


        foreach($optionsArr as $index=>$opt){
            $option_label = isset($optionsLabelsArr[$index]) ? $optionsLabelsArr[$index] : $opt;
            $selected = ($check_selected($opt)) ? ((isset($atts['type']) && $atts['type'] ==='radio')) ? 'checked' : 'selected' : '';
            $br = (isset($atts['orientation']) && $atts['orientation']==='vertical') ? '<br>' : '';
            $rdr = (!empty($rdrsArr) && !empty($rdrsArr[$index])) ? "rdr_url='{$rdrsArr[$index]}'" : '' ;
            $options .= ((isset($atts['type']) && $atts['type'] ==='radio')) ? "<input {$submit['onchange']} {$selected} type='radio' name='ifso-selection-options' id='ifso-selector-radio-{$opt}' value='{$opt}' {$rdr}><label for='ifso-selector-radio-{$opt}'>{$option_label}</label>{$br}"  : "<option {$selected} value='{$opt}' {$rdr}>{$option_label}</option>";
        }

        if((isset($atts['type']) && $atts['type'] ==='radio')){
            $ret = "<div class='if-so-add-to-grp-radio-options'>
					{$options}
				</div>";
        }
        else{
            $ret = "<select {$submit['onchange']} name='ifso-selection-options' class='if-so-add-to-grp-options'>
                            <option value=''>{$default_select_text}</option>
                            {$options}
				</select>";
        }

        return $ret;
    }

    private function create_self_selection_ajax_tag($atts,$formType){
        $loader_classes = ['', 'ifso-logo-loader', 'lds-dual-ring'];
        $loader_type = \IfSo\Services\PluginSettingsService\PluginSettingsService::get_instance()->ajaxLoaderAnimationType->get();
        $atts['self_select_form_type'] = $formType;
        unset($atts['ajax-render']);
        $attsJSON = esc_html(json_encode($atts));

        foreach($atts as $attName=>$attVal){
            if($attName === 'loader'){
                if(is_numeric($attVal))
                    $loader_type = $attVal;
                if($attVal === 'no')
                    $loader_type = 0;
            }
        }
        if(method_exists(\IfSo\PublicFace\Services\AjaxTriggersService\AjaxTriggersService::get_instance(),'get_ajax_loader_list'))
            $loader_classes = is_numeric($loader_type) ? $loader_classes : \IfSo\PublicFace\Services\AjaxTriggersService\AjaxTriggersService::get_instance()->get_ajax_loader_list();      //Compat

        $html = "<IfSoSelfSelection class='{$loader_classes[$loader_type]}'  style='display:inline-block;'><data style='display: none;'>{$attsJSON}</data></IfSoSelfSelection>";
        return $html;
    }

    private function handle_select_form($rdrBack = true,$ajax=false){
        if(($ajax || (!is_admin() && !wp_doing_ajax())) && !empty($_REQUEST['action']) && $_REQUEST['action']==='ifso_select_form_handle' && isset($_REQUEST['ifso-selection-options']) && check_ajax_referer('ifso-groups-nonce')){
            if(!empty($_REQUEST['ifso-selection-type'])){
                switch ($_REQUEST['ifso-selection-type']){
                    case 'group':
                        $this->add_user_to_group_by_form();
                        break;
                    case 'geo':
                        $this->add_geo_override_by_form();
                        break;
                    case 'geo-old':
                    case 'generic':
                        $this->add_cookie_data_by_form();
                        break;
                }
            }

            do_action('ifso_selection_form_handled');

            if($rdrBack) {
                header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");     //Redirect to back to the same page to prevent form resubmission
                exit();
            }
        }
    }

    public function ajax_print_selected_form_value(){
        if(wp_doing_ajax()){
            $form_atts = $_REQUEST['bloop_form_atts'];
            if(!empty($form_atts) && is_array($form_atts)){
                $optionsArr = array_map('trim',explode(',',$form_atts['options']));
                foreach ($optionsArr as $opt){
                    if(true){       //check whether this one was selected
                        echo $opt;
                        break;
                    }
                }
            }
        }
        wp_die();
    }



    public function add_user_to_group_by_form($rdrBack = true,$ajax=false){
        $allOpts = json_decode(stripslashes($_REQUEST['ifso-selection-options-allOptions']),true);
        $selected = $_REQUEST['ifso-selection-options'];
        if(is_array($allOpts) && is_string($selected)){
            $this->groups_service->remove_user_from_groups($allOpts);
            $this->groups_service->add_user_to_group($selected);
        }
    }

    public function add_geo_override_by_form(){
        if(isset($_REQUEST['ifso-geo-override-geo-type']) && isset($_REQUEST['ifso-selection-options'])){
            if(class_exists('\IfSo\Addons\Geolocation\GeolocationExtension')){
                $cname = \IfSo\Addons\Geolocation\GeolocationExtension::get_instance()->geo_override_cookie_name;
                if(json_decode(stripslashes($_REQUEST['ifso-selection-options']))!==null){
                    $selected_arr = json_decode(stripslashes($_REQUEST['ifso-selection-options']),true);
                    if(empty($selected_arr['val'])) return;
                    if(!empty($selected_arr['extra_fields']))
                        $extra_fields = $selected_arr['extra_fields'];

                    $cval = [$_REQUEST['ifso-geo-override-geo-type'] => $selected_arr['val']];
                    if(!empty($extra_fields))
                        $cval = array_merge($cval,$extra_fields);
                    $cval= json_encode($cval);
                }
                else
                    $cval = json_encode([$_REQUEST['ifso-geo-override-geo-type'] => $_REQUEST['ifso-selection-options']]);
                $expire = (!empty($_REQUEST['ifso-selection-options'])) ? 0 : 1;    //Delete cookie if the selection is empty
                setcookie($cname,$cval,$expire,'/');
            }
        }
    }

    public function add_cookie_data_by_form(){
        if(isset($_REQUEST['ifso-selection-cookie-name']) && isset($_REQUEST['ifso-selection-options'])){
            $cname = $_REQUEST['ifso-selection-cookie-name'];
            $cval = $_REQUEST['ifso-selection-options'];
            setcookie($cname,$cval,0,'/');
            $_COOKIE[$cname] = $cval;
        }
    }

    public function handle_pageload_submission(){
        $this->handle_select_form(true,false);
    }

    public function handle_ajax_submission(){
        if(wp_doing_ajax()){
            $this->handle_select_form(false,true);
        }
        wp_die();
    }

    public function handle_form_request(){
        if(check_admin_referer('ifso-nonce','nonce')){
            if(wp_doing_ajax() && !empty($_REQUEST['selection_forms']) && $selectionForms = json_decode(stripslashes($_REQUEST['selection_forms']),true)){
                if($selectionForms && is_array($selectionForms)){
                    $res = [];
                    foreach($selectionForms as $form){
                        $atts = json_decode($form,true);
                        if(!empty($atts['self_select_form_type']))
                            $res[] = $this->render_ifso_form($atts,$atts['self_select_form_type']);
                    }
                    if(!empty($res)){
                        header('Content-Type: application/json');
                        echo json_encode($res);
                    }
                }
            }
        }
        wp_die();
    }
}