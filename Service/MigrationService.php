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

use Exception;
use Psr\Log\LoggerInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\ContainerAwareTrait;
use Velocity\Bundle\ApiBundle\Migrator\MigratorInterface;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Migration Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MigrationService implements ContainerAwareInterface
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use ContainerAwareTrait;
    use FormServiceAwareTrait;
    use ServiceAware\DatabaseServiceAwareTrait;
    /**
     * List of migrators.
     *
     * @var MigratorInterface[]
     */
    protected $migrators;
    /**
     * Construct a new service.
     *
     * @param DatabaseService    $databaseService
     * @param LoggerInterface    $logger
     * @param FormService        $formService
     * @param ContainerInterface $container
     * @param string             $collectionName
     * @param string             $directory
     * @param string             $environment
     */
    public function __construct(
        DatabaseService $databaseService, LoggerInterface $logger,
        FormService $formService, ContainerInterface $container,
        $collectionName, $directory, $environment
    )
    {
        $this->setDatabaseService($databaseService);
        $this->setLogger($logger);
        $this->setFormService($formService);
        $this->setContainer($container);
        $this->setCollectionName($collectionName);
        $this->setDirectory($directory);
        $this->setEnvironment($environment);

        $this->migrators = [];
    }
    /**
     * Return the list of registered migrators.
     *
     * @return MigratorInterface[]
     */
    public function getMigrators()
    {
        return $this->migrators;
    }
    /**
     * Add a new migrator for the specified extension (replace if exist).
     *
     * @param MigratorInterface $migrator
     * @param string            $extension
     *
     * @return $this
     */
    public function addMigrator(MigratorInterface $migrator, $extension)
    {
        $this->migrators[$extension] = $migrator;

        return $this;
    }
    /**
     * Return the migrator registered for the specified extension.
     *
     * @param string $extension
     *
     * @return MigratorInterface
     *
     * @throws Exception if no migrator registered for this extension
     */
    public function getMigratorByExtension($extension)
    {
        if (!isset($this->migrators[$extension])) {
            throw $this->createException(
                412, "No migrator registered for extension '%s'", $extension
            );
        }

        return $this->migrators[$extension];
    }
    /**
     * Return the directory containing the diff files.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->getParameter('directory');
    }
    /**
     * Set the directory containing the diff files.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function setDirectory($directory)
    {
        return $this->setParameter('directory', $directory);
    }
    /**
     * Return the current application environment (prod, preprod, ...).
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getParameter('environment');
    }
    /**
     * Set the current application environment (prod, preprod, ...).
     *
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        return $this->setParameter('environment', $environment);
    }
    /**
     * Return the collection name for the migrations.
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->getParameter('collectionName');
    }
    /**
     * Set the collection name for the migrations.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function setCollectionName($collectionName)
    {
        return $this->setParameter('collectionName', $collectionName);
    }
    /**
     * Executes the upgrade (i.e. apply missing diff files).
     *
     * @return $this
     *
     * @throws Exception
     */
    public function upgrade()
    {
        $dir = $this->getDirectory();
        $env = $this->getEnvironment();

        $appliedDiffs = [];

        foreach($this->getDatabaseService()->find($this->getCollectionName()) as $doc) {
            $appliedDiffs[$doc['id']] = true;
        }

        $files = [];

        foreach(scandir($dir) as $item) {
            $realPath = $dir . '/' . $item;
            if ('.' === $item || '..' === $item || false === is_file($realPath)) {
                continue;
            }
            $extension = null;
            if (false !== strpos($item, '.')) {
                $extension = strtolower(substr($item, strrpos($item, '.') + 1));
            }
            if (true === isset($appliedDiffs[$item])) {
                continue;
            }
            $envName  = 'common';

            if (false !== strrpos($item, '__')) {
                $envName  = substr($item, strrpos($item, '__') + 2);
                if ($extension) {
                    $envName = substr($envName, 0, strrpos($envName, '.'));
                }
            }
            $envNames = array_fill_keys(preg_split('/[,_]/', strtolower($envName)), true);

            if (!isset($envNames['common']) && !isset($envNames[$env])) {
                continue;
            }

            $files[$item] = ['path' => $realPath, 'extension' => $extension];
        }

        ksort($files);

        try {
            foreach ($files as $fileId => $file) {
                $migrator = $this->getMigratorByExtension($file['extension']);
                $this->log(sprintf("+ %s", $fileId), 'info');
                $migrator->upgrade($file['path']);
                $this->getDatabaseService()
                    ->getCollection($this->getCollectionName())
                    ->insert([
                        'id' => $fileId,
                        'date' => date('c'),
                        'md5' => md5_file($file['path']),
                    ])
                ;
            }
        } catch (FormValidationException $e) {
            throw $this->createException(
                $e->getCode(),
                "Error when processing document: %s%s",
                $e->getMessage(),
                PHP_EOL . PHP_EOL . $this->getFormService()->getErrorsAsString($e)
            );
        }

        return $this;
    }
}