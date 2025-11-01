#!/bin/bash

##############################################################################
# Smart Cron Job: Tool Governance Weekly Audit
##############################################################################
# Category: maintenance
# Schedule: Weekly (Monday 3 AM)
# Priority: MEDIUM
# Resource: Low CPU, Low Memory
# Estimated Duration: 2-5 minutes
##############################################################################

# Job Configuration (for Smart Cron discovery)
# @schedule: 0 3 * * 1
# @priority: MEDIUM
# @max_concurrent: 1
# @timeout: 600
# @retry_on_failure: true
# @max_retries: 2
# @resource_check: true
# @description: Weekly tool audit - discovers all tools, detects duplicates, maintains registry

# Execute the main tool governance script
exec /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh
