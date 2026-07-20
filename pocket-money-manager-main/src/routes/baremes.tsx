import { createFileRoute } from "@tanstack/react-router";
import { OperatorLayout } from "@/components/OperatorLayout";
import { Plus, Trash2, Save } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/baremes")({
  head: () => ({
    meta: [
      { title: "Barèmes de frais — FluxPay Opérateur" },
      { name: "description", content: "Édition des barèmes de frais par tranche de montant pour chaque type d'opération." },
    ],
  }),
  component: Baremes,
});

type Tier = { min: number; max: number | null; type: "Fixe" | "Pourcentage"; value: number };
type OpType = { id: string; name: string; tiers: Tier[] };

const initial: OpType[] = [
  {
    id: "retrait",
    name: "Retrait",
    tiers: [
      { min: 100, max: 5000, type: "Fixe", value: 150 },
      { min: 5001, max: 20000, type: "Fixe", value: 450 },
      { min: 20001, max: 100000, type: "Pourcentage", value: 1.5 },
      { min: 100001, max: null, type: "Pourcentage", value: 1.0 },
    ],
  },
  {
    id: "transfert",
    name: "Transfert",
    tiers: [
      { min: 100, max: 10000, type: "Fixe", value: 100 },
      { min: 10001, max: 50000, type: "Fixe", value: 300 },
      { min: 50001, max: null, type: "Pourcentage", value: 0.8 },
    ],
  },
  { id: "depot", name: "Dépôt", tiers: [{ min: 0, max: null, type: "Fixe", value: 0 }] },
];

function Baremes() {
  const [ops, setOps] = useState(initial);
  const [activeId, setActiveId] = useState("retrait");
  const active = ops.find((o) => o.id === activeId)!;

  const updateTier = (i: number, patch: Partial<Tier>) => {
    setOps(ops.map((o) => o.id === activeId ? { ...o, tiers: o.tiers.map((t, idx) => idx === i ? { ...t, ...patch } : t) } : o));
  };
  const addTier = () => {
    setOps(ops.map((o) => o.id === activeId ? { ...o, tiers: [...o.tiers, { min: 0, max: null, type: "Fixe", value: 0 }] } : o));
  };
  const removeTier = (i: number) => {
    setOps(ops.map((o) => o.id === activeId ? { ...o, tiers: o.tiers.filter((_, idx) => idx !== i) } : o));
  };

  return (
    <OperatorLayout
      title="Barèmes de frais"
      subtitle="Configurez les frais par tranche de montant pour chaque type d'opération."
      actions={
        <button className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90">
          <Save className="size-4" /> Enregistrer
        </button>
      }
    >
      <div className="flex gap-2 mb-6 border-b">
        {ops.map((o) => (
          <button
            key={o.id}
            onClick={() => setActiveId(o.id)}
            className={`px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors ${
              activeId === o.id
                ? "border-primary text-foreground"
                : "border-transparent text-muted-foreground hover:text-foreground"
            }`}
          >
            {o.name}
            <span className="ml-2 text-xs font-mono text-muted-foreground">{o.tiers.length}</span>
          </button>
        ))}
      </div>

      <div className="bg-card border rounded-xl overflow-hidden">
        <div className="px-6 py-4 border-b flex justify-between items-center">
          <div>
            <h2 className="text-sm font-semibold">Barème : {active.name}</h2>
            <p className="text-xs text-muted-foreground mt-0.5">Chaque tranche définit un frais selon le montant de l'opération.</p>
          </div>
          <button onClick={addTier} className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline">
            <Plus className="size-4" /> Ajouter une tranche
          </button>
        </div>

        <table className="w-full">
          <thead className="bg-background border-b">
            <tr className="text-[11px] font-mono uppercase text-muted-foreground">
              <th className="px-6 py-3 text-left font-medium w-16">#</th>
              <th className="px-6 py-3 text-left font-medium">Montant min (Ar)</th>
              <th className="px-6 py-3 text-left font-medium">Montant max (Ar)</th>
              <th className="px-6 py-3 text-left font-medium">Type de frais</th>
              <th className="px-6 py-3 text-left font-medium">Valeur</th>
              <th className="px-6 py-3 w-16"></th>
            </tr>
          </thead>
          <tbody className="divide-y">
            {active.tiers.map((t, i) => (
              <tr key={i} className="hover:bg-background/60 transition-colors">
                <td className="px-6 py-3 font-mono text-xs text-muted-foreground">T{i + 1}</td>
                <td className="px-6 py-3">
                  <input
                    type="number"
                    value={t.min}
                    onChange={(e) => updateTier(i, { min: Number(e.target.value) })}
                    className="w-32 px-3 py-1.5 rounded-md border bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring"
                  />
                </td>
                <td className="px-6 py-3">
                  <input
                    type="text"
                    value={t.max ?? "∞"}
                    onChange={(e) => updateTier(i, { max: e.target.value === "∞" || e.target.value === "" ? null : Number(e.target.value) })}
                    className="w-32 px-3 py-1.5 rounded-md border bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring"
                  />
                </td>
                <td className="px-6 py-3">
                  <select
                    value={t.type}
                    onChange={(e) => updateTier(i, { type: e.target.value as Tier["type"] })}
                    className="px-3 py-1.5 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                  >
                    <option>Fixe</option>
                    <option>Pourcentage</option>
                  </select>
                </td>
                <td className="px-6 py-3">
                  <div className="flex items-center gap-1.5">
                    <input
                      type="number"
                      step="0.1"
                      value={t.value}
                      onChange={(e) => updateTier(i, { value: Number(e.target.value) })}
                      className="w-24 px-3 py-1.5 rounded-md border bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                    <span className="text-xs font-mono text-muted-foreground">{t.type === "Fixe" ? "Ar" : "%"}</span>
                  </div>
                </td>
                <td className="px-6 py-3">
                  <button
                    onClick={() => removeTier(i)}
                    className="size-8 rounded-md hover:bg-destructive/10 hover:text-destructive text-muted-foreground grid place-items-center transition-colors"
                  >
                    <Trash2 className="size-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs text-muted-foreground uppercase tracking-wider mb-2">Simulateur</div>
          <div className="text-sm">Pour un {active.name.toLowerCase()} de <span className="font-mono font-semibold">50.000 Ar</span></div>
          <div className="mt-3 text-2xl font-bold text-primary">
            {(() => {
              const t = active.tiers.find((x) => 50000 >= x.min && (x.max === null || 50000 <= x.max));
              if (!t) return "—";
              return t.type === "Fixe" ? `${t.value.toLocaleString("fr-FR")} Ar` : `${((50000 * t.value) / 100).toLocaleString("fr-FR")} Ar`;
            })()}
          </div>
          <div className="text-xs text-muted-foreground mt-1 font-mono">frais estimés</div>
        </div>

        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs text-muted-foreground uppercase tracking-wider mb-2">Tranches</div>
          <div className="text-2xl font-bold">{active.tiers.length}</div>
          <div className="text-xs text-muted-foreground mt-1">définies pour {active.name}</div>
        </div>

        <div className="bg-card border rounded-xl p-5">
          <div className="text-xs text-muted-foreground uppercase tracking-wider mb-2">Dernière modif.</div>
          <div className="text-sm font-medium">Il y a 3 jours</div>
          <div className="text-xs text-muted-foreground mt-1 font-mono">par admin@fluxpay.mg</div>
        </div>
      </div>
    </OperatorLayout>
  );
}
