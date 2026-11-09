<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteExpedienteController extends Controller
{
    private function obtenerExpedientes(Request $request)
    {
        $usuario = $request->user();

        return DB::table('expedientes as e')
            ->leftJoin(
                'estados_expediente as ee',
                'ee.id',
                '=',
                'e.estado_expediente_id'
            )
            ->select(
                'e.numero_expediente',
                'ee.nombre as estado'
            )
            ->selectSub(function ($query) {
                $query->from('actuaciones as a')
                    ->select('a.descripcion')
                    ->whereColumn('a.expediente_id', 'e.id')
                    ->whereNull('a.deleted_at')
                    ->orderByDesc('a.fecha_actuacion')
                    ->orderByDesc('a.id')
                    ->limit(1);
            }, 'ultima_accion')
            ->where('e.encargado_actual_id', $usuario->id)
            ->whereNull('e.deleted_at')
            ->orderBy('e.numero_expediente')
            ->get()
            ->map(function ($expediente) {
                return [
                    'numero_expediente' => $expediente->numero_expediente,
                    'estado' => $expediente->estado ?? 'Sin estado',
                    'ultima_accion' => $expediente->ultima_accion
                        ?: 'Sin actuaciones registradas',
                ];
            });
    }

    private function validarGestor(Request $request): void
    {
        abort_unless(
            (int) $request->user()->perfil_id === 2,
            403,
            'Solo los gestores pueden exportar reportes.'
        );
    }

    public function excel(Request $request)
    {
        $this->validarGestor($request);
        $expedientes = $this->obtenerExpedientes($request);

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $hoja->setTitle('Mis expedientes');

        // Encabezados
        $hoja->setCellValue('A1', 'Número de expediente');
        $hoja->setCellValue('B1', 'Estado');
        $hoja->setCellValue('C1', 'Última acción');

        $fila = 2;

        foreach ($expedientes as $expediente) {
            $hoja->setCellValueExplicit(
                'A' . $fila,
                $expediente['numero_expediente'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $hoja->setCellValue('B' . $fila, $expediente['estado']);

            $hoja->setCellValueExplicit(
                'C' . $fila,
                $expediente['ultima_accion'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $fila++;
        }

        // Formato
        $hoja->getStyle('A1:C1')->getFont()->setBold(true);

        foreach (['A', 'B', 'C'] as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }

        $nombreArchivo = 'mis_expedientes_' . now()->format('Ymd_His') . '.xlsx';

        $archivoTemporal = tempnam(sys_get_temp_dir(), 'sigep_');

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($archivoTemporal);
        } catch (\Throwable $e) {
            @unlink($archivoTemporal);
            throw $e;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        return response()->download($archivoTemporal, $nombreArchivo)
            ->deleteFileAfterSend(true);
    }

    public function pdf(Request $request)
    {
        $this->validarGestor($request);
        
        $expedientes = $this->obtenerExpedientes($request);

        $pdf = Pdf::loadView('reportes.expedientes', [
            'expedientes' => $expedientes,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $nombreArchivo = 'mis_expedientes_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($nombreArchivo);
    }
}
