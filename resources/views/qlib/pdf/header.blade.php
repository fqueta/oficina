<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            margin: 0;
            /* padding: 0; */
            padding-bottom: 7px;
            background-color: #f1f1f1;
        }
        .header {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .header img {
            display: block; /* Remove espaços abaixo da imagem */
            width: 100%; /* Ou tamanho fixo conforme necessário */
            height: auto;
        }
        .header-info {
            font-size: 9pt;
            color: #555;
            margin-top: 5px;
        }

    </style>
</head>
<body>
    <div class="header">
        <!-- Imagem do cabeçalho -->
        <img src="https://crm.aeroclubejf.com.br/enviaImg/uploads/ead/684057323a505/684058365b8d2.png" alt="header da pagina">

        <!-- Informações adicionais (opcional) -->
        {{-- <div class="header-info">
            Relatório Gerado em: {{ now()->format('d/m/Y H:i') }}
        </div> --}}
    </div>
</body>
</html>
