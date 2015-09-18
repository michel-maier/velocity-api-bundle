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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\FilesystemAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TemplatingAwareTrait;
use Zend\Code\Generator\FileGenerator;

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
    use ServiceAware\MetaDataServiceAwareTrait;
    use ServiceAware\CodeGeneratorServiceAwareTrait;
    /**
     * @param Filesystem           $filesystem
     * @param LoggerInterface      $logger
     * @param EngineInterface      $templating
     * @param MetaDataService      $metaDataService
     * @param CodeGeneratorService $codeGeneratorService
     * @param array                $variables
     */
    public function __construct(Filesystem $filesystem, LoggerInterface $logger, EngineInterface $templating, MetaDataService $metaDataService, CodeGeneratorService $codeGeneratorService, array $variables = [])
    {
        $this->setLogger($logger);
        $this->setVariables($variables);
        $this->setFilesystem($filesystem);
        $this->setTemplating($templating);
        $this->setMetaDataService($metaDataService);
        $this->setCodeGeneratorService($codeGeneratorService);
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

        $this
            ->generateStaticFiles($path, $exceptions, $options)
            ->generateServices($path, $exceptions, $options)
        ;

        if (count($exceptions)) {
            throw array_shift($exceptions);
        }

        unset($options);

        return $this;
    }
    /**
     * @param string       $path
     * @param \Exception[] $exceptions
     * @param array        $options
     *
     * @return $this
     */
    protected function generateStaticFiles($path, array &$exceptions, array $options = [])
    {
        $f = new Finder();
        $f->ignoreDotFiles(false);
        $rClass = new \ReflectionClass($this);
        $root = dirname(dirname($rClass->getFileName()));
        foreach ($f->in($root.'/Resources/views/sdk/root') as $file) {
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
            if ('bin/' === substr(str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()), 0, 4)) {
                $this->getFilesystem()->chmod($realPath, 0755);
            }
        }

        return $this;
    }
    /**
     * @param string $path
     * @param array  $exceptions
     * @param array  $options
     *
     * @return $this
     */
    protected function generateServices($path, array &$exceptions, array $options = [])
    {
        foreach ($this->getMetaDataService()->getSdkServices() as $serviceName => $service) {
            $this->generateService($path, $serviceName, $service, $exceptions, $options);
            $this->generateServiceTest($path, $serviceName, $service, $exceptions, $options);
        }

        return $this;
    }
    /**
     * @param string $path
     * @param string $serviceName
     * @param array  $service
     * @param array  $exceptions
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function generateService($path, $serviceName, array $service, array &$exceptions, array $options = [])
    {
        $sdkConfig = $this->getArrayParameterKey('variables', 'sdk');

        $className = $sdkConfig['namespace'].'\\Service\\'.ucfirst($serviceName).'Service';

        $service += ['methods' => [], 'uses' => [], 'traits' => []];
        $service['methods']['__construct'] = ['type' => 'sdk.construct'];

        $service['uses'][] = ['use' => 'Phppro\\Sdk\\SdkInterface'];
        $service['uses'][] = ['use' => 'Phppro\\Sdk\\Traits', 'as' => 'SdkTraits'];

        $service['traits'][] = ['traitName' => 'SdkTraits\\SdkAwareTrait'];

        asort($service['methods']);

        $this->getFilesystem()->dumpFile(
            $path.'/src/'.str_replace('\\', '/', 'Service\\'.ucfirst($serviceName).'Service').'.php',
            $this->getCodeGeneratorService()->createClassFile($className, ['serviceName' => $serviceName] + $service)->generate()
        );

        return $this;
    }
    /**
     * @param string $path
     * @param string $serviceName
     * @param array  $service
     * @param array  $exceptions
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function generateServiceTest($path, $serviceName, array $service, array &$exceptions, array $options = [])
    {
        $sdkConfig = $this->getArrayParameterKey('variables', 'sdk');

        $serviceClassName = $sdkConfig['namespace'].'\\Service\\'.ucfirst($serviceName).'Service';
        $className = $sdkConfig['namespace'].'\\Tests\\Service\\'.ucfirst($serviceName).'ServiceTest';

        $testClass = [
            'properties' => [
                'sdk' => ['cast' => ['SdkInterface', 'PHPUnit_Framework_MockObject_MockObject'], 'visibility' => 'protected'],
            ],
            'parent' => 'PHPUnit_Framework_TestCase',
            'uses'    => [
                ['use' => 'PHPUnit_Framework_TestCase'],
                ['use' => $serviceClassName],
                ['use' => 'Phppro\\Sdk\\SdkInterface'],
                ['use' => 'PHPUnit_Framework_MockObject_MockObject'],
            ],
            'methods' => [
                'testConstruct' => ['type' => 'sdk.service.test.testConstruct'],
            ],
        ];

        asort($testClass['methods']);

        $this->getFilesystem()->dumpFile(
            $path.'/src/'.str_replace('\\', '/', 'Tests\\Service\\'.ucfirst($serviceName).'ServiceTest').'.php',
            $this->getCodeGeneratorService()->createClassFile($className, ['serviceName' => $serviceName] + $testClass)->generate()
        );

        return $this;
    }
}
