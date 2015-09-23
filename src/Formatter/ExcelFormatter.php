<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Formatter;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\DocumentBuilderService;

/**
 * Json Formatter Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExcelFormatter
{
    use ServiceTrait;
    use ServiceAware\DocumentBuilderServiceAwareTrait;
    /**
     * @param DocumentBuilderService $documentBuilderService
     */
    public function __construct(DocumentBuilderService $documentBuilderService)
    {
        $this->setDocumentBuilderService($documentBuilderService);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("text/csv")
     */
    public function formatCsv($data, array $options = [])
    {
        return $this->getDocumentBuilderService()->build('excel', $data, ['filename' => 'result.csv'], $options);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("text/html")
     */
    public function formatHtml($data, array $options = [])
    {
        return $this->getDocumentBuilderService()->build('excel', $data, ['filename' => 'result.html'], $options);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("application/vnd.ms-excel")
     */
    public function formatXls($data, array $options = [])
    {
        return $this->getDocumentBuilderService()->build('excel', $data, ['filename' => 'result.xls'], $options);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("application/vnd.oasis.opendocument.spreadsheet")
     */
    public function formatOds($data, array $options = [])
    {
        return $this->getDocumentBuilderService()->build('excel', $data, ['filename' => 'result.ods'], $options);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
     */
    public function formatXlsx($data, array $options = [])
    {
        return $this->getDocumentBuilderService()->build('excel', $data, ['filename' => 'result.xlsx'], $options);
    }
}
