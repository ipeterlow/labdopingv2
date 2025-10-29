<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Recepción de Muestras</title>

    <style>
    @font-face {
        font-family: 'Carlito';
        src: url('{{ storage_path('fonts/Carlito-Regular.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    @font-face {
        font-family: 'Carlito';
        src: url('{{ storage_path('fonts/Carlito-Bold.ttf') }}') format('truetype');
        font-weight: bold;
        font-style: normal;
    }

    html, body {
        font-family: 'Carlito', Helvetica, sans-serif;
        margin: 0 auto; /* Mantiene el centrado */
        padding: 0;
        max-width: 90%;
        position: relative;
        line-height: 1.1; 
    }

    /* Reset de márgenes */
    h1, h2, h4, h5, h6, p, label, span, div {
        margin: 0;
        padding: 0;
        line-height: 1.1;
        /* ¡Importante! Evita que DomPDF añada espacios extra */
        border: none; 
    }

    .bg-image {
        position: absolute;
        z-index: -1;
        opacity: 0.04;
        width: 280px;
        left: 50%;
        top: 120px;
        transform: translateX(-50%);
    }

    .title {
        text-align: center;
        display: table;
        margin: 0 auto;
    }

    .title img {
        width: 45px; 
        margin-right: 30px;
        margin-top: -5px;
    }

    .title h1 {
        font-size: 18px; 
        font-weight: bold;
        display: table-cell;
        vertical-align: middle;
    }

    .title h1 span {
        font-size: 16px; 
        font-weight: normal;
        display: block;
        margin-top: 2px;
    }

    .subtitle {
        margin-top: -15px; 
        font-size: 19px; 
        text-align: center;
        margin-bottom: 15px; 
    }

    /* ---
       ARREGLO DE ALINEACIÓN
    --- */
    .data-block {
        margin-bottom: 18px;
    }
    .data-row {
        margin-bottom: 6px;
        clear: both;
        overflow: hidden;
    }
    .data-label {
        display: inline-block;
        vertical-align: top;
        font-size: 16px;
        width: 200px;
        font-weight: normal;
    }
    .data-value {
        display: inline-block;
        vertical-align: top;
        font-weight: normal;
        font-size: 16px;
        max-width: calc(100% - 210px);
        word-wrap: break-word;
    }
    /* --- Fin del Arreglo --- */


    /* Tamaños de fuente para los valores (reducidos) */
    .font-value { font-size: 20px; } 
    .font-value-2 { font-size: 19px; } 
    .font-value-mutual {
        font-weight: bold;
        text-decoration: underline;
        font-size: 20px; 
    }

    /* ---
       Contenedor de Muestras A/B
    --- */
    .float-container-inventao {
        width: 100%;
        display: table;
        table-layout: fixed;
        margin-bottom: 10px;
    }
    .float-container-inventao .float-child {
        width: 50%;
        display: table-cell;
        vertical-align: top;
        padding-right: 10px;
    }
    .float-child .data-row {
        margin-bottom: 6px;
    }
    .float-child .data-label { 
        display: inline-block;
        vertical-align: top;
        width: 140px;
        font-size: 16px;
    }
    .float-child .data-value { 
        display: inline-block;
        vertical-align: top;
        font-size: 21px;
        max-width: calc(100% - 145px);
    }


    .muestras-box {
        width: 100%;
        height: 240px; 
        overflow: hidden;
        margin-top: 0; /* Sin espacio extra arriba */
    }

    .muestras {
        display: inline-block;
        min-width: 75px;
        font-size: 15px; 
        font-weight: normal;
        margin-right: 3px;
        line-height: 1.3;
    }

    .desc {
        font-size: 17px; 
        margin-top: 3px;
    }

    footer {
        position: absolute;
        bottom: 70px; 
        width: 100%;
    }
    
    .footer-container {
        width: 100%;
        overflow: auto;
    }
    .footer-container .float-child {
        width: 49%;
        float: left;
    }

    footer hr {
        width: 90%;
        margin: 0 auto;
        border: 1px solid #666;
    }

    footer h5 {
        text-align: center;
        font-weight: normal;
        margin: 8px 0;
        font-size: 17px; 
    }
    </style>
</head>

<body>

    <img class="bg-image"
         src="file://{{ public_path('images/pdf/escudo_uchile_fondo.jpg') }}"
         alt="Escudo Universidad de Chile" />

    <div class="title">
        <img src="file://{{ public_path('images/pdf/escudo_uchile_fondo.jpg') }}" alt="Escudo Universidad de Chile" />
        <h1>
            LABORATORIO DE ANÁLISIS ANTIDOPING/DROGAS DE ABUSO
            <span>Universidad de Chile – Facultad de Ciencias Químicas y Farmacéuticas</span>
        </h1>
    </div>

    <div class="subtitle">
        <h2>Formulario de recepción de muestras</h2>
    </div>

    <div class="data-block">
        <div class="data-row">
            <label class="data-label">N° de recepción:</label>
            <span class="data-value font-value">{{ e($id) }}</span>
        </div>
        <div class="data-row">
            <label class="data-label">Institución:</label>
            <span class="data-value font-value-mutual">{{ e($company) }}</span>
        </div>
        <div class="data-row">
            <label class="data-label">Fecha Recepción:</label>
            <span class="data-value font-value">{{ e($received_at) }}</span>
        </div>
        <div class="data-row">
            <label class="data-label">Fecha Hora:</label>
            <span class="data-value font-value">{{ e($received_at_hour) }}</span>
        </div>
    </div>

    <div class="data-block">
        <div class="data-row">
            <label class="data-label">Funcionario del laboratorio:</label>
            <span class="data-value font-value-2">{{ e($user_register_sample) }}</span>
        </div>
        <div class="data-row">
            <label class="data-label">Funcionario de institución cliente:</label>
            <span class="data-value font-value-2">{{ e($shipping_type) }}</span>
        </div>
    </div>

    <div class="float-container-inventao">
        <div class="float-child">
            <div class="data-row">
                <label class="data-label">Muestra Orina (A):</label>
                <span class="data-value font-value">{{ e($orinaA) }}</span>
            </div>
            <div class="data-row">
                <label class="data-label">Muestra Pelo (A):</label>
                <span class="data-value font-value">{{ e($peloA) }}</span>
            </div>
            <div class="data-row">
                <label class="data-label">Muestra Saliva (A):</label>
                <span class="data-value font-value">{{ e($salivaA) }}</span>
            </div>
            <div class="data-row">
                <label class="data-label">Muestra Suero (A):</label>
                <span class="data-value font-value">{{ e($sueroA) }}</span>
            </div>
        </div>
        
        <div class="float-child">
            <div class="data-row">
                <label class="data-label">Muestra Orina (B):</label>
                <span class="data-value font-value">{{ e($orinaB) }}</span>
            </div>
            <div class="data-row">
                <label class="data-label">Muestra Pelo (B):</label>
                <span class="data-value font-value">{{ e($peloB) }}</span>
            </div>
            <div class="data-row">
                <label class="data-label">Muestra Saliva (B):</label>
                <span class="data-value font-value">{{ e($salivaB) }}</span>
            </div>
             <div class="data-row">
                <label class="data-label">Muestra Suero (B):</label>
                <span class="data-value font-value">{{ e($sueroB) }}</span>
            </div>
        </div>
    </div>
    
    <div class="data-block" style="margin-top: 10px;">
        <div class="data-row">
            <label class="data-label">Total frascos:</label>
            <span class="data-value font-value">{{ e($samples) }}</span>
        </div>
        <div class="data-row" style="margin-top: 8px;">
            <label class="data-label" style="vertical-align: top;">Listado de muestras:</label>
            <div class="data-value" style="display: inline-block; vertical-align: top;">
                <div class="muestras-box">
                    @foreach ($samples_list as $samp)
                        <label class="muestras">N°: {{ e($samp->external_id) }}</label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="data-block" style="margin-top: 2px;">
        <div class="data-row">
            <label class="data-label" style="vertical-align: top;">Observaciones:</label>
            <span class="data-value desc" style="display: inline-block; vertical-align: top; max-width: calc(100% - 210px);">{{ e($description) }}</span>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="float-child">
                <hr>
                <h5>{{ e($user_register_sample) }}</h5>
            </div>
            <div class="float-child">
                <hr>
                <h5>{{ e($shipping_type) }}</h5>
            </div>
        </div>
    </footer>

</body>
</html>