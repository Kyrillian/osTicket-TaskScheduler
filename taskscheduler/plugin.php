<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');

return array(
    'id' => 'task-scheduler',
    'version' => '0.1',
            'ost_version' => '1.17', # Require osTicket 1.17+
    'name' => 'Task Scheduler',
    'author' => 'Kyrillian',
    'description' => 'A plugin to schedule recurring tasks in osTicket.
    
    Automatically created tickets based on a schedule using the osTicket API.
    Simply create an instance for each scheduled task.
    Documentation can be found at https://github.com/Kyrillian/osTicket-TaskScheduler',
    'url' => 'https://github.com/Kyrillian/osTicket-TaskScheduler',
    'plugin' => 'scheduler.php:TaskSchedulerPlugin',
);
?>
