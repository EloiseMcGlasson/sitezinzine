<?php

namespace App\Tests\Entity;

use App\Entity\Evenement;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EvenementTest extends TestCase
{
    private function getValidator(): ValidatorInterface
{
    $metadataFactory = new LazyLoadingMetadataFactory(new AttributeLoader());
    $validatorFactory = new ConstraintValidatorFactory();

    return Validation::createValidatorBuilder()
        ->setMetadataFactory($metadataFactory)
        ->setConstraintValidatorFactory($validatorFactory)
        ->getValidator();
}

    public function testValidEvenement(): void
    {
        $evenement = (new Evenement())
            ->setTitre('Festival')
            ->setOrganisateur('Org sympa')
            ->setVille('Paris')
            ->setDepartement('75')
            ->setAdresse('1 avenue des Champs')
            ->setDateDebut(new \DateTime())
            ->setDateFin(new \DateTime('+1 day'))
            ->setHoraire('20h-23h')
            ->setPrix('Gratuit')
            ->setPresentation('Une belle présentation')
            ->setContact('email@example.com')
            ->setType('Concert')
            ->setValid(true)
            ->setUpdateAt(new \DateTime())
            ->setThumbnail('thumb.jpg')
            ->setSoftDelete(false)
            ->setUser(new User());

        $validator = $this->getValidator();
        $violations = $validator->validate($evenement);

        $this->assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testTitreTooLong(): void
    {
        $evenement = new Evenement();
        $evenement->setTitre(str_repeat('A', 101)); // dépasse 100 caractères

        $validator = $this->getValidator();
        $violations = $validator->validate($evenement);

        $this->assertGreaterThan(0, count($violations), 'Aucune violation détectée pour un titre trop long');
        $this->assertSame('Le titre ne doit pas dépasser 100 caractères.', $violations[0]->getMessage());
    }

    public function testOrganisateurTooLong(): void
    {
        $evenement = new Evenement();
        $evenement->setTitre('Valide'); // requis pour ne pas faire échouer à cause du NotBlank
        $evenement->setOrganisateur(str_repeat('B', 101));

        $validator = $this->getValidator();
        $violations = $validator->validate($evenement);

        $this->assertGreaterThan(0, count($violations), 'Aucune violation détectée pour organisateur trop long');
        $this->assertSame("L'organisateur ne doit pas dépasser 100 caractères.", $violations[0]->getMessage());
    }

    private function formatViolations($violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }
        return implode("\n", $messages);
    }
}
