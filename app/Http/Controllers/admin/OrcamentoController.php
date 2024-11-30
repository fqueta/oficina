<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarEmail;
use App\Jobs\SendEmailJob;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;

class OrcamentoController extends Controller
{
    public function get_rab(Request $request){
        $matricula = $request->get('matricula') ? $request->get('matricula') : null;
        $config = $request->get('config') ? $request->get('config') : null;
        $api = $request->get('api') ? $request->get('api') : 'true';
        $matricula = strtoupper($matricula);
        $ret['exec'] = false;
        $ret['color'] = 'danger';
        $ret['mens'] = 'Aeronave não encontrada na Anac';
        // $dg = $request->all();
        $passa_consulta = isset($config['consulta']) ? $config['consulta'] : '';
        if($matricula && $api == 'true' && (!$passa_consulta || empty($passa_consulta)) ){
            $url = 'https://api.aeroclubejf.com.br/api/v1/rab?matricula='.$matricula;
            $json = file_get_contents($url);
            if($json){
                $ret = Qlib::lib_json_array($json);
                if($ret['exec']){
                    $ret['color'] = 'success';
                    $ret['mens'] = 'Aeronave Localizada';
                    $ret['consulta'] = Qlib::codificarBase64($json);
                }else{
                    $ret['color'] = 'danger';
                    $ret['mens'] = 'Aeronave não encontrada na Anac tente mais tarde';
                }
            }
            $ret['salv'] = $this->salverContato($request);
        }else{

            $json = base64_decode($passa_consulta);
            // $json = str_replace('b"','',$json);
            // $json1 = '{"Matrcula":"PRHNA","Proprietrio":"SBXL LOCADORA DE AERONAVES LTDA","CPF/CNPJ":"21616420000177","Cota Parte %":"100","Data da Compra/Transferncia":"19/10/22","Operador":"AEROCLUBE DE JUIZ DE FORA","Fabricante":"CESSNA AIRCRAFT","Ano de Fabricao":"1977","Modelo":"152","Nmero de Srie":"15281007","Tipo ICAO":"C152","Categoria de Homologao":"UTILIDADE","Tipo de Habilitao para Pilotos":"MNTE","Classe da Aeronave":"POUSO CONVECIONAL 1 MOTOR CONVENCIONAL","Peso Mximo de Decolagem":"757 - Kg","Nmero de Passageiros":"001","Tipo de voo autorizado":"VFR Noturno","Tripulao Mnima prevista na Certificao":"1","Nmero de Assentos":"2","Categoria de Registro":"PRIVADA INSTRUCAO","Nmero da Matrcula":"20879","Status da Operao":"OPERAO NEGADA PARA TXI AREO","Gravame":"ARRENDAMENTO OPERACIONAL","Data de Validade do CVA":"23/01/25","Situao de Aeronavegabilidade":"SITUAO NORMAL","Motivo(s)":"","Data da consulta":"20/11/2024 19:00:32"}';
            $ret = Qlib::lib_json_array($json);
            $ret['mens'] = '';
            $ret['color'] = 'success';
            $ret = $this->salverContato($request);
        }
        return response()->json($ret);

    }
    public function salverContato(Request $request){
        $ret['exec'] = false;
        $email = $request->get('email');
        $d = $request->all();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $ret['mens'] = "O endereço de Email '$email' é inválido.\n";
            return $ret;
        }
        $config = $request->get('config');
        // $config['_token'] = $config['
        $name = strip_tags($request->get('name'));
        $id_cliente = false;
        $id_cliente_front = Qlib::qoption('id_permission_front'); //id da permissão do cliente
        try {
            $arr_sav = [
                'email'=>$email,
                'config'=>$config,
                'name'=>$name,
                'id_permission'=>$id_cliente_front,
            ];
            $salv = User::create($arr_sav);
            $id_cliente = isset($salv['id']) ? $salv['id'] : false;
            $ret['salv'] = $salv;
        } catch (\Throwable $th) {
            //verrifica se ja tem o email cadastrado
            // $ret['catch'] = $th;
            $dc = User::where('email', '=', $email)->get();
            if($dc) {
                $id_cliente = isset($dc[0]['id']) ? $dc[0]['id'] : false;
                if($id_cliente) {
                    $ret['up'] = User::where('id', $id_cliente)->update([
                        'name' => $name,
                        'config' => $config,
                        // 'id_permission'=>$id_cliente_front,
                    ]);
                }
            }
        }
        $ret['id_cliente'] = $id_cliente;
        if($id_cliente && isset($config['consulta'])) {
            $ret = $this->salvarOrcamento($id_cliente,$d,$config);
        }
        if(Qlib::isAdmin(1)){
            $ret['d'] = $d;
        }
        return $ret;
    }
    /***
     * Metodo para revelar um orcamento whatsapp pela api
     */
    public function orcamento_zap(request $request){
        $ret['link'] = '';
        if($token=$request->get('token')){
            $ret['link'] = $this->orcamento_html($token,'whatsapp');
        }
        return response()->json($ret);
    }
    /**
     * Metodo para salver uma solicitação de oraçamento do formulario do fornt End
     * @param string $config base64
     * @param string $id_cliente
     * @return array $ret
     */
    public function salvarOrcamento($id_cliente,$d,$config){
        $ret['exec'] = false;
        $ret['mens'] = __('Erro ao enviar orçamento!');
        $ret['color'] = 'danger';
        $arr_config    = $config;
        $arr_config['consulta'] = Qlib::decodeArray($config['consulta']);
        $ret['arr_config'] = $arr_config;
        $post_type = 'orcamentos';
        $dc = User::find($id_cliente);
        $post_title = 'Solicitação de orçamento '.@$dc['name'];
        $post_status = 'aguardando'; //Status de orçamentos enviado,aguardando
        $token = isset($d['token']) ? $d['token'] : uniqid(); //Status de orçamentos enviado,aguardando
        $arr_salv = [
            'guid'=>$id_cliente,  //id do cliente
            'config'=>Qlib::lib_array_json($arr_config),
            'post_type'=>$post_type,
            'post_title'=>$post_title,
            'post_author'=> (Auth::id() ? Auth::id() : $id_cliente),
            'post_status'=> $post_status,
            'token'=> $token,
            'post_content'=> @$d['obs'],
        ];
        //primeiro tenha atualizar um token que ja existe
        $salv = Post::where('token','=',$token)->update($arr_salv);
        if(!$salv){
            $salv = Post::create($arr_salv);
        }
        if($salv){
            $link_redirect = '/'.Qlib::get_slug_post_by_id(5);
            $link_zap = $this->orcamento_html($token,'whatsapp');
            try {
                //code...
                $email_admin = explode(',',Qlib::qoption('email_gerente'));
                $ret['exec'] = true;
                //enviar 2 emails para admin
                $subject = 'SOLICITAÇÃO DE AGENDAMENTO DE MANUTENÇÃO';
                // $ret['env'] = EnviarEmail::dispatch($data);
                $mensagem =  'Antenção foi solicitado um orçamento por <b>'.$dc['name'].'</b> em '.Qlib::dataLocal();
                $mensagem .= $this->orcamento_html($token);
                //Salvar o contrato
                $post_id = Qlib::get_id_by_token($token);
                $ret['termo'] = $this->salvar_aceito_termo(['id_cliente'=>$id_cliente,'post_id'=>$post_id,'meta'=>@$d['meta']]);
                if(is_array($email_admin)){
                    $from = 'suporte@aeroclubejf.com.br';
                    // dd($email_admin);
                    // foreach ($email_admin as $email) {
                        if(isset($email_admin[0])){
                            $details = [
                                'email' => $email_admin[0],
                                'from' => $from,
                                'name' => '',
                                'subject' => $subject,
                                'message' => $mensagem,
                                'cc' => $email_admin[1]
                            ];
                            SendEmailJob::dispatch($details);
                        }
                    // }
                }
                $mensagem = '<p>Olá <b>'.$dc['name'].'</b> obrigado pelo seu contato!</p><p>Sua solicitação foi encaminhada para a nossa oficina em breve entraremos em contato.</p>';
                $mensagem .= $this->orcamento_html($token);
                $details_cliente = [
                    'email' => $dc['email'],
                    'name' => $dc['name'],
                    'subject' => $subject,
                    'message' => $mensagem
                ];
                //enviar 1 email copia para o cliente
                SendEmailJob::dispatch($details_cliente);
                //criar um link de redirecioanmento
                $ret['mens'] = __('Orçamento enviado com sucesso!');
                $ret['color'] = 'success';
                $ret['link_zap'] = $link_zap;
                $ret['redirect'] = $link_redirect;
            } catch (\Throwable $th) {
                $ret['link_zap'] = $link_zap;
                $ret['redirect'] = $link_redirect;
                $ret['erro'] = $th;
                $ret['mens'] = __('Tivemos um erro inesperdado entre em contato com o suporte!');
                $ret['color'] = 'danger';
            }
        }
        // $ret['salv'] = $salv;
        return $ret;
    }
    /**
     * Metodo para salvar a aceitação do termo
     * @param array $config
     * @return arr $config
     */
    public function salvar_aceito_termo($config=[]){
        $id_cliente = isset($config['id_cliente']) ? $config['id_cliente'] : false;
        $post_id = isset($config['post_id']) ? $config['post_id'] : false;
        $meta = isset($config['meta']) ? $config['meta'] : false;
        $ret['exec'] = false;
        if(isset($meta['termo']) && $meta['termo']=='s' && $id_cliente && $post_id){
            //nesse caso o contrato foi aceito por um cliente válido
            $assinatura = Qlib::update_postmeta($post_id,'assinatura_termo',Qlib::lib_array_json([
                'aceito_termo' => 's',
                'data' => Qlib::dataLocal(),
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]));
            if($assinatura){
                $texto_termo_assinado = Qlib::update_postmeta($post_id,'texto_termo_assinado',Qlib::lib_array_json([
                    'texto' => $this->get_termo_texto(),
                ]));
                //salvar assinatura e
                if($texto_termo_assinado){
                    $ret['exec'] = true;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para exibir o texto de um termo cadastrado
     * @return string $ret o texto html no content do post
     */
    public function get_termo_texto(){
        $id = 10;
        $d = Post::find($id);
        $ret = isset($d['post_content']) ? $d['post_content'] : '';
        return $ret;
    }
    public function enviar_orcamento(Request $request){
        $ret['exec'] = false;
        $d = $request->all();
        $ret['d'] = $d;
        return $ret;
    }
    public function orcamento_html($token,$type=false){
        $d = Post::where('token','=', $token)->get();
        $ret = '';
        $tm1 = '';
        $type = $type ? $type : 'markdown';
        if($d->isNotEmpty()){
            $tbody = '';
            if($type == 'whatsapp'){
                $link_zap = 'https://api.whatsapp.com/send?phone={celular_zap}&text={text}';
                $tbody = '<br>';
            }
            $dc = User::find($d[0]['guid']);
            $email = isset($dc['email']) ? $dc['email'] : '';
            $name = '';
            if(isset($dc['name']) && $type == 'whatsapp'){
                $tm1 .= 'Meu nome é *'.@$dc['name'].'* gostaria de um orçamento de serviço para a Aeronave abaixo: %0A--------%0A';
            }else{
                $name = isset($dc['name']) ? $dc['name'] : '';
                $tm1 .= '<p class=\'mb-0\'><b>Nome</b>: {name}</p>';
            }
            if($type == 'whatsapp'){
                $tm1 .= '<p><b>Telefone</b>: {telefone}</p>';
                $tm1 .= '<p><b>Email</b>: {email}</p>';
            }else{
                $tm1 .= '<p class=\'mb-0\'><b>Telefone</b>: {telefone}</p>';
                $tm1 .= '<p class=\'mb-0\'><b>Email</b>: {email}</p>';
            }

            if($type=='markdown' || $type == 'whatsapp'){
                $tm1 .= '
                   <p>{tbody}</p>
                   <br><p><b>'.__('Serviço').'</b>: {servicos}</p>
                   <br><p><b>'.__('Descrição').'</b>:<br>{obs}</p>
                ';
                $tm2 = ' - <b>{label}:</b> {value}<br>';
            }else{
                $tm1 .= '
                <table class="table">
                    {tbody}
                </table>
                <p><b>'.__('Serviço').'</b>: {servicos}</p>
                <p><b>'.__('Descrição').'</b>:<br>{obs}</p>
                ';
                $tm2 = '
                <tr>
                    <th>{label}</th>
                    <td>{value}</td>
                </tr>
                ';
            }
            $darr = $d->toArray();
            $dar = $darr[0];
            if(is_string($dar['config'])){
                $dar['config'] = Qlib::lib_json_array($dar['config']);
            }
            $config = $dar['config'];
            // dump($config);
            $telefone = isset($config['ddi']) ? $config['ddi'] : '';
            // if($telefone && $type=='whatsapp'){

            // }
            $telefone .= ' '.isset($config['whatsapp']) ? $config['whatsapp'] : '';
            $servicos = isset($dar['config']['servicos']) ? $dar['config']['servicos'] : false;
            $arr = isset($dar['config']['consulta']['data']) ? $dar['config']['consulta']['data'] : [];

            if(isset($arr) && is_array($arr)){
                foreach ($arr as $k => $v) {
                    $tbody .= str_replace('{label}',$k,$tm2);
                    $tbody = str_replace('{value}',$v,$tbody);
                }
            }
            $ret = str_replace('{tbody}',$tbody,$tm1);
            $ret = str_replace('{obs}',@$dar['post_content'],$ret);
            $ret = str_replace('{servicos}',@$servicos,$ret);
            $ret = str_replace('{telefone}',@$telefone,$ret);
            $ret = str_replace('{email}',@$email,$ret);
            $ret = str_replace('{name}',@$name,$ret);
            if($type == 'whatsapp'){
                $text = $ret;
                $celular_zap = Qlib::qoption('celular_zap');
                $celular_zap = str_replace(' ','',trim($celular_zap));
                $celular_zap = str_replace('(','',$celular_zap);
                $celular_zap = str_replace(')','',$celular_zap);
                $celular_zap = str_replace('-','',$celular_zap);
                $link_zap = 'https://api.whatsapp.com/send?phone={celular_zap}&text={text}';
                $link_zap = str_replace('{celular_zap}',$celular_zap,$link_zap);
                $link_zap = str_replace('{text}',trim($text),$link_zap);
                $celular_zap = str_replace('-','',$celular_zap);
                $ret = str_replace('<b>','*',$link_zap);
                $ret = str_replace('</b>','*',$ret);
                $ret = str_replace('<br>','%0A',$ret);
                $ret = str_replace('</p>','%0A',$ret);
                $ret = str_replace('<p>','',$ret);
                $ret = str_replace(' ','%20',$ret);
                // $ret = $link_zap;
            }
        }
        return $ret;
    }
}
