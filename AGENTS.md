# Project Operating Notes

- Build a lightweight custom WordPress + WooCommerce storefront with PHP templates, WooCommerce hooks/APIs, `theme.json` where useful, vanilla CSS, and small vanilla JavaScript only when needed.
- Use WooCommerce first, then WordPress core, then a small amount of custom code. Add third-party dependencies only when clearly justified.
- Good enough is enough: satisfy the current requirement with the simplest maintainable implementation. Do not build speculative infrastructure or future features early.
- Do not use page builders, React/Next frontend, Tailwind, Bootstrap, unnecessary npm tooling, or unnecessary Composer dependencies.
- Presentation code belongs in `theme/vapestore/`; store-specific business behavior belongs in `plugins/vapestore-core/`.
- Content model: Classic Editor for normal pages, ACF fields for fixed homepage content, WooCommerce for store data, with no Gutenberg/page-builder dependency.
- Never modify WordPress core, WooCommerce, third-party plugins, uploads, credentials, server config, or the database unless explicitly instructed.
- Do not run destructive filesystem/database commands, update software, activate/deactivate plugins or themes, create symlinks, commit, or push unless asked.
- Follow WordPress coding conventions, WooCommerce public APIs/hooks, semantic HTML, escaped output, sanitized input, nonces for state changes, and capability checks.
- Preserve unrelated changes. Inspect Git status before edits and never reset or overwrite existing work.
