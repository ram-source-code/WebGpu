// Removes // comments and blank/indented whitespace inside shader template strings of minified files.
import { readFileSync, writeFileSync } from 'node:fs';
for (const file of process.argv.slice(2)) {
  const src = readFileSync(file, 'utf8');
  const out = src.replace(/`[^`]*`/g, (tpl) => {
    if (!/fn |void main|@vertex|@compute|#version/.test(tpl)) return tpl;
    return tpl
      .split(/\\n|\n/)
      .map((l) => l.replace(/\/\/(?![^"]*"\)).*$/, '').trim())
      .filter(Boolean)
      .join('\\n');
  });
  writeFileSync(file, out);
}
