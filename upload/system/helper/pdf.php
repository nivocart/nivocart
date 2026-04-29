<?php
/**
 * Helper PDF
 */
function pdf($data, $type, $number) {
    $doc_type = str_replace(" ", "", $type);
    $title = $doc_type . '-' . $number;

    $options = new Dompdf\Options();
    $options->setChroot([realpath(DIR_SYSTEM . '../')]);

    $pdf = new Dompdf\Dompdf($options);
    $pdf->loadHtml($data);
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();
    $pdf->stream($title . '.pdf');
}
