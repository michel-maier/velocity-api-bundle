<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\FilesystemAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TemplatingAwareTrait;

/**
 * Sdk Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SdkService
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use FilesystemAwareTrait;
    use TemplatingAwareTrait;
    /**
     * @param Filesystem      $filesystem
     * @param LoggerInterface $logger
     * @param EngineInterface $templating
     * @param array           $variables
     */
    public function __construct(Filesystem $filesystem, LoggerInterface $logger, EngineInterface $templating, array $variables = [])
    {
        $this->setFilesystem($filesystem);
        $this->setLogger($logger);
        $this->setTemplating($templating);
        $this->setVariables($variables);
    }
    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        return $this->setParameter('variables', $variables);
    }
    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getVariables()
    {
        return $this->getParameter('variables');
    }
    /**
     * @param string $path
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function generate($path, array $options = [])
    {
        $this->log(sprintf("create '%s' directory", $path), 'info');
        $this->getFilesystem()->mkdir($path);

        $exceptions = [];

        $sdk = $this->getArrayParameterKey('variables', 'sdk');

        if (null === $sdk) {
            throw $this->createRequiredException("No information set for SDK");
        }

        $f = new Finder();
        $f->ignoreDotFiles(false);
        foreach ($f->in(__DIR__.'/../Resources/views/sdk/root') as $file) {
            /** @var SplFileInfo $file */
            $realPath = $path.'/'.$file->getRelativePathname();
            if (false !== strpos($realPath, '{')) {
                $realPath = $this->getTemplating()->render('VelocityApiBundle:sdk:filename.txt.twig', ['name' => $realPath]);
            }
            if ($file->isDir()) {
                $this->getFilesystem()->mkdir($realPath);
            } else {
                try {
                    $content = 'twig' === strtolower($file->getExtension()) ? $this->getTemplating()->render('VelocityApiBundle:sdk:root/'.$file->getRelativePathname()) : $file->getContents();
                    $this->getFilesystem()->dumpFile(preg_replace('/\.twig$/', '', $realPath), $content);
                } catch (\Exception $e) {
                    $exceptions[] = $e;
                }
            }
        }

        if (count($exceptions)) {
            throw array_shift($exceptions);
        }

        unset($options);

        return $this;
    }
}
