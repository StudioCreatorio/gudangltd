<?php
require_once(__DIR__ . '/ifso-custom-conditions.class.php');

class IfsoCustomConditionsInitializer{
    private $customConditionsObj;

    public function __construct(){
        $this->customConditionsObj = IfsoCustomConditions::get_instance();

        $this->add_actions();
        $this->add_filters();
    }

    private function add_actions(){
        add_action('ifso_custom_conditions_ui_selector',[$this,'print_selector_ui']);
        add_action('ifso_custom_conditions_ui_data_inputs',[$this,'print_data_inputs_ui'],10,2);
    }

    private function add_filters(){
        add_filter('ifso_data_rules_model_filter',[$this,'filter_data_rules_model']);
        add_filter('ifso_triggers_list_filter',[$this,'extend_triggers_list']);
        add_filter('ifso_custom_conditions_new_rule_data_extension',[$this,'filter_new_rule_data'],10,2);
        add_filter('ifso_custom_conditions_expand_data_reset_by_selector',[$this,'expand_ui_data_attributes']);
    }

    public function filter_data_rules_model($conditions){
        return array_merge($conditions,$this->customConditionsObj->data_rules_model_extension());
    }

    public function extend_triggers_list($triggers){
        return array_merge($triggers,$this->customConditionsObj->export_triggers());
    }

    public function filter_new_rule_data($data,$group_item){
        return array_merge($data,$this->customConditionsObj->new_rule_data_extension($group_item));
    }

    public function expand_ui_data_attributes($data){
        return array_merge($data,$this->customConditionsObj->get_new_triger_type_fields());
    }

    public function print_selector_ui($rule){
        require (__DIR__. '/markup/ifso-custom-conditions-selector-ui.php');
    }

    public function print_data_inputs_ui($rule,$current_version_index){
        require (__DIR__. '/markup/ifso-custom-conditions-data-ui.php');
    }

}