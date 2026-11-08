import {
    AcademicCapIcon,
    ArrowTrendingUpIcon,
    BeakerIcon,
    BookOpenIcon,
    BriefcaseIcon,
    CameraIcon,
    CodeBracketIcon,
    ComputerDesktopIcon,
    FilmIcon,
    FolderIcon,
    GlobeAltIcon,
    HeartIcon,
    HomeIcon,
    MusicalNoteIcon,
    PaintBrushIcon,
    PresentationChartLineIcon,
    RocketLaunchIcon,
    SparklesIcon,
    Squares2X2Icon,
    StarIcon,
    SwatchIcon,
    TrophyIcon,
    UsersIcon,
    VideoCameraIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import type { Component } from 'vue';
import { defineAsyncComponent } from 'vue';

export const ICON_REGISTRY: Record<string, Component> = {
    Apps: Squares2X2Icon,
    Book: BookOpenIcon,
    Books: BookOpenIcon,
    Braces: CodeBracketIcon,
    Briefcase: BriefcaseIcon,
    Brush: PaintBrushIcon,
    Camera: CameraIcon,
    ChartLine: PresentationChartLineIcon,
    Code: CodeBracketIcon,
    DeviceLaptop: ComputerDesktopIcon,
    Flask: BeakerIcon,
    Folder: FolderIcon,
    Globe: GlobeAltIcon,
    Headphones: MusicalNoteIcon,
    Heart: HeartIcon,
    Home: HomeIcon,
    Leaf: SparklesIcon,
    Movie: FilmIcon,
    Music: MusicalNoteIcon,
    Palette: SwatchIcon,
    Rocket: RocketLaunchIcon,
    School: AcademicCapIcon,
    Star: StarIcon,
    Tool: WrenchScrewdriverIcon,
    TrendingUp: ArrowTrendingUpIcon,
    Trophy: TrophyIcon,
    Users: UsersIcon,
    Video: VideoCameraIcon,
    World: GlobeAltIcon,
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

// Curated icons are already in the main bundle, so raw names that match one
// resolve synchronously. Everything else comes from the full set on demand.
const BUNDLED_ICONS: Record<string, Component> = {
    AcademicCapIcon,
    ArrowTrendingUpIcon,
    BeakerIcon,
    BookOpenIcon,
    BriefcaseIcon,
    CameraIcon,
    CodeBracketIcon,
    ComputerDesktopIcon,
    FilmIcon,
    FolderIcon,
    GlobeAltIcon,
    HeartIcon,
    HomeIcon,
    MusicalNoteIcon,
    PaintBrushIcon,
    PresentationChartLineIcon,
    RocketLaunchIcon,
    SparklesIcon,
    Squares2X2Icon,
    StarIcon,
    SwatchIcon,
    TrophyIcon,
    UsersIcon,
    VideoCameraIcon,
    WrenchScrewdriverIcon,
};

const RAW_ICON_NAME = /^[A-Z][A-Za-z0-9]*Icon$/;
const asyncIcons = new Map<string, Component>();

function loadFullSetIcon(name: string): Component {
    let icon = asyncIcons.get(name);

    if (!icon) {
        icon = defineAsyncComponent({
            loader: async () => {
                const { loadOutlineIcon } = await import('@/lib/outlineIconLoaders');

                return (await loadOutlineIcon(name)) ?? FolderIcon;
            },
            loadingComponent: FolderIcon,
            delay: 0,
        });
        asyncIcons.set(name, icon);
    }

    return icon;
}

/**
 * Resolves a stored group icon. Accepts a raw Heroicons name (RocketLaunchIcon)
 * or a legacy curated key (Braces). Falls back to a name-based guess, then Folder.
 */
export function resolveGroupIcon(iconName: string | null | undefined, groupName: string): Component {
    if (iconName) {
        if (ICON_REGISTRY[iconName]) {
            return ICON_REGISTRY[iconName];
        }

        if (RAW_ICON_NAME.test(iconName)) {
            return BUNDLED_ICONS[iconName] ?? loadFullSetIcon(iconName);
        }
    }

    for (const [pattern, name] of NAME_HINTS) {
        if (pattern.test(groupName)) {
            return ICON_REGISTRY[name] ?? FolderIcon;
        }
    }

    return FolderIcon;
}
