@php
    /** @var \App\Models\Company $company */
    /** @var \Illuminate\Support\Carbon $date */
    /** @var \Illuminate\Support\Collection $samples */
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informes disponibles {{ $date->format('d-m-Y') }} - {{ $company->name }}</title>
</head>
<body style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; color: #111827; background-color: #F9FAFB; padding: 24px;">
<div style="max-width: 640px; margin: 0 auto; background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 10px 30px rgba(15,23,42,0.08); overflow: hidden;">
    <div style="padding: 20px 24px 8px 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #E5E7EB;">
        {{-- Importante: usar URL absoluta accesible públicamente para correos (no localhost) --}}
        <img src="https://www.labdoping-uchile.cl/images/pdf/escudo_uchile_fondo.png" alt="Logo Universidad de Chile" style="width: 40px; height: auto; border-radius: 6px; object-fit: contain;" />
        <div>
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #6B7280;">
                LAB DOPING · U. de Chile
            </div>
            <h2 style="margin: 2px 0 0 0; font-size: 16px; font-weight: 600; color:#111827;">
                Informes disponibles {{ $date->format('d-m-Y') }}
            </h2>
        </div>
    </div>

    <div style="padding: 20px 24px 24px 24px;">

<p>
Estimado/a,
</p>

<p>
Le informamos que los resultados de los análisis correspondientes a las muestras de la empresa
<strong>{{ $company->name }}</strong>
se encuentran disponibles para su revisión en la plataforma, con fecha
<strong>{{ $date->format('d-m-Y') }}</strong>.
</p>

<p>
En la plataforma podrá visualizar y descargar los documentos de informe asociados a las muestras registradas.
</p>

    @if($samples->isEmpty())
    <p>No se registraron informes para esta empresa en la fecha indicada.</p>
    @else
    <p>A continuación encontrarás el detalle de las muestras:</p>

        <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse; width:100%; font-size: 13px; margin-top: 12px;">
            <thead>
        <tr>
                    <th align="left" style="padding: 8px 6px; border-bottom: 1px solid #E5E7EB; color:#6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">N° Externo</th>
                    <th align="left" style="padding: 8px 6px; border-bottom: 1px solid #E5E7EB; color:#6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Tipo de muestra</th>
                    <th align="left" style="padding: 8px 6px; border-bottom: 1px solid #E5E7EB; color:#6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Fecha informe</th>
                    <th align="left" style="padding: 8px 6px; border-bottom: 1px solid #E5E7EB; color:#6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Tipo documento</th>
        </tr>
        </thead>
        <tbody>
        @foreach($samples as $item)
            <tr>
                    <td style="padding: 7px 6px; border-bottom: 1px solid #F3F4F6;">{{ $item['external_id'] ?? '-' }}</td>
                    <td style="padding: 7px 6px; border-bottom: 1px solid #F3F4F6;">{{ $item['type'] ?? '-' }}</td>
                    <td style="padding: 7px 6px; border-bottom: 1px solid #F3F4F6;">
                        {{ isset($item['document_created_at']) ? \Illuminate\Support\Carbon::parse($item['document_created_at'])->format('d-m-Y H:i') : '-' }}
                    </td>
                    <td style="padding: 7px 6px; border-bottom: 1px solid #F3F4F6;">{{ $item['type_document'] ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    @endif

<p style="margin-top: 12px; margin-bottom: 8px;">
    <a
        href="https://www.labdoping-uchile.cl/login"
        style="
            display: inline-block;
            padding: 10px 18px;
            border-radius: 9999px;
            background-color: #2563EB;
            color: #FFFFFF;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        "
    >
        Ir a la plataforma
    </a>
</p>

<p>
Ante cualquier consulta o inconveniente con el acceso, no dude en contactarnos.
</p>

<p style="margin-top:16px;">
Este es un correo generado automáticamente por el sistema de LAB DOPING para mantenerte informado sobre el estado de
los informes emitidos. Si tienes dudas o requieres más información, por favor contáctanos.
</p>

<p style="margin-top:8px;">
Saludos cordiales,<br>
<strong>LAB DOPING</strong>
</p>

    </div>
</div>

</body>
</html>

