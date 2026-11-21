// Entry point. Module URLs are versioned through the import map in the page head.
import { startScene } from 'rs/core';

const root = document.querySelector('[data-scene]');
if (root) startScene(root);
