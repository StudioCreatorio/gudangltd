( function( blocks, editor,  element ) {
    var el = wp.element.createElement;
    var InspectorControls = editor.InspectorControls;
    var PanelRow = wp.components.PanelRow;
    var PanelBody = wp.components.PanelBody;

    window.ifsoLocGenPipe = {
        changing_input : null,
        open: function (url,el) {
            var input_name = jQuery(el).closest('label').attr('for');
            if(typeof (input_name)!=='undefined'){
                var inp =jQuery(el).closest('form').find('[name="' + input_name + '"]');
                this.changing_input = inp[0];
            }
            window.open(url + "&ui_type=adder", 'newwindow', 'width=800,height=600');
        },
        accept: function (data) {
            if(typeof(this.changing_input)!=='undefined' && this.changing_input!==null){
                this.changing_input.focus();
                this.changing_input.value = data;
                this.changing_input.dispatchEvent(new KeyboardEvent('keyup', {code: 'Enter', key: 'Enter', charCode: 13, keyCode: 13, view: window, bubbles: true}));
                this.changing_input.blur();
            }
        }
    }

    var multibox = function(){
        this.data_separator = '!!';
        this.version_separator = '^^';
        this.geo_symbols = ['CITY','COUNTRY','STATE','CONTINENT','TIMEZONE'];
        this.symInputKeyupCallback = this.symInputKeyup.bind(this);
        this.deleteVersionButtonPressedCallback = this.deleteVersionButtonPressed.bind(this);
        this.country_cache = {};
    };
    multibox.prototype = {
        getVersions : function(data=''){
            return (data==='') ? [] : data.split(this.version_separator);
        },
        addVersion : function (data,toAdd){
            if(!data.includes(toAdd)){
                data += (data!=='') ? this.version_separator : '';
                data += toAdd;
                return data;
            }
            return data;
        },
        removeVersion : function (data,removeId){
            var versions = this.getVersions(data);
            versions.splice(removeId,1);
            return versions.join(this.version_separator);
        },
        createNewLocation : function (locationType, behindSceneLocationData, visualLocationData) {
            var data = [locationType, visualLocationData, behindSceneLocationData];
            return data.join(this.data_separator);
        },
        symInputKeyup : function (event,props,forceaAllow=false){
            if(event.which===13 || forceaAllow){               //enter was pressed
                var _this = this;
                var focused_input = event.target;
                if((focused_input.tagName!=='input' && focused_input.getAttribute('type')!=='text') && !forceaAllow) return false;    //Only for text inputs
                var newVal = focused_input.value;
                var symbol = focused_input.getAttribute('symbol');
                var interacted_form  = jQuery(focused_input).closest('form');
                var data_input = interacted_form.find('[multiData]')[0];
                var data_input_name = data_input.name;
                var versionData;
                if(symbol && newVal!==''){
                    var newVals = this.parseInputValue(newVal,symbol);
                    var current_data_value = props.attributes.ifso_condition_rules[data_input_name] || '';
                    newVals.forEach(function(newVal){
                        if(typeof(newVal['loc_type'])!=='undefined' && typeof(newVal['loc_val'])!=='undefined'){
                            versionData = _this.createVersionData(newVal['loc_type'],newVal['loc_val'],interacted_form);
                            current_data_value = _this.addVersion(current_data_value,versionData);
                            if(symbol==='COUNTRY'){
                                _this.country_cache[newVal['loc_val']] = focused_input.querySelector('option[value="'+ newVal['loc_val'] +'"]').innerHTML;
                            }
                        }
                    });
                    focused_input.value = '';
                    var rules_copy = Object.assign({},props.attributes.ifso_condition_rules);
                    rules_copy[focused_input.getAttribute('name')] = '';
                    rules_copy[data_input_name] = current_data_value;
                    props.setAttributes({ifso_condition_rules:rules_copy});
                }
            }
        },
        createVersionData : function(symbol,newVal,interacted_form){
            var versionData;
            var _this = this;
            if(_this.geo_symbols.includes(symbol)){
                versionData = _this.createNewLocation(symbol,newVal,newVal);
            }
            else{
                var symbol_inputs = interacted_form.find('[symbol='+symbol+']');
                versionData = _this.createNewLocation(symbol,jQuery(symbol_inputs[0]).val(),jQuery(symbol_inputs[1]).val());
            }
            return versionData;
        },
        parseInputValue : function(val,symbol=''){
            try{return JSON.parse(val);}
            catch(e){return [{loc_type:symbol,loc_val:val}];}
        },
        deleteVersionButtonPressed : function (event,props,field){
            if(event.clientX===0 && event.clientY===0) return;     //Make sure it was actually called by click on delete(not while rendering still)
            var newData = this.removeVersion(props.attributes.ifso_condition_rules[field],jQuery(event.target).closest('[version_number]').attr('version_number'));
            var new_rules = {...props.attributes.ifso_condition_rules};
            new_rules[field] = newData;
            props.setAttributes({ifso_condition_rules:new_rules});
        },
        searchDRModelForCountryName : function (countryCode){
            for (var i = 0; i < data_rules_model['Geolocation']['fields']['geolocation_country_input']['options'].length; i++){
                var countryOpt = data_rules_model['Geolocation']['fields']['geolocation_country_input']['options'][i];
                if(countryOpt.value === countryCode){
                    return countryOpt.display_value;
                }
            }
        },
        renderMultiboxVersions : function(props,field){
            var _this = this;
            var data = props.attributes.ifso_condition_rules[field];
            var versions = this.getVersions(data);
            var ret;
            if(versions.length!==0){
                ret = [];
                for(var i=0;i<versions.length;i++){
                    var v_data = versions[i].split(this.data_separator);
                    if(typeof(v_data[1])!=='undefined') {
                        var label = '';
                        var display_value = v_data[1];
                        switch (props.attributes.ifso_condition_type) {
                            case 'Geolocation':
                                label = v_data[0];
                                if(label==='COUNTRY'){
                                    if(typeof(this.country_cache[display_value])==='undefined')
                                        this.country_cache[display_value] = this.searchDRModelForCountryName(display_value);
                                    if(typeof(this.country_cache[display_value])!=='undefined')
                                        display_value = this.country_cache[display_value];
                                }

                                break;
                            case 'PageVisit':
                                label = v_data[2];
                                break;
                        }
                        ret.push(el('div',{className:'ifso-multibox-version',version_number:i}
                            ,el('div',{className:'content-label'}, [label.toLowerCase() + '\u00A0:\u00A0',el('span',{},display_value)])
                            ,el('button',{className:'ifso-mb-del',onClick:function(e){_this.deleteVersionButtonPressedCallback(e,props,field)}},'X')
                        ));
                    }
                }
            }
            else{
                ret = el('span',{className:'no-versions-text'},'No targets selected')
            }
            return ret;
        },
    };


    var iconEl = el('svg', {width:20, height:20,  viewBox: '0 0 1080 1080', class:'ifso-block-icon' },[ el('path', { d: "M418.9,499.8c-32.2,0-61.5,0-92.2,0c0-46.7,0-92.6,0-140c29.8,0,59.6,0,91.9,0c0-7.6-0.7-14,0.1-20.1c4.6-32.2,5.5-65.6,15.3-96.2c19.4-60.5,67.6-90.1,127.1-102.1c67.4-13.6,135.3-6.5,204.2-3c0,51.9,0,102.8,0,155.4c-15.7-1.8-30.7-3.7-45.6-5.2c-7.5-0.8-15.2-1.7-22.7-1.2c-43.8,3.2-61,25.8-53.6,71.6c38.1,0,76.5,0,116.2,0c0,47,0,92.5,0,139.9c-37.1,0-74.3,0-113.2,0c0,152.1,0,302.3,0,453.7c-76.3,0-151,0-227.5,0C418.9,802.1,418.9,652,418.9,499.8z", class:'st0'})
        ,el('path', { d: "M0,134.5c83.7,0,166.3,0,250,0c0,272.8,0,544.9,0,818.3c-82.8,0-165.8,0-250,0C0,680.8,0,408.3,0,134.5z", class:'st0'}),
        el('path', {style: {fill:'#FD5B56'},  d: "M893.5,392.3c62.2,44.4,123.4,88.1,185.8,132.7c-62.2,44.4-123.3,88-185.8,132.7C893.5,568.8,893.5,481.5,893.5,392.3z", class:'st1'})]);

    var data_rules_model = (typeof(data_rules_model_json)==='string') ? JSON.parse(data_rules_model_json) : data_rules_model_json;
    var license_status_object = (typeof(license_status)==='string') ? JSON.parse(license_status) : license_status;
    var pages_links = (typeof(ifso_pages_links)==='string') ? JSON.parse(ifso_pages_links) : ifso_pages_links;
    var ajax_loaders_names = (typeof(ajax_loaders_json)==='string') ? JSON.parse(ajax_loaders_json) : ajax_loaders_json;
    var ajax_loaders_names_opts = function(){var ret = [];Object.keys(ajax_loaders_names).forEach(function (opt){ret.push({'value':opt,'display_value':ajax_loaders_names[opt]});});return ret;}();
    var multibox_control = new multibox();
    console.log(data_rules_model);

    function get_form_data(form){
        var arr = jQuery(form).serializeArray();
        var ret ={};
        arr.forEach(function(field){
            ret[field.name] = field.value;
        });
        return ret;
    }

    function save_form_data(form,props){
        var interacted_form_data = get_form_data(form);
        props.setAttributes({ifso_condition_rules:interacted_form_data});
    }

    function create_sidebar_condition_ui_elements(props){
        //var title = el('h3',{className:'title'},iconEl,'Dynamic Content');
        var title = null;

        var trigger_type_select = el('select',{onChange:function(e){
                    var rules_form_wrap = e.target.parentElement.parentElement.querySelector('.ifso-standalone-condition-form-wrap');
                    var selected_value = e.target.selectedOptions[0].value;
                    var old_selected_form = rules_form_wrap.querySelector('.selected');
                    if (old_selected_form){
                        old_selected_form.classList.remove('selected');
                        hard_reset_form(old_selected_form);
                    }
                    props.setAttributes({ifso_condition_type : selected_value, ifso_condition_rules : {}});
                    var new_selected_form = rules_form_wrap.querySelector('[formtype="'+selected_value+'"]');
                    if (selected_value){
                        new_selected_form.classList.add('selected');
                        save_form_data(new_selected_form,props);
                        var subgroup = new_selected_form.getAttribute('contains_subgroups');
                        if(null!==subgroup && subgroup){
                            //switch_form_to_subgroup(new_selected_form,subgroup);
                        }
                    }}},
            function(){
                var ret = [];
                var noneSelected = (''===props.attributes.ifso_condition_type);
                ret.push(el('option',{value:'',selected:noneSelected},null,'Select a Condition'));
                Object.keys(data_rules_model).forEach(function(type){if(type==='general' || type==='AB-Testing') return;var selected = (type===props.attributes.ifso_condition_type);var not_allowed_marker = (!license_status_object['is_license_valid'] && !in_array(license_status_object['free_conditions'],type)) ? '*' : '';ret.push(el('option',{value:type, selected:selected},data_rules_model[type]['name']+not_allowed_marker))});
                return ret;
            }());
        var trigger_type_wrap = el('div',{className:'ifso-standalone-condition-trigger-type-wrap'},[el('label',{},null,'Only show this block if: '),trigger_type_select]);

        var trigger_rules_form = el('div',{className:'ifso-standalone-condition-form-wrap'},create_data_rules_forms(data_rules_model,props));

        var default_content_wrap = el('div',{className:'default-content-wrap'},[el('input',{type:'checkbox',className:'default-content-exists-input input-control',checked:props.attributes.ifso_default_exists,onChange:function(e){props.setAttributes({ifso_default_exists: e.target.checked})}}),
            el('label',{className:(props.attributes.ifso_default_exists) ? '' : 'ifso-gray'},null,'Default Content:'), el(wp.blockEditor.RichText,{value: props.attributes.ifso_default_content, className:((props.attributes.ifso_default_exists) ? '' : 'nodisplay ') + 'default-content-input block-editor-plain-text input-control',placeholder:'Type here (HTML)',onChange:function(e){props.setAttributes({ifso_default_content : e});}})
        ]);

        var aud_add_rm_wrap = el('div',{className:'audiences-addrm-wrap'},[el('input',{type:'checkbox',className:'audiences-addrm-exists-input',checked:!(is_empty(props.attributes.ifso_aud_addrm)),onChange:function(e){var toSet = (e.target.checked) ? {add:[],rm:[]} : {}; props.setAttributes({ifso_aud_addrm : toSet})} }),
        el('label',{className:(is_empty(props.attributes.ifso_aud_addrm)) ? 'ifso-gray' : ''},null,'Audiences'),create_audience_addrm_ui(props)]);

        var render_with_ajax_wrap = el('div',{className:'ifso-render-with-ajax-wrap'},[el('input',{type:'checkbox',className:'ifso-render-with-ajax-input input-control',checked:props.attributes.ifso_render_with_ajax,onChange:function(e){props.setAttributes({ifso_render_with_ajax: e.target.checked,ajax_loader_type: ''});}}),el('label',{className:(props.attributes.ifso_render_with_ajax) ? '' : 'ifso-gray'},null,'Load with Ajax')]);

        var ajax_loader_wrap = props.attributes.ifso_render_with_ajax ? el('div',{className:'ifso-ajax-loader-type-wrap'},[el('label',{},null,'Loader type'),el('select',{className:'loader-type-select input-control',value:props.attributes.ajax_loader_type,onChange:function(e){props.setAttributes({ajax_loader_type: e.target.value});}},null,create_ifso_ui_select_options(ajax_loaders_names_opts))]) : null

        var base_div = el('div',{className:'custom-condition-base-div'},[title,trigger_type_wrap,trigger_rules_form,default_content_wrap,aud_add_rm_wrap,render_with_ajax_wrap,ajax_loader_wrap]);

        return base_div;
    }

    function create_data_rules_forms(model,props){
        var ret = [];
        if(model && typeof(model)==='object'){
            Object.keys(model).forEach(function(condition){
                var form = create_data_rules_form(model,condition,props);
                ret.push(form);
            });
        }
        return ret;
    }

    function create_data_rules_form(model,condition,props){
        if(model && typeof(model)==='object' && condition){
            var form_elements = [];
            form_elements.push(create_license_condition_message(condition));
            var selected_form = (condition===props.attributes.ifso_condition_type);
            var contains_subgroups = false;
            var switcher_value = null;
            if(model[condition]){
                Object.keys(model[condition]['fields']).forEach(function(index){
                    var created_element = createElementFromModel(model[condition]['fields'][index],props,selected_form);
                    if (created_element.props.subgroup) contains_subgroups = true;
                    if (created_element.props.is_switcher) switcher_value = created_element.props.switcher_init_value;
                    form_elements.push(created_element);
                });
            }
            var form = el('form',{className:'ifso-standalone-condition-form',formType:condition,onSubmit:function(e){e.preventDefault();}},form_elements);
            if(selected_form) form.props.className += ' selected';
            if(contains_subgroups) form.props.contains_subgroups = 'true';
            if(contains_subgroups) switch_form_to_subgroup_two(form,switcher_value);
            return form;
        }
    }

    function createElementFromModel(elObj,props,fillWitData=false){
        if(elObj && typeof(elObj)==='object'){
            var ret;
            var element;
            var label = null;
            var saveInteractedFormData = function(e){
                var interacted_form = jQuery(e.target).closest('form');
                if(elObj['is_switcher']){
                    var switched_to = e.target.value;
                    switch_form_to_subgroup(interacted_form[0],switched_to);
                }
                save_form_data(interacted_form,props);
            };

            if(elObj['type'] === 'noticebox'){
                return create_noticebox(elObj);
            }

            if(elObj['type']==='text'){
                element = el('input',{type:'text',name:elObj['name'],required:elObj['required'],onChange:saveInteractedFormData})
            }

            if(elObj['type']==='select'){
                var select_options = create_ifso_ui_select_options(elObj['options']);
                element = el('select',{name:elObj['name'],required:elObj['required'],onChange:saveInteractedFormData},select_options);
            }

            if(elObj['type']==='checkbox'){
                element= el('input',{type:'checkbox',name:elObj['name'],onChange:saveInteractedFormData});
            }

            if(in_array(['text','select','checkbox'],elObj['type']))
                label = el('label',{for:elObj['name'],dangerouslySetInnerHTML: {__html: elObj['prettyName']}});

            if(elObj['type']==='multi'){
                var multibox_versions = !fillWitData ? null : multibox_control.renderMultiboxVersions(props,elObj['name']);
                var multibox_description = !fillWitData ? null : data_rules_model[props.attributes.ifso_condition_type]['multibox']['description'];
                element = el('div',{className:'ifso-multibox-wrap'},[el('input',{type:'text', name:elObj['name'], hidden:true, multiData:'true', value:props.attributes.ifso_condition_rules[elObj['name']] , onChange:saveInteractedFormData}),
                    el('div',{className:'ifso-multibox-wrapper'},[
                        el('div',{className:'ifso-multibox-description',dangerouslySetInnerHTML: {__html: multibox_description}}),
                        el('div',{className:'ifso-multibox-versions'},multibox_versions)
                    ])]);
            }

            element.props.className = elObj['extraClasses'];

            if(null !== elObj['symbol'] ){
                element.props.symbol = elObj['symbol'];
                if(elObj['type']==='select')
                    element.props.onChange = function(e){multibox_control.symInputKeyupCallback(e,props,true);}
                else{
                    element.props.onKeyUp = function(e){multibox_control.symInputKeyupCallback(e,props);}
                    element.props.onBlur = function(e){multibox_control.symInputKeyupCallback(e,props,true);}
                }
                element.props.title = 'Press Enter to insert an entry';
            }

            if(element.props.className === 'countries-autocomplete'){}

            var elsOrder = elObj['type']==='checkbox' ? [element,label] : [label,element];
            var extraClasses = elObj['type']==='checkbox' ? ' ifso-widget-checkbox' : '';
            ret = el('div',{className:'ifso-standalone-condition-control-wrap'+extraClasses},null,elsOrder)

            if(fillWitData && props.attributes.ifso_condition_rules[elObj.name]){
                element.props.value = props.attributes.ifso_condition_rules[elObj.name];

                if(elObj['type']==='checkbox' && props.attributes.ifso_condition_rules[elObj.name]){
                    if( 'on' === props.attributes.ifso_condition_rules[elObj.name])
                        element.props.checked = true;
                    else
                        element.props.checked = false;

                }
            }

            if(null!==elObj['subgroup']){
                ret.props.subgroup = elObj['subgroup'];
            }

            if(elObj['is_switcher']){
                ret.props.is_switcher = true;
                ret.props.className += ' is_switcher';
                ret.props.switcher_init_value = element.props.value;
            }

            return ret;
        }
    }

    function switch_form_to_subgroup(form,subgroup){
        jQuery(form).attr('showing_subgroup',subgroup);
        form.querySelectorAll('[subgroup]').forEach(function(e){
            if(subgroup === e.getAttribute('subgroup')){
                e.classList.remove('nodisplay');
            }
            else{
                e.classList.add('nodisplay');
            }
        });
    }

    function switch_form_to_subgroup_two(form,subgroup){
        form.props.showing_subgroup = subgroup;
        form.props.children.forEach(function(el){
            if(null===el || (el.props.className!=='ifso-standalone-condition-control-wrap' && el.props.className!=='ifso-standalone-condition-noticebox')) return;
            if(subgroup === el.props.subgroup){
                el.props.className = el.props.className.replace(' nodisplay','');
            }
            else if(el.props.subgroup){
                el.props.className += ' nodisplay'
            }
        });
    }

    function create_ifso_ui_select_options(optionsArr){
        var ret = [];
        if(optionsArr && optionsArr.length>0){
            optionsArr.forEach(function(opt){
                ret.push(el('option',{value:opt['value']},null,opt['display_value']));
            })
        }
        return ret;
    }

    function create_license_condition_message(condition){
        var ret = null;
        var get_license_url = 'https://www.if-so.com/plans/?utm_source=Plugin&utm_medium=direct&utm_campaign=getFree&utm_term=lockedConditon&utm_content=Gutenberg';
        if(!license_status_object['is_license_valid'] && !in_array(license_status_object['free_conditions'],condition)){
            ret = el('div',{error_message:'1',className:'ifso-stantalone-error-message'},null,[
                el('a',{href:get_license_url, target:'_blank'},'This condition is only available upon license activation. Click here to get a free license if you do not have one.')
            ]);
        }
        return ret;
    }

    function hard_reset_form(form){
        form.reset();
        jQuery(form).find(':input').each(function() {
            switch(this.type){
                case 'textarea':
                case 'text':
                    jQuery(this).val('');
            }
        });
    }

    function in_array(array,member){
        if(array.indexOf(member)===-1){
            return false;
        }
        return true;
    }

    function is_empty(obj) {
        for(var prop in obj) {
            if(obj.hasOwnProperty(prop)) {
                return false;
            }
        }
        return JSON.stringify(obj) === JSON.stringify({});
    }

    function create_noticebox(elObj){
        var closingX = (elObj['closeable']) ? el('span',{className:'closingX',onClick:function(e){e.target.parentElement.classList.add('nodisplay');}},'X') : null;
        var ret = el('div',{className:'ifso-standalone-condition-noticebox'},[el('p',{className:'notice-content',dangerouslySetInnerHTML:{__html:elObj['content']}}),closingX]);

        if(null!==elObj['subgroup']){
            ret.props.subgroup = elObj['subgroup'];
        }

        ret.props.style = {color:elObj['color'],backgroundColor:elObj['bgcolor'],border:'1px solid' +elObj['color']};

        return ret;
    }

    function create_audience_addrm_ui(props){
        if(data_rules_model['Groups']['fields']['group-name']['options'] ){
            var groupsList = data_rules_model['Groups']['fields']['group-name']['options'];

            var updateStatus = function(e){
                var statusType = (e.target.name === 'ifso-aud-add') ? 'add' : 'rm' ;
                var otherStatusType = (statusType==='add') ? 'rm' : 'add';
                var statusUpdate = jQuery(e.target.parentElement).find('input').serializeArray().map(function(val){return val['value']});
                var full_status = {};
                full_status[statusType] = statusUpdate;
                full_status[otherStatusType] = props.attributes.ifso_aud_addrm[otherStatusType] || [];

                props.setAttributes({ifso_aud_addrm:full_status});
            };

            var create_addrm_form = function(type='add'){
                var checkSelects = groupsList.map( function(val){return [el('input',{type:'checkbox',checked : (props.attributes.ifso_aud_addrm && props.attributes.ifso_aud_addrm !== null && !is_empty(props.attributes.ifso_aud_addrm) && Object.prototype.toString.call(props.attributes.ifso_aud_addrm[type])==='[object Array]' && in_array(props.attributes.ifso_aud_addrm[type],val['value'])), name:'ifso-aud-'+type,value:val['value'],onChange:updateStatus}),el('label',{},null,val['display_value']),el('br')] });
                var form = el('form',{className:'ifso-aud-addrm-form'},checkSelects);
                return form;
            };

            var aud_addrm_ui = el('div',{className:'ifso-aud-addrm-ui-wrap '+((is_empty(props.attributes.ifso_aud_addrm)) ? 'nodisplay' : '')}, (groupsList && groupsList.length > 0) ?
                [el('p',{},null,['Add or remove users from the following audiences when the version is displayed. ',el('a',{href:'https://www.if-so.com/help/documentation/segments/?utm_source=Plugin&utm_medium=Micro&utm_campaign=GutenbergGroups', target:'_blank'},'Learn More')]),
                    el('h4',{},null,'Add to audiences:'),create_addrm_form('add'), el('h4',{},null,'Remove from audiences:'),create_addrm_form('rm')]
                :
                el('p', {className: 'ifso-no-aud-error'}, 'You haven\'t created any audiences yet. ', el('a', {href: pages_links['gropus_page'], target: '_blank'}, 'Create an audience'), el('span', {},' (and refresh).')));


            return aud_addrm_ui;
        }
    }


    wp.hooks.addFilter( 'blocks.registerBlockType', 'ifso/ifso-standalone-conditions-block-filter', function(opts,name){
        opts.attributes = {
            ...opts.attributes,
            ifso_condition_type:{
                type:'string',
                default:''
            },
            ifso_condition_rules:{
                type:'object',
                default:{}
            },
            ifso_default_exists:{
                type:'boolean',
                default:false
            },
            ifso_default_content:{
                type:'string',
                default:''
            },
            ifso_aud_addrm: {
                type:'object',
                default: {}
            },
            ifso_render_with_ajax:{
                type:'boolean',
                default:false
            },
            ajax_loader_type:{
                type:'string',
                default:'same-as-global'
            }
        }

        return opts;
    } );

    var withIfSoSidebar = wp.compose.createHigherOrderComponent( function( BlockEdit ) {
        return function( props ) {
            var isOpen = (props.attributes.ifso_condition_type !== '');
            return el(
                wp.element.Fragment,
                {},
                //el('div', {className: 'ifso-block-wrap wp-block', style: {position: 'relative'}},
                    el(
                        BlockEdit,
                        props
                    ),
                  //  (isOpen) ? el('span', {className: 'ifso-has-standalone-marker'}, 'If',el('span',{style:{color:'#fd5b56'}},'\u2023'),'So active') : null
                //),
                el(wp.blockEditor.InspectorControls,
                    {},
                    el(
                        PanelBody,
                        {className:'ifso-condition-sidebar-wrap',initialOpen:isOpen,title:el('span',{className:'title'},iconEl,'Dynamic Content')},
                        el(PanelRow,{},create_sidebar_condition_ui_elements(props)),
                        ''
                    )
                )
            );
        };
    }, 'withIfSoSidebar' );

    var withIfsoBorder = wp.compose.createHigherOrderComponent( function( BlockListBlock ) {
        return function( props ) {
            var isOpen = (props.attributes.ifso_condition_type !== '');
            var newProps = lodash.assign( {}, props, {className: (isOpen) ? 'ifso-widget-inuse' : '',} );
            /*var newProps = {...props,wrapperProps: {...props.wrapperProps,'className': inuse_cname,},};*/
            return el( BlockListBlock, newProps );
        }
    }, 'withIfsoBorder' );

    wp.hooks.addFilter( 'editor.BlockEdit', 'ifso/ifso-standalone-conditions-block-filter-edit',withIfSoSidebar);
    wp.hooks.addFilter( 'editor.BlockListBlock', 'ifso/ifso-standalone-conditions-label-edit',withIfsoBorder);

} )( window.wp.blocks, window.wp.editor, window.wp.element );