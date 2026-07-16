# PNS Blocks

Project-owned portable WordPress blocks for Protests and Suffragettes.

## Scope

- Plugin path: `app/public/wp-content/plugins/pns-blocks/`
- Namespace: `pns/*`
- Blocks: `pns/split-section`, `pns/featured-post`, `pns/post-metadata`, and `pns/post-details`

The plugin owns block registration, block editor behavior, frontend behavior,
data access, stable rendered markup contracts, and self-contained block styles.
Block styles should rely on WordPress/theme.json design artifacts such as preset
CSS variables and block supports, not project-global theme CSS.

Themes may tune the result through standard theme.json presets and scoped block
overrides, but a block should remain usable when moved into a generic plugin.

WordPress content, synced patterns, templates, and template parts own placement
and editorial copy.

## Structure

```text
pns-blocks.php
includes/
  Assets.php
  Blocks.php
blocks/
  layout/
    split-section/
      block.json
      index.js
      render.php
  query/
    featured-post/
      block.json
      index.js
      render.php
```

`includes/Blocks.php` recursively discovers and registers any
`blocks/<family>/<block-name>/block.json` directory on `init`.

## Tooling

Run plugin tooling from this directory:

```sh
pnpm install
pnpm run build
pnpm run check
pnpm run test:featured-post-assets
pnpm run test:post-metadata
```

`blocks/` is the source tree. `build/blocks/` is the runtime tree registered by
WordPress when present, and generated build assets should be committed with block
source changes.

`test:featured-post-assets` is a read-only WordPress registry check for the
Featured Post block's shared Split Section frontend and editor style handles.

`test:post-metadata` is a read-only WordPress render check for the Post
Metadata and Post Details blocks' explicit post context, inserter visibility,
metadata-only cards, hero taxonomy pills, and empty-taxonomy behaviour.

The plugin uses `@wordpress/scripts` for block builds, JavaScript linting,
package metadata linting, and WordPress-compatible formatting. Stylelint extends
the WordPress CSS standards with the project-owned `.pns-*` selector convention.

`pnpm-workspace.yaml` records the dependency build scripts approved for pnpm 11
installs.

## Block Families

- `blocks/layout/` owns portable structural layout blocks such as split
  sections. Structural layout, editor parity, edge-media alignment, and image
  frame behavior belong in the block package. The active PNS theme supplies
  tokens, surfaces, and site-level visual defaults rather than recreating split
  section mechanics through theme cascade overrides.
- `blocks/query/featured-post/` owns the fixed featured-query renderer used by
  the Home and Herstories archive templates. It deliberately shares Split
  Section's registered frontend and editor styles because its renderer emits
  the Split Section markup contract; this dependency is declared in
  `featured-post/block.json`, not assumed from page asset order.
- New families should be added only when a block has a different product domain,
  dependency profile, or release cadence from the existing families.

## Optional integrations

PNS Blocks does not require Jetpack. When Jetpack's `jetpack/slideshow` block
is registered in the editor, Split Section exposes its Jetpack slideshow media
control and slideshow variation. Without that block, editors can still create
image and video Split Sections; existing saved slideshow content is left
unchanged.

## Guardrails

- Do not edit third-party plugins to add PNS behavior.
- Do not duplicate this scaffold per block.
- Do not put unrelated block families directly under `blocks/`.
- Keep secrets and external-service credentials out of markup, logs, and docs.
- Prefer stable `.pns-*` classes for rendered markup contracts.

## Extracted Blocks

The Ecwid shop teaser now lives in the sibling `ran-ecwid-shop-teaser` plugin
as the canonical `ran/ecwid-shop-teaser` block. Its README owns the Ecwid
integration, credential, cache, and extension details.

RAN Video Cover now lives in the sibling `ran-video-cover` plugin as the
canonical `ran/video-cover` block. Its README owns the video, reduced-motion,
and pause/play contracts.
