<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{!! $titulo !!}</title>
    <style>
        body{
            margin: 0;
            font-family: "Source Sans Pro",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #f1f1f1;
        }
        .conteudo{
            text-align: justify;
            widows: 100%;
            /* margin: 0 25px ; */
            padding: 10px 30px ;

        }
    </style>
</head>
<body>
    {{-- <h1>{{ $titulo }}</h1> --}}
    <div class="conteudo">
        {!! $conteudo !!}
    </div>
</body>
</html>
