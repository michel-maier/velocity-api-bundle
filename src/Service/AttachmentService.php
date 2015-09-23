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

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;

/**
 * Attachment Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AttachmentService
{
    use ServiceTrait;
    use ServiceAware\GeneratorServiceAwareTrait;
    /**
     * @param GeneratorService $generatorService
     */
    public function __construct(GeneratorService $generatorService)
    {
        $this->setGeneratorService($generatorService);
    }
    /**
     * @param array $definition
     * @param array $params
     * @param array $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function build(array $definition, array $params = [], array $options = [])
    {
        if (isset($definition['path'])) {
            return $this->buildFromPath($definition, $params, $options);
        }

        if (isset($definition['generator'])) {
            return $this->buildFromGenerator($definition, $params, $options);
        }

        throw $this->createRequiredException('Missing source for attachment');
    }
    /**
     * @param array $definition
     * @param array $params
     * @param array $options
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function buildFromPath(array $definition, array $params = [], array $options = [])
    {
        unset($params);
        unset($options);

        if (!is_file($definition['path'])) {
            throw $this->createNotFoundException("File '%s' not found", $definition['path']);
        }

        return $this->package(
            $definition,
            base64_encode(file_get_contents($definition['path']))
        );
    }
    /**
     * @param array $definition
     * @param array $params
     * @param array $options
     *
     * @return array
     */
    protected function buildFromGenerator(array $definition, array $params = [], array $options = [])
    {
        return $this->package(
            $definition,
            base64_encode($this->getGeneratorService()->generate($definition['generator'], $params, $options))
        );
    }
    /**
     * @param array $definition
     * @param mixed $content
     *
     * @return array
     */
    protected function package(array $definition, $content)
    {
        return [
            'name' => $definition['name'],
            'type' => $this->getMimeTypeFromFileName($definition['name']),
            'content' => $content,
        ];
    }
    /**
     * @param string $fileName
     *
     * @return string
     */
    protected function getMimeTypeFromFileName($fileName)
    {
        list($ext) = array_slice(explode('.', $fileName), -1);

        $exts = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'git' => 'image/gif',
            '.'   => 'application/octet-stream',
        ];

        if (!isset($exts[$ext])) {
            return $exts['.'];
        }

        return $exts[$ext];
    }
}
