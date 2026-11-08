export interface GroupColor {
    key: string;
    label: string;
    /** Hero gradient top stop. */
    top: string;
    /** Hero gradient bottom stop. Both stops are dark enough for white text. */
    deep: string;
    /** Sidebar icon color and swatch fill. */
    icon: string;
}

// Keys mirror App\Models\ChannelGroup::COLORS. No color means the Cherry default.
export const GROUP_COLORS: GroupColor[] = [
    { key: 'ocean', label: 'Ocean', top: '#1d6fb8', deep: '#0f3f73', icon: '#3b9bea' },
    { key: 'teal', label: 'Teal', top: '#0f8a85', deep: '#0a4d52', icon: '#2bbdb5' },
    { key: 'forest', label: 'Forest', top: '#2f8f4e', deep: '#17512c', icon: '#4cbb6f' },
    { key: 'amber', label: 'Amber', top: '#b7791f', deep: '#6e4210', icon: '#e5a43a' },
    { key: 'orange', label: 'Orange', top: '#d2561c', deep: '#7c2d0e', icon: '#f27a3d' },
    { key: 'rose', label: 'Rose', top: '#d1365f', deep: '#7d1535', icon: '#f06b8e' },
    { key: 'fuchsia', label: 'Fuchsia', top: '#b32aa6', deep: '#661160', icon: '#d95bcd' },
    { key: 'violet', label: 'Violet', top: '#7a47d1', deep: '#3f1d8a', icon: '#9d77ea' },
    { key: 'indigo', label: 'Indigo', top: '#4350c9', deep: '#222a80', icon: '#7783ea' },
    { key: 'slate', label: 'Slate', top: '#56667a', deep: '#2a3441', icon: '#8c9bb0' },
];

export function resolveGroupColor(key: string | null | undefined): GroupColor | null {
    return GROUP_COLORS.find((color) => color.key === key) ?? null;
}

export function heroGradient(key: string | null | undefined): string {
    const color = resolveGroupColor(key);

    return `linear-gradient(180deg, ${color?.top ?? 'var(--cherry)'} 0%, ${color?.deep ?? 'var(--cherry-deep)'} 100%)`;
}
