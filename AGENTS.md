# AGENTS.md

## Project workflow

This is a standalone `ProtestsAndSuffragettes/pns-blocks` repository. It is a
portable block plugin: block source lives in `blocks/` and the generated
`build/blocks/` runtime assets are committed alongside source changes. Do not
change the declared WordPress or PHP compatibility baseline without a separate
product-support decision.

Use the project-scoped WordPress skills in `.codex/skills/` to classify and
carry out WordPress work. Keep PNS site presentation in the theme rather than
adding site-wide styling to this plugin.

## Development and verification

Run Node commands from this repository with the pinned pnpm version:

```sh
pnpm install
pnpm run build
pnpm run check
pnpm run test:featured-post-assets
pnpm run test:post-metadata
```

The pre-commit hook verifies generated block assets for relevant source and
tooling changes. Never bypass it to land stale `build/blocks/` output; rebuild
and include the generated assets in the same change.

## Dex planning state

Dex is local agent-planning state, not project content. Use
`dex --storage-path .dex` for plans and executions when tracking is useful;
`.dex/` is ignored and must not be committed or synchronised externally.

## Commits

Use Conventional Commits. `feat:` and `fix:` are releasable through Release
Please; use `chore:`, `docs:`, `test:`, `build:`, or `ci:` for non-releasable
maintenance unless a release is deliberately intended.

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
