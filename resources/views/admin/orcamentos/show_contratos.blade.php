@can('ler_arquivos',$routa)
    @php
        $aceito_termo = isset($config['assinatura']['aceito_termo']) ? $config['assinatura']['aceito_termo'] : 'n';
        $data = isset($config['assinatura']['data']) ? $config['assinatura']['data'] : '';
        $ip = isset($config['assinatura']['ip']) ? $config['assinatura']['ip'] : '';
        $arquivo_gerado = isset($config['gerado']['caminho']) ? $config['gerado']['caminho'] : '';
        $assinado = isset($config['assinado']['link']) ? $config['assinado']['link'] : '';
        $data_assinado = isset($config['assinado']['data']) ? $config['assinado']['data'] : '';
        $termo_enviado = false;
        $token = isset($value['token']) ? $value['token'] : '';
    @endphp
    {{-- @if ($aceito_termo == 's') --}}


        <div class="card card-primary card-outline mb-5">
            <div class="card-header">
                <h3 class="card-title">{{__('Arquivos')}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="row">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            {{__('Termo gerado')}}
                                        </th>
                                        <th>
                                            {{__('Termo Enviado')}}
                                        </th>
                                        <th>
                                            {{__('Termo assinado')}}
                                        </th>
                                        <th>
                                            {{__('Ação')}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            @if ($arquivo_gerado)
                                                <a href="{{$arquivo_gerado}}" class="text-danger" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-file-pdf fa-2x"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                        </td>
                                        <td>
                                            @if ($assinado)
                                            <div>
                                                <a href="{{url('/storage/'.$assinado)}}" class="text-danger" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-file-pdf fa-2x"></i>
                                                </a>
                                            </div>
                                            <div>
                                                <small>{{@$data_assinado}}</small>
                                            </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($termo_enviado || $assinado)
                                                <button class="btn">
                                                        <i>Enviado</i> <span class="badge badge-primary"></span>
                                                </button>
                                            @else
                                                <button class="btn btn-primary" onclick="envia_zapSing('{{$token}}')" title="Enviar para assinatura">
                                                    <i class="fas fa-envelope "></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- <div class="col-12">
                                <a href="{{route('termo.aceito',@$config['id'])}}?redirect_base={{base64_encode(App\Qlib\Qlib::UrlAtual())}}" title="Termo acento digitalmente em {{$data}}">
                                    <i class="fas fa-file-pdf fa-3x"></i>
                                </a>
                            </div>
                            <div class="col-12">
                                {{$data}}
                            </div>
                            <div class="col-12">
                                IP: {{$ip}}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- @endif --}}
@endcan
