import { createFileRoute } from "@tanstack/react-router";
import { ArrowUpRight, ArrowDownRight, Wallet, ArrowLeftRight, Users } from "lucide-react";
import { OperatorLayout } from "@/components/OperatorLayout";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "Tableau de bord — FluxPay Opérateur" },
      { name: "description", content: "Vue d'ensemble des gains, opérations et comptes clients pour l'opérateur FluxPay." },
    ],
  }),
  component: Dashboard,
});

const stats = [
  { label: "Gains totaux (24h)", value: "4.820.000", unit: "Ar", icon: Wallet },
  { label: "Volume transactions", value: "128.450.000", unit: "Ar", icon: ArrowLeftRight },
  { label: "Opérations", value: "3.284", unit: "", icon: ArrowUpRight },
  { label: "Comptes actifs", value: "12.402", unit: "", icon: Users },
];

const recent = [
  { id: "TX-88214", type: "Retrait", client: "033 45 678 90", amount: "-25.000", fee: "450", time: "il y a 2 min" },
  { id: "TX-88213", type: "Transfert", client: "037 12 903 44", amount: "-80.000", fee: "1.200", time: "il y a 4 min" },
  { id: "TX-88212", type: "Dépôt", client: "033 22 156 01", amount: "+150.000", fee: "0", time: "il y a 6 min" },
  { id: "TX-88211", type: "Retrait", client: "037 88 442 12", amount: "-12.000", fee: "350", time: "il y a 9 min" },
  { id: "TX-88210", type: "Transfert", client: "033 51 004 78", amount: "-45.000", fee: "675", time: "il y a 12 min" },
];

const gainSummary = [
  { label: "Frais de retrait", value: "2.940.000 Ar" },
  { label: "Frais de transfert", value: "1.680.000 Ar" },
  { label: "Autres", value: "200.000 Ar" },
];

function Dashboard() {
  return (
    <OperatorLayout title="Tableau de bord" subtitle="Vue d'ensemble de l'activité du réseau.">
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        {stats.map((s) => {
          const Icon = s.icon;
          return (
            <div key={s.label} className="bg-card border rounded-xl p-5">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{s.label}</span>
                <Icon className="size-4 text-muted-foreground" />
              </div>
              <div className="flex items-baseline gap-1.5">
                <span className="text-2xl font-bold tracking-tight">{s.value}</span>
                {s.unit && <span className="text-sm text-muted-foreground font-mono">{s.unit}</span>}
              </div>
            </div>
          );
        })}
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div className="xl:col-span-2 bg-card border rounded-xl overflow-hidden">
          <div className="px-6 py-4 border-b flex justify-between items-center">
            <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Opérations récentes</h2>
            <button className="text-xs font-medium text-primary hover:underline">Tout voir</button>
          </div>
          <table className="w-full">
            <thead className="bg-background border-b">
              <tr className="text-[11px] font-mono uppercase text-muted-foreground">
                <th className="px-6 py-3 text-left font-medium">ID</th>
                <th className="px-6 py-3 text-left font-medium">Type</th>
                <th className="px-6 py-3 text-left font-medium">Client</th>
                <th className="px-6 py-3 text-right font-medium">Montant</th>
                <th className="px-6 py-3 text-right font-medium">Frais</th>
                <th className="px-6 py-3 text-right font-medium">Temps</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {recent.map((r) => (
                <tr key={r.id} className="hover:bg-background transition-colors text-sm">
                  <td className="px-6 py-3.5 font-mono text-xs text-muted-foreground">{r.id}</td>
                  <td className="px-6 py-3.5">
                    <span className={`inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full ${
                      r.type === "Dépôt" ? "bg-success/10 text-success" :
                      r.type === "Retrait" ? "bg-primary/10 text-primary" :
                      "bg-accent text-accent-foreground"
                    }`}>{r.type}</span>
                  </td>
                  <td className="px-6 py-3.5 font-mono text-xs">{r.client}</td>
                  <td className="px-6 py-3.5 text-right font-mono font-medium">{r.amount} Ar</td>
                  <td className="px-6 py-3.5 text-right font-mono text-xs text-muted-foreground">{r.fee} Ar</td>
                  <td className="px-6 py-3.5 text-right text-xs text-muted-foreground">{r.time}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="bg-card border rounded-xl p-6">
          <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-6">Répartition des gains</h2>
          <div className="divide-y">
            {gainSummary.map((g) => (
              <div key={g.label} className="flex justify-between items-center py-3 first:pt-0">
                <span className="text-sm font-medium">{g.label}</span>
                <span className="font-mono text-sm text-muted-foreground">{g.value}</span>
              </div>
            ))}
          </div>

          <div className="mt-6 pt-6 border-t">
            <div className="text-xs text-muted-foreground mb-1">Total collecté (mois)</div>
            <div className="text-3xl font-bold tracking-tight">142,8M <span className="text-sm font-mono text-muted-foreground">Ar</span></div>
          </div>
        </div>
      </div>
    </OperatorLayout>
  );
}
