<?php
class TaskSchedulerConfig extends PluginConfig {
    function getOptions() {
        // Fetch SLA plans, departments, and priorities from osTicket
        $slaOptions = $this->getSLAOptions();
        $departmentOptions = $this->getDepartmentOptions();
        $priorityOptions = $this->getPriorityOptions();

        return array(
            'task_scheduler' => new SectionBreakField(array(
                'label' => 'Scheduled Task Settings',
            )),
            'task_name' => new TextboxField(array(
                'label' => 'Task Name',
                'configuration' => array(
                    'size' => 40,
                    'length' => 100,
                ),
            )),
            'task_description' => new TextareaField(array(
                'label' => 'Task Description',
                'configuration' => array(
                    'html' => true,
                    'rows' => 10,
                    'cols' => 40,
                    'class' => 'richtext draft draft-delete', // Add the CSS classes for richtext
                ),
            )),
            'task_date' => new DateTimeField(array(
                'label' => 'Task Date (YYYY-MM-DD)',
                'configuration' => array(
                    'size' => 20,
                    'length' => 20,
                ),
            )),
            'task_interval' => new ChoiceField(array(
                'label' => 'Task Interval',
                'choices' => array(
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly',
                    'custom' => 'Custom',
                ),
                'default' => 'daily',
            )),
            'custom_interval_days' => new TextboxField(array(
                'label' => 'Custom Interval (Days)',
                'configuration' => array(
                    'size' => 10,
                    'length' => 5,
                ),
                'required' => false,
            )),
            'sla' => new ChoiceField(array(
                'label' => 'SLA',
                'choices' => $slaOptions,
                'required' => true,
            )),
            'department' => new ChoiceField(array(
                'label' => 'Department',
                'choices' => $departmentOptions,
                'required' => true,
            )),
            'priority' => new ChoiceField(array(
                'label' => 'Priority',
                'choices' => $priorityOptions,
                'required' => true,
            )),
        );
    }

    private function getSLAOptions() {
        $options = array();
        $slas = SLA::objects();
        foreach ($slas as $sla) {
            if ($sla->flags % 2 == 1) {
                $options[$sla->getId()] = $sla->getName();
            }
        }
        return $options;
    }

    private function getDepartmentOptions() {
        $options = array();
        $departments = Dept::objects();
        foreach ($departments as $department) {
            $options[$department->getId()] = $department->getName();
        }
        return $options;
    }

    private function getPriorityOptions() {
        $options = array();
        $priorities = Priority::objects();
        foreach ($priorities as $priority) {
            $options[$priority->getId()] = $priority->getDesc();
        }
        return $options;
    }

    function pre_save(&$config, &$errors) {
        if (empty($config['task_name']) || empty($config['task_date']) || empty($config['task_interval'])) {
            $errors['err'] = 'Validation failed, "Task Name", "Task Date", and "Task Interval" are required fields.';
            return false;
        }

        if ($config['task_interval'] == 'custom' && empty($config['custom_interval_days'])) {
            $errors['err'] = 'Validation failed, "Custom Interval (Days)" is required when "Custom" interval is selected.';
            return false;
        }

        return true;
    }
}
?>
