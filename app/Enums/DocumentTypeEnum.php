<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum DocumentTypeEnum: string
{
    use EnumTrait;

    case CNI = 'cni';
    case PASSEPORT = 'passeport';
    case PERMIS = 'permis';
    case ACTE_NAISSANCE = 'acte_naissance';
    case ATTESTATION_RESIDENCE = 'attestation_residence';
    case CARTE_CONTRIBUABLE = 'carte_contribuable';

        // ⚙️ Documents contractuels & RH
    case CONTRAT = 'contrat';
    case ADDENDUM = 'addendum';
    case FICHE_POSTE = 'fiche_poste';
    case BULLETIN_PAIE = 'bulletin_paie';
    case CONGE = 'conge';
    case EXPLICATION = 'explication';
    case AVERTISSEMENT = 'avertissement';
    case LICENCIEMENT = 'licenciement';
    case DEMISSION = 'demission';

        // 💰 Documents financiers
    case AVI = 'avi';
    case RIB = 'rib';
    case ATTESTATION_SALAIRE = 'attestation_salaire';
    case ATTESTATION_TRAVAIL = 'attestation_travail';

        // 🎓 Documents académiques
    case DIPLOME = 'diplome';
    case CERTIFICAT_TRAVAIL = 'certificat_travail';
    case FORMATION = 'formation';

        // 🏥 Documents médicaux
    case CERTIFICAT_MEDICAL = 'certificat_medical';
    case ARRET_MALADIE = 'arret_maladie';
    case VACCINATION = 'vaccination';

        // 🧳 Autres
    case AUTRE = 'autre';



    public function label(): string
    {
        return match ($this) {
            // Administratifs
            self::CNI => 'Carte nationale d\'identité',
            self::PASSEPORT => 'Passeport',
            self::PERMIS => 'Permis de conduire',
            self::ACTE_NAISSANCE => 'Acte de naissance',
            self::ATTESTATION_RESIDENCE => 'Attestation de résidence',
            self::CARTE_CONTRIBUABLE => 'Carte de contribuable',

            // Contractuels & RH
            self::CONTRAT => 'Contrat de travail',
            self::ADDENDUM => 'Avenant au contrat',
            self::FICHE_POSTE => 'Fiche de poste',
            self::BULLETIN_PAIE => 'Bulletin de paie',
            self::CONGE => 'Demande de congé',
            self::EXPLICATION => 'Demande d\'explication',
            self::AVERTISSEMENT => 'Lettre d\'avertissement',
            self::LICENCIEMENT => 'Lettre de licenciement',
            self::DEMISSION => 'Lettre de démission',

            // Financiers
            self::AVI => 'Attestation de virement irrévocable (AVI)',
            self::RIB => 'Relevé d\'identité bancaire',
            self::ATTESTATION_SALAIRE => 'Attestation de salaire',
            self::ATTESTATION_TRAVAIL => 'Attestation de travail',

            // Académiques
            self::DIPLOME => 'Diplôme ou certificat académique',
            self::CERTIFICAT_TRAVAIL => 'Certificat de travail antérieur',
            self::FORMATION => 'Attestation de formation',

            // Médicaux
            self::CERTIFICAT_MEDICAL => 'Certificat médical',
            self::ARRET_MALADIE => 'Arrêt de travail pour maladie',
            self::VACCINATION => 'Carnet ou preuve de vaccination',

            // Divers
            self::AUTRE => 'Autre document',
        };
    }
}
