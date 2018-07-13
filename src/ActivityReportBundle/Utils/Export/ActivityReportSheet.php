<?php

namespace ActivityReportBundle\Utils\Export;

use Liuggio\ExcelBundle\Factory;
use Symfony\Component\HttpFoundation\Response;

class ActivityReportSheet
{
    /**
     * PHPExcel object.
     *
     * @var
     */
    private $phpExcelObject;

    /**
     * @param Factory $phpExcel
     */
    public function __construct(Factory $phpExcel)
    {
        $this->phpExcel = $phpExcel;
    }

    /**
     * Builds balance sheet and returns the file through response.
     *
     * @return Response
     */
    public function getResponse()
    {
        $writer = $this->phpExcel->createWriter($this->phpExcelObject, 'Excel5');
        $response = $this->phpExcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=evaluations_session.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }
}
