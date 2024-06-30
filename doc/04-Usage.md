# Usage

With everything set up according to this documentation, there isn't really any 'usage' aspect to this plugin.

Checks are performed everytime there is activity on the server on a per-instance basis. (e.g. when an agent navigates between the pages)
If a check finds that the `Task Date` of an instance is in the past, a ticket will be created according to the configuration of that instance.
Afterwards, the `Task Date` will be updated to a time in the future, corresponding to the `Task Interval` provided.

This plugin makes use of record locking to prevent near-simultaneous activity creating multiple tickets before `Task Date` is updated.