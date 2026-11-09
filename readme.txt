=== Goldnat for Ninja Forms ===
Contributors: chagold
Tags: ninja forms, ai, mcp, forms, submissions
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: Proprietary

Read-only AI tools for Ninja Forms: forms, fields, notifications and submissions, via the Goldnat WebMCP Bridge.

== Description ==

Goldnat for Ninja Forms lets an AI agent connected through the Goldnat WebMCP Bridge read your Ninja Forms data:

* List forms and read their settings, fields, actions, email notifications, calculations and embed code.
* List, search, count and export submissions (date range, status, user filters, pagination; CSV export returned inline).

Every tool applies the same permission rule Ninja Forms applies in its own admin screens: submission data needs `manage_options` (filter `ninja_forms_api_allow_get_submissions`), form definitions need the form-builder capability (filter `ninja_forms_admin_all_forms_capabilities`).

This add-on is read only. Editing forms, fields, notifications and submissions is in **Goldnat for Ninja Forms — Pro**.

Requires the Goldnat WebMCP Bridge plugin and Ninja Forms 3.x.

== Changelog ==

= 1.2.0 =
* Breaking: write tools (updateFormTitle, duplicateForm, deleteSubmission) moved to Goldnat for Ninja Forms — Pro. The free add-on is read only.

= 1.1.0 =
* Seven new read tools: listSubmissions, searchSubmissions, exportSubmissions, getField, listNotifications, getFormEmbedCode, getCalculations.

= 1.0.1 =
* Security: submission and form reads follow the Ninja Forms permission rules.

= 1.0.0 =
* Initial release.
