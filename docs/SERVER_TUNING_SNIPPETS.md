# Server Tuning Snippets (Cloudways-Safe)

Date: 2025-11-02

Apply via Cloudways UI or custom includes where allowed. Validate with curl and the verification scripts after enabling.

## PHP OPcache (php.ini)
```
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=60
opcache.enable_file_override=1
realpath_cache_size=4096k
realpath_cache_ttl=600
```

## PHP-FPM (pool)
```
pm = dynamic
pm.max_children = 32
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 1000
```

## Nginx static cache headers
```
location ~* \.(?:css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ {
  expires 30d;
  add_header Cache-Control "public, max-age=2592000, immutable";
}
```

## Nginx gzip (or brotli if available)
```
gzip on;
gzip_comp_level 5;
gzip_min_length 1024;
gzip_types text/plain text/css application/javascript application/json image/svg+xml;
```

## Nginx micro-cache (optional)
```
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=microcache:10m inactive=60s;
map $request_method $bypass { default 0; POST 1; }
location / {
  proxy_cache microcache;
  proxy_cache_bypass $bypass;
  proxy_cache_valid 200 1s;
  proxy_cache_valid 301 404 10s;
}
```

## Apache (if needed) – .htaccess headers
```
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 30 days"
  ExpiresByType application/javascript "access plus 30 days"
  ExpiresByType image/jpeg "access plus 30 days"
  ExpiresByType image/png "access plus 30 days"
  ExpiresByType image/svg+xml "access plus 30 days"
</IfModule>
<IfModule mod_headers.c>
  Header set Cache-Control "public, max-age=2592000, immutable"
</IfModule>
```

Verification
- URL checks:
```bash
bash public_html/tools/verify/url_check.sh
```
- Response sizes (compression in effect):
```bash
curl -sS -H "Accept-Encoding: br,gzip" -I https://staff.vapeshed.co.nz | grep -iE "content-encoding|content-length"
```
