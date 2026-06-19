import type { Component } from 'vue';
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

export function resolveGroupIcon(iconName: string | null | undefined, groupName: string): Component {
    if (iconName && ICON_REGISTRY[iconName]) {
        return ICON_REGISTRY[iconName];
    }
    for (const [pattern, name] of NAME_HINTS) {
        if (pattern.test(groupName)) {
            return ICON_REGISTRY[name] ?? FolderIcon;
        }
    }
    return FolderIcon;
}
