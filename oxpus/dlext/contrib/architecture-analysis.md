# oxpus/dlext Architecture Analysis

Fork: https://github.com/avatharbe/Download-Extension
Version analysed: 8.3.1

## Overview

Mature download manager for phpBB 3.2+. 70+ services, 22 database tables, 25 frontend routes, 14 ACP modes, 8 notification types. Localised in 4 languages (en, de, es, fr).

## Directory Structure

```
acp/            - ACP module registration (main_info.php, main_module.php)
adm/style/      - 20+ ACP templates, JS, event hooks
config/         - 14 YAML files (services, routing, tables, notifications)
controller/
  acp/          - 14 ACP controllers
  mcp/          - 5 MCP controllers (approve, broken, capprove, edit, manage)
  tracker/      - 3 bug tracker controllers (main, view, edit)
  ucp/          - 3 UCP controllers (config, favorite, privacy)
  *.php         - 19 frontend controllers
core/           - 20 services + 16 interfaces
  fields/       - Custom field system (admin.php, fields.php)
  helpers/      - constants, footer, navigation
event/          - listener.php (800+ lines, 17 phpBB event hooks)
language/       - en, de, es, fr
migrations/     - basics/ + v800/ + v810/ + v820/ + v830/
notification/   - 8 notification type handlers
styles/         - prosilver templates + all/ (prettyPhoto 3rdparty)
ucp/            - UCP module registration
```

## Database Tables (22)

### Core
| Table | Columns | Purpose |
|-------|---------|---------|
| `downloads` | 24 | Main download entries |
| `downloads_cat` | 34 | Categories with permissions, traffic, bug tracker flag |
| `dl_versions` | 16 | Version/release history |
| `dl_ver_files` | 7 | Files/images per version |

### Permissions & Users
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_auth` | 6 | Per-category, per-group permissions (view/dl/up/mod) |
| `dl_favorites` | 4 | User favorite downloads |
| `dl_notraf` | 2 | Traffic quota exceptions |
| `users` (extended) | +13 | Traffic, favorites, notification prefs, sort prefs |
| `groups` (extended) | +1 | Group auto-traffic allocation |

### Comments & Feedback
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_comments` | 11 | BBCode comments with approval status |
| `dl_ratings` | 3 | User ratings (dl_id, user_id, rate_point) |

### Bug Tracker
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_bug_tracker` | 17 | Bug reports with assignment, status, version |
| `dl_bug_history` | 6 | Audit trail of report changes |

### Statistics & Security
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_stats` | 9 | Download event log (user, IP, browser, traffic) |
| `dl_cat_traf` | 2 | Per-category traffic usage |
| `dl_hotlink` | 4 | Hotlink protection tokens |
| `dl_ext_blacklist` | 1 | Forbidden file extensions |

### Custom Fields
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_fields` | 11 | Field definitions |
| `dl_fields_data` | 1 | Field values per download |
| `dl_fields_lang` | 5 | Localised field labels |
| `dl_lang` | 5 | Field language storage |

### Images
| Table | Columns | Purpose |
|-------|---------|---------|
| `dl_images` | 4 | Version screenshots |

### Removed
| Table | Status | Notes |
|-------|--------|-------|
| `dl_banlist` | Dropped v8.2.5 | User/IP banning |
| `dl_rem_traf` | Dropped v8.0.0 | Migrated to phpBB config |

## Feature Inventory

### Core (keep)
- **Download management** - categories, files, versions, file hashing
- **Permission system** - per-category, per-group, cached via dl_auth
- **External downloads** - URL-based with redirect through load.php

### Likely Keep
- **Comments** - BBCode, approval queue, notifications (dl_comments)
- **Favorites** - simple CRUD, notification on new versions (dl_favorites)
- **Search** - full-text search with category/author/date filters
- **Notifications** - 8 types, integrated with phpBB notification system

### Candidates for Removal

#### 1. Traffic/Bandwidth Management - HIGH priority removal
- **What:** Per-user, per-group, per-category bandwidth quotas with monthly reset
- **Scope:** 20+ config settings, dl_notraf + dl_cat_traf tables, `users.user_traffic` column, group auto-allocation, ACP traffic controller, listener hooks
- **Why remove:** Relic from dialup/shared-hosting era. Modern hosting has no bandwidth concern. Adds complexity to every download (quota check in load.php), user management (group traffic), and admin (ACP traffic module). Nobody configures this.
- **Files:** `controller/acp/acp_traffic_controller.php`, traffic logic in `core/download.php`, `core/status.php`, `helpers/footer.php`, `listener.php`
- **Tables:** `dl_notraf`, `dl_cat_traf`, columns on `users` and `groups`
- **Config keys:** `dl_overall_traffic`, `dl_traffics_*`, `dl_traffic_*`, `dl_remain_*`, `dl_upload_traffic_count`, etc.

#### 2. Bug Tracker - HIGH priority removal
- **What:** Full issue tracker (create, assign, status, history, notifications)
- **Scope:** 3 controllers (tracker/), 2 tables, 2 notification types, per-category enable flag
- **Why remove:** Duplicates GitHub Issues / forum topics. Niche feature that adds 3 controllers, 2 tables, 2 notification types. A forum topic or link to GitHub is sufficient.
- **Files:** `controller/tracker/` (3 files), `notification/bt_assign.php`, `notification/bt_status.php`
- **Tables:** `dl_bug_tracker`, `dl_bug_history`
- **Routes:** 3 frontend routes

#### 3. Hacklist - MEDIUM priority removal
- **What:** Separate "mod directory" listing for downloads flagged as hacks/mods
- **Scope:** 1 controller, 1 core service, dedicated template, `downloads.hacklist` flag
- **Why remove:** Legacy phpBB 2.x concept. A download category achieves the same thing. The hacklist flag + metadata (author, version, dl_url) duplicates what categories + description fields already provide.
- **Files:** `controller/hacklist.php`, `core/hacklist.php`, `core/hacklist_interface.php`

#### 4. Statistics / Event Logging - MEDIUM priority removal
- **What:** Logs every download event (user, IP, browser, timestamp, traffic)
- **Scope:** dl_stats table, ACP stats controller, frontend stats pages, privacy controller
- **Why remove:** Basic click counting (already on `downloads.klicks`) is sufficient. Full event logging with IP/browser adds privacy liability (GDPR). Server access logs provide the same data. The privacy.php service exists solely to anonymise stats data.
- **Files:** `controller/acp/acp_stats_controller.php`, `controller/stats.php`, `controller/overall.php`, `core/counter.php`, `core/privacy.php`
- **Tables:** `dl_stats`
- **Keep:** Simple `klicks` / `overall_klicks` counters on `downloads` table

#### 5. Custom Profile Fields - MEDIUM priority removal
- **What:** Extensible per-download metadata with type system, validation, localisation
- **Scope:** 2 core services (844 + 280 lines), ACP controller (55KB), 3 tables, multi-language
- **Why remove:** Massive complexity (the ACP controller alone is 55KB) for a feature most installs never use. Download description + long_desc BBCode fields cover most needs. If structured metadata is needed, a simpler key-value approach would suffice.
- **Files:** `core/fields/fields.php` (844 lines), `core/fields/admin.php` (280 lines), `controller/acp/acp_fields_controller.php` (55KB)
- **Tables:** `dl_fields`, `dl_fields_data`, `dl_fields_lang`, `dl_lang`

#### 6. Hotlink Protection - LOW priority removal
- **What:** Token-based protection against direct file URL sharing
- **Scope:** dl_hotlink table, token logic in load.php, config settings
- **Why remove:** Files are already served through load.php with permission checks. The hashed filenames are not guessable. Hotlink tokens add complexity without meaningful security over the existing auth checks. Only relevant if file URLs were directly exposed, which they aren't.
- **Files:** Token logic in `controller/load.php`, `dl_hotlink` table
- **Config keys:** `dl_prevent_hotlink`, `dl_hotlink_action`

#### 7. Thumbnails/Image Gallery - LOW priority removal
- **What:** Screenshot uploads with thumbnail generation, AJAX management, version images
- **Scope:** 1 controller (490 lines), core service, dl_images table, toolbox integration
- **Why remove:** For a download manager, screenshots in the description (BBCode img) are sufficient. The thumbnail system adds image processing, file I/O complexity, and storage management. Most phpBB extensions use description text for screenshots.
- **Files:** `controller/thumbs.php`, `core/thumbnail.php`, toolbox cleanup code
- **Tables:** `dl_images`
- **Consider:** Could be kept if actively used; lower priority than traffic/bug tracker

#### 8. Ratings - LOW priority removal
- **What:** 5-point rating per download, aggregate display
- **Scope:** 1 controller (130 lines), dl_ratings table, display in listings
- **Why remove:** Low engagement feature on small communities. Simple and low-complexity though, so removal is optional.
- **Files:** `controller/rate.php`, `dl_ratings` table

### Keep As-Is
- **MCP (Moderation)** - needed for approval workflow
- **RSS Feeds** - lightweight, useful
- **Setup Wizard (Assistant)** - good UX for initial config
- **Toolbox** - useful admin utilities (though can be trimmed if bug tracker / stats removed)
- **File blacklist** - simple security feature
- **CAPTCHA integration** - leverages phpBB, minimal code

## Oversized Files

These files are candidates for refactoring regardless of feature removal:

| File | Size | Concern |
|------|------|---------|
| `core/download.php` | 1,558 lines | Upload, edit, submit, version all in one class |
| `controller/acp/acp_fields_controller.php` | ~55KB | Entire custom fields admin UI |
| `controller/acp/acp_config_controller.php` | ~63KB | All global settings in one controller |
| `controller/mcp/mcp_manage.php` | ~29KB | All moderation in one controller |
| `controller/acp/acp_toolbox_controller.php` | ~21KB | All admin tools in one file |
| `controller/details.php` | 1,326 lines | Download detail page with comments |
| `event/listener.php` | 800+ lines | 17 event handlers in one class |

## Removal Impact Estimate

If all HIGH + MEDIUM candidates are removed:

| Metric | Before | After (est.) |
|--------|--------|--------------|
| Database tables | 22 | 14 |
| Controllers | ~45 | ~35 |
| Core services | ~20 | ~14 |
| Config settings | 100+ | ~60 |
| Notification types | 8 | 4 |
| Frontend routes | 25 | 19 |
| ACP modes | 14 | 10 |

This would remove ~30% of the codebase while keeping all essential download management functionality.
