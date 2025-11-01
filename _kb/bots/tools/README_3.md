# 🏢 Ecigdis AI-Powered Enterprise Command Center

**Platform:** gpt.ecigdis.co.nz  
**Version:** 1.0.0  
**Date:** October 21, 2025  
**Status:** 🚧 Under Construction

---

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [System Architecture](#system-architecture)
3. [Core Components](#core-components)
4. [Database Schema](#database-schema)
5. [API Documentation](#api-documentation)
6. [Deployment Guide](#deployment-guide)
7. [Progress Tracker](#progress-tracker)
8. [Knowledge Base](#knowledge-base)

---

## 🎯 Executive Summary

The **Ecigdis AI-Powered Enterprise Command Center** is a centralized intelligence platform that powers the entire Ecigdis business empire including:

- **The Vape Shed** (17 retail stores)
- **Ecigdis Wholesale** (B2B operations)
- **The Vaping Kiwi** (Secondary retail website)
- **Juice Manufacturing** (Production facility)
- **Corporate Operations** (Ecigdis Limited)

### What This Platform Provides:

✅ **Centralized Business Intelligence** - All business metrics in one place  
✅ **Universal Knowledge Base** - Technical and business documentation  
✅ **AI Agent Infrastructure** - Bots deployed across all websites  
✅ **Live Chat Everywhere** - Customer and staff support on every site  
✅ **API Gateway** - Unified API access for all systems  
✅ **Real-time Analytics** - Performance monitoring across the empire  
✅ **Predictive Intelligence** - AI-powered insights and recommendations  

---

## 🏗️ System Architecture

### High-Level Overview

```
┌─────────────────────────────────────────────────────────────┐
│         gpt.ecigdis.co.nz (Command Center)                 │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ Knowledge   │  │  Business   │  │  AI Agent   │       │
│  │    Base     │  │Intelligence │  │Infrastructure│       │
│  │ (ecig_kb_*) │  │ (ecig_bi_*) │  │ (ecig_ai_*) │       │
│  └─────────────┘  └─────────────┘  └─────────────┘       │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │  Live Chat  │  │ API Gateway │  │   Reports   │       │
│  │(ecig_chat_*)│  │(ecig_api_*) │  │  & Alerts   │       │
│  └─────────────┘  └─────────────┘  └─────────────┘       │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ API Connections
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ staff.       │    │ www.         │    │ www.         │
│ vapeshed.    │    │ vapeshed.    │    │ vapingkiwi.  │
│ co.nz (CIS)  │    │ co.nz        │    │ co.nz        │
└──────────────┘    └──────────────┘    └──────────────┘
        │                   │                   │
    💬 Staff AI        💬 Customer AI      💬 Customer AI
    📊 BI Dashboard    🛒 Smart Cart      🎁 Recommendations
```

---

## 🧩 Core Components

### 1. Knowledge Base Infrastructure (`ecig_kb_*`)

**Purpose:** Central repository for all technical and business knowledge

**Tables:**
- `ecig_kb_file_memory` - Code files, functions, classes
- `ecig_kb_domain_map` - Business domain definitions
- `ecig_kb_component_registry` - Component inventory
- `ecig_kb_relationships` - File dependencies
- `ecig_kb_intelligence` - AI-discovered insights

**Use Cases:**
- Staff KB lookups via chat
- Code intelligence for developers
- System documentation
- Onboarding new staff
- Troubleshooting guides

### 2. Business Intelligence Platform (`ecig_bi_*`)

**Purpose:** Multi-tenant business analytics and insights

**Tables:**
- `ecig_bi_business_units` - 5 business units
- `ecig_bi_domains` - 50+ business domains
- `ecig_bi_components` - All system components
- `ecig_bi_metrics` - Performance tracking
- `ecig_bi_events` - Activity logging
- `ecig_bi_alerts` - Monitoring & notifications

**Use Cases:**
- Executive dashboards
- Cross-business analytics
- Supply chain visibility
- Performance monitoring
- Predictive forecasting

### 3. AI Agent Infrastructure (`ecig_ai_*`)

**Purpose:** Deploy and manage AI bots across all platforms

**Tables:**
- `ecig_ai_agents` - Bot registry
- `ecig_ai_models` - AI model configs
- `ecig_ai_training` - Learning data
- `ecig_ai_conversations` - Chat history
- `ecig_ai_deployments` - Where bots are active

**Use Cases:**
- Customer service automation
- Staff assistance
- Business intelligence queries
- Product recommendations
- Order tracking

### 4. Live Chat System (`ecig_chat_*`)

**Purpose:** Universal chat widget for all websites

**Tables:**
- `ecig_chat_sessions` - Active conversations
- `ecig_chat_messages` - Message history
- `ecig_chat_users` - Customer profiles
- `ecig_chat_routing` - Agent assignment
- `ecig_chat_analytics` - Chat metrics

**Use Cases:**
- 24/7 customer support
- Staff help desk
- Sales assistance
- Technical support
- Feedback collection

### 5. API Gateway (`ecig_api_*`)

**Purpose:** Unified API access for all systems

**Tables:**
- `ecig_api_keys` - Authentication
- `ecig_api_endpoints` - Endpoint catalog
- `ecig_api_logs` - Request tracking
- `ecig_api_quotas` - Rate limiting
- `ecig_api_webhooks` - Event subscriptions

**Use Cases:**
- Site-to-hub communication
- Third-party integrations
- Mobile app connections
- Partner API access
- Webhook notifications

---

## 📊 Database Schema

**Total Tables:** 50+  
**Naming Convention:** `ecig_[subsystem]_[entity]`

### Subsystems:
- `ecig_kb_*` - Knowledge Base (10 tables)
- `ecig_bi_*` - Business Intelligence (15 tables)
- `ecig_ai_*` - AI Agents (12 tables)
- `ecig_chat_*` - Live Chat (8 tables)
- `ecig_api_*` - API Gateway (5 tables)

**Full Schema:** See [database/COMPLETE_SCHEMA.md](database/COMPLETE_SCHEMA.md)

---

## 🔌 API Documentation

### Base URL
```
https://gpt.ecigdis.co.nz/api/v1
```

### Authentication
```http
GET /api/v1/endpoint
Authorization: Bearer {api_key}
X-Site-ID: vapeshed|vapingkiwi|wholesale|etc
```

### Core Endpoints

**Business Intelligence:**
- `GET /bi/business-units` - List all business units
- `GET /bi/domains` - List all domains
- `GET /bi/metrics/{domain}` - Get domain metrics
- `GET /bi/dashboard` - Executive dashboard data

**Knowledge Base:**
- `GET /kb/search?q={query}` - Search KB
- `GET /kb/file/{path}` - Get file documentation
- `GET /kb/component/{name}` - Get component details

**AI Agents:**
- `POST /ai/chat` - Send message to AI
- `GET /ai/agents` - List available agents
- `POST /ai/deploy` - Deploy agent to site

**Live Chat:**
- `POST /chat/start` - Start chat session
- `POST /chat/message` - Send message
- `GET /chat/history` - Get conversation history

**Full API Docs:** See [api/API_REFERENCE.md](api/API_REFERENCE.md)

---

## 🚀 Deployment Guide

### Prerequisites
- PHP 8.1+
- MySQL/MariaDB 10.5+
- Apache/Nginx
- SSL Certificate

### Installation Steps

1. **Database Setup**
   ```bash
   mysql -u hdgwrzntwa -p'password' hdgwrzntwa < database/complete_schema.sql
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

3. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

4. **Seed Data**
   ```bash
   php artisan migrate --seed
   ```

5. **Start Services**
   ```bash
   systemctl restart apache2
   ```

**Full Deployment:** See [deployment/DEPLOYMENT_GUIDE.md](deployment/DEPLOYMENT_GUIDE.md)

---

## 📈 Progress Tracker

### Phase 1: Foundation ✅ COMPLETE
- [x] System architecture design
- [x] Database schema design
- [x] Documentation structure

### Phase 2: Database Installation 🚧 IN PROGRESS
- [ ] Create all database tables
- [ ] Seed business units
- [ ] Populate domains
- [ ] Test relationships

### Phase 3: Application Layer 📋 PLANNED
- [ ] Build API gateway
- [ ] Deploy KB system
- [ ] Install BI platform
- [ ] Configure AI agents
- [ ] Implement live chat

### Phase 4: Integration 📋 PLANNED
- [ ] Connect CIS (staff.vapeshed.co.nz)
- [ ] Connect Vape Shed retail site
- [ ] Connect Vaping Kiwi site
- [ ] Connect wholesale portal
- [ ] Connect manufacturing system

### Phase 5: Testing & Launch 📋 PLANNED
- [ ] System testing
- [ ] Performance optimization
- [ ] Security audit
- [ ] Staff training
- [ ] Production launch

**Detailed Progress:** See [progress/BUILD_STATUS.md](progress/BUILD_STATUS.md)

---

## 📚 Knowledge Base

### Quick Links

**For Developers:**
- [Architecture Overview](architecture/SYSTEM_ARCHITECTURE.md)
- [Database Schema](database/COMPLETE_SCHEMA.md)
- [API Reference](api/API_REFERENCE.md)
- [Development Guide](guides/DEVELOPMENT_GUIDE.md)

**For Business Users:**
- [Platform Overview](knowledge-base/PLATFORM_OVERVIEW.md)
- [Using the Dashboard](guides/DASHBOARD_GUIDE.md)
- [BI Reports Guide](knowledge-base/business-intelligence/REPORTS_GUIDE.md)
- [Chat Bot Setup](knowledge-base/live-chat/SETUP_GUIDE.md)

**For Management:**
- [Executive Summary](EXECUTIVE_SUMMARY.md)
- [ROI Analysis](knowledge-base/business-intelligence/ROI_ANALYSIS.md)
- [System Capabilities](specifications/CAPABILITIES.md)
- [Roadmap](progress/ROADMAP.md)

---

## 🔐 Security & Compliance

- All API access requires authentication
- Role-based access control (RBAC)
- GOD/SUPER ADMIN tiers
- Audit logging on all actions
- HTTPS enforced
- PCI DSS compliant (for payment data)
- GDPR considerations for customer data

**Security Docs:** See [knowledge-base/security/](knowledge-base/security/)

---

## 📞 Support & Contact

**Technical Support:**
- Documentation: This repository
- Issues: Report via GitHub issues
- Contact: dev@ecigdis.co.nz

**Business Inquiries:**
- Contact: info@ecigdis.co.nz
- Website: https://www.ecigdis.co.nz

---

## 📄 License

Proprietary - © 2025 Ecigdis Limited. All rights reserved.

---

**Last Updated:** October 21, 2025  
**Version:** 1.0.0  
**Status:** 🚧 Active Development
