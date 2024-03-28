<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FonctionPersonnaliserRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FonctionPersonnaliserExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [FonctionPersonnaliserRuntime::class, 'doSomething']),
        ];
    }
    // public function getFunctions(): array
    // {
    //     return [
    //         new TwigFunction('function_name', [FonctionPersonnaliserRuntime::class, 'doSomething']),
    //     ];
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('etatDemande', [FonctionPersonnaliserRuntime::class, 'etatDemande']),
            new TwigFunction('getNomFichier', [FonctionPersonnaliserRuntime::class, 'getNomFichier']),
            new TwigFunction('obtenirDelegueEncours', [FonctionPersonnaliserRuntime::class, 'obtenirDelegueEncours']),
            new TwigFunction('nombreHabitant', [FonctionPersonnaliserRuntime::class, 'nombreHabitant']),
            new TwigFunction('nombreCertificatQuartier', [FonctionPersonnaliserRuntime::class, 'nombreCertificatQuartier']),
            new TwigFunction('obtenirDateDeDebutDelegue', [FonctionPersonnaliserRuntime::class, 'obtenirDateDeDebutDelegue']),
            new TwigFunction('nombreDemandeInscriptionDeleue', [FonctionPersonnaliserRuntime::class, 'nombreDemandeInscriptionDeleue']),
            new TwigFunction('isRoleDelgue', [FonctionPersonnaliserRuntime::class, 'isRoleDelgue']),
            new TwigFunction('maireListeHabitant', [FonctionPersonnaliserRuntime::class, 'maireListeHabitant']),
            new TwigFunction('obtenirMaireEncours', [FonctionPersonnaliserRuntime::class, 'obtenirMaireEncours']),
            new TwigFunction('etatDemandeCertificat', [FonctionPersonnaliserRuntime::class, 'etatDemandeCertificat']),
            new TwigFunction('nombreDemandeInscriptionNonTraiter', [FonctionPersonnaliserRuntime::class, 'nombreDemandeInscriptionNonTraiter']),
        ];
    }
}
