import { createFileRoute } from "@tanstack/react-router";
import { OperatorLayout } from "@/components/OperatorLayout";
import { Search, Filter, MoreVertical } from "lucide-react";

export const Route = createFileRoute("/comptes")({
  head: () => ({
    meta: [
      { title: "Comptes clients — FluxPay Opérateur" },
      { name: "description", content: "Situation des comptes clients : soldes, statut et dernières opérations." },
    ],
  }),
  component: Comptes,
});

const clients = [
  { phone: "033 45 678 90", name: "Rakoto R.", balance: "245.500", ops: 42, status: "Actif", last: "il y a 2 min" },
  { phone: "037 12 903 44", name: "Miora S.", balance: "1.245.000", ops: 128, status: "Actif", last: "il y a 8 min" },
  { phone: "033 22 156 01", name: "Andry F.", balance: "58.200", ops: 12, status: "Actif", last: "il y a 1h" },
  { phone: "037 88 442 12", name: "Naina H.", balance: "0", ops: 3, status: "Inactif", last: "il y a 12j" },
  { phone: "033 51 004 78", name: "Toky R.", balance: "845.000", ops: 88, status: "Actif", last: "il y a 4h" },
  { phone: "037 66 001 22", name: "Vola M.", balance: "12.400", ops: 5, status: "Suspendu", last: "il y a 3j" },
  { phone: "033 78 445 60", name: "Fara L.", balance: "324.700", ops: 34, status: "Actif", last: "hier" },
];

const statusColor = (s: string) =>
  s === "Actif" ? "bg-success/10 text-success"
  : s === "Suspendu" ? "bg-destructive/10 text-destructive"
  : "bg-muted text-muted-foreground";

function Comptes() {
  return (
    <OperatorLayout title="Comptes clients" subtitle="Situation en temps réel de tous les comptes du réseau.">
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs uppercase tracking-wider text-muted-foreground mb-1">Total comptes</div>
          <div className="text-2xl font-bold">12.402</div>
        </div>
        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs uppercase tracking-wider text-muted-foreground mb-1">Actifs</div>
          <div className="text-2xl font-bold text-success">11.240</div>
        </div>
        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs uppercase tracking-wider text-muted-foreground mb-1">Inactifs</div>
          <div className="text-2xl font-bold text-muted-foreground">1.108</div>
        </div>
        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs uppercase tracking-wider text-muted-foreground mb-1">Suspendus</div>
          <div className="text-2xl font-bold text-destructive">54</div>
        </div>
      </div>

      <div className="bg-card border rounded-xl overflow-hidden">
        <div className="px-6 py-4 border-b flex items-center gap-3">
          <div className="flex-1 flex items-center gap-2 px-3 py-2 bg-background border rounded-lg">
            <Search className="size-4 text-muted-foreground" />
            <input placeholder="Rechercher par numéro ou nom…" className="flex-1 bg-transparent outline-none text-sm" />
          </div>
          <button className="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-sm hover:bg-background">
            <Filter className="size-4" /> Filtrer
          </button>
        </div>

        <table className="w-full">
          <thead className="bg-background border-b">
            <tr className="text-[11px] font-mono uppercase text-muted-foreground">
              <th className="px-6 py-3 text-left font-medium">Numéro</th>
              <th className="px-6 py-3 text-left font-medium">Titulaire</th>
              <th className="px-6 py-3 text-right font-medium">Solde</th>
              <th className="px-6 py-3 text-right font-medium">Opérations</th>
              <th className="px-6 py-3 text-left font-medium">Statut</th>
              <th className="px-6 py-3 text-right font-medium">Dernière activité</th>
              <th className="px-6 py-3 w-12"></th>
            </tr>
          </thead>
          <tbody className="divide-y">
            {clients.map((c) => (
              <tr key={c.phone} className="hover:bg-background/50 transition-colors text-sm">
                <td className="px-6 py-4 font-mono">{c.phone}</td>
                <td className="px-6 py-4 font-medium">{c.name}</td>
                <td className="px-6 py-4 text-right font-mono font-medium">{c.balance} Ar</td>
                <td className="px-6 py-4 text-right font-mono text-muted-foreground">{c.ops}</td>
                <td className="px-6 py-4">
                  <span className={`inline-flex text-xs font-medium px-2 py-0.5 rounded-full ${statusColor(c.status)}`}>{c.status}</span>
                </td>
                <td className="px-6 py-4 text-right text-xs text-muted-foreground">{c.last}</td>
                <td className="px-6 py-4">
                  <button className="size-8 rounded-md hover:bg-muted grid place-items-center text-muted-foreground">
                    <MoreVertical className="size-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        <div className="px-6 py-3 border-t flex justify-between items-center text-xs text-muted-foreground">
          <span className="font-mono">7 sur 12.402 comptes</span>
          <div className="flex gap-1">
            <button className="px-3 py-1 rounded border text-foreground">1</button>
            <button className="px-3 py-1 rounded hover:bg-muted">2</button>
            <button className="px-3 py-1 rounded hover:bg-muted">3</button>
            <button className="px-3 py-1 rounded hover:bg-muted">…</button>
          </div>
        </div>
      </div>
    </OperatorLayout>
  );
}
