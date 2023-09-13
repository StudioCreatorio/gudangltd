<?php

namespace WPCodeBox\ConditionBuilder;


use WPCodeBox\ConditionBuilder\Condition\Group;

class ConditionBuilder
{

    /** @var Group[] */
    private $condition_groups = [];

    public function __construct($code)
    {
        $wordpress_context = new WordPressContext();
        $condition_factory = new ConditionFactory($wordpress_context);

		$decodedCode = json_decode($code, true);

		if(is_array($decodedCode)) {
			$code = $decodedCode;
		}

		foreach($code as $condition_group_data) {
            $group = new Group($wordpress_context, new ConditionData('GROUP_DATA0','GROUP_DATA','GROUP_DATA'));

            foreach ($condition_group_data['conditions'] as $condition) {

                $condition = $condition_factory->create_condition($condition['conditionTitle'], new ConditionData($condition['conditionVerb'], $condition['extraData'], $condition['extraData2']));
                $group->addCondition($condition);

            }

            $this->add_group($group);
        }
    }

    public function add_group(Group $condition_group)
    {
        $this->condition_groups[] = $condition_group;
    }

    public function is_satisfied()
    {
        foreach($this->condition_groups as $condition_group) {
            if($condition_group->is_satisfied()) {
                return true;
            }
         }

         return false;
    }
}
