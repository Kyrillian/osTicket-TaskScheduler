<?php
require_once INCLUDE_DIR . 'class.plugin.php';

class TaskSchedulerPluginConfig extends PluginConfig {
    function getOptions() {
        return array(
            'general' => new SectionBreakField(array(
                'label' => 'General Settings',
            )),
            'global_task_email' => new TextboxField(array(
                'label' => 'Global Task Email',
                'configuration' => array(
                    'size' => 40,
                    'length' => 100,
                ),
            )),
        );
    }
}
?>
