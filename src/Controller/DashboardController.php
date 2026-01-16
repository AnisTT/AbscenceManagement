<?php

namespace App\Controller;

use App\Repository\AbsenceRepository;
use App\Repository\EtudiantRepository;
use App\Repository\JustificationRepository;
use App\Repository\ClasseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EtudiantRepository $etudiantRepository,
        private AbsenceRepository $absenceRepository,
        private JustificationRepository $justificationRepository,
        private ClasseRepository $classeRepository
    ) {}

    #[Route('/', name: 'admin_dashboard')]
    #[Route('/dashboard', name: 'admin_dashboard_alt')]
    public function index(): Response
    {
        // ===== STATISTIQUES KPI =====
        $totalEtudiants = $this->etudiantRepository->countAll();
        $totalAbsences = $this->absenceRepository->countAll();
        $justificationsEnAttente = $this->justificationRepository->countEnAttente();
        
        // Calcul du taux d'absentéisme
        // Formule: (Nombre d'absences / (Nombre d'étudiants * Nombre de jours ouvrés)) * 100
        // Simplifié ici: on utilise un ratio absences/étudiants
        $tauxAbsenteisme = $totalEtudiants > 0 
            ? round(($totalAbsences / ($totalEtudiants * 20)) * 100, 1) // 20 jours de cours estimés
            : 0;

        // ===== DONNÉES GRAPHIQUE =====
        $absencesParClasse = $this->absenceRepository->countByClasse();
        
        // Préparer les données pour Chart.js
        $chartLabels = array_map(fn($item) => $item['classe'], $absencesParClasse);
        $chartData = array_map(fn($item) => $item['total'], $absencesParClasse);

        // ===== DERNIÈRES ABSENCES =====
        $dernieresAbsences = $this->absenceRepository->findLatest(10);

        // ===== STATISTIQUES SUPPLÉMENTAIRES =====
        $absencesCeMois = $this->absenceRepository->countThisMonth();
        $absencesCetteSemaine = $this->absenceRepository->countThisWeek();
        $absencesNonJustifiees = $this->absenceRepository->countNonJustifiees();

        return $this->render('admin/dashboard.html.twig', [
            // KPI Cards
            'totalEtudiants' => $totalEtudiants,
            'totalAbsences' => $totalAbsences,
            'justificationsEnAttente' => $justificationsEnAttente,
            'tauxAbsenteisme' => $tauxAbsenteisme,
            
            // Chart data
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
            
            // Table data
            'dernieresAbsences' => $dernieresAbsences,
            
            // Extra stats
            'absencesCeMois' => $absencesCeMois,
            'absencesCetteSemaine' => $absencesCetteSemaine,
            'absencesNonJustifiees' => $absencesNonJustifiees,
        ]);
    }

    #[Route('/absences', name: 'admin_absences')]
    public function absences(): Response
    {
        $absences = $this->absenceRepository->findLatest(50);
        
        return $this->render('admin/absences.html.twig', [
            'absences' => $absences,
        ]);
    }

    #[Route('/justifications', name: 'admin_justifications')]
    public function justifications(): Response
    {
        $justificationsEnAttente = $this->justificationRepository->findEnAttente();
        $justificationsTraitees = $this->justificationRepository->findLatestProcessed(10);
        
        return $this->render('admin/justifications.html.twig', [
            'justificationsEnAttente' => $justificationsEnAttente,
            'justificationsTraitees' => $justificationsTraitees,
        ]);
    }

    #[Route('/rapport', name: 'admin_rapport')]
    public function rapport(): Response
    {
        // Page de génération de rapport
        $classes = $this->classeRepository->findAll();
        $statsParClasse = $this->absenceRepository->countByClasse();
        $statsMensuelles = $this->absenceRepository->getMonthlyStats();
        
        return $this->render('admin/rapport.html.twig', [
            'classes' => $classes,
            'statsParClasse' => $statsParClasse,
            'statsMensuelles' => $statsMensuelles,
        ]);
    }
}
