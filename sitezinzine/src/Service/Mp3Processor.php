<?php
// src/Service/Mp3Processor.php

namespace App\Service;

use App\Entity\Emission;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Storage\StorageInterface;

final class Mp3Processor
{
    public function __construct(
        private StorageInterface $storage,
        private Filesystem $fs,
        private string $mp3BaseDir, // ex: '%kernel.project_dir%/public/uploads/mp3'
    ) {}

    public function process(Emission $emission): void
    {
        $categorie = $emission->getCategorie();
        $code = $categorie?->getSlug();

        if ($code === null || !preg_match('/^[A-Z]{3}$/', $code)) {
            throw new \RuntimeException("Categorie.slug manquant/invalide : impossible de ranger le MP3.");
        }

        // Chemin réel du fichier uploadé par Vich (après flush)
        $sourcePath = $this->storage->resolvePath($emission, 'thumbnailFileMp3');
        if (!$sourcePath || !is_file($sourcePath)) {
            throw new \RuntimeException("Fichier MP3 introuvable après upload.");
        }

        $date = $emission->getDatepub() ?? new \DateTime();
        $yyyy = $date->format('Y');
        $mm   = $date->format('m');

        $destDir = rtrim($this->mp3BaseDir, '/\\') . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR . $yyyy . DIRECTORY_SEPARATOR . $mm;
        $this->fs->mkdir($destDir);

        // Nom de fichier final (tu peux ajuster la convention)
        $safeTitle = $this->slugifyFilename($emission->getTitre());
        $idPart = $emission->getId() ? (string) $emission->getId() : 'noid';
        $baseName = sprintf('%s_%s_%s_%s', $code, $date->format('Y-m-d'), $idPart, $safeTitle);
        $finalName = $this->uniqueName($destDir, $baseName, 'mp3');

        $destPath = $destDir . DIRECTORY_SEPARATOR . $finalName;

        // 1) écriture tags (sur le fichier source, avant déplacement)
        $this->writeTags($sourcePath, $emission);

        // 2) move + rename
        $this->fs->rename($sourcePath, $destPath, true);

        // 3) enregistrer le chemin relatif en BDD
        $relative = $code . '/' . $yyyy . '/' . $mm . '/' . $finalName;
        $emission->setThumbnailMp3($relative);
    }

    private function slugifyFilename(string $s): string
    {
        // filename safe minimal (ASCII, tirets)
        $s = trim($s);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/[^A-Za-z0-9]+/', '-', $s) ?? '';
        $s = trim($s, '-');
        return $s !== '' ? strtolower($s) : 'emission';
    }

    private function uniqueName(string $dir, string $base, string $ext): string
    {
        $i = 0;
        do {
            $suffix = $i === 0 ? '' : '-' . $i;
            $name = $base . $suffix . '.' . $ext;
            $i++;
        } while (is_file($dir . DIRECTORY_SEPARATOR . $name));

        return $name;
    }

    private function writeTags(string $filePath, Emission $emission): void
    {
        // getID3 : include + getid3_writetags (cf demos)
        // IMPORTANT: adapte le chemin si ton autoload ne charge pas ces fichiers
        $getId3Root = \dirname(__DIR__, 2) . '/vendor/james-heinrich/getid3/getid3';

        require_once $getId3Root . '/getid3.php';
        require_once $getId3Root . '/write.php';

        $tagwriter = new \getid3_writetags();
        $tagwriter->filename = $filePath;
        $tagwriter->tagformats = ['id3v2.3']; // standard très compatible
        $tagwriter->overwrite_tags = true;
        $tagwriter->tag_encoding = 'UTF-8';

        $cat = $emission->getCategorie();

        $data = [
            'title'  => [$emission->getTitre()],
            'artist' => [$emission->getRef() ?? 'Radio Zinzine'],
            'album'  => [$cat?->getTitre() ?? 'Radio Zinzine'],
            'year'   => [$emission->getDatepub()?->format('Y') ?? (new \DateTime())->format('Y')],
            'comment'=> [$this->trimComment($emission->getDescriptif())],
        ];

        $tagwriter->tag_data = $data;

        if (!$tagwriter->WriteTags()) {
            $errors = $tagwriter->errors ?? [];
            throw new \RuntimeException('Écriture des tags impossible : ' . implode(' | ', $errors));
        }
    }

    private function trimComment(string $htmlOrText): string
    {
        $text = trim(strip_tags($htmlOrText));
        // commentaire court pour éviter de gonfler les tags
        return mb_substr($text, 0, 500);
    }
}
