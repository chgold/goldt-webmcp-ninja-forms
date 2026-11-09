# Changelog

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — getSubmissions / getSubmission / countSubmissions now require the same rule Ninja Forms uses for its own submission reads: `manage_options`, passed through the `ninja_forms_api_allow_get_submissions` filter (previously only `edit_posts`, so Author/Contributor tokens could read submission PII).

## 1.0.0

- Initial release.
