import { createFileRoute } from "@tanstack/react-router";
import { Plus, X, Check } from "lucide-react";
import { OperatorLayout } from "@/components/OperatorLayout";
import { useState } from "react";

export const Route = createFileRoute("/prefixes")({
  head: () => ({
    meta: [
      { title: "Préfixes réseau — FluxPay Opérateur" },
      { name: "description", content: "Configuration des préfixes de numéros de téléphone valables pour l'opérateur." },
    ],
  }),
  component: Prefixes,
});

function Prefixes() {
  const [prefixes, setPrefixes] = useState([
    { code: "033", label: "Telma", active: true, count: 6402 },
    { code: "037", label: "Airtel", active: true, count: 6000 },
  ]);
  const [draft, setDraft] = useState("");

  const add = () => {
    if (/^0\d{2}$/.test(draft) && !prefixes.find((p) => p.code === draft)) {
      setPrefixes([...prefixes, { code: draft, label: "Nouveau", active: true, count: 0 }]);
      setDraft("");
    }
  };

  return (
    <OperatorLayout title="Préfixes réseau" subtitle="Définissez les préfixes de numéros valables pour l'inscription et les opérations.">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-card border rounded-xl">
          <div className="px-6 py-4 border-b">
            <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Préfixes autorisés</h2>
          </div>
          <div className="divide-y">
            {prefixes.map((p) => (
              <div key={p.code} className="px-6 py-4 flex items-center justify-between">
                <div className="flex items-center gap-4">
                  <div className="size-10 rounded-lg bg-primary/10 text-primary grid place-items-center font-mono font-semibold">
                    {p.code}
                  </div>
                  <div>
                    <div className="font-medium">{p.label}</div>
                    <div className="text-xs text-muted-foreground font-mono">{p.count.toLocaleString("fr-FR")} comptes</div>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <span className="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full bg-success/10 text-success">
                    <Check className="size-3" /> Actif
                  </span>
                  <button
                    onClick={() => setPrefixes(prefixes.filter((x) => x.code !== p.code))}
                    className="size-8 rounded-md hover:bg-muted grid place-items-center text-muted-foreground hover:text-destructive transition-colors"
                  >
                    <X className="size-4" />
                  </button>
                </div>
              </div>
            ))}
          </div>

          <div className="px-6 py-4 border-t bg-background/50 flex gap-3">
            <div className="flex-1 flex items-center gap-2 px-3 py-2 bg-card border rounded-lg">
              <span className="text-muted-foreground font-mono text-sm">+261</span>
              <input
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && add()}
                placeholder="ex. 034"
                className="flex-1 bg-transparent outline-none text-sm font-mono placeholder:text-muted-foreground/50"
                maxLength={3}
              />
            </div>
            <button onClick={add} className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
              <Plus className="size-4" /> Ajouter
            </button>
          </div>
        </div>

        <div className="bg-card border rounded-xl p-6 space-y-5">
          <div>
            <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Règle de validation</h2>
            <p className="text-sm text-muted-foreground">
              Seuls les numéros commençant par un préfixe autorisé peuvent se connecter et effectuer des opérations.
            </p>
          </div>
          <div className="p-4 rounded-lg bg-background border font-mono text-xs">
            <div className="text-muted-foreground mb-2">Exemple valide :</div>
            <div className="text-foreground">+261 <span className="text-primary font-semibold">033</span> 45 678 90</div>
          </div>
          <div className="pt-4 border-t">
            <div className="text-xs text-muted-foreground mb-1">Format attendu</div>
            <div className="font-mono text-sm">3 chiffres, commence par 0</div>
          </div>
        </div>
      </div>
    </OperatorLayout>
  );
}
