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
use Symfony\Component\Yaml\Yaml;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\FilesystemAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\LoggerAwareTrait;
use Velocity\Core\Traits\TemplatingAwareTrait;

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
     * @param string|null          $customTemplateDir
     */
    public function __construct(Filesystem $filesystem, LoggerInterface $logger, EngineInterface $templating, MetaDataService $metaDataService, CodeGeneratorService $codeGeneratorService, array $variables = [], $customTemplateDir = null)
    {
        $this->setLogger($logger);
        $this->setVariables($variables);
        $this->setFilesystem($filesystem);
        $this->setTemplating($templating);
        $this->setMetaDataService($metaDataService);
        $this->setCodeGeneratorService($codeGeneratorService);
        $this->setCustomTemplateDir($customTemplateDir);
    }
    /**
     * @param string $dir
     *
     * @return $this
     */
    public function setCustomTemplateDir($dir)
    {
        return $this->setParameter('customTemplateDir', $dir);
    }
    /**
     * @return string|null
     *
     * @throws \Exception
     */
    public function getCustomTemplateDir()
    {
        return $this->getParameterIfExists('customTemplateDir');
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

        $root = dirname(dirname((new \ReflectionClass($this))->getFileName()));

        if (is_file($this->getCustomTemplateDir().'/extension/load.php')) {
            $options['extension_load'] = trim(preg_replace('/<\?php/', '', file_get_contents($this->getCustomTemplateDir().'/extension/load.php')));
        }

        if (is_file($this->getCustomTemplateDir().'/extension/prepend.php')) {
            $options['extension_prepend'] = trim(preg_replace('/<\?php/', '', file_get_contents($this->getCustomTemplateDir().'/extension/prepend.php')));
        }

        $this
            ->generateStaticFiles($root.'/Resources/views/sdk/root', $path, 'VelocityApiBundle:sdk:root/', $exceptions, $options)
            ->generateStaticFiles($this->getCustomTemplateDir().'/root', $path, null, $exceptions, $options)
            ->generateServices($path, $exceptions, $options)
            ->generateContainer($path, $exceptions, $options)
        ;

        if (count($exceptions)) {
            throw array_shift($exceptions);
        }

        unset($options);

        return $this;
    }
    /**
     * @param string $sourceDir
     * @param string $targetDir
     * @param array  $exceptions
     * @param array  $options
     *
     * @return $this
     */
    protected function generateStaticFiles($sourceDir, $targetDir, $twigPrefix, array &$exceptions, array $options = [])
    {
        if (!is_dir($sourceDir)) {
            return $this;
        }

        $f = new Finder();
        $f->ignoreDotFiles(false);

        foreach ($f->in($sourceDir) as $file) {
            /** @var SplFileInfo $file */
            $realPath = $targetDir.'/'.$file->getRelativePathname();
            if (false !== strpos($realPath, '{')) {
                $realPath = $this->getTemplating()->render('VelocityApiBundle:sdk:filename.txt.twig', ['name' => $realPath]);
            }
            if ($file->isDir()) {
                $this->getFilesystem()->mkdir($realPath);
            } else {
                try {
                    $content = 'twig' === strtolower($file->getExtension()) && null !== $twigPrefix ? $this->getTemplating()->render($twigPrefix.$file->getRelativePathname(), $options) : $file->getContents();
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
     * @param array  $exceptions
     * @param array  $options
     *
     * @return $this
     */
    protected function generateContainer($path, array &$exceptions, array $options = [])
    {
        $d = ['parameters' => [], 'services' => []];

        $prefix = $this->getSdkConfigValue('bundle_key');
        foreach ($this->getMetaDataService()->getSdkServices() as $serviceName => $service) {
            $d['parameters'][$prefix.'.'.$serviceName.'.class'] = $this->getServiceClass($serviceName);
            $d['services'][$prefix.'.'.$serviceName] = [
                'class' => '%'.$prefix.'.'.$serviceName.'.class%',
                'arguments' => ['@sdk'],
            ];
        }

        $this->getFilesystem()->dumpFile($path.'/src/Resources/config/services/sdk.yml', Yaml::dump($d, 5));

        unset($options);

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    protected function getSdkConfigValue($key, $defaultValue = null)
    {
        $sdkConfig = $this->getArrayParameterKey('variables', 'sdk');

        if (isset($sdkConfig[$key])) {
            return $sdkConfig[$key];
        }

        return $defaultValue;
    }
    /**
     * @param string $serviceName
     *
     * @return string
     */
    protected function getServiceClass($serviceName)
    {
        return $this->getSdkConfigValue('namespace').'\\Service\\'.ucfirst($serviceName).'Service';
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
        $className = $this->getServiceClass($serviceName);

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

        unset($options);

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

        unset($service);
        unset($options);

        return $this;
    }
}
