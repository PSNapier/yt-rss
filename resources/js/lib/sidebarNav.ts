const base = 'border border-transparent text-sidebar-foreground';

const active =
    'border-cherry bg-transparent font-medium text-sidebar-foreground hover:bg-transparent hover:text-sidebar-foreground data-[active=true]:bg-transparent data-[active=true]:text-sidebar-foreground';

export const sidebarNavButtonClass = (isActive: boolean): string => (isActive ? `${base} ${active}` : base);
