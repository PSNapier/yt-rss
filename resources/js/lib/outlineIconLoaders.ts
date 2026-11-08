import type { Component } from 'vue';

// Per-icon lazy loaders (one tiny chunk each). This module is itself only
// dynamically imported, so the path table stays out of the main bundle.
const OUTLINE_DIR = '/node_modules/@heroicons/vue/24/outline/esm';
const loaders = import.meta.glob<Component>(
    '/node_modules/@heroicons/vue/24/outline/esm/*Icon.js',
    { import: 'default' },
);

export function loadOutlineIcon(name: string): Promise<Component> | null {
    return loaders[`${OUTLINE_DIR}/${name}.js`]?.() ?? null;
}
