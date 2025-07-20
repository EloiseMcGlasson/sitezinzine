<?php

namespace App\Service;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Exception\NameGenerationException;

class SafeFilenameNamer implements NamerInterface, ConfigurableInterface
{
    private string $propertyPath = 'titre'; // Valeur par défaut

    public function configure(array $options): void
    {
        if (empty($options['property'])) {
            throw new \InvalidArgumentException('Option "property" is missing or empty.');
        }

        $this->propertyPath = $options['property'];
    }

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        // Récupérer le titre depuis la propriété spécifiée
        try {
            $accessor = PropertyAccess::createPropertyAccessor();
            $name = $accessor->getValue($object, $this->propertyPath);
        } catch (NoSuchPropertyException $e) {
            throw new NameGenerationException(sprintf(
                'File name could not be generated: property "%s" does not exist.',
                $this->propertyPath
            ), 0, $e);
        }

        if (empty($name)) {
            throw new NameGenerationException(sprintf(
                'File name could not be generated: property "%s" is empty.',
                $this->propertyPath
            ));
        }

        // Slugifier le nom
        $safeName = $this->slugify($name);

        // Ajouter l'ID si disponible
        $id = method_exists($object, 'getId') ? $object->getId() : uniqid();

        // Ajouter l'extension du fichier
        $extension = strtolower($file->getClientOriginalExtension());

        return sprintf('%s-%s.%s', $safeName, $id, $extension);
    }

    private function slugify(string $string): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim(strtolower($slug), '-');
    }
}
