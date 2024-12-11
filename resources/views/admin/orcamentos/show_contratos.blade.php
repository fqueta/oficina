@can('ler_arquivos',$routa)
    @php
        $aceito_termo = isset($config['assinatura']['aceito_termo']) ? $config['assinatura']['aceito_termo'] : 'n';
        $data = isset($config['assinatura']['data']) ? $config['assinatura']['data'] : '';
        $ip = isset($config['assinatura']['ip']) ? $config['assinatura']['ip'] : '';
    @endphp
    @if ($aceito_termo == 's')


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
                    <div class="col-3 text-center">
                        <div class="row">
                            <div class="col-12">
                                <a href="{{route('termo.aceito',@$config['id'])}}?redirect_base={{base64_encode(App\Qlib\Qlib::UrlAtual())}}" title="Termo acento digitalmente em {{$data}}">
                                    <i class="fas fa-file-pdf fa-3x"></i>
                                </a>
                            </div>
                            <div class="col-12">
                                {{$data}}
                            </div>
                            <div class="col-12">
                                IP: {{$ip}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endcan
