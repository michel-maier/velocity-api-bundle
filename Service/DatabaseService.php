<?php

namespace Velocity\Bundle\ApiBundle\Service;

use MongoClient;
use MongoCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;

class DatabaseService implements ContainerAwareInterface
{
    use ServiceTrait;
    use FormServiceAwareTrait;
    use LoggerServiceAwareTrait;
    /**
     * @param MongoClient        $mongoClient
     * @param LoggerInterface    $logger
     * @param ContainerInterface $container
     */
    public function __construct(MongoClient $mongoClient, LoggerInterface $logger, FormService $formService, ContainerInterface $container)
    {
        $this->setMongoClient($mongoClient);
        $this->setLoggerService($logger);
        $this->setFormService($formService);
        $this->setContainer($container);
    }
    /**
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        return $this->setService('container', $container);
    }
    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->getService('container');
    }
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->getParameter('directory');
    }
    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDirectory($value)
    {
        return $this->setParameter('directory', $value);
    }
    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getParameter('environment');
    }
    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEnvironment($value)
    {
        return $this->setParameter('environment', $value);
    }
    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->getParameter('databaseName');
    }
    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDatabaseName($value)
    {
        return $this->setParameter('databaseName', $value);
    }
    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->getParameter('collectionName');
    }
    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCollectionName($value)
    {
        return $this->setParameter('collectionName', $value);
    }
    /**
     * @return MongoClient
     */
    public function getMongoClient()
    {
        return $this->getService('mongoClient');
    }
    /**
     * @param MongoClient $service
     *
     * @return $this
     */
    public function setMongoClient(MongoClient $service)
    {
        return $this->setService('mongoClient', $service);
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    public function drop($name = null)
    {
        $realName = null === $name ? $this->getDatabaseName() : $name;

        $this->log(sprintf("Dropping database '%s'", $realName), 'info');
        $this->getMongoClient()->dropDB($realName);

        return $this;
    }
    /**
     * @param string $name
     * @param string $db
     *
     * @return MongoCollection
     */
    public function getCollection($name = null, $db = null)
    {
        return $this->getMongoClient()->selectCollection(
            null === $db   ? $this->getDatabaseName()   : $db,
            null === $name ? $this->getCollectionName() : $name
        );
    }
    /**
     * @return $this
     *
     * @throws \Exception
     */
    public function upgrade()
    {
        $dir = $this->getDirectory();
        $env = $this->getEnvironment();

        $appliedDiffs = [];

        foreach($this->getCollection()->find() as $doc) {
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

        foreach($files as $fileId => $file) {
            switch($file['extension']) {
                case 'yaml':
                    $this->log(sprintf("+ %s", $fileId), 'info');
                    $this->applyYamlDiffFile($file['path']);
                    $this->getCollection()->insert(
                        ['id' => $fileId, 'date' => date('c'), 'md5' => md5_file($file['path'])]
                    );
                    break;
                case 'php':
                    $this->log(sprintf("+ %s", $fileId), 'info');
                    $this->applyPhpDiffFile($file['path']);
                    $this->getCollection()->insert(
                        ['id' => $fileId, 'date' => date('c'), 'md5' => md5_file($file['path'])]
                    );
                    break;
                case 'disabled': break;
                default:     $this->throwException(412, "Unknown database diff file type '%s'", $file['extension']);
            }
        }

        return $this;
    }
    /**
     * @param string $path
     *
     * @return $this
     */
    protected function applyPhpDiffFile($path)
    {
        $container  = $this->getContainer();
        $logger     = $this->getLoggerService();
        $dispatcher = $this->getEventDispatcher();

        if (!is_file($path)) {
            $this->throwException(404, "Unknown PHP Diff file '%s'", $path);
        }

        include $path;

        unset($container);
        unset($logger);
        unset($dispatcher);

        return $this;
    }
    /**
     * @param string $path
     *
     * @return $this
     */
    protected function applyYamlDiffFile($path)
    {
        $data = Yaml::parse(file_get_contents($path));

        if (isset($data['db']) && is_array($data['db'])) {
            foreach($data['db'] as $type => $items) {
                $subSubType = null;
                $officialType = $type;
                if (substr_count($type, '_') > 1) {
                    $officialType = substr($type, 0, strpos($type, '_', strpos($type, '_') + 1));
                    $subSubType = substr($type, strpos($type, '_', strpos($type, '_') + 1) + 1);
                }
                $service = $this->getContainer()->get(sprintf('app.%s', str_replace('_', '.', $officialType)));
                foreach($items as $id => $item) {
                    if (!is_numeric($id) && !isset($item['id'])) {
                        $item['id'] = $id;
                    }
                    $parentTypes = [];
                    if (false !== strpos($type, '_')) {
                        $parentTypes = explode('_', $type);
                        array_pop($parentTypes);
                    }
                    try {
                        switch (count($parentTypes)) {
                            case 0:
                                if (isset($item['id']) && 'index' === $item['id']) {
                                    unset($item['id']);
                                    $indexList = [];
                                    foreach($item as $kkk => $vvv) {
                                        $indexList[] = is_string($vvv) ? $vvv : $vvv['field'];
                                    }
                                    $this->log(sprintf("  + @index %s (%s)", $type, join(',', $indexList)), 'info');
                                    $service->getRepository()->createIndexes($item);
                                    continue;
                                }
                                if (!isset($item['id']) || !$service->has($item['id'])) {
                                    $this->log(sprintf("  + %s %s", $type, is_numeric($id) ? reset($item) : (string)$id), 'info');
                                    $service->create($item);
                                } else {
                                    $itemId = $item['id'];
                                    $this->log(sprintf("  . %s %s", $type, $itemId), 'info');
                                    unset($item['id']);
                                    $service->update($itemId, $item);
                                    $item['id'] = $itemId;
                                }
                                break;
                            case 1:
                                if (!isset($item['id'])) {
                                    $this->throwException(500, 'Missing sub type id');
                                }
                                if (false === strpos($item['id'], '_')) {
                                    $this->throwException(500, "Missing parent id for '%s'", $item['id']);
                                }
                                list($itemId, $subItemId) = explode('_', $item['id'], 2);
                                if (!$service->has($itemId, $subItemId)) {
                                    $item['id'] = $subItemId;
                                    $service->create($itemId, $item);
                                } else {
                                    unset($item['id']);
                                    $service->update($itemId, $subItemId, $item);
                                }
                                break;
                            case 2:
                                if (!isset($item['id'])) {
                                    $this->throwException(500, "Missing sub sub type id");
                                }
                                if (false === strpos($item['id'], '_')) {
                                    $this->throwException(500, "Missing parent id for '%s'", $item['id']);
                                }
                                list($itemId, $subItemId, $subSubItemId) = explode('_', $item['id'], 3);
                                $method = sprintf('has%s', ucfirst($subSubType));
                                if (!method_exists($service, $method) || !$service->$method($itemId, $subItemId, $subSubItemId)) {
                                    $item['id'] = $subSubItemId;
                                    $service->{'create' . ucfirst($subSubType)}($itemId, $subItemId, $item);
                                } else {
                                    unset($item['id']);
                                    $service->update($itemId, $subItemId, $subSubItemId, $item);
                                }
                                break;
                            default:
                                $this->throwException(500, "Unsupported type '%s' (too much parent levels)", $type);
                        }
                    } catch (\Exception $e) {
                        $extraDescription = null;
                        if ($e instanceof FormValidationException) {
                            $extraDescription = $this->getFormService()->getErrorsAsString($e);
                        }
                        $this->throwException($e->getCode(), "Error when processing document '%s' of type '%s': %s", $id, $type, $e->getMessage() . ($extraDescription ? (PHP_EOL.PHP_EOL) : null) . $extraDescription);
                    }
                }
            }
        }

        return $this;
    }
}