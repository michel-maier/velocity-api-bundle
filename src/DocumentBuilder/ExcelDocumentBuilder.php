<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DocumentBuilder;

use Velocity\Bundle\ApiBundle\DocumentBuilder\Base\AbstractSpreadSheetDocumentBuilder;

/**
 * Basic Excel Document Builder.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExcelDocumentBuilder extends AbstractSpreadSheetDocumentBuilder
{
    /**
     * @param \PHPExcel_DocumentProperties $properties
     * @param array                        $metas
     */
    protected function buildProperties(\PHPExcel_DocumentProperties $properties, $metas)
    {
        if (isset($metas['title'])) {
            $properties->setTitle($metas['title']);
        }

        if (isset($metas['subject'])) {
            $properties->setSubject($metas['subject']);
        }

        if (isset($metas['description'])) {
            $properties->setDescription($metas['description']);
        }
    }
    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param array               $data
     */
    protected function buildSheet(\PHPExcel_Worksheet $sheet, $data)
    {
        $headers = null;

        $row = 1;
        foreach($data as $k => $v) {
            if (null === $headers) {
                $headers = array_keys($v);
                foreach($headers as $kk => $vv) {
                    $sheet->setCellValueByColumnAndRow($kk, 1, ucwords($vv));
                }
            }
            $row++;
            foreach($headers as $kk => $vv) {
                $sheet->setCellValueByColumnAndRow($kk, $row, isset($v[$vv]) ? $v[$vv] : null);
            }
        }
    }
}
