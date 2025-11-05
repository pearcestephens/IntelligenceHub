# Intelligence Hub - Visual Design Reference

**CIS Professional Dashboard - Design Specifications**

---

## 🎨 Color Palette

### Primary Colors
```css
Primary Blue:    #4e73df  ████████ (AI, active states, primary actions)
Success Green:   #1cc88a  ████████ (approvals, success states, positive metrics)
Warning Yellow:  #f6c23e  ████████ (pending, warnings, attention needed)
Danger Red:      #e74a3b  ████████ (errors, critical alerts, declines)
Info Cyan:       #36b9cc  ████████ (information, neutral highlights)
```

### Neutral Colors
```css
Text Primary:    #2c3e50  ████████ (body text, headings)
Text Secondary:  #6c757d  ████████ (supporting text)
Text Muted:      #858796  ████████ (timestamps, meta info)
Background:      #f8f9fc  ████████ (body background)
Card Background: #ffffff  ████████ (card surfaces)
Border:          #e3e6f0  ████████ (dividers, borders)
Sidebar:         #2c3e50  ████████ (sidebar background)
```

### **NO PURPLE** - Explicitly Removed Per Requirements

---

## 📐 Layout Structure

```
┌─────────────────────────────────────────────────────┐
│  NAVBAR (65px height)                               │
│  [Logo] [Search] [Health] [Notifications] [User]   │
└─────────────────────────────────────────────────────┘
┌──────────┬──────────────────────────────────────────┐
│ SIDEBAR  │ MAIN CONTENT                             │
│ (260px)  │                                          │
│          │  ┌────────────────────────────────┐     │
│ AI       │  │  Content Wrapper (padding)     │     │
│ Control  │  │                                │     │
│          │  │  ┌──────────────────────────┐ │     │
│ - Over   │  │  │ Cards, Grids, Lists      │ │     │
│ - Agents │  │  │                          │ │     │
│ - AI     │  │  │                          │ │     │
│ - Auto   │  │  └──────────────────────────┘ │     │
│          │  └────────────────────────────────┘     │
│ Ops      │                                          │
│          │                                          │
│ Insights │                                          │
│          │                                          │
│ System   │                                          │
│          │                                          │
│ [Status] │                                          │
└──────────┴──────────────────────────────────────────┘
```

---

## 🎯 Component Showcase

### 1. Metric Cards (Quick Stats)

```
┌─────────────────────────────────────────────────────────────┐
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  ┌────┐  │  │  ┌────┐  │  │  ┌────┐  │  │  ┌────┐  │   │
│  │  │ 🤖 │  │  │  │ ✓  │  │  │  │ ⏱  │  │  │  │ $  │  │   │
│  │  └────┘  │  │  └────┘  │  │  └────┘  │  │  └────┘  │   │
│  │    9     │  │   247    │  │    12    │  │ $47,230  │   │
│  │  Active  │  │   Tasks  │  │ Pending  │  │   Cost   │   │
│  │  Agents  │  │ Complete │  │Approvals │  │ Savings  │   │
│  │  Blue    │  │  Green   │  │  Yellow  │  │   Blue   │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

**Design Features:**
- 60px circular icon separator (gradient background)
- Large number display (2rem, bold)
- Label text (0.875rem, muted)
- Hover: translateY(-2px), shadow elevation
- 4-column grid (responsive to 1 column on mobile)

---

### 2. Agent Cards (Agent Status Grid)

```
┌─────────────────────────────────────────────────────────────┐
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ ┌──────────┐ │  │ ┌──────────┐ │  │ ┌──────────┐ │     │
│  │ │ 🤖 Invt. │ │  │ │ 🔒 Secur │ │  │ │ 🌐 WebMon│ │     │
│  │ │   Active │ │  │ │   Active │ │  │ │    Idle  │ │     │
│  │ └──────────┘ │  │ └──────────┘ │  │ └──────────┘ │     │
│  │              │  │              │  │              │     │
│  │ Monitors inv │  │ Security mon │  │ Traffic moni │     │
│  │ and creates  │  │ and threat d │  │ and performa │     │
│  │ orders auto  │  │              │  │              │     │
│  │              │  │              │  │              │     │
│  │ 47 tasks ↗   │  │ 12 tasks ↗   │  │ 8 tasks ↗    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

**Design Features:**
- Header with icon (40px) + name + status badge
- Status badge colors: active (blue pulse), idle (gray), busy (yellow), error (red)
- Description text (secondary color)
- Meta row with task count
- Auto-fit grid (min 280px)
- Hover: shadow elevation, border highlight

---

### 3. Activity & Alert Lists

**Activity List:**
```
┌─────────────────────────────────────────────────────┐
│ ┃ Transfer Order Created              2 hours ago   │
│ ┃ 150 units from AKL to WLG                         │
│ ┃ Inventory Agent                                   │
├─────────────────────────────────────────────────────┤
│ ┃ Purchase Order Approved              4 hours ago  │
│ ┃ PO-2024-123 for $12,450                           │
│ ┃ Purchasing Agent                                  │
└─────────────────────────────────────────────────────┘
```

**Alert List:**
```
┌─────────────────────────────────────────────────────┐
│ ┃ Low Stock Alert                      [Dismiss X]  │
│ ┃ Product #12345 below minimum (3 left)             │
│ ┃ 10 minutes ago                                    │
├─────────────────────────────────────────────────────┤
│ ┃ Security Alert                       [Dismiss X]  │
│ ┃ Failed login attempts detected                    │
│ ┃ 1 hour ago                                        │
└─────────────────────────────────────────────────────┘
```

**Design Features:**
- Left border highlight (4px, color-coded)
- Hover: background tint, slight scale
- Time badges right-aligned
- Dismiss buttons (ghost style)
- Stacked items with spacing

---

### 4. AI Command Center

```
┌──────────────────────────────────────────────────────────┐
│  AI Command Center                                       │
├──────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────┐ │
│  │ 🤖 │ Type your command or question...           │ │
│  │    │_______________________________________     │ │
│  │    │                                    [Send] │ │
│  │    │                             [🎤 Voice]    │ │
│  └────────────────────────────────────────────────────┘ │
│                                                          │
│  ✓ AI Response: Order created successfully...           │
│    Transfer scheduled for tomorrow morning.              │
└──────────────────────────────────────────────────────────┘
```

**Design Features:**
- Large robot icon (2rem, primary color)
- Input with focus ring (primary color)
- Send button (primary, with icon)
- Voice button (secondary, with microphone)
- Response container (slideDown animation, success background)

---

### 5. Recommendation Cards

```
┌──────────────────────────────────────────────────────────┐
│ ⚠ Create Transfer Order                  [Approve] [✕]  │
│                                                          │
│ Product #12345 is low in Wellington store (3 units).    │
│ Suggest transferring 50 units from Auckland.            │
│                                                          │
│ ⚡ Inventory Agent • Confidence: 95% • Impact: $450     │
└──────────────────────────────────────────────────────────┘
```

**Design Features:**
- Warning/info background (10% opacity)
- Left border (4px, semantic color)
- Bold title (0.9375rem)
- Action buttons (success/danger, small)
- Meta row with icon, agent name, confidence, impact
- Flex layout with gap

---

## 🎭 Animations & Interactions

### Hover Effects
```css
Transform: translateY(-2px)  /* Lift on hover */
Box-shadow: 0 4px 12px rgba(0,0,0,0.15)  /* Elevate */
Transition: 0.2s ease  /* Smooth */
```

### Active Agent Pulse
```css
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
/* Applied to agent status dot */
```

### Heartbeat (System Health)
```css
@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    14% { transform: scale(1.2); }
    28% { transform: scale(1); }
}
/* Applied to health indicator icon */
```

### Slide Down (AI Response)
```css
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

---

## 📱 Responsive Breakpoints

### Desktop (> 768px)
- Sidebar: 260px fixed left
- Main content: calc(100% - 260px) right
- Metrics: 4 columns
- Agents: 3 columns (auto-fit)
- Grids: 2-3 columns

### Mobile (≤ 768px)
- Sidebar: Off-canvas (toggle menu)
- Main content: 100% width
- Metrics: 1 column (stacked)
- Agents: 1 column (stacked)
- Grids: 1 column (stacked)

---

## ✨ Design Philosophy

### Professional & Clean
- **Whitespace:** Generous spacing (var(--space-3) = 1rem)
- **Typography:** Inter font, clear hierarchy
- **Colors:** Semantic, purposeful usage
- **Borders:** Subtle (1px, light gray)

### Business Focus
- **Information Density:** Balanced, not cluttered
- **Scannability:** Clear headings, grouped content
- **Actionable:** Buttons and CTAs are obvious
- **Trustworthy:** Professional color palette

### Dashboard Flare
- **Gradients:** On card headers (subtle, 10-15 degrees)
- **Icons:** Circular separators with background
- **Animations:** Purposeful, not distracting
- **Depth:** Shadows create card hierarchy
- **Status:** Pulse, heartbeat for live indicators

---

## 🎯 Icon Usage (Font Awesome 6.5)

```
AI/Agent:       fa-robot
Success:        fa-check-circle
Pending:        fa-clock
Money:          fa-dollar-sign
Activity:       fa-history
Alert:          fa-exclamation-triangle
Security:       fa-shield-alt
Inventory:      fa-boxes
Sales:          fa-chart-line
Analytics:      fa-chart-bar
Reports:        fa-file-alt
Settings:       fa-cog
Logs:           fa-list-alt
Web Monitor:    fa-globe
Automation:     fa-magic
Forecasting:    fa-crystal-ball
Heartbeat:      fa-heartbeat
Microphone:     fa-microphone
Send:           fa-paper-plane
Info:           fa-info-circle
Close:          fa-times
Check:          fa-check
Bolt/Lightning: fa-bolt
```

---

## 📏 Spacing Scale

```css
--space-1: 0.25rem  (4px)   - Tight spacing
--space-2: 0.5rem   (8px)   - Small gaps
--space-3: 1rem     (16px)  - Default spacing ⭐
--space-4: 1.5rem   (24px)  - Section spacing
--space-5: 2rem     (32px)  - Large spacing
--space-6: 3rem     (48px)  - Hero spacing
```

---

## 🎨 Typography Scale

```css
Font Family: Inter, system-ui, -apple-system, sans-serif

Sizes:
--text-xs:   0.75rem    (12px)  - Tiny meta
--text-sm:   0.8125rem  (13px)  - Small labels
--text-base: 0.875rem   (14px)  - Body text ⭐
--text-lg:   0.9375rem  (15px)  - Large body
--text-xl:   1rem       (16px)  - Headings
--text-2xl:  1.25rem    (20px)  - Large headings
--text-3xl:  1.5rem     (24px)  - Hero text

Weights:
Normal:  400  - Body text
Medium:  500  - Emphasis
Semibold: 600 - Subheadings ⭐
Bold:    700  - Headings
```

---

## 🔲 Border Radius

```css
--radius-sm:  0.25rem  (4px)   - Buttons, badges
--radius-md:  0.375rem (6px)   - Cards, inputs ⭐
--radius-lg:  0.5rem   (8px)   - Large cards
--radius-xl:  1rem     (16px)  - Modals
--radius-full: 50%             - Circular icons
```

---

## 🌓 Shadows

```css
Card Shadow:
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12),
            0 1px 2px rgba(0, 0, 0, 0.08);

Hover Shadow:
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);

Modal Shadow:
box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
```

---

**Status:** This design system creates a **professional, consistent, and engaging** dashboard experience that balances business requirements with modern UI patterns. The careful use of color, animation, and spacing ensures information is both accessible and actionable.
