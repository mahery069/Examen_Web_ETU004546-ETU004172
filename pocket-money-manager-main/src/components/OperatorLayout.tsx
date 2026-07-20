import { Link, useRouterState } from "@tanstack/react-router";
import { LayoutDashboard, Radio, Receipt, Users, TrendingUp, Settings, LogOut } from "lucide-react";
import type { ReactNode } from "react";

const nav = [
  { to: "/", label: "Tableau de bord", icon: LayoutDashboard },
  { to: "/prefixes", label: "Préfixes réseau", icon: Radio },
  { to: "/baremes", label: "Barèmes de frais", icon: Receipt },
  { to: "/gains", label: "Situation des gains", icon: TrendingUp },
  { to: "/comptes", label: "Comptes clients", icon: Users },
] as const;

export function OperatorLayout({ children, title, subtitle, actions }: {
  children: ReactNode;
  title: string;
  subtitle?: string;
  actions?: ReactNode;
}) {
  const path = useRouterState({ select: (s) => s.location.pathname });

  return (
    <div className="min-h-screen flex bg-background text-foreground">
      <aside className="w-64 shrink-0 bg-sidebar text-sidebar-foreground flex flex-col">
        <div className="h-16 flex items-center gap-3 px-6 border-b border-sidebar-border">
          <div className="size-8 rounded-lg bg-primary grid place-items-center text-primary-foreground font-bold">F</div>
          <div>
            <div className="text-sm font-semibold text-white">FluxPay</div>
            <div className="text-[10px] font-mono uppercase tracking-widest text-sidebar-foreground/60">Opérateur</div>
          </div>
        </div>

        <nav className="flex-1 p-3 space-y-1">
          {nav.map((item) => {
            const active = path === item.to;
            const Icon = item.icon;
            return (
              <Link
                key={item.to}
                to={item.to}
                className={`flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors ${
                  active
                    ? "bg-sidebar-primary text-sidebar-primary-foreground font-medium"
                    : "hover:bg-sidebar-accent text-sidebar-foreground/80"
                }`}
              >
                <Icon className="size-4" />
                {item.label}
              </Link>
            );
          })}
        </nav>

        <div className="p-3 border-t border-sidebar-border space-y-1">
          <button className="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground/80 hover:bg-sidebar-accent transition-colors">
            <Settings className="size-4" /> Paramètres
          </button>
          <button className="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground/80 hover:bg-sidebar-accent transition-colors">
            <LogOut className="size-4" /> Déconnexion
          </button>
        </div>
      </aside>

      <div className="flex-1 flex flex-col min-w-0">
        <header className="h-16 border-b bg-card flex items-center justify-between px-8">
          <div>
            <div className="text-[10px] font-mono uppercase tracking-widest text-primary">Operator Terminal v4.2</div>
            <h1 className="text-lg font-semibold tracking-tight">{title}</h1>
          </div>
          <div className="flex items-center gap-3">
            {actions}
            <div className="flex items-center gap-2 pl-3 border-l">
              <div className="size-2 rounded-full bg-success"></div>
              <span className="text-xs font-mono">RÉSEAU OK</span>
            </div>
          </div>
        </header>

        <main className="flex-1 p-8 overflow-y-auto">
          {subtitle && <p className="text-sm text-muted-foreground mb-6 -mt-2">{subtitle}</p>}
          {children}
        </main>
      </div>
    </div>
  );
}
