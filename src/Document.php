<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

/**
 * Document Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class Document implements DocumentInterface
{
    /**
     * @var mixed
     */
    protected $content;
    /**
     * @var string
     */
    protected $contentType;
    /**
     * @var string
     */
    protected $fileName;
    /**
     * @param mixed  $content
     * @param string $contentType
     * @param string $fileName
     */
    public function __construct($content, $contentType, $fileName)
    {
        $this->setContent($content);
        $this->setContentType($contentType);
        $this->setFileName($fileName);
    }
    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * @param mixed $content
     *
     * @return $this
     */
    protected function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    /**
     * @param string $contentType
     *
     * @return $this
     */
    protected function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }
    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    /**
     * @param string $fileName
     *
     * @return $this
     */
    protected function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }
}
