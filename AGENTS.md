# AGENTS.md

## Release automation

This plugin is maintained in its own `ProtestsAndSuffragettes/pns-blocks`
repository. Use the global `$release-please` skill before configuring or
operating its release automation.

The repository-level Release Please manifest keeps the plugin header and
`PNS_BLOCKS_VERSION` constant in `pns-blocks.php` aligned with `package.json`.
Audit block metadata and built assets before each automated release. The
manifest must explicitly update every nonstandard WordPress version source.

Preserve the existing `0.1.0` version in the bootstrap manifest and validate
the generated release PR and built plugin assets before merging it.
