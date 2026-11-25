# Nexsus AI Task Scheduler & Reminder — Figma AI Design Prompt

> **Usage:** Copy the entire content below and paste it into Figma AI as a design prompt. All pages, components, interactions, and specifications are fully described.

---

## 🎯 PROJECT BRIEF

You are building a premium, production-grade web app prototype for an **AI-powered task scheduler and reminder platform** called **"Nexsus Tasks"**. This is a personal productivity tool that helps users create tasks from natural language, automatically schedules them based on priorities and energy patterns, and delivers intelligent, context-aware reminders.

The design must feel like a world-class productivity app — think Notion meets Reclaim.ai meets Todoist. It must be **immaculate, premium, user-friendly, easy to navigate**, and accessible across all devices. Every interaction should feel smooth, intentional, and delightful.

**Design tone:** Clean, calm, intelligent. Minimal but warm. Premium SaaS feel. Think linear.app or arc browser aesthetics — generous whitespace, refined typography, subtle motion.

---

## 🎨 VISUAL DESIGN SYSTEM

### Color Palette

| Token | Light Mode | Dark Mode | Usage |
|---|---|---|---|
| `--bg-primary` | `#FFFFFF` | `#0D0D0D` | Page background |
| `--bg-secondary` | `#F8F9FA` | `#161616` | Card backgrounds, sections |
| `--bg-tertiary` | `#F1F3F5` | `#1F1F1F` | Hover states, inputs |
| `--bg-focus` | `#E8F5E9` (soft green) | `#1A2E1A` (deep green) | Focus mode activation |
| `--text-primary` | `#1A1A2E` | `#F0F0F0` | Headlines, body text |
| `--text-secondary` | `#6C757D` | `#A0A0A0` | Captions, descriptions |
| `--text-muted` | `#ADB5BD` | `#666666` | Placeholders, disabled |
| `--accent-primary` | `#4361EE` (vibrant blue) | `#4361EE` | Primary actions, links |
| `--accent-secondary` | `#7209B7` (purple) | `#7209B7` | AI features, special actions |
| `--accent-success` | `#06D6A0` (mint green) | `#06D6A0` | Completion, success |
| `--accent-warning` | `#FFD166` (amber) | `#FFD166` | Warnings, pending |
| `--accent-danger` | `#EF476F` (coral) | `#EF476F` | Delete, critical alerts |
| `--border` | `#E9ECEF` | `#2A2A2A` | Dividers, borders |
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | `0 1px 2px rgba(0,0,0,0.3)` | Card shadows |
| `--shadow-md` | `0 4px 12px rgba(0,0,0,0.08)` | `0 4px 12px rgba(0,0,0,0.4)` | Modal shadows |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.12)` | `0 8px 32px rgba(0,0,0,0.5)` | Dropdown shadows |
| `--glass-bg` | `rgba(255,255,255,0.8)` | `rgba(20,20,20,0.8)` | Glass morphism overlays |
| `--glass-blur` | `12px` | `12px` | Backdrop blur |

### Typography

| Role | Font | Size | Weight | Line Height |
|---|---|---|---|---|
| Display | Inter / Plus Jakarta Sans | 48px | 700 | 1.1 |
| H1 | Inter / Plus Jakarta Sans | 32px | 700 | 1.2 |
| H2 | Inter / Plus Jakarta Sans | 24px | 600 | 1.3 |
| H3 | Inter / Plus Jakarta Sans | 20px | 600 | 1.35 |
| Body Large | Inter / Plus Jakarta Sans | 16px | 400 | 1.5 |
| Body | Inter / Plus Jakarta Sans | 14px | 400 | 1.5 |
| Caption | Inter / Plus Jakarta Sans | 12px | 500 | 1.4 |
| Label | Inter / Plus Jakarta Sans | 11px | 600 | 1.3 |
| Overline | Inter / Plus Jakarta Sans | 10px | 700 | 1.5 |

### Spacing System

```
4px — Minimum unit
8px — Small gap
12px — Component internal padding
16px — Standard gap (card padding, form spacing)
24px — Section spacing
32px — Block spacing
48px — Page section gap
64px — Major section divider
```

### Border Radius

```
4px — Tags, small elements
8px — Inputs, buttons, badges
12px — Cards, panels
16px — Modals, dropdowns
24px — Large panels, task creation modal
9999px — Pills, avatars, status badges
```

### Iconography (Phosphor Icons recommended)

| Feature | Icon Name | Notes |
|---|---|---|
| Add task | `plus-circle` | Floating action button (FAB) |
| Complete task | `check-circle` | Animates fill on completion |
| Delete task | `trash` | Subtle, secondary placement |
| Edit task | `pencil-simple` | Appears on hover |
| Priority low | `arrow-down` | Downward motion, green |
| Priority high | `arrow-up` | Upward motion, red |
| Priority critical | `alert-triangle` | Pulse animation, red |
| Calendar | `calendar` | On schedule entries |
| Clock | `clock` | On time estimates |
| AI/Parse | `sparkle` | Purple glow on hover, rotate 90° on click |
| Bell (reminder) | `bell` | On notification |
| Bell (unread) | `bell-ring` | Vibrates gently when new |
| Search | `magnifying-glass` | In search bars |
| Filter | `funnel` | Filter panel toggle |
| Sort | `arrow-down-up` | Sort dropdown |
| Tasks | `check-square` | Sidebar nav |
| Calendar view | `calendar-blank` | Sidebar nav |
| Analytics | `chart-bar` | Sidebar nav |
| Settings | `gear` | Top right corner |
| Profile | `user` | Top right corner |
| Chevron down | `caret-down` | Dropdown indicators |
| Chevron right | `caret-right` | Expand/collapse |
| Done/Focus mode | `moon` | Focus mode toggle, fills in |
| Natural language | `chat-centered-dots` | AI input field |

### Icon Motion (Micro-animations)

| Icon | Interaction | Animation |
|---|---|---|
| `sparkle` (AI) | Hover | Gentle glow pulse, subtle rotation |
| `bell` | New notification | Gentle shake (0.3s ease-in-out) |
| `check-circle` | Task complete | Circle fills left-to-right, check draws in (0.5s) |
| `plus-circle` (FAB) | Click | Scale down to 0.9, then up to 1.1, settle at 1.0 (0.3s) |
| `moon` | Focus mode toggle | Crescent fills to full moon over 1s (ease-in-out) |
| `arrow-up/down` | Priority change | Arrow bounces up or down 4px then settles (0.3s) |
| `calendar` | Date hover | Calendar flips open revealing date grid (0.4s) |

---

## 📐 LAYOUT ARCHITECTURE

### Global Layout (3 Column)

```
┌─────────────────────────────────────────────────────────────────┐
│  TOP NAVIGATION BAR (60px) — Sticky, glass morphism            │
│  [Logo] [Search] [AI Parse] [🔔] [⚙️] [👤]                  │
├──────┬──────────────────────────────────────────────┬───────────┤
│      │                                              │           │
│ LEFT │          MAIN CONTENT AREA                   │  RIGHT    │
│ NAV  │            (fluid)                           │  PANEL    │
│(240px)│                                              │  (320px)  │
│      │                                              │           │
└──────┴──────────────────────────────────────────────┴───────────┘
```

### Responsive Breakpoints

| Device | Width | Layout Changes |
|---|---|---|
| Desktop XL | 1440px+ | 3-column layout, full right panel |
| Desktop | 1024-1439px | 3-column, right panel narrower |
| Tablet | 768-1023px | 2-column (left nav + main), right panel becomes bottom drawer |
| Mobile | <768px | Single column, left nav becomes hamburger menu, FAB for add |

---

## 🧭 NAVIGATION STRUCTURE

### Left Sidebar (Desktop)

```
┌────────────────────────┐
│  NEXUSUS TASKS         │ ← Logo area (60px height)
│                        │
│ ── My Tasks ────────── │ ← Section label (overline)
│  □ All Tasks           │ ← Nav item with count badge
│  □ Today               │ ← Nav item
│  □ Upcoming            │ ← Nav item
│  □ Completed           │ ← Nav item
│  □ Cancelled           │ ← Nav item
│                        │
│ ── Views ───────────── │ ← Section label
│  📊 Kanban             │ ← Nav item with icon
│  📅 Calendar           │ ← Nav item with icon
│  📋 List               │ ← Nav item (active state)
│                        │
│ ── AI Features ────── │ ← Section label (accent border)
│  ✨ AI Parse           │ ← Nav item with sparkle icon
│  🧠 Smart Schedule     │ ← Nav item
│                        │
│ ── Tools ───────────── │ ← Section label
│  🔔 Reminders          │ ← Nav item
│  📈 Analytics          │ ← Nav item
│  🌙 Focus Mode         │ ← Nav item with moon icon
│  📋 Daily Digest       │ ← Nav item
│                        │
│ [⚙️ Settings]          │ ← Bottom pinned
│ [👤 Profile]           │
└────────────────────────┘
```

### Top Navigation Bar

```
┌──────────────────────────────────────────────────────────────────────┐
│  ☰  [Nexsus Tasks Logo]    [Search tasks... (280px)]  [✨ AI] [🔔3] [⚙️] [👤 Name] │
└──────────────────────────────────────────────────────────────────────┘
```

- Search: Instant search with debounce 300ms, dropdown results with task title + project + priority badge
- AI button: Opens AI parse modal
- Notifications bell: Shows unread count badge (red dot with number)
- Profile: Dropdown with Profile, Settings, Theme toggle, Logout

---

## 🖥️ PAGE-BY-PAGE DESIGN

### PAGE 1: Task Dashboard (Default Landing)

**URL:** `/tasks`
**Description:** Central hub. Shows today's tasks, AI suggestions, and quick actions.

**Layout:**
- Greeting header: "Good morning, [Name] ☀️" with date and quick date filters (Today, Tomorrow, Week)
- AI summary line: "You have 5 tasks today, 2 are high priority"
- Focus Mode banner (when active): Green glass morphism bar with timer
- Two-column top section: "Today's Snapshot" (stats cards) + "AI Suggestions" (contextual recommendations)
- Task list header with Search, Filter, Sort, and Add Task button
- Task rows with priority indicator, title, project, due date, and hover actions

**Task Row Design:**
```
[○/●] [PRIORITY BADGE] [████████░░░░░░░░░░░░] [Title]    [📁Project] [📅Due] [Edit] [Complete]
        [progress bar animating]              [Description snippet]?
```

- Circle: Empty = pending, Filled/colored = in progress, Green check = complete
- Priority badge colors: Red (Critical/High), Orange (Med/High), Green (Low/Med), Gray (Low)
- Progress bar: Animates smoothly from 0% to completion %
- Actions on hover: Edit (pencil icon), Complete (check), Delete (trash, appears last)
- Completed task rows: Strikethrough title, faded background, green left border

### PAGE 2: Task Creation / Edit Modal

**Trigger:** Click `[+ Add Task]` button or `Cmd/Ctrl + N`
**Modal Size:** 520px wide, centered, `shadow-lg`

**Modal Content (top to bottom):**
1. Title bar: "✨ NEW TASK"
2. Task Title field (required, large input)
3. `[Parse with AI]` button (purple, with sparkle icon, hint text: "Type a description and let AI structure it for you")
4. Description textarea (optional, resizable)
5. Three-column row: Priority dropdown, Due Date picker, Project dropdown
6. Estimated Duration dropdown (minutes)
7. Subtasks section (dynamic, `[+ Add subtask]` button adds new rows with slide-in animation)
8. Dependencies section (optional, linked task references)
9. Action buttons: `[Cancel]` (secondary) and `[Save Task]` (primary, blue)

**Modal Interactions:**
- Opens with: Backdrop fade in (0.2s), Modal scale from 0.95 to 1 (0.3s ease-out)
- Closes with: Modal scale to 0.95 (0.2s ease-in), backdrop fade out
- ESC key or click outside closes the modal
- `[+ Add subtask]`: New input row slides in from top (0.3s), staggered
- `[Parse with AI]`: Button shows loading spinner, then results appear inline below (staggered fade-in 0.1s per field)
- `[Save Task]`: Button press animation (scale 0.98), success toast appears top-right, modal closes (0.4s), new task appears at top of list with highlight animation (yellow flash → fade, 1.5s)

### PAGE 3: AI Parse Interface

**URL:** `/tasks/parse`
**Description:** Dedicated page for natural language task parsing with AI.

**Layout:**
- Page header: "✨ AI Task Parser" with subtitle "Describe your task in plain English"
- Large text area (placeholder: e.g. "Finish the report by Friday afternoon, high priority")
- `[✨ Parse]` button (purple, primary, right-aligned in text area footer)
- Results section (appears after parse):
  - Title, Priority badge (with number), Due date, Project, Estimated duration
  - Subtasks as tag chips, Dependencies as tag chips
  - Provider badge: "⚡ OpenAI" or "🤖 Anthropic"
  - Action buttons: `[✓ Create as Task]`, `[✏️ Edit first]`, `[Try again ↻]`
- Examples section: Clickable example chips that fill the text area
  - "After my meeting at 3, prepare the deck"
  - "Remind me weekly to check invoices"
  - "Call dentist next Tuesday morning"

**AI Parse Interactions:**
- Text area grows with content (auto-resize, max 6 rows)
- `[✨ Parse]` button: Loading state (spinner + "Parsing...") for 1-2 seconds, then results reveal with staggered fade-in (each field 0.1s apart)
- If provider fallback triggers (Anthropic after OpenAI fails): Subtle banner "Used Anthropic as backup — results may vary"
- `[✓ Create as Task]`: Creates task, toast notification, redirect to `/tasks` with new task highlighted

### PAGE 4: Calendar View

**URL:** `/tasks/calendar`
**Description:** Visual calendar showing tasks scheduled on each day.

**Layout:**
- Header: `[←]` `[September 2026]` `[Today]` `[→]` with Month/Week/Day tabs (right-aligned)
- Calendar grid: 7 columns (Mon-Sun), each cell shows date number and task bars
- Bottom: `[← Prev]` `[Today]` `[Next →]` date navigation

**Calendar Cell Design:**
- Each task appears as a colored bar (priority color) with task title inside
- Time shown next to task title (e.g., "9AM")
- Hover: Bar lifts slightly (translateY -2px), shadow appears
- Click: Opens task details modal
- Current day: Subtle blue border highlight on date number
- Drag a task to another date → reschedules it (animate position change 0.3s)
- Empty day: Shows only date number, subtle placeholder dots

**Tabs:**
- Month: 7 columns, no time axis, task bars stacked vertically in each cell
- Week: 7 columns with hourly time axis on left, task bars positioned by time
- Day: Single column with hourly grid, tasks positioned by time

### PAGE 5: Reminders / Notifications Center

**URL:** `/tasks/reminders`
**Description:** All reminders, sent history, and notification preferences.

**Layout:**
- Header: "🔔 Reminders" with `[Mark all read]` and `[Settings]` buttons
- Sections grouped by time: "TODAY", "UPCOMING", "PAST"
- Each reminder card: Icon (bell), Title, Due info, Status, Action buttons (Mark done, Snooze, View task)
- Unread: Blue left border on card
- Acknowledged: Green checkmark icon, no border
- Reminder Log table (sent history):
  - Columns: Task | Sent at | Channel | User Response
  - Rows show email/push/desivery timestamps and responses (Snoozed, Acknowledged, Dismissed)
- Preferences section (bottom): Toggle switches for Email reminders, Desktop push, Sound; Lead time checkboxes (15min, 60min, 1day before due)

### PAGE 6: Analytics Dashboard

**URL:** `/tasks/analytics`
**Description:** Productivity insights, completion trends, focus session history.

**Layout:**
- Header: "📈 Analytics" with date range tabs: `[7 Days]` `[30 Days]` `[90 Days]`
- Stat cards row (4 cards across):
  1. Tasks Created (with trend arrow ↑/↓ and percentage)
  2. Tasks Completed (with trend arrow and percentage)
  3. Average Priority (star rating, e.g., "4.2 ⭐")
  4. Focus Time (hours, with trend)
- Charts row (2 charts side by side):
  1. Completion Trend: Line chart showing daily completion rate over selected period
  2. Priority Distribution: Donut chart (High / Med / Low percentages)
- Bottom section:
  - "Top Tasks Completed" — sortable list with task name, duration, date
  - Productivity Score gauge (0-100, animated arc fill)
  - AI Insight text: e.g., "Your best hours are 9-11 AM — protect this time"

### PAGE 7: Focus Mode (Full Screen Overlay)

**URL:** `/tasks/focus` or overlay modal
**Description:** Distraction-free mode. Replaces entire screen with single task, timer, and minimal UI.

**Layout:**
- Background: Soft gradient (light: blue-to-purple; dark: dark blue-to-purple)
- Centered glass morphism panel containing:
  - "FOCUS MODE 🌙" label (overline)
  - Task title (H1 size, centered, prominent)
  - Large countdown timer (96px, monospace, color changes: green → amber → red as time runs low)
  - Session info: Start time, Pomodoro count (e.g., "2 of 4")
  - Action buttons: `[⏸ Pause]` and `[■ End]`
- Bottom of screen: Subtle tip — "Focus mode blocks notifications except critical ones"
- No sidebar, no navigation, no scrolling — pure focus

**Focus Mode Details:**
- Timer ticks with smooth seconds animation (no refresh flicker)
- ESC or `[End]` button exits with smooth transition (0.5s fade back to dashboard)
- When Pomodoro completes: Gentle chime sound, confetti micro-animation (particle burst from timer), auto-suggest next task
- Desktop notification: "Focus session complete! Great work."

### PAGE 8: Daily Digest View

**URL:** `/tasks/digest`
**Description:** Morning briefing of today's tasks with yesterday's review and AI insights.

**Layout:**
- Header: "📋 Daily Digest — [Day, Date]"
- Section 1: "🌅 TODAY'S PLAN" — Timeline format (vertical timeline with time, priority badge, task title, estimated duration)
  - Each item shows: Time, Priority badge, Title, Duration, Project
- Section 2: "📊 YESTERDAY'S REVIEW" — Stats row (Completed: X/Y tasks, Time spent, Overdue items, AI insight)
- Section 3: "🤖 AI INSIGHTS" — Bullet points with insights and suggestions
- Bottom: `[✓ Got it]` and `[Snooze 1 hour]` buttons (digest dismissal)

---

## 🔔 NOTIFICATION SYSTEM DESIGN

### Notification Types

| Type | Icon | Color | Sound | Behavior |
|---|---|---|---|---|
| Task due soon (15min) | `bell` | Amber | Gentle tap | Toast top-right, stays 5s |
| Task due now | `bell-ring` | Red | Distinct chime | Toast + browser notification |
| Task overdue | `alert-triangle` | Red | Strong chime | Toast + badge counter + dashboard banner |
| Focus mode complete | `moon` | Green | Soft chime | Toast + confetti |
| AI parse ready | `sparkle` | Purple | Soft pop | Toast with "View results" button |
| Daily digest ready | `newspaper` | Blue | Soft pop | Toast at scheduled time |
| Reminder acknowledged | `check` | Green | None | In-app toast |
| Reminder snoozed | `clock` | Blue | None | In-app toast |

### Notification Toast Component

```
┌────────────────────────────────────────┐
│ [icon] Task Due in 15min              │ ← Title (bold)
│ Q3 Report due at 2:00 PM today        │ ← Description
│                    [View] [Snooze]    │ ← Actions (right-aligned)
│────────────────────────────────────────│ ← Left border color (accent)
│ Appears top-right, stacks vertically  │ ← Position
│ Max 4 toasts visible, older fade out  │ ← Stacking behavior
└────────────────────────────────────────┘
```

**Toast Animations:**
- Appears: Slide in from right (0.3s ease-out), backdrop opacity 0 → 1
- Hover: Slight scale up (1.02), pause auto-dismiss timer
- Dismiss (auto): Fade out (0.3s ease-in), slide right 20px
- Manual dismiss: Slide right + fade (0.2s)

### Badge Notifications (Top Nav)

- Red circle badge on bell icon, white number, min-width 18px, height 18px, 10px font
- Badge appears only when count > 0 (animate in with bounce 0.3s)
- When count changes: Number animates (scale up 1.3x → settle 1.0x, 0.3s)
- Bell icon: Subtle ring animation when count increases from 0

### Desktop Notifications (Browser Push)

- Slides in from bottom-right (desktop standard)
- Windows: Native toast style
- macOS: Notification center style
- Duration: 5s or until user clicks
- Click opens app to relevant task

---

## 🎭 MOTION & MICRO-ANIMATIONS

### Global Animations

| Transition | Duration | Easing | When |
|---|---|---|---|
| Page navigation | 0.3s | ease-out | Between pages |
| Modal open | 0.3s | ease-out (scale 0.95 → 1) | Modals |
| Modal close | 0.2s | ease-in (scale 1 → 0.95) | Modals |
| Card hover | 0.2s | ease-out (translateY -2px) | Cards, task rows |
| Button press | 0.1s | ease-in (scale 0.98) | All buttons |
| Button hover | 0.15s | ease | Buttons (slight brightness +1) |
| Task complete | 0.5s | ease-out | Checkbox → checkmark animation |
| List item appear | 0.3s | stagger 0.05s | Loading tasks into list |
| Toast appear | 0.3s | ease-out | All toasts |
| Notification badge | 0.3s | spring | Count changes |
| Loading skeleton | shimmer | gray-to-gray-200 | Data loading |

### Loading State

When tasks are loading, show skeleton screens:
- Gray placeholder bars matching real content dimensions
- Shimmer effect: Light gray gradient sweeps left to right (1.5s loop)
- Transitions seamlessly from skeleton → real content (fade 0.2s)

### Empty State

- Large illustration (200px), centered
- Title (H3): e.g., "No tasks yet"
- Description (body, muted): "Add your first task to get started"
- CTA button: `[+ Add Your First Task]`

---

## 🌓 THEME SYSTEM

### Themes

| Theme | Background | Cards | Text | Accent | Best For |
|---|---|---|---|---|---|
| **Light** (default) | `#FFFFFF` | `#F8F9FA` | `#1A1A2E` | `#4361EE` | Daytime, well-lit environments |
| **Dark** | `#0D0D0D` | `#161616` | `#F0F0F0` | `#4361EE` | Nighttime, low-light, focus |
| **Focus Mode** (dynamic) | Soft gradient blue→purple | Glass morphism cards | High contrast white/cream | Gold `#FFD166` | Deep work sessions |
| **High Contrast** | `#000000` | `#1A1A1A` | `#FFFFFF` | `#00FF88` | Accessibility |

### Theme Toggle Design

- Location: Profile dropdown → Settings → Appearance
- Toggle switch: `[☀️ Dark 🌙]`
- Light → Dark: Sun icon fades out, moon icon fades in, background crossfades (0.5s)
- Focus Mode: Moon icon fills in fully, background gradient activates, accent changes to gold
- Theme preference auto-saved to localStorage / user settings

### Focus Mode Theme Details

**Light Focus:**
- Background: `linear-gradient(135deg, #E8F5E9 0%, #F3E5F5 100%)`
- Card BG: `rgba(255, 255, 255, 0.85)` with 12px blur
- Text: `#1B3A1B` (dark green)
- Accent: `#FFD166` (gold)
- Timer: `#4CAF50` (green)

**Dark Focus:**
- Background: `linear-gradient(135deg, #0A1A0A 0%, #1A0A1A 100%)`
- Card BG: `rgba(30, 30, 30, 0.85)` with 12px blur
- Text: `#E8F5E9` (soft white-green)
- Accent: `#FFD166` (gold)
- Timer: `#66BB6A` (light green)

---

## 📱 MOBILE RESPONSIVE DESIGN

### Mobile Layout (< 768px)

```
┌─────────────────────┐
│ [☰]  Nexsus Tasks   │ ← Hamburger replaces sidebar
│                     │
│ [✨ AI Parse] [🔔3] │ ← Compact top bar
│                     │
│ ┌─────────────────┐ │
│ │ Good morning!   │ │ ← Greeting replaces stats row
│ │ 5 tasks today   │ │
│ └─────────────────┘ │
│                     │
│ ┌─ [+ Add] ──────┐ │ ← FAB floats bottom-right
│ ├───────────────┤ │
│ │ ● Q3 Report    │ │ ← Task rows (full width)
│ │ ● Client Email │ │
│ │ ○ Team Sync    │ │
│ └───────────────┘ │
│                     │
│ [📋]  [📅]  [📊]   │ ← Bottom tab bar replaces sidebar nav
└─────────────────────┘
```

**Mobile-Specific Changes:**
- Left sidebar → Hamburger menu (slide-in drawer from left)
- Right panel → Bottom sheet (swipe up from bottom)
- FAB `[+]` button floats bottom-right (shadow-md, scale 1.1 hover)
- Bottom tab bar: Tasks | Calendar | Analytics (replaces sidebar nav)
- Task rows: Full width, priority badge inline, compact action icons on right
- Modal: Full-screen on mobile (slide-up from bottom), dismissible by swipe down
- Calendar: Single-day view by default, swipe left/right for days, Month view as toggle
- Focus mode: Full screen, no UI chrome, single task + timer only
- Touch targets: Minimum 44x44px for all interactive elements
- Swipe gestures: Swipe right on task → Complete, Swipe left on task → Edit

**Tablet (768-1023px):**
- Left sidebar stays (narrower: 200px)
- Right panel → Bottom drawer (300px height, slide up)
- Task list: Wider, shows project column
- Calendar: Month view default, Week view available

---

## 🎯 USER FLOWS

### Flow 1: Create a Task (Happy Path)

```
[Dashboard] → Click [+ Add Task] → [Modal opens]
  → Type "Finish report by Friday" in Title field
  → Click [Parse with AI] → [Loading spinner 1.5s]
  → AI results appear (Title, Priority, Due Date, Subtasks)
  → Review and adjust if needed
  → Click [Save Task] → [Button press animation]
  → Modal closes (0.4s), toast "Task created" appears
  → New task appears at top of list (highlight flash 1.5s)
```

### Flow 2: Complete a Task

```
[Task Row] → Click [○] checkbox
  → Checkbox fills with accent color (0.3s)
  → Title gets strikethrough (0.3s)
  → Row background changes to green-tinted (0.3s)
  → Task moves to Completed section (smooth scroll, 0.5s)
  → Toast "Task completed ✨" appears top-right (3s)
  → If all tasks done: Celebration animation on dashboard
```

### Flow 3: AI Parse a Task

```
[Dashboard] → Click [✨ AI] in top nav → [Parse page opens]
  → Type "After my 3pm meeting, prepare the deck" in text area
  → Click [✨ Parse] → [Loading state 1.5s]
  → Results appear (staggered fade-in 0.1s per field)
  → Title: "Prepare the deck"
  → Priority: Medium, Dependencies: ["3pm meeting"]
  → Click [Create as Task] → [Loading 0.5s]
  → Redirect to Dashboard → New task highlighted at top
```

### Flow 4: Start Focus Mode

```
[Dashboard] → Click [🌙 Focus Mode] → [Full screen overlay]
  → Background gradient activates (0.5s)
  → Task list shows as single selection → Pick a task
  → Timer starts counting down (smooth ticks)
  → Distractions blocked (notification banner at bottom)
  → [⏸ Pause] → Timer pauses, toast "Paused"
  → [⏸ Resume] → Timer resumes, toast "Resumed"
  → Timer reaches 0 → Chime sound, confetti, "Session complete!" toast
  → [End] → Fade back to Dashboard (0.5s)
```

### Flow 5: View Reminder Notification

```
[Working in app] → 🔔 Bell badge appears (shake animation)
  → Click bell → [Notification dropdown opens]
  → See: "Task Due in 15 min: Q3 Report"
  → Options: [View task] [Snooze 15min] [Mark done]
  → [View task] → Redirect to task detail
  → [Snooze 15min] → Toast "Snoozed until 2:15 PM"
  → [Mark done] → Task completes, notification closes
```

### Flow 6: Switch Theme

```
[Top nav] → Click [👤 Profile] → [Dropdown opens]
  → Click [⚙️ Settings] → [Settings panel opens]
  → Under Appearance: Toggle [Light/Dark/Focus]
  → Background crossfades (0.5s)
  → All components update colors in real-time
  → Preference auto-saved (toast "Theme saved")
```

---

## 🧩 COMPONENT LIBRARY

### Buttons

| Type | Background | Text | Border | Hover | Height | Radius |
|---|---|---|---|---|---|---|
| Primary | `#4361EE` | White | None | Brightness +5% | 40px | 8px |
| Secondary | Transparent | `#4361EE` | 1px `#4361EE` | Background `#EEF2FF` | 40px | 8px |
| Ghost | Transparent | Text | None | Background `#F1F3F5` | 36px | 8px |
| Danger | `#EF476F` | White | None | Brightness +5% | 40px | 8px |
| FAB | `#4361EE` | `+` icon | None | Scale 1.1 | 56px | 28px (circle) |

### Cards

- Background: `var(--bg-secondary)`
- Border: 1px solid `var(--border)`
- Radius: 12px
- Padding: 16px (standard), 24px (featured)
- Shadow: `var(--shadow-sm)` default, `var(--shadow-md)` on hover
- Hover: translateY -2px, shadow increases

### Inputs

- Background: `var(--bg-tertiary)`
- Border: 1px solid `var(--border)`, 1px on focus → `var(--accent-primary)`
- Radius: 8px
- Padding: 10px 12px
- Height: 40px (standard), auto (textarea)
- Placeholder: `var(--text-muted)`
- Focus ring: 0 0 0 3px `rgba(67, 97, 238, 0.15)`

### Badges

| Type | Background | Text | Radius |
|---|---|---|---|
| Priority Critical | `#EF476F` 15% | `#EF476F` | 9999px |
| Priority High | `#FFD166` 15% | `#D97706` | 9999px |
| Priority Medium | `#4361EE` 15% | `#4361EE` | 9999px |
| Priority Low | `#06D6A0` 15% | `#059669` | 9999px |
| Status Completed | `#06D6A0` 15% | `#059669` | 9999px |
| Status Pending | `#FFD166` 15% | `#D97706` | 9999px |

### Tags (Chips)

- Background: `var(--bg-tertiary)`
- Text: `var(--text-secondary)`
- Padding: 4px 8px
- Radius: 4px
- Font: Caption size, weight 500
- With icon: Icon left of text, 12px

---

## 📊 DATA VISUALIZATION

### Progress Bars
- Background: `var(--bg-tertiary)`
- Fill: `var(--accent-success)` (green) or `var(--accent-primary)` (blue)
- Height: 6px
- Radius: 3px
- Animate width change (0.5s ease-out)
- Percentage shown at end (12px Caption, `var(--text-muted)`)

### Donut Chart (Priority Distribution)
- Center text: Total number (H2)
- Segments colored: High (red), Med (orange), Low (green)
- Hover segment: Scale 1.05, tooltip shows count + percentage
- Legend below with colored dots + labels

### Line Chart (Completion Trend)
- X-axis: Dates
- Y-axis: Completion percentage (0-100%)
- Line: `var(--accent-primary)` (blue), 2px width
- Points: 6px circles, white fill, blue border
- Hover point: Scale to 8px, tooltip with date + percentage
- Grid lines: `var(--border)`

---

## 📝 COPYWRITING TONES

| Context | Tone | Example |
|---|---|---|
| Empty state | Encouraging | "No tasks yet. Add your first task to get started." |
| Error | Helpful, not blaming | "Something went wrong. Try again or contact support." |
| Success | Warm, celebratory | "Task completed! Great work. 🎉" |
| AI suggestion | Helpful, not pushy | "Based on your schedule, we suggest starting with..." |
| Reminder | Gentle, not nagging | "Task due in 15 min — you've got this!" |
| Focus mode | Calm, minimal | Single task title and timer only. No other text. |
| Loading | Transparent | "Loading your tasks..." (brief, never more than 2s) |
| Onboarding | Friendly, brief | "Welcome! Let's set up your first task." |

---

## 🚀 GENERATION INSTRUCTIONS FOR FIGMA AI

1. **Start with the Design System** — Create all color tokens, typography styles, and component primitives first
2. **Build the Layout** — Create the 3-column grid (sidebar + main + panel) with responsive breakpoints
3. **Build the Dashboard** — Page 1 is the primary landing page. Get this right first
4. **Build the Modal** — Task creation modal with all fields and AI parse button
5. **Build remaining pages** — AI Parse, Calendar, Reminders, Analytics, Focus Mode, Digest
6. **Add Interactions** — All animations, hover states, transitions, and micro-interactions
7. **Create Components** — Convert reusable elements (cards, badges, buttons, inputs, toasts) into components
8. **Test Responsiveness** — Verify all pages at Desktop XL, Desktop, Tablet, and Mobile breakpoints
9. **Add Theme Variants** — Create Light and Dark versions of every frame/page
10. **Final Polish** — Check spacing consistency, alignment, typography hierarchy, and visual flow across all pages

---

**Project:** Nexsus AI Task Scheduler & Reminder
**Version:** 1.0 — Design Phase
**Created:** 2026-09-16
**Status:** Ready for Figma AI generation
