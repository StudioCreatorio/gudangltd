<?php
require_once __DIR__ . '/triggers/numberofviews-trigger.class.php';

class IfsoCustomConditions{
    private static $instance;

    private function __construct(){}

    public static function get_instance() {
        if ( NULL == self::$instance )
            self::$instance = new IfsoCustomConditions();

        return self::$instance;
    }

    public function export_triggers(){
        $export = [];

        $export[] = new \IfSo\Extensions\CustomConditions\Triggers\numberOfViewsTrigger();

        return $export;
    }

    public function data_rules_model_extension(){
        $condtions = [
            'numberOfViews' => ['numberofviews-value']
        ];

        return $condtions;
    }

    public function new_rule_data_extension($group_item){
        $newModel = $this->data_rules_model_extension();
        $ret = [];
        foreach($newModel as $dataPoint => $dataVal){
            if(is_array($dataVal)){
                foreach($dataVal as $subDataVal){
                    $ret[$subDataVal] = $group_item[$subDataVal];
                }
            }
        }
        return $ret;
    }

    public function get_new_triger_type_fields(){
        $rules = $this->data_rules_model_extension();
        $ret = [];
        foreach($rules as $rule){
            if(is_array($rule)){
                foreach($rule as $datafield){
                    $ret[] = $datafield;
                }
            }
        }

        return $ret;
    }


}