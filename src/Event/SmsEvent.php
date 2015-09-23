<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Sms Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SmsEvent extends Event
{
    /**
     * @var string
     */
    protected $content;
    /**
     * @var array
     */
    protected $recipients;
    /**
     * @var array
     */
    protected $attachments;
    /**
     * @var array
     */
    protected $images;
    /**
     * @var array
     */
    protected $sender;
    /**
     * @var array
     */
    protected $options;
    /**
     * @param string $content
     * @param array  $recipients
     * @param array  $attachments
     * @param array  $images
     * @param mixed  $sender
     * @param array  $options
     */
    public function __construct($content, $recipients, array $attachments = [], array $images = [], $sender = null, array $options = [])
    {
        $this->setContent($content);
        $this->setRecipients($recipients);
        $this->setAttachments($attachments);
        $this->setImages($images);
        $this->setSender($sender);
        $this->setOptions($options);
    }
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
    /**
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }
    /**
     * @return array
     */
    public function getSender()
    {
        return $this->sender;
    }
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * @param string $content
     *
     * @return $this
     */
    protected function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
    /**
     * @param array $recipients
     *
     * @return $this
     */
    protected function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }
    /**
     * @param array $attachments
     *
     * @return $this
     */
    protected function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }
    /**
     * @param array $images
     *
     * @return $this
     */
    protected function setImages(array $images)
    {
        $this->images = $images;

        return $this;
    }
    /**
     * @param array $sender
     *
     * @return $this
     */
    protected function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }
    /**
     * @param array $options
     *
     * @return $this
     */
    protected function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
