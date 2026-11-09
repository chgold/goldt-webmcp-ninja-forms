# Changelog

## 1.2.0 — 2026-11-09

- Breaking: write tools moved to Goldnat for Ninja Forms — Pro (`goldt-webmcp-ninja-forms-pro`). `ninjaforms.updateFormTitle`, `ninjaforms.duplicateForm` and `ninjaforms.deleteSubmission` are no longer in the free add-on; install the Pro add-on to keep them (same tool names). The free add-on is now read only (14 tools).
- Added readme.txt.

## 1.1.0 — 2026-11-09

- New (free, read): `ninjaforms.listSubmissions` — submissions with form / date range / status (active, trash, all) / user filters, pagination (`limit`, `page`, `total`, `total_pages`), sequence number and field values.
- New (free, read): `ninjaforms.searchSubmissions` — keyword search across all submission field values, with the same filters; returns the matched field keys.
- New (free, read): `ninjaforms.exportSubmissions` — CSV export of a form's submissions (date range, field selection, up to 2000 rows) returned inline; nothing is written to the public uploads folder, and cells are protected against spreadsheet formula injection.
- New (free, read): `ninjaforms.getField` — full configuration of one field (by ID or key) on a form.
- New (free, read): `ninjaforms.listNotifications` — the form's email actions: recipient, subject, message, from/reply-to/cc/bcc, format, CSV attachment, conditions.
- New (free, read): `ninjaforms.getFormEmbedCode` — shortcode, block markup and PHP call for a form.
- New (free, read): `ninjaforms.getCalculations` — form calculations plus the fields that carry or display calculation values.
- Permissions: the three submission tools use the Ninja Forms submission rule (`manage_options` through `ninja_forms_api_allow_get_submissions`); the four form tools use the form-builder capability (`ninja_forms_admin_all_forms_capabilities`).
- Fix: a non-existent `form_id` now returns `form_not_found` (404) in every tool — the Ninja Forms model echoes back any requested ID, so the previous existence check always passed.

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — getSubmissions / getSubmission / countSubmissions now require the same rule Ninja Forms uses for its own submission reads: `manage_options`, passed through the `ninja_forms_api_allow_get_submissions` filter (previously only `edit_posts`, so Author/Contributor tokens could read submission PII).
- Security: permission ceiling (SEC-00) — listForms / getForm / getFormFields / getFormActions now require the Ninja Forms form-builder capability (`manage_options`, filterable via `ninja_forms_admin_all_forms_capabilities`) instead of `edit_posts`.

## 1.0.0

- Initial release.
