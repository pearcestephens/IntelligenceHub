#!/bin/bash
# One-liner to re-test the entire Smart Cron system after fixes

cd /home/master/applications/hdgwrzntwa/public_html/assets/services/cron && php MASTER_AUTONOMOUS_EXECUTOR.php
