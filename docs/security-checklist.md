# Security checklist

- [x] Dedicated administrator sign-in, `is_admin` authorization, scoped owner/manager/catalog/operations/content roles, and login throttling
- [x] Rate-limited hire and checkout endpoints; admin login is rate limited
- [x] CSRF protection and server-side validation on implemented forms
- [x] Image uploads validated by type and size and stored outside the public source tree
- [ ] Production session/cookie settings and session regeneration reviewed for the chosen host
- [x] Audit log for administrator catalog/order/enquiry/article changes (changed field names, actor and subject; no customer field values)
- [ ] Audit role assignment and permission changes; owner/manager role assignments currently use a server-console command
- [x] No legacy secrets, payment credentials, or production customer/order data copied
- [ ] Payment callbacks authenticated and replay-safe (payment integration is not implemented)
- [ ] Production dependency audit and error handling review
- [ ] Backups and restore procedure validated before production data import

These checks describe the current local implementation, not a production security certification.

The legacy database dump is treated as sensitive. It is not copied into this project and its credential/payment rows were not inspected.
