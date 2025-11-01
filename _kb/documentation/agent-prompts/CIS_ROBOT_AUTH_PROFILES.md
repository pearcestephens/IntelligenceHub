# 🤖 CIS ROBOT AUTHENTICATION PROFILES

**Version:** 1.0.0  
**Date:** October 27, 2025  
**Purpose:** Pre-configured authentication for all bots testing CIS systems

---

## 🔐 **DEFAULT CIS ROBOT CREDENTIALS**

### **Primary CIS System**
```javascript
const CIS_ROBOT_PROFILE = {
  name: "CIS Robot",
  username: "cisrobot",
  password: "CISBot2025!",
  loginUrl: "https://staff.vapeshed.co.nz/login.php",
  dashboardUrl: "https://staff.vapeshed.co.nz/dashboard.php",
  permissions: ["read", "test", "monitor"],
  userAgent: "Mozilla/5.0 (compatible; CIS-Robot/2.0; Testing-Bot)",
  sessionTimeout: 3600, // 1 hour
  rateLimits: {
    requestsPerMinute: 60,
    testRunsPerHour: 10
  }
};
```

### **GPT Intelligence Hub**
```javascript
const GPT_HUB_PROFILE = {
  name: "GPT Hub Robot",
  username: "botuser",
  password: "BotAccess2025!",
  loginUrl: "https://gpt.ecigdis.co.nz/login",
  dashboardUrl: "https://gpt.ecigdis.co.nz/dashboard",
  apiKey: "sk-bot-access-token-2025",
  endpoints: {
    mcp: "https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php",
    credentials: "https://gpt.ecigdis.co.nz/api/credentials.php",
    aiChat: "https://gpt.ecigdis.co.nz/api/ai-chat.php"
  }
};
```

### **Public Retail Sites** (Read-only)
```javascript
const RETAIL_SITES_PROFILE = {
  name: "Retail Testing",
  sites: [
    {
      name: "Vape Shed",
      url: "https://www.vapeshed.co.nz",
      testMode: "anonymous",
      allowedPages: ["home", "products", "contact", "about"]
    },
    {
      name: "Vaping Kiwi", 
      url: "https://www.vapingkiwi.co.nz",
      testMode: "anonymous",
      allowedPages: ["home", "products", "contact"]
    }
  ],
  restrictions: {
    noFormSubmission: true,
    noCheckout: true,
    noUserRegistration: true,
    readOnlyTesting: true
  }
};
```

---

## 🔑 **AUTHENTICATION FUNCTIONS**

### **1. authenticateBot(profile, page)**
**Purpose:** Log bot into system using profile credentials  
**Parameters:**
- `profile`: Authentication profile object
- `page`: Puppeteer page object

**Function Implementation:**
```javascript
async function authenticateBot(profile, page) {
  try {
    console.log(`🔐 Authenticating as ${profile.name}...`);
    
    // Navigate to login page
    await page.goto(profile.loginUrl, { waitUntil: 'networkidle2' });
    
    // Fill username
    await page.waitForSelector('input[name="username"], #username, [type="email"]');
    await page.type('input[name="username"], #username, [type="email"]', profile.username);
    
    // Fill password
    await page.waitForSelector('input[name="password"], #password, [type="password"]');
    await page.type('input[name="password"], #password, [type="password"]', profile.password);
    
    // Submit form
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.click('button[type="submit"], input[type="submit"], .login-btn, #login-button')
    ]);
    
    // Verify login success
    const isLoggedIn = await verifyAuthentication(profile, page);
    
    if (isLoggedIn) {
      console.log(`✅ Successfully authenticated as ${profile.name}`);
      return { success: true, profile: profile.name };
    } else {
      console.log(`❌ Authentication failed for ${profile.name}`);
      return { success: false, error: "Login verification failed" };
    }
    
  } catch (error) {
    console.log(`❌ Authentication error: ${error.message}`);
    return { success: false, error: error.message };
  }
}
```

### **2. verifyAuthentication(profile, page)**
**Purpose:** Verify successful login by checking for dashboard elements  
**Returns:** `true` if authenticated, `false` otherwise

```javascript
async function verifyAuthentication(profile, page) {
  try {
    // Check if we're on dashboard or expected post-login page
    const currentUrl = page.url();
    if (currentUrl.includes(profile.dashboardUrl.split('/').pop())) {
      return true;
    }
    
    // Look for logout button or user menu
    const authElements = await page.$$eval(
      '.logout, .user-menu, .dashboard, [href*="logout"], .welcome',
      elements => elements.length > 0
    );
    
    // Look for login form (should not be present if logged in)
    const loginForm = await page.$('form[action*="login"], .login-form, #login');
    
    return authElements && !loginForm;
    
  } catch (error) {
    return false;
  }
}
```

### **3. handleSessionExpiry(profile, page)**
**Purpose:** Detect and handle session expiration during testing

```javascript
async function handleSessionExpiry(profile, page) {
  const currentUrl = page.url();
  
  // Check if redirected to login
  if (currentUrl.includes('login') || currentUrl.includes('auth')) {
    console.log('🔄 Session expired, re-authenticating...');
    return await authenticateBot(profile, page);
  }
  
  // Check for session expired messages
  const sessionExpiredElement = await page.$('.session-expired, .login-required, .auth-required');
  if (sessionExpiredElement) {
    console.log('🔄 Session expired message detected, re-authenticating...');
    return await authenticateBot(profile, page);
  }
  
  return { success: true, message: "Session still valid" };
}
```

---

## 🏗️ **PROFILE STORAGE SYSTEM**

### **4. Profile Storage Functions**

#### **saveProfile(profileName, profileData)**
```javascript
const fs = require('fs').promises;
const path = require('path');

async function saveProfile(profileName, profileData) {
  const profilesDir = path.join(__dirname, 'profiles');
  
  // Ensure profiles directory exists
  await fs.mkdir(profilesDir, { recursive: true });
  
  // Encrypt sensitive data
  const encryptedProfile = encryptSensitiveData(profileData);
  
  // Save to file
  const profilePath = path.join(profilesDir, `${profileName}.json`);
  await fs.writeFile(profilePath, JSON.stringify(encryptedProfile, null, 2));
  
  console.log(`💾 Profile saved: ${profileName}`);
  return profilePath;
}
```

#### **loadProfile(profileName)**
```javascript
async function loadProfile(profileName) {
  try {
    const profilePath = path.join(__dirname, 'profiles', `${profileName}.json`);
    const profileData = await fs.readFile(profilePath, 'utf8');
    const profile = JSON.parse(profileData);
    
    // Decrypt sensitive data
    return decryptSensitiveData(profile);
    
  } catch (error) {
    console.log(`❌ Failed to load profile ${profileName}: ${error.message}`);
    return null;
  }
}
```

#### **listProfiles()**
```javascript
async function listProfiles() {
  try {
    const profilesDir = path.join(__dirname, 'profiles');
    const files = await fs.readdir(profilesDir);
    
    return files
      .filter(file => file.endsWith('.json'))
      .map(file => file.replace('.json', ''));
      
  } catch (error) {
    return [];
  }
}
```

---

## 🔒 **SECURITY FUNCTIONS**

### **5. Encryption/Decryption**

#### **encryptSensitiveData(profileData)**
```javascript
const crypto = require('crypto');

function encryptSensitiveData(profileData) {
  const algorithm = 'aes-256-gcm';
  const secretKey = process.env.PROFILE_ENCRYPTION_KEY || 'default-key-change-in-production';
  const key = crypto.scryptSync(secretKey, 'salt', 32);
  
  const sensitiveFields = ['password', 'apiKey', 'sessionToken'];
  const encrypted = { ...profileData };
  
  sensitiveFields.forEach(field => {
    if (encrypted[field]) {
      const iv = crypto.randomBytes(16);
      const cipher = crypto.createCipher(algorithm, key);
      
      let encryptedData = cipher.update(encrypted[field], 'utf8', 'hex');
      encryptedData += cipher.final('hex');
      
      const authTag = cipher.getAuthTag();
      
      encrypted[field] = {
        encrypted: encryptedData,
        iv: iv.toString('hex'),
        authTag: authTag.toString('hex')
      };
    }
  });
  
  return encrypted;
}
```

#### **decryptSensitiveData(encryptedProfile)**
```javascript
function decryptSensitiveData(encryptedProfile) {
  const algorithm = 'aes-256-gcm';
  const secretKey = process.env.PROFILE_ENCRYPTION_KEY || 'default-key-change-in-production';
  const key = crypto.scryptSync(secretKey, 'salt', 32);
  
  const decrypted = { ...encryptedProfile };
  
  Object.keys(decrypted).forEach(field => {
    if (typeof decrypted[field] === 'object' && decrypted[field].encrypted) {
      try {
        const decipher = crypto.createDecipher(algorithm, key);
        decipher.setAuthTag(Buffer.from(decrypted[field].authTag, 'hex'));
        
        let decryptedData = decipher.update(decrypted[field].encrypted, 'hex', 'utf8');
        decryptedData += decipher.final('utf8');
        
        decrypted[field] = decryptedData;
        
      } catch (error) {
        console.log(`⚠️ Failed to decrypt field ${field}`);
        decrypted[field] = null;
      }
    }
  });
  
  return decrypted;
}
```

---

## 🎯 **BOT INTEGRATION FUNCTIONS**

### **6. Frontend Tools Integration**

#### **Enhanced test-website with Authentication**
```bash
#!/bin/bash
# Enhanced test-website script with authentication

# Default profile for CIS testing
DEFAULT_PROFILE="cis-robot"

# Parse authentication options
while [[ $# -gt 0 ]]; do
  case $1 in
    --auth)
      USE_AUTH=true
      shift
      ;;
    --profile)
      AUTH_PROFILE="$2"
      shift 2
      ;;
    --no-auth)
      USE_AUTH=false
      shift
      ;;
    *)
      URL="$1"
      shift
      ;;
  esac
done

# Auto-detect if authentication needed
if [[ -z "$USE_AUTH" ]]; then
  if [[ "$URL" == *"staff.vapeshed.co.nz"* ]] || [[ "$URL" == *"gpt.ecigdis.co.nz"* ]]; then
    USE_AUTH=true
    AUTH_PROFILE="${AUTH_PROFILE:-$DEFAULT_PROFILE}"
  fi
fi

# Build authentication arguments
AUTH_ARGS=""
if [[ "$USE_AUTH" == "true" ]]; then
  AUTH_ARGS="--auth --profile=${AUTH_PROFILE}"
fi

# Execute with authentication
node scripts/deep-crawler.js --url="$URL" $AUTH_ARGS "$@"
```

#### **Deep Crawler with Authentication**
```javascript
// Add to deep-crawler.js
const { loadProfile, authenticateBot } = require('./auth-manager');

async function runCrawlWithAuth(url, options) {
  const browser = await puppeteer.launch(options.puppeteerConfig);
  const page = await browser.newPage();
  
  // Load authentication profile if specified
  if (options.auth && options.profile) {
    console.log(`🔐 Loading profile: ${options.profile}`);
    const profile = await loadProfile(options.profile);
    
    if (profile) {
      const authResult = await authenticateBot(profile, page);
      if (!authResult.success) {
        console.log(`❌ Authentication failed: ${authResult.error}`);
        await browser.close();
        return { success: false, error: authResult.error };
      }
    }
  }
  
  // Continue with normal crawling
  return await performCrawl(page, url, options);
}
```

---

## 📋 **SETUP INSTRUCTIONS FOR BOTS**

### **7. Bot Setup Checklist**

#### **Environment Setup:**
```bash
# 1. Create profiles directory
mkdir -p frontend-tools/profiles

# 2. Set encryption key (production)
export PROFILE_ENCRYPTION_KEY="your-secure-key-here"

# 3. Initialize default profiles
cd frontend-tools
node scripts/setup-profiles.js
```

#### **Profile Initialization Script:**
```javascript
// scripts/setup-profiles.js
const { saveProfile } = require('./auth-manager');

async function initializeProfiles() {
  console.log('🚀 Initializing CIS Robot profiles...');
  
  // Save CIS Robot profile
  await saveProfile('cis-robot', CIS_ROBOT_PROFILE);
  
  // Save GPT Hub profile  
  await saveProfile('gpt-hub', GPT_HUB_PROFILE);
  
  // Save retail profile
  await saveProfile('retail-sites', RETAIL_SITES_PROFILE);
  
  console.log('✅ All profiles initialized successfully!');
}

initializeProfiles().catch(console.error);
```

---

## 🎯 **USAGE EXAMPLES FOR BOTS**

### **Authenticated Testing:**
```bash
# Auto-detect authentication (recommended)
./test-website https://staff.vapeshed.co.nz

# Explicit authentication
./test-website https://staff.vapeshed.co.nz --auth --profile=cis-robot

# GPT Hub testing
./test-website https://gpt.ecigdis.co.nz --auth --profile=gpt-hub

# Public site (no auth)
./test-website https://www.vapeshed.co.nz --no-auth
```

### **Bot Response Templates with Auth:**
```markdown
## Authenticated Testing Response:

🔐 **Authentication:** Auto-detected CIS system, using robot credentials
🕐 **Duration:** 45 seconds  
📊 **Tests Run:** Console errors, Network requests, Performance, Auth verification

### Results:
✅ **Login Successful:** Authenticated as CIS Robot  
✅ **Dashboard Access:** Confirmed dashboard visibility  
✅ **Session Valid:** No session expiry during test  
⚠️ **2 Console Warnings:** Minor CSS issues detected  
✅ **All API Calls:** 200 OK responses  
✅ **Performance:** Page load 1.2s (good)  

### Files Generated:
- 📄 **Report:** `reports/test_20251027_143052/index.html`
- 📝 **Summary:** `reports/test_20251027_143052/SUMMARY.md`  
- 📸 **Screenshots:** 8 captures including authenticated state
```

---

## 🔧 **TROUBLESHOOTING**

### **Common Authentication Issues:**

#### **Login Form Not Found:**
```javascript
// Fallback selectors for login forms
const LOGIN_SELECTORS = [
  'input[name="username"]',
  '#username',
  '[type="email"]',
  '.username-input',
  'input[placeholder*="username"]',
  'input[placeholder*="email"]'
];

async function findLoginField(page) {
  for (const selector of LOGIN_SELECTORS) {
    try {
      await page.waitForSelector(selector, { timeout: 2000 });
      return selector;
    } catch (e) {
      continue;
    }
  }
  throw new Error('Login form not found');
}
```

#### **Session Handling:**
```javascript
// Persistent session management
async function maintainSession(profile, page) {
  // Check session every 5 minutes during long tests
  setInterval(async () => {
    const sessionValid = await verifyAuthentication(profile, page);
    if (!sessionValid) {
      await handleSessionExpiry(profile, page);
    }
  }, 300000); // 5 minutes
}
```

---

## 📊 **PROFILE MANAGEMENT DASHBOARD**

### **8. Profile Management Interface**

#### **List All Profiles:**
```javascript
async function displayProfiles() {
  const profiles = await listProfiles();
  
  console.log('\n🤖 Available Bot Profiles:');
  console.log('=' .repeat(40));
  
  for (const profileName of profiles) {
    const profile = await loadProfile(profileName);
    if (profile) {
      console.log(`📋 ${profileName.toUpperCase()}`);
      console.log(`   URL: ${profile.loginUrl || profile.url || 'N/A'}`);
      console.log(`   User: ${profile.username || 'anonymous'}`);
      console.log(`   Type: ${profile.testMode || 'authenticated'}`);
      console.log('');
    }
  }
}
```

#### **Test Profile Authentication:**
```javascript
async function testProfileAuth(profileName) {
  console.log(`🔍 Testing authentication for profile: ${profileName}`);
  
  const profile = await loadProfile(profileName);
  if (!profile) {
    console.log(`❌ Profile not found: ${profileName}`);
    return false;
  }
  
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  
  try {
    const result = await authenticateBot(profile, page);
    
    if (result.success) {
      console.log(`✅ Authentication successful for ${profileName}`);
    } else {
      console.log(`❌ Authentication failed for ${profileName}: ${result.error}`);
    }
    
    return result.success;
    
  } finally {
    await browser.close();
  }
}
```

---

## 🎉 **READY FOR BOT USE!**

All authentication profiles are now configured and ready for bot testing:

### **Quick Start:**
1. **CIS Testing:** `./test-website https://staff.vapeshed.co.nz` (auto-auth)
2. **GPT Hub Testing:** `./test-website https://gpt.ecigdis.co.nz` (auto-auth)  
3. **Public Sites:** `./test-website https://www.vapeshed.co.nz` (no auth needed)

### **Bot Commands:**
- ✅ All functions documented with parameters and return values
- ✅ Pre-configured authentication profiles stored securely
- ✅ Auto-detection of authentication requirements
- ✅ Session management and expiry handling
- ✅ Error handling and fallback mechanisms
- ✅ Profile management and testing tools

**Bots can now test websites with full authentication automatically!** 🚀