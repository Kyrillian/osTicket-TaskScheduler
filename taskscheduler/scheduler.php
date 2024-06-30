<?php
require_once INCLUDE_DIR . 'class.plugin.php';
require_once 'config.php';

class TaskSchedulerPlugin extends Plugin {
    var $config_class = 'TaskSchedulerConfig';
    private $lock_file;
    private $general_config;

    function bootstrap() {
        // Load the general configuration
        $this->general_config = $this->loadGeneralConfig();
        if (!$this->general_config) {
            error_log("General configuration is missing or incomplete, stopping execution.");
            return;
        }

        // Generate a unique lock file path for this instance
        $instance_id = $this->getId();
        $this->lock_file = sys_get_temp_dir() . '/task_scheduler_lock_' . md5($instance_id);

        // Check if the request has the X-API-Key header set
        if (!isset($_SERVER['HTTP_X_API_KEY'])) {
            // Try to acquire lock
            if ($this->acquireLock()) {
                $this->createScheduledTicket();
                $this->releaseLock();
            } else {
                error_log("Could not acquire lock, another process is running for instance: " . $instance_id);
            }
        }
    }

    private function loadGeneralConfig() {
        $config_path = __DIR__ . '/general_config.php';
        if (!file_exists($config_path)) {
            error_log("General configuration file not found: " . $config_path);
            return false;
        }

        $config = require $config_path;
        $required_keys = array('api_key', 'api_url', 'ticket_user', 'ticket_email');
        foreach ($required_keys as $key) {
            if (!isset($config[$key])) {
                error_log("Missing required configuration key: " . $key);
                return false;
            }
        }

        return $config;
    }

    private function acquireLock() {
        // Try to create a lock file
        if (file_exists($this->lock_file)) {
            // Check if the lock file is older than 10 seconds (to prevent stale locks)
            if (filemtime($this->lock_file) < (time() - 10)) {
                unlink($this->lock_file);
            } else {
                return false;
            }
        }
        // Create a new lock file
        return touch($this->lock_file);
    }

    private function releaseLock() {
        // Remove the lock file
        if (file_exists($this->lock_file)) {
            unlink($this->lock_file);
        }
    }

    function createScheduledTicket() {
        $config = $this->getConfig();
        $taskName = $config->get('task_name');
        $taskDescription = $config->get('task_description');
        $taskDate = $config->get('task_date');
        $taskInterval = $config->get('task_interval');
        $customIntervalDays = $config->get('custom_interval_days');

        $parsedTaskDate = date('Y-m-d', strtotime($taskDate));
        $currentDate = date('Y-m-d');

        if ($currentDate >= $parsedTaskDate) {
            if ($this->createTicket($taskName, $taskDescription, $config->get('sla'), $config->get('department'), $config->get('priority'))) {
                // Calculate the next task date based on the interval
                switch ($taskInterval) {
                    case 'daily':
                        $newTaskDate = date('Y-m-d', strtotime('+1 day'));
                        break;
                    case 'weekly':
                        $newTaskDate = date('Y-m-d', strtotime('+1 week'));
                        break;
                    case 'monthly':
                        $newTaskDate = date('Y-m-d', strtotime('+1 month'));
                        break;
                    case 'yearly':
                        $newTaskDate = date('Y-m-d', strtotime('+1 year'));
                        break;
                    case 'custom':
                        $newTaskDate = date('Y-m-d', strtotime("+$customIntervalDays days"));
                        break;
                    default:
                        $newTaskDate = $currentDate;
                }

                // Update the task date to the new calculated date
                $this->updateConfig('task_date', $newTaskDate);

                return true;
            } else {
                error_log('Ticket creation failed');
                return false;
            }
        }
    }

    function createTicket($taskName, $taskDescription, $slaId, $deptId, $priorityId) {
        $api_key = $this->general_config['api_key'];
        $api_url = $this->general_config['api_url'];
        $ticket_user = $this->general_config['ticket_user'];
        $ticket_email = $this->general_config['ticket_email'];

        $data = array(
            'name' => $ticket_user,
            'email' => $ticket_email,
            'subject' => $taskName,
            'message' => 'data:text/html,' . $taskDescription,
            'slaId' => $slaId,
            'deptId' => $deptId,
            'priorityId' => $priorityId,
        );

        $json_data = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-API-Key: ' . $api_key,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            error_log('cURL error: ' . $error);
            return false;
        }

        return $http_status == 201;
    }

    private function updateConfig($key, $value) {
        $config = $this->getConfig();
        $config->set($key, $value);
    }
}
?>
