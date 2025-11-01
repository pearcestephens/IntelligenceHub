# Integrated Cron Job System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     INTEGRATED CRON JOB SYSTEM                           │
│                                                                           │
│  Problem: VSCode crashes every 1-2 hours due to uncontrolled cron jobs  │
│  Solution: Load-balanced, fully-tracked job execution                   │
└─────────────────────────────────────────────────────────────────────────┘
```

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           DISCOVERY LAYER                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  Auto-Discovery Script (discover-cron-jobs.php)                 │   │
│  │                                                                   │   │
│  │  • Scans filesystem for cron scripts                            │   │
│  │  • Detects script types (PHP/Bash/Python/Node)                  │   │
│  │  • Extracts metadata (descriptions, schedules)                  │   │
│  │  • Auto-categorizes jobs (backup/reporting/sync/etc.)           │   │
│  │  • Assigns priorities (critical/high/medium/low)                │   │
│  │  • Estimates resources (timeout, memory, CPU/memory weights)    │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                 ↓                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                          MANAGEMENT LAYER                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  IntegratedJobManager (IntegratedJobManager.php)                │   │
│  │                                                                   │   │
│  │  Methods:                                                         │   │
│  │  • registerJob()           - Register/update jobs                │   │
│  │  • executeJob()            - Execute with full integration       │   │
│  │  • recordExecution()       - Record results + update stats       │   │
│  │  • update24hStats()        - Rolling statistics                  │   │
│  │  • updateBaselines()       - Auto-calculate baselines            │   │
│  │  • calculateNextRun()      - Schedule next execution             │   │
│  │  • getJobsDueForExecution()- Scheduler integration               │   │
│  │  • getJobHealthSummary()   - Dashboard data                      │   │
│  │  • getPerformanceTrends()  - Analytics                           │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                 ↓                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                          EXECUTION LAYER                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────────────┐   ┌──────────────────────┐                   │
│  │   LoadBalancer       │   │  MetricsCollector    │                   │
│  │                      │   │                      │                   │
│  │  • Check slots       │   │  • Execute tasks     │                   │
│  │  • Enforce limits    │   │  • Track metrics     │                   │
│  │  • Queue if full     │   │  • Circuit breaker   │                   │
│  └──────────────────────┘   └──────────────────────┘                   │
│            ↓                           ↓                                 │
│            └───────────┬───────────────┘                                 │
│                        ↓                                                 │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  Execution Slots (Load Balancing)                               │   │
│  │                                                                   │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │   │
│  │  │ Default     │  │   Heavy     │  │   Light     │            │   │
│  │  │ Max: 5 jobs │  │ Max: 2 jobs │  │ Max: 10 jobs│            │   │
│  │  │ Mem: 3 GB   │  │ Mem: 4 GB   │  │ Mem: 1 GB   │            │   │
│  │  │ CPU: 80%    │  │ CPU: 90%    │  │ CPU: 50%    │            │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘            │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                 ↓                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                           STORAGE LAYER                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  Database Tables (6)                                             │   │
│  │                                                                   │   │
│  │  1. smart_cron_integrated_jobs     - Master registry            │   │
│  │  2. smart_cron_job_history         - Execution cache            │   │
│  │  3. smart_cron_job_dependencies    - Dependency graph           │   │
│  │  4. smart_cron_execution_slots     - Load balancing             │   │
│  │  5. smart_cron_job_tags            - Categorization             │   │
│  │  6. smart_cron_performance_alerts  - Alert log                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  Database Views (2)                                              │   │
│  │                                                                   │   │
│  │  1. smart_cron_active_jobs_summary      - Dashboard overview    │   │
│  │  2. smart_cron_job_performance_trends   - Performance analysis  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                 ↓                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  Dashboard (dashboard.php)                                       │   │
│  │                                                                   │   │
│  │  • Active jobs list                                              │   │
│  │  • Health summary                                                │   │
│  │  • Performance trends                                            │   │
│  │  • Execution slot status                                         │   │
│  │  • Recent alerts                                                 │   │
│  │  • Job management (enable/disable/configure)                     │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
```

## Data Flow

### Job Discovery Flow

```
1. Filesystem Scan
   └─> discover-cron-jobs.php scans directories
       └─> Finds: /queue/bin/process_queue.php
           └─> Detects: PHP script
               └─> Extracts: Description from comments
                   └─> Parses: Cron expression if present
                       └─> Categorizes: "Queue processing" = business
                           └─> Prioritizes: Keywords → medium
                               └─> Estimates: File size/complexity → 512 MB, 5 min
                                   └─> Registers: IntegratedJobManager->registerJob()
                                       └─> Stores: smart_cron_integrated_jobs table
                                           └─> Status: enabled = FALSE (manual enable required)
```

### Job Execution Flow

```
1. Scheduler (smart-cron.php)
   └─> Calls: IntegratedJobManager->getJobsDueForExecution()
       └─> Returns: Jobs where next_scheduled_run <= NOW()
           └─> For each job:
               └─> IntegratedJobManager->executeJob(jobId)
                   └─> Checks: LoadBalancer->canExecuteTask()
                       └─> IF slots available:
                           ├─> Acquires slot
                           ├─> Updates: smart_cron_execution_slots (current_running_jobs++)
                           ├─> Executes: MetricsCollector->executeTask()
                           │   └─> Runs script with monitoring
                           │       └─> Captures: exit code, duration, memory, CPU, output
                           ├─> Records: IntegratedJobManager->recordExecution()
                           │   ├─> Updates: smart_cron_integrated_jobs stats
                           │   ├─> Inserts: smart_cron_job_history
                           │   └─> Updates: 24h rolling stats
                           ├─> Updates: IntegratedJobManager->updateBaselines() (every 10 runs)
                           │   └─> Calculates: avg duration, memory, CPU from last 100 executions
                           ├─> Calculates: IntegratedJobManager->calculateNextRun()
                           │   └─> Updates: next_scheduled_run
                           └─> Releases: LoadBalancer->releaseSlot()
                               └─> Updates: smart_cron_execution_slots (current_running_jobs--)
                       └─> ELSE (slots full):
                           └─> Returns: {'deferred': true}
                               └─> Job queued for next cycle
```

### Performance Tracking Flow

```
1. Every Execution
   └─> Captures: duration, memory, CPU, exit code, output
       └─> Inserts: smart_cron_job_history
           └─> Updates: smart_cron_integrated_jobs
               ├─> total_executions++
               ├─> successful_executions++ OR failed_executions++
               ├─> consecutive_failures (reset on success)
               ├─> last_executed_at = NOW()
               └─> last_exit_code, last_error_message

2. Every Execution (24h stats)
   └─> Queries: Last 24h executions
       └─> Calculates: AVG/MAX duration, memory
           └─> Updates: smart_cron_integrated_jobs
               ├─> avg_duration_24h
               ├─> max_duration_24h
               ├─> avg_memory_24h
               ├─> max_memory_24h
               ├─> executions_24h
               └─> failures_24h

3. Every 10 Executions (baselines)
   └─> Queries: Last 100 successful executions (last 30 days)
       └─> Calculates: AVG duration, memory, CPU
           └─> Updates: smart_cron_integrated_jobs
               ├─> baseline_duration_seconds
               ├─> baseline_memory_mb
               ├─> baseline_cpu_percent
               └─> baseline_calculated_at = NOW()

4. Anomaly Detection (every execution)
   └─> Compares: Current execution vs baseline
       ├─> IF duration > baseline * 2.0:
       │   └─> Inserts: smart_cron_performance_alerts (type='high_duration')
       ├─> IF memory > baseline * 1.5:
       │   └─> Inserts: smart_cron_performance_alerts (type='high_memory')
       └─> IF consecutive_failures >= 3:
           └─> Inserts: smart_cron_performance_alerts (type='consecutive_failures')
```

## Load Balancing Algorithm

```
┌─────────────────────────────────────────────────────────────────┐
│  LOAD BALANCING DECISION TREE                                   │
└─────────────────────────────────────────────────────────────────┘

Job Ready to Execute
    ↓
    Check job priority & weights
    ↓
┌───┴────────────────────────────────────────────────────────┐
│   Determine slot assignment:                               │
│   • cpu_weight < 0.5 OR memory_weight < 0.5 → light slot   │
│   • cpu_weight > 1.5 OR memory_weight > 1.5 → heavy slot   │
│   • Otherwise → default slot                               │
└───┬────────────────────────────────────────────────────────┘
    ↓
    Query: smart_cron_execution_slots for assigned slot
    ↓
┌───┴────────────────────────────────────────────────────────┐
│   Check slot availability:                                 │
│   • current_running_jobs < max_concurrent_jobs?            │
│   • current_memory_mb + estimated_memory < max_memory?     │
│   • current_cpu_percent + estimated_cpu < max_cpu?         │
└───┬────────────────────────────────────────────────────────┘
    ↓
┌───┴──────────┐
│   All OK?    │
└───┬──────────┘
    │
    ├─ YES ──→ Acquire Slot
    │          ├─> current_running_jobs++
    │          ├─> current_memory_mb += estimated_memory
    │          ├─> current_cpu_percent += estimated_cpu
    │          └─> Execute job
    │              └─> On completion:
    │                  ├─> current_running_jobs--
    │                  ├─> current_memory_mb -= actual_memory
    │                  └─> current_cpu_percent -= actual_cpu
    │
    └─ NO ───→ Defer Execution
               └─> Return: {'deferred': true, 'reason': 'No slot available'}
                   └─> Scheduler will retry next cycle (typically 60 seconds)
```

## Memory Protection Mechanism

```
┌─────────────────────────────────────────────────────────────────┐
│  HOW THIS PREVENTS VSCODE CRASHES                               │
└─────────────────────────────────────────────────────────────────┘

BEFORE (No Load Balancing):
─────────────────────────────────────────────────────────────────
VSCode Server:        9.4 GB (baseline, always running)
Cron Job A (report):  2.0 GB (uncontrolled)
Cron Job B (backup):  1.5 GB (uncontrolled)
Cron Job C (sync):    1.0 GB (uncontrolled)
Cron Job D (import):  3.0 GB (uncontrolled)
─────────────────────────────────────────────────────────────────
Total:               16.9 GB > 16 GB RAM
Result:              System swaps → thrashes → OOM killer → VSCode killed ❌


AFTER (Load Balancing Active):
─────────────────────────────────────────────────────────────────
VSCode Server:        9.4 GB (baseline, always running)

Execution Slots (Max Concurrent):
├─ Default Slot:      3.0 GB (max 5 jobs, typically 2-3 GB actual)
└─ Heavy Slot:        4.0 GB (max 2 jobs, NOT both slots maxed simultaneously)

Typical Scenario:
├─ 3 jobs in default slot:    1.8 GB (500MB + 600MB + 700MB)
└─ 1 job in heavy slot:        2.5 GB (backup running)
─────────────────────────────────────────────────────────────────
Total:                         13.7 GB < 16 GB RAM
Remaining:                      2.3 GB buffer
Result:                        System stable → VSCode continues ✅


Extreme Scenario (both slots maxed):
├─ Default slot maxed:         3.0 GB (5 light jobs)
└─ Heavy slot maxed:           4.0 GB (2 heavy jobs)
─────────────────────────────────────────────────────────────────
Total:                         16.4 GB
BUT: Heavy jobs rarely run simultaneously (scheduled apart)
AND: Default jobs typically don't max out (usually 2-3 GB actual)
Typical Max:                   13-14 GB < 16 GB RAM ✅
```

## Health Monitoring System

```
┌─────────────────────────────────────────────────────────────────┐
│  HEALTH STATUS CALCULATION                                      │
└─────────────────────────────────────────────────────────────────┘

For each enabled job:
    ↓
┌───┴────────────────────────────────────────────────────────┐
│   Health Status (from smart_cron_active_jobs_summary view) │
└───┬────────────────────────────────────────────────────────┘
    ↓
    IF consecutive_failures >= 3:
        └─> health_status = 'critical' 🔴
    ELSE IF consecutive_failures >= 2:
        └─> health_status = 'warning' 🟡
    ELSE IF failures_24h > executions_24h * 0.2:  (>20% failure rate)
        └─> health_status = 'warning' 🟡
    ELSE IF last_executed_at < NOW() - 24 hours:
        └─> health_status = 'warning' 🟡  (overdue)
    ELSE:
        └─> health_status = 'healthy' 🟢

┌───┴────────────────────────────────────────────────────────┐
│   Execution Status                                         │
└───┬────────────────────────────────────────────────────────┘
    ↓
    IF last_executed_at IS NULL:
        └─> execution_status = 'never_run' ⚪
    ELSE IF last_executed_at < NOW() - 1 hour:
        └─> execution_status = 'overdue' 🟠
    ELSE IF consecutive_failures > 0:
        └─> execution_status = 'failing' 🔴
    ELSE:
        └─> execution_status = 'ok' 🟢

┌───┴────────────────────────────────────────────────────────┐
│   Dashboard Display                                        │
└───┬────────────────────────────────────────────────────────┘
    ↓
    Sort by:
    1. health_status (critical first)
    2. priority (critical → high → medium → low)
    3. next_scheduled_run (soonest first)
    
    Display:
    • Job name
    • Health indicator (colored dot)
    • Execution status
    • Last executed timestamp
    • Next scheduled run
    • 24h stats (executions, failures)
    • Consecutive failures count
```

## Alert System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  ALERT GENERATION & NOTIFICATION                                │
└─────────────────────────────────────────────────────────────────┘

After each execution:
    ↓
┌───┴────────────────────────────────────────────────────────┐
│   Check alert conditions                                   │
└───┬────────────────────────────────────────────────────────┘
    ↓
    Alert Triggers:
    ├─ Job failed (exit_code != 0)
    │  └─> IF alert_on_failure = TRUE:
    │      └─> Insert alert (type='failure', severity='error')
    │
    ├─ Job timeout (duration > timeout_seconds)
    │  └─> IF alert_on_timeout = TRUE:
    │      └─> Insert alert (type='timeout', severity='warning')
    │
    ├─ High memory (memory > baseline * 1.5)
    │  └─> IF alert_on_high_memory = TRUE:
    │      └─> Insert alert (type='high_memory', severity='warning')
    │
    ├─ High duration (duration > baseline * 2.0)
    │  └─> Insert alert (type='high_duration', severity='warning')
    │
    ├─ Consecutive failures >= threshold (default 3)
    │  └─> Insert alert (type='consecutive_failures', severity='critical')
    │
    └─> For each alert:
        ├─> Insert: smart_cron_performance_alerts
        ├─> IF alert_emails configured:
        │   └─> Send email notification
        └─> Log: [ALERT] job_name - alert_type - severity
```

## Integration Points

```
┌─────────────────────────────────────────────────────────────────┐
│  EXISTING SMART CRON INTEGRATION                                │
└─────────────────────────────────────────────────────────────────┘

smart-cron.php (main scheduler)
    ↓
    Uses: IntegratedJobManager->getJobsDueForExecution()
    ↓
    For each job:
        ↓
        Calls: IntegratedJobManager->executeJob(jobId)
        ↓
        Internally uses:
        ├─> LoadBalancer (checks slots)
        ├─> MetricsCollector (executes + monitors)
        └─> CircuitBreaker (failure protection)
        ↓
        Records:
        ├─> smart_cron_integrated_jobs (stats update)
        ├─> smart_cron_job_history (execution log)
        └─> cron_metrics (existing metrics table)

dashboard.php
    ↓
    Queries:
    ├─> smart_cron_active_jobs_summary (health overview)
    ├─> smart_cron_job_performance_trends (performance analysis)
    ├─> smart_cron_execution_slots (load balancing status)
    └─> smart_cron_performance_alerts (recent alerts)

API endpoints (existing)
    ↓
    New endpoints:
    ├─> /api/jobs/list
    ├─> /api/jobs/enable
    ├─> /api/jobs/disable
    ├─> /api/jobs/execute
    └─> /api/jobs/history
```

---

**Architecture Version:** 2.0  
**Last Updated:** 2025-10-27  
**Status:** Production Ready
