import type { Component } from 'vue';
import {
    IconApps,
    IconBook,
    IconBooks,
    IconBraces,
    IconBriefcase,
    IconBrush,
    IconCamera,
    IconChartLine,
    IconCode,
    IconDeviceLaptop,
    IconFlask,
    IconFolder,
    IconGlobe,
    IconHeadphones,
    IconHeart,
    IconHome,
    IconLeaf,
    IconMovie,
    IconMusic,
    IconPalette,
    IconRocket,
    IconSchool,
    IconStar,
    IconTool,
    IconTrendingUp,
    IconTrophy,
    IconUsers,
    IconVideo,
    IconWorld,
} from '@tabler/icons-vue';

export const ICON_REGISTRY: Record<string, Component> = {
    Apps: IconApps,
    Book: IconBook,
    Books: IconBooks,
    Braces: IconBraces,
    Briefcase: IconBriefcase,
    Brush: IconBrush,
    Camera: IconCamera,
    ChartLine: IconChartLine,
    Code: IconCode,
    DeviceLaptop: IconDeviceLaptop,
    Flask: IconFlask,
    Folder: IconFolder,
    Globe: IconGlobe,
    Headphones: IconHeadphones,
    Heart: IconHeart,
    Home: IconHome,
    Leaf: IconLeaf,
    Movie: IconMovie,
    Music: IconMusic,
    Palette: IconPalette,
    Rocket: IconRocket,
    School: IconSchool,
    Star: IconStar,
    Tool: IconTool,
    TrendingUp: IconTrendingUp,
    Trophy: IconTrophy,
    Users: IconUsers,
    Video: IconVideo,
    World: IconWorld,
};

const NAME_HINTS: Array<[RegExp, string]> = [
    [/dev(elop)?|coding|program|engineer|tech|software/i, 'Braces'],
    [/book|read|literature|library/i, 'Books'],
    [/mak(e|er|ing)|build|craft|diy|hack/i, 'Tool'],
    [/design|art|ui|ux|graphic|visual|creative/i, 'Palette'],
    [/business|finance|money|invest|market|econom/i, 'ChartLine'],
    [/music|audio|sound|podcast|listen/i, 'Music'],
    [/video|film|cinema|movie|watch|stream/i, 'Video'],
    [/photo|camera|picture|image/i, 'Camera'],
    [/science|research|lab|study|experiment/i, 'Flask'],
    [/news|world|global|international/i, 'Globe'],
    [/health|fitness|sport|exercise/i, 'Heart'],
    [/education|learn|school|course|tutorial/i, 'School'],
    [/travel|home|lifestyle|living/i, 'Home'],
    [/nature|garden|environment|eco/i, 'Leaf'],
    [/gaming|game|esport/i, 'Trophy'],
    [/social|community|people|group/i, 'Users'],
];

export function resolveGroupIcon(iconName: string | null | undefined, groupName: string): Component {
    if (iconName && ICON_REGISTRY[iconName]) {
        return ICON_REGISTRY[iconName];
    }
    for (const [pattern, name] of NAME_HINTS) {
        if (pattern.test(groupName)) {
            return ICON_REGISTRY[name] ?? IconFolder;
        }
    }
    return IconFolder;
}
