# AGENTS.md

## Release automation

This plugin belongs to the root `bnjmnrsh/pns` repository; do not create a
nested Git repository or a plugin-local GitHub release workflow. Use the global
`$release-please` skill before configuring or operating its release automation.

The root Release Please manifest must model `pns-blocks` as an independent
component at `app/public/wp-content/plugins/pns-blocks`, using component-tagged
releases. Keep the plugin header and `PNS_BLOCKS_VERSION` constant in
`pns-blocks.php` aligned with `package.json`; audit block metadata and built
assets before each first automated release. The root manifest must explicitly
update every nonstandard WordPress version source.

Preserve the existing `0.1.0` version in the bootstrap manifest and validate
the generated release PR and built plugin assets before merging it.
