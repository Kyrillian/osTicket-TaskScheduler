# Installation

Follow these steps to install the Task Scheduler Plugin for osTicket.

## Download the Plugin
Download the latest version of the Task Scheduler Plugin from the [releases page](https://github.com/Kyrillian/osTicket-TaskScheduler/releases/latest).

## Copy the `taskscheduler` directory into the `include/plugins`  directory
Self explanatory. Just copy the entire `taskscheduler` directory with all it's contents over.

## Prepare `general_config.php`
1. Rename `general_config.tpl.php` to `general_config.php`
2. Enter the required information
3. Save the changes

## Enable the Plugin
1. Log in to the osTicket admin panel.
2. Navigate to `Admin Panel` > `Manage` > `Plugins`.
3. Click on `Add New Plugin` and select `Task Scheduler`.
4. Click `Install` and then `Enable` the plugin.

<img src="img/03-plugin_installed.png" alt="plugin installed" width="600"/>

# Continue with Step 3:
[Configuration](03-Configuration.md)
