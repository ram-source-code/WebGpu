#!/bin/sh
# Minify src/js into site/assets/js (the files the site serves). Run after editing anything in src/js.
# Needs Node; the site itself needs nothing.
set -e
cd "$(dirname "$0")/.."
npx -y esbuild@0.25.10 src/js/*.js --minify --format=esm --target=es2022 --legal-comments=none --outdir=site/assets/js --log-level=warning
# shader sources are strings: drop their comments and indentation too
node tools/strip-shaders.mjs site/assets/js/scene-a.js site/assets/js/scene-b.js
ls -l site/assets/js
