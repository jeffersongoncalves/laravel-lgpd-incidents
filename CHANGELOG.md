# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-11-16

First release: LGPD security incident register (Resolução CD/ANPD 15/2024).

- `open()` sets the deadline: end of the 3rd business day after discovery, weekends and Brazilian holidays skipped (laravel-brazil-holidays); `deadline_business_days` = 6 for small processing agents
- Risk assessment (low / relevant), ANPD notification with protocol, data subject notification, close with measures
- `isPending()` / `isOverdue()` and query scopes; low-risk incidents stop counting down
- `php artisan lgpd:incidents-overdue` dispatches `IncidentOverdue`; `IncidentOpened` on register
- Records younger than 5 years can't be deleted (art. 10)

## [Unreleased]
