# Performance Quick Wins (Low-Risk, High-Impact)

Date: 2025-11-02
Scope: IntelligenceHub (Cloudways PHP stack)
URLs referenced: https://staff.vapeshed.co.nz

This is a prioritized list of quick wins you can apply fast. Each item includes: what, why, how, and expected impact. All changes are Cloudways-safe and reversible.

## 1) Turn on and tune PHP OPcache (Critical)

- Why: Eliminates PHP file re-compilation; typical 20–50% CPU reduction on PHP-heavy pages.
- How (Cloudways → Server → Settings & Packages → PHP Settings):
  - opcache.enable=1
  - opcache.memory_consumption=256
  - opcache.interned_strings_buffer=16
  - opcache.max_accelerated_files=20000
  - opcache.validate_timestamps=1 (dev) or 0 (prod with controlled deploys)
  - opcache.revalidate_freq=60 (prod)
  - opcache.enable_file_override=1
- Expected: p95 latency down 20–35% on PHP endpoints.

## 2) FPM process manager right-size (Fast)

- Why: Avoids queueing or overcommit. Under/over-sized pools waste CPU/memory.
- How (Cloudways → Server → Settings & Packages → PHP-FPM Settings):
  - pm=dynamic
  - pm.max_children = (RAM_free_MB / 60) ≈ 20–40 typical
  - pm.start_servers = 2
  - pm.min_spare_servers = 2
  - pm.max_spare_servers = 8
  - pm.max_requests = 1000
- Expected: Fewer 502/504 under bursts; steadier RPS.

## 3) Static asset caching headers (Very Low Risk)

- Why: Let browsers cache CSS/JS/images aggressively; cuts repeat load times to near-zero.
- How: In Cloudways → Application → Application Settings → Varnish/Nginx, or custom Nginx include.
  - Cache static types for 30d–365d and add immutable when filenames are hashed.
  - Example (Nginx):
    ```
    location ~* \.(?:css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ {
      expires 30d;
      add_header Cache-Control "public, max-age=2592000, immutable";
    }
    ```
- Expected: Repeat views LCP ↓ 30–60%.

## 4) Brotli/Gzip compression (Toggle)

- Why: Shrinks transfer size for HTML/JSON/CSS/JS by 20–70%.
- How: Cloudways → Server → Nginx Settings → Enable gzip; enable brotli if available.
  - Ensure min types: text/html, application/json, text/css, application/javascript.
- Expected: Bandwidth ↓ 30–50%; faster TTFB on slow links.

## 5) Hot 404/redirect cleanup (Fast)

- Why: Avoid wasted work on missing URLs; reduce error noise.
- How: Run analyzer and add a small redirect map.
  - Run:
    ```bash
    bash public_html/tools/verify/access_summary.sh 8000
    ```
  - For top offenders, add redirects at application or Nginx level.
- Expected: Error rate ↓, CPU saved on repeated 404s.

## 6) Realpath and file system wins (No code changes)

- Why: Speed up file lookups for thousands of includes.
- How: PHP.ini
  - realpath_cache_size=4096k
  - realpath_cache_ttl=600
- Expected: Lower syscalls; 5–10% wall-time improvement on include-heavy pages.

## 7) Composer autoloader optimize (One-time per deploy)

- Why: Classmap optimization reduces autoload IO.
- How (on deploy):
  ```bash
  composer dump-autoload -o
  ```
- Expected: 5–10% faster bootstrap.

## 8) Session cost reduction (Safe)

- Why: Sessions serialize I/O; avoid on read-only pages.
- How: Ensure pages that don’t need sessions do not call session_start(); set:
  - session.use_strict_mode=1
  - session.lazy_write=1
  - session.cookie_samesite=Lax
- Expected: Less lock contention, better concurrency.

## 9) Micro-cache on error-prone endpoints (Optional)

- Why: Throttle spiky identical requests (e.g., bots hitting the same URL).
- How: Nginx micro-cache 1–5s for 200/404/301:
  ```
  proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=microcache:10m inactive=60s;
  map $request_method $bypass { default 0; POST 1; }
  location / {
    proxy_cache microcache;
    proxy_cache_bypass $bypass;
    proxy_cache_valid 200 1s; proxy_cache_valid 404 301 10s;
  }
  ```
- Expected: Big relief for bursty traffic; zero code change.

## 10) Observability hooks (Immediate)

- Why: Know what to tune next.
- How: Use provided scripts under `public_html/tools/verify/`:
  - URL health:
    ```bash
    bash public_html/tools/verify/url_check.sh
    ```
  - Access distribution + top 404s:
    ```bash
    bash public_html/tools/verify/access_summary.sh 8000
    ```
  - PHP slowlog buckets:
    ```bash
    bash public_html/tools/verify/slowlog_summary.sh
    ```
- Expected: Data-driven iterations within minutes.

---

Notes
- Production safety: All items are reversible via panel toggles or config revert. Take snapshots prior to server-level changes.
- Align with company targets: E-commerce API p95 < 500ms; page LCP < 2.5s.
