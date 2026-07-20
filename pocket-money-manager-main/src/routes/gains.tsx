import { createFileRoute } from "@tanstack/react-router";
import { OperatorLayout } from "@/components/OperatorLayout";

export const Route = createFileRoute("/gains")({
  head: () => ({
    meta: [
      { title: "Situation des gains — FluxPay Opérateur" },
      { name: "description", content: "Vue détaillée des gains générés via les frais de retrait et de transfert." },
    ],
  }),
  component: Gains,
});

const dailyGains = [
  { day: "J-14", value: "12M" },
  { day: "J-13", value: "18M" },
  { day: "J-12", value: "15M" },
  { day: "J-11", value: "22M" },
  { day: "J-10", value: "28M" },
  { day: "J-9", value: "20M" },
  { day: "J-8", value: "32M" },
  { day: "J-7", value: "26M" },
  { day: "J-6", value: "30M" },
  { day: "J-5", value: "24M" },
  { day: "J-4", value: "35M" },
  { day: "J-3", value: "42M" },
  { day: "J-2", value: "38M" },
  { day: "J-1", value: "45M" },
];

const topRanges = [
  { range: "20.001 – 100.000 Ar", ops: 1240, gain: "18.6M Ar" },
  { range: "100.001+ Ar", ops: 420, gain: "12.4M Ar" },
  { range: "5.001 – 20.000 Ar", ops: 2180, gain: "9.8M Ar" },
  { range: "100 – 5.000 Ar", ops: 3420, gain: "5.1M Ar" },
];

const monthly = [
  { month: "Juillet", value: "121M Ar" },
  { month: "Août", value: "118M Ar" },
  { month: "Septembre", value: "132M Ar" },
  { month: "Octobre", value: "142M Ar" },
];

function Gains() {
  return (
    <OperatorLayout title="Situation des gains" subtitle="Revenus générés par les frais d'opérations.">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div className="bg-foreground text-background rounded-xl p-6">
          <div className="text-xs uppercase tracking-widest opacity-60 mb-2">Gains totaux — mois en cours</div>
          <div className="text-4xl font-bold tracking-tight">142.8M <span className="text-lg font-mono opacity-60">Ar</span></div>
        </div>
        <div className="bg-card border rounded-xl p-6">
          <div className="text-xs uppercase tracking-widest text-muted-foreground mb-2">Frais de retrait</div>
          <div className="text-3xl font-bold text-primary">88.4M <span className="text-sm font-mono text-muted-foreground">Ar</span></div>
        </div>
        <div className="bg-card border rounded-xl p-6">
          <div className="text-xs uppercase tracking-widest text-muted-foreground mb-2">Frais de transfert</div>
          <div className="text-3xl font-bold">54.4M <span className="text-sm font-mono text-muted-foreground">Ar</span></div>
        </div>
      </div>

      <div className="bg-card border rounded-xl p-6 mb-6">
        <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">Évolution — 14 derniers jours</h2>
        <div className="grid grid-cols-7 sm:grid-cols-14 gap-2">
          {dailyGains.map((d) => (
            <div key={d.day} className="text-center p-2 bg-background rounded-lg border">
              <div className="text-xs text-muted-foreground mb-1">{d.day}</div>
              <div className="text-sm font-mono font-medium">{d.value}</div>
            </div>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-card border rounded-xl overflow-hidden">
          <div className="px-6 py-4 border-b">
            <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Top tranches contributrices</h2>
          </div>
          <div className="divide-y">
            {topRanges.map((r) => (
              <div key={r.range} className="px-6 py-4 flex justify-between items-center">
                <div>
                  <div className="text-sm font-medium font-mono">{r.range}</div>
                  <div className="text-xs text-muted-foreground mt-0.5">{r.ops} opérations</div>
                </div>
                <div className="text-sm font-bold">{r.gain}</div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-card border rounded-xl p-6">
          <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-6">Comparaison mensuelle</h2>
          <div className="divide-y">
            {monthly.map((m) => (
              <div key={m.month} className="flex justify-between items-center py-3 first:pt-0">
                <span className="text-sm font-medium">{m.month}</span>
                <span className="font-mono text-sm text-muted-foreground">{m.value}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </OperatorLayout>
  );
}
