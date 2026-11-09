
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte de expedientes</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 10px;
            }

            h2 {
                text-align: center;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            th, td {
                border: 1px solid #555;
                padding: 8px;
                text-align: left;
                vertical-align: top;
                overflow-wrap: break-word;
            }

            th {
                background-color: #e5e7eb;
                font-weight: bold;
            }

            th:nth-child(1) { width: 25%; }
            th:nth-child(2) { width: 20%; }
            th:nth-child(3) { width: 55%; }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        </style>
    </head>
    <body>

        <h2>REPORTE DE EXPEDIENTES ASIGNADOS</h2>

        <table>
            <thead>
                <tr>
                    <th>Número de expediente</th>
                    <th>Estado</th>
                    <th>Última acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expedientes as $expediente)
                    <tr>
                        <td>{{ $expediente['numero_expediente'] }}</td>
                        <td>{{ $expediente['estado'] }}</td>
                        <td>{{ $expediente['ultima_accion'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center;">
                            No tiene expedientes asignados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </body>
</html>
