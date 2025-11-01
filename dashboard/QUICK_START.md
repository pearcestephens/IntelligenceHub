# 🚀 QUICK START GUIDE - Admin Dashboard

## ⚡ TL;DR - Get Started in 30 Seconds

### 1. **Access the Dashboard**
```
https://your-domain.com/dashboard/admin/
```

### 2. **View Pages**
- **Overview** → `?page=overview` (Main dashboard)
- **Files** → `?page=files` (File browser)
- **Dependencies** → `?page=dependencies` (Dependency view)
- **Violations** → `?page=violations` (Rule violations)
- **Rules** → `?page=rules` (Coding standards)
- **Metrics** → `?page=metrics` (Analytics)

### 3. **Call APIs from JavaScript**
```javascript
// Get data
API.get('/dashboard/api/projects/get?id=1', (data) => {
    console.log(data.data);
});

// Post data
API.post('/dashboard/api/scan/run', {project_id: 1}, (data) => {
    Notify.success('Scan started!');
});
```

### 4. **Show Notifications**
```javascript
Notify.success('Done!');
Notify.error('Failed!');
Notify.warning('Warning!');
Notify.info('Info!');
```

---

## 🎯 ADD NEW THINGS FAST

### Add New Page
```bash
# 1. Create the file
cat > pages/my-page.php << 'EOF'
<div class="page-header"><h1>My Page</h1></div>
<div class="card"><div class="card-body">Hello!</div></div>
EOF

# 2. Access it
# https://domain.com/dashboard/admin/?page=my-page
# ✅ DONE! CSS and JS auto-loaded
```

### Add New CSS
```bash
# Create file (keep 01-99 naming)
touch assets/css/11-my-styles.css

# ✅ Auto-loaded, no router changes needed
```

### Add New JavaScript
```bash
# Create file (keep 01-99 naming)
touch assets/js/11-my-functions.js

# ✅ Auto-loaded, can use all previous modules
```

### Add New API Endpoint
```bash
# Create file
mkdir -p api/my-feature
cat > api/my-feature/get.php << 'EOF'
<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");
    $stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
EOF

# Call from JS
API.get('/dashboard/api/my-feature/get?id=1', (data) => {
    console.log(data.data);
});
```

---

## 📚 USEFUL JAVASCRIPT FUNCTIONS

### API Module
```javascript
// GET request
API.get('/dashboard/api/endpoint', callback);

// POST request
API.post('/dashboard/api/endpoint', {key: 'value'}, callback);

// PUT request
API.put('/dashboard/api/endpoint', {id: 1, key: 'value'}, callback);

// DELETE request
API.delete('/dashboard/api/endpoint?id=1', callback);
```

### Notifications
```javascript
Notify.success('Operation successful');
Notify.error('Something went wrong');
Notify.warning('Please be careful');
Notify.info('Here is some information');
Notify.queue([
    {type: 'success', message: 'First'},
    {type: 'error', message: 'Then'},
    {type: 'info', message: 'Finally'}
]);
```

### Storage
```javascript
// Set data
Storage.set('key', {data: 'value'}, 'local'); // or 'session'

// Get data
const data = Storage.get('key', 'local');

// Remove
Storage.remove('key', 'local');

// Clear all
Storage.clear('local');
```

### Forms
```javascript
// Validate required field
Forms.validateRequired(field);

// Validate email
Forms.validateEmail(email);

// Validate pattern
Forms.validatePattern(value, /regex/);

// Validate form
Forms.validate(formElement);

// Submit form
Forms.submit(formElement, '/api/endpoint');
```

### Tables
```javascript
// Initialize table
Tables.init('.my-table');

// Sort
Tables.sort('.my-table', 'column-name');

// Filter
Tables.filter('.my-table', 'search-term');

// Paginate
Tables.paginate('.my-table', 20); // 20 per page
```

### Modals
```javascript
// Show modal
Modal.show('#myModal');

// Hide modal
Modal.hide('#myModal');

// Confirm dialog
Modal.confirm('Are you sure?', (ok) => {
    if (ok) { /* yes */ }
});

// Alert dialog
Modal.alert('Alert message', () => {
    console.log('closed');
});
```

### Utilities
```javascript
// Format bytes
Utils.formatBytes(1024); // "1.0 KB"

// Format date
Utils.formatDate(new Date()); // "Oct 31, 2025"

// Throttle function
const throttled = Utils.throttle(myFunc, 300);

// Debounce function
const debounced = Utils.debounce(myFunc, 500);

// Deep clone object
const cloned = Utils.deepClone({a: {b: 'c'}});
```

---

## 📊 COMMON TASKS

### Display Data in a Table
```php
<?php
$pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");
$stmt = $pdo->query("SELECT * FROM table LIMIT 20");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="table">
  <thead class="table-light">
    <tr><th>Column</th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr><td><?php echo htmlspecialchars($row['column']); ?></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>
```

### Create a Metric Card
```php
<div class="col-md-3">
  <div class="card metric-card">
    <div class="card-body">
      <div class="metric-icon bg-primary">
        <i class="fas fa-chart"></i>
      </div>
      <h6 class="text-muted">METRIC LABEL</h6>
      <h3><?php echo $value; ?></h3>
    </div>
  </div>
</div>
```

### Create a Chart
```javascript
const ctx = document.getElementById('myChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
            label: 'Data',
            data: [10, 20, 30],
            borderColor: '#4e73df'
        }]
    }
});
```

### Filter and Search
```html
<input type="text" id="search" placeholder="Search...">
<select id="filter">
  <option value="">All</option>
  <option value="active">Active</option>
</select>

<script>
document.getElementById('search').addEventListener('keyup', function() {
    const term = this.value;
    // Filter table rows...
});
</script>
```

---

## 🎨 CUSTOMIZE COLORS

Edit `assets/css/01-base.css`:
```css
:root {
    --primary:    #4e73df;     /* Change this */
    --success:    #1cc88a;     /* And this */
    --warning:    #f6c23e;     /* And this */
    --danger:     #e74c3c;     /* And this */
    /* ... rest of colors ... */
}
```

All components automatically update!

---

## 🔍 DEBUG

### Check JavaScript Errors
```javascript
// In browser console
console.log(API);  // Should show API object
console.log(Notify);  // Should show Notify object
console.log(Storage);  // Should show Storage object
```

### Check PHP Errors
```bash
# View PHP error log
tail -100 /home/master/applications/hdgwrzntwa/public_html/logs/apache_*.error.log
```

### Test API Endpoint
```bash
# Test GET
curl https://domain.com/dashboard/admin/api/projects/get?id=1

# Test POST
curl -X POST https://domain.com/dashboard/admin/api/scan/run \
  -d "project_id=1"
```

---

## 🚀 PRODUCTION DEPLOYMENT

### Before Going Live
1. ✅ Test all pages load without errors
2. ✅ Verify database connections work
3. ✅ Check all API endpoints respond
4. ✅ Test with real data
5. ✅ Verify file permissions (755 for dirs, 644 for files)
6. ✅ Enable HTTPS (required for production)
7. ✅ Add SSL certificate
8. ✅ Set up backups

### Production Checklist
```bash
# Set proper permissions
chmod 755 /dashboard/admin
chmod 755 /dashboard/admin/assets
chmod 755 /dashboard/admin/assets/*
chmod 644 /dashboard/admin/*.php
chmod 644 /dashboard/admin/assets/css/*.css
chmod 644 /dashboard/admin/assets/js/*.js

# Create log directory
mkdir -p /dashboard/admin/logs
chmod 755 /dashboard/admin/logs

# Test access
curl -I https://your-domain.com/dashboard/admin/
```

---

## 📞 QUICK HELP

**Page not showing?**
- Check `?page=name` parameter spelling
- Check pages/ directory has file `name.php`
- Check PHP errors in log

**Styles not loading?**
- Check assets/css/ files exist
- Check filename matches 01-*.css pattern
- Check browser cache (Ctrl+Shift+Delete)

**JavaScript errors?**
- Open browser console (F12)
- Check for red error messages
- Check network tab for 404s

**Database errors?**
- Check database credentials in code
- Check MySQL connection
- Check table names and columns

**API not working?**
- Test with curl command (see above)
- Check api/ directory exists
- Check endpoint file exists
- Check PHP syntax (php -l file.php)

---

## 🎓 LEARN MORE

- Full build documentation: `DASHBOARD_BUILD_COMPLETE.md`
- CSS reference: Each file has comments
- JS module reference: Each file has JSDoc comments
- API docs: Each endpoint has comments

---

## 🎉 YOU'RE READY!

Start building! The dashboard is:
- ✅ Production ready
- ✅ Fully featured
- ✅ Easily customizable
- ✅ Well documented
- ✅ Performance optimized

Happy building! 🚀

---

**Dashboard Location:** `/dashboard/admin/`
**Access URL:** `https://your-domain.com/dashboard/admin/`
**Build Date:** October 31, 2025
**Status:** ✅ Production Ready
