<?php
namespace IfSo\Extensions\CustomConditions\Triggers;
require_once IFSO_PLUGIN_SERVICES_BASE_DIR . 'triggers-service/triggers/trigger-base.class.php';
use IfSo\PublicFace\Services\TriggersService\Triggers as IfSoTriggers;

class numberOfViewsTrigger extends IfSoTriggers\TriggerBase{
    public function __construct() {
        parent::__construct('numberOfViews');
    }

    public function handle($trigger_data){
        $rule = $trigger_data->get_rule();
        $content = $trigger_data->get_content();
		$views_count = $rule['views'];
        $numberofviewsvalue=$rule['numberofviews-value'];
		if($views_count<$numberofviewsvalue){
		    return $content;
		}

        return false;
    }

}  

    