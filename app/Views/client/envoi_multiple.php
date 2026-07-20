<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-xl">
    <div class="bg-card border border-border rounded-xl p-6">
        <div class="size-10 rounded-lg bg-accent text-accent-foreground grid place-items-center mb-4"><?= icon('users', 'size-5') ?></div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">Envoi vers plusieurs destinataires</h2>

        <form class="space-y-4" action="<?= url_to('envoi_multiple_apercu') ?>" method="post">
            <div id="lignes-destinataires" class="space-y-3">
                <div class="ligne-destinataire flex gap-2 items-start">
                    <input type="text" name="numero_destinataire[]" placeholder="Numéro (0331234567)"
                           class="flex-1 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                    <input type="number" step="0.01" min="0" name="montant[]" placeholder="Montant (Ar)"
                           class="w-36 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                    <button type="button" class="btn-supprimer-ligne px-2.5 py-2 rounded-lg border border-border text-muted-foreground hover:bg-background transition-colors" title="Supprimer cette ligne">
                        <?= icon('x', 'size-4') ?>
                    </button>
                </div>
                <div class="ligne-destinataire flex gap-2 items-start">
                    <input type="text" name="numero_destinataire[]" placeholder="Numéro (0331234567)"
                           class="flex-1 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                    <input type="number" step="0.01" min="0" name="montant[]" placeholder="Montant (Ar)"
                           class="w-36 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                    <button type="button" class="btn-supprimer-ligne px-2.5 py-2 rounded-lg border border-border text-muted-foreground hover:bg-background transition-colors" title="Supprimer cette ligne">
                        <?= icon('x', 'size-4') ?>
                    </button>
                </div>
            </div>

            <button type="button" id="btn-ajouter-ligne" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                <?= icon('plus', 'size-4') ?> Ajouter un destinataire
            </button>

            <label class="flex items-start gap-2.5 p-3 rounded-lg border border-border bg-background cursor-pointer">
                <input type="checkbox" name="inclure_frais_retrait" value="1"
                       <?= old('inclure_frais_retrait') ? 'checked' : '' ?>
                       class="mt-0.5 size-4 rounded border-input">
                <span class="text-xs text-muted-foreground">
                    <span class="font-medium text-foreground">Inclure les frais de retrait pour tous les destinataires</span><br>
                    Chacun recevra son montant net, sans frais lors de son prochain retrait (frais pris en charge par avance).
                </span>
            </label>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                <?= icon('check', 'size-4') ?> Continuer
            </button>
        </form>
    </div>

    <p class="text-xs text-muted-foreground mt-4 text-center">
        Un récapitulatif détaillé (par destinataire) vous sera présenté avant validation définitive.
        En cas d'erreur sur une seule ligne, aucun envoi n'est effectué.
    </p>
</div>

<script>
(function () {
    var conteneur = document.getElementById('lignes-destinataires');
    var boutonAjouter = document.getElementById('btn-ajouter-ligne');

    function creerLigne() {
        var ligne = document.createElement('div');
        ligne.className = 'ligne-destinataire flex gap-2 items-start';
        ligne.innerHTML =
            '<input type="text" name="numero_destinataire[]" placeholder="Numéro (0331234567)" ' +
            'class="flex-1 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">' +
            '<input type="number" step="0.01" min="0" name="montant[]" placeholder="Montant (Ar)" ' +
            'class="w-36 px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">' +
            '<button type="button" class="btn-supprimer-ligne px-2.5 py-2 rounded-lg border border-border text-muted-foreground hover:bg-background transition-colors" title="Supprimer cette ligne">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
            '</button>';
        return ligne;
    }

    boutonAjouter.addEventListener('click', function () {
        conteneur.appendChild(creerLigne());
    });

    conteneur.addEventListener('click', function (event) {
        var bouton = event.target.closest('.btn-supprimer-ligne');
        if (! bouton) {
            return;
        }
        var lignes = conteneur.querySelectorAll('.ligne-destinataire');
        if (lignes.length > 1) {
            bouton.closest('.ligne-destinataire').remove();
        } else {
            bouton.closest('.ligne-destinataire').querySelectorAll('input').forEach(function (champ) {
                champ.value = '';
            });
        }
    });
})();
</script>
<?= $this->endSection() ?>
