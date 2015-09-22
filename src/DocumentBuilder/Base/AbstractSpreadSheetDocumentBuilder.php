<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DocumentBuilder\Base;

use Velocity\Bundle\ApiBundle\Document;
use Velocity\Bundle\ApiBundle\DocumentInterface;

/**
 * Abstract SpreadSheet Document Builder.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractSpreadSheetDocumentBuilder extends AbstractDocumentBuilder
{
    /**
     * @param array $data
     * @param array $metas
     * @param array $options
     *
     * @return DocumentInterface
     */
    public function build(array $data = [], $metas = [], array $options = [])
    {
        $metas += ['creator' => null, 'modifier' => null, 'filename' => 'spreadsheet.xlsx'];

        $path = tempnam(sys_get_temp_dir(), md5(__DIR__).'-excel');

        $excel = new \PHPExcel();

        $excel->getProperties()->setCreator($metas['creator']);
        $excel->getProperties()->setLastModifiedBy($metas['modifier']);

        $this->buildExcel($excel, $data);

        $extension = null;

        if (false !== ($p = strrpos($metas['filename'], '.'))) {
            $extension = substr($metas['filename'], $p + 1);
        }

        list($writer, $contentType) = $this->getWriterForExtension($excel, $extension);
        /** @var \PHPExcel_Writer_IWriter $writer */
        $writer->save($path);

        $content = file_get_contents($path);
        unlink($path);

        unset($options);

        return new Document($content, $contentType, $metas['filename']);
    }
    /**
     * @param \PHPExcel_DocumentProperties $properties
     *
     * @param $data
     */
    protected function buildProperties(\PHPExcel_DocumentProperties $properties, $data)
    {
    }
    /**
     * @param \PHPExcel_Worksheet $sheet
     *
     * @param $data
     */
    protected function buildSheet(\PHPExcel_Worksheet $sheet, $data)
    {
    }
    /**
     * @param \PHPExcel $excel
     * @param $data
     *
     * @throws \PHPExcel_Exception
     */
    protected function buildExcel(\PHPExcel $excel, $data)
    {
        $this->buildProperties($excel->getProperties(), $data);
        $excel->setActiveSheetIndex(0);
        $this->buildSheet($excel->getActiveSheet(), $data);
    }
    /**
     * @param \PHPExcel $excel
     * @param string    $extension
     *
     * @return \PHPExcel_Writer_IWriter
     *
     * @throws \Exception
     */
    protected function getWriterForExtension(\PHPExcel $excel, $extension)
    {
        switch(strtolower(trim($extension))) {
            case 'xlsx':
                return [
                    new \PHPExcel_Writer_Excel2007($excel),
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];
            case 'xls':
                return [
                    new \PHPExcel_Writer_Excel5($excel),
                    'application/vnd.ms-excel',
                ];
            case 'ods':
                return [
                    new \PHPExcel_Writer_OpenDocument($excel),
                    'application/vnd.oasis.opendocument.spreadsheet',
                ];
            case 'html':
                return [
                    new \PHPExcel_Writer_HTML($excel),
                    'text/html',
                ];
            case 'csv':
                return [
                    new \PHPExcel_Writer_CSV($excel),
                    'text/csv',
                ];
            default:
                throw $this->createRequiredException("Unsupported excel writer '%s'", $extension);
        }
    }
}
