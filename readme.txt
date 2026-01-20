=== DailyBuddy ===
Contributors: beckerilja
Tags: tools, admin, dashboard, elementor, widgets
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modular collection of essential WordPress enhancements, dashboard widgets, productivity tools, and additional Elementor features – all in one place.

== Description ==

DailyBuddy is a lightweight, modular enhancement suite for WordPress.  
It provides a curated set of powerful admin features, custom dashboard widgets, and extended Elementor widgets – all of which can be activated or deactivated individually.

Whether you want a cleaner workflow, better insights, or more creative Elementor options, DailyBuddy gives you the essential tools to improve your daily workflow.

### 🔧 Key Features

#### Dashboard Widgets (6 modules)
Enhance your WordPress admin dashboard with helpful information and productivity tools:

- Quick Notes – Personal notes and to-do lists displayed directly in the dashboard.
- Quick Stats – Displays basic site statistics such as posts, users, comments, and media counts.
- Recent Activity – Shows recent content-related activity with timestamps and direct links.
- Security Monitor – Displays general security-related system and status information.
- Server & Performance Check – Shows server and environment information such as PHP version, memory limit, database version, disk usage, and system health.
- User Activity – Displays recent user activity and currently active users in the admin area.


#### Elementor Extensions (9 modules)
Additional Elementor widgets and editor enhancements (Elementor required):

- Advanced Accordion – Configurable accordion widget with multiple layouts, numbering, icons, and styling options.
- Advanced Tabs – Tab widget with multiple design styles, icon support, scheduled tabs, and Elementor templates.
- Content Timeline – Displays content in a horizontal or vertical timeline layout, supporting dynamic posts and custom content.
- Filterable Gallery – Create filterable image galleries with multiple layout styles, search functionality, and lightbox support.
- FlipBox – Animated flip box widget for presenting content on front and back sides.
- Logo Carousel – Responsive logo carousel/slider with configurable animation options.
- Product Card – Product-style content widget with configurable layout elements such as badges, countdowns, and call-to-action buttons.
- Content Switcher – Toggle between two content sections within a single layout (e.g. pricing tables or light/dark sections).


#### WordPress Tools (7 modules)
Small utilities to improve everyday WordPress workflows:

- AI Bot Signals – Adds robots.txt and meta directives to provide guidance signals for AI crawlers regarding content handling.
- Content Folders – Organize posts, pages, and media files into folders using a drag-and-drop interface.
- Custom Login URL – Allows changing the default login URL slug for administrative access.
- Dashboard Access Control – Controls access to selected dashboard areas based on user roles and capabilities.
- Duplicate Posts/Pages – Adds an option to duplicate posts and pages within the admin interface.
- Media Replace – Replace media files while keeping existing URLs intact.
- Maintenance Mode – Displays a temporary maintenance page for visitors who are not logged in.

### Modular by Design
Every feature can be individually enabled or disabled, allowing you to use only what you need — keeping your site clean and efficient.

---

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/dailybuddy` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Go to **DailyBuddy → Modules** to enable or disable individual features.

---

== Frequently Asked Questions ==

= Does this plugin require Elementor? =
Only the Elementor Extensions modules require Elementor.  
All dashboard widgets and WordPress tools work without Elementor.

= Does this plugin slow down my site? =
No. All modules are fully modular and only load when enabled.  
Unused features remain completely inactive.

= Can I disable features I don’t use? =
Yes, every module can be switched on or off individually in the dailybuddy settings.

= Is this plugin compatible with Elementor Pro? =
Yes, and it even includes an option to hide Elementor Pro widgets from the editor.

---

== Screenshots ==

1. **Dashboard Widgets Overview** – Displays all currently activated dashboard widgets, providing quick access to notes, statistics, activities, security information, and other essential insights.
2. **Dashboard Widgets** – Notes, statistics, activities, security information, and more.
3. **Resource Folder Module** – Activated resource folder feature that allows you to organize media, pages, or posts into folders for better content management.
4. **WordPress Modules Overview**– Overview of various WordPress modules that can be easily activated or deactivated to extend functionality.

---

== Changelog ==
= 1.0.10 =
* Add Branding
* Add Mega Menu Elementor Extension
* Bugfixes resource folder Module
* Bugfixes Vertical Nav Bar Elementor Extension

= 1.0.9 =
* Improved security, compatibility, and code quality

= 1.0.8 =
* Improved security, compatibility, and code quality

= 1.0.7 =
* WordPress.org review fixes and compliance updates
* Improved security, compatibility, and code quality
* Updated plugin slug and text domain
* Refactored internal naming and asset handling

= 1.0.6 =
* Maintenance update for WordPress.org review
* Unified plugin name, text domain, and internal identifiers
* Removed reserved WP_ prefixes and improved prefixing consistency
* Fixed script and style enqueueing
* Internal refactoring and code cleanup
* No locked features or license checks

= 1.0.5 =
* Added Filterable Gallery module with multiple layouts and lightbox support

= 1.0.4 =
* Added Elementor Advanced Tabs module
* Added Elementor Content Timeline module

= 1.0.3 =
* Added AI Bot Blocker module
* Centralized module settings CSS
* Improved template structure for better maintainability

= 1.0.2 =
* Added Custom Login URL module

= 1.0.1 =
* Added Media Replace module
* Added Dashboard Access Control module
* Added Content Folders module
* Improved dashboard widgets and permission handling

= 1.0.0 =
* Initial release.