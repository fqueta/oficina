<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\AsaasController;
use App\Http\Controllers\admin\CobrancaController;
use App\Http\Controllers\admin\ContratosController;
use App\Http\Controllers\admin\OrcamentoController;
use App\Http\Controllers\admin\RdstationController;
use App\Http\Controllers\admin\PostController;
use App\Http\Controllers\admin\ZapguruController;
use App\Http\Controllers\admin\ZapsingController;
use App\Http\Controllers\BacklistController;
use App\Jobs\NotificWinnerJob;
use App\Jobs\RdstationJob;
use App\Jobs\SendEmailJob;
use App\Mail\EnviaMail;
use App\Mail\leilao\lancesNotific;
use App\Models\Familia;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PHPUnit\Util\Blacklist;
use stdClass;

class TesteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {


        $token = $request->get('token') ? $request->get('token') : '675855ed0b876';
        $titulo = $request->get('titulo') ? $request->get('titulo') : 'Meu teste';
        $opc = $request->get('opc') ? $request->get('opc') : 1;
        if($opc==1){
            $email = $request->get('email') ? $request->get('email') : 'ger.maisaqui1@gmail.com';
            $ret = (new ZapguruController)->criar_chat(['email'=>$email,'text'=>'Mensagem de teste']);
            // $ret = (new ZapguruController)->post($chat_number,$action,$comple_url='');
        }elseif($opc==2){
            // $link = (new OrcamentoController)->orcamento_html($token,'whatsapp');
            // $tm =$link.'<br>';
            // // $ret = $link;
            // $ret = $tm.'<a href="'.$link.'" target="_blank">Acessar</a>';
            $ret = (new ZapguruController)->enviar_link_assinatura($token);
        }elseif($opc==3){
            $ret = (new OrcamentoController)->send_to_zapSing($token);
            // dd($token);
            // $ret = (new OrcamentoController)->gerar_termo_orcamento($token);

        }elseif($opc==4){
            //download file
            $url = "https://zapsign.s3.amazonaws.com/sandbox/dev/2024/12/pdf/72d30d89-da1f-4e10-9025-3689b03ef3d4/7a773057-05d3-4843-be1d-0fe6bffdb730.pdf?AWSAccessKeyId=AKIASUFZJ7JCTI2ZRGWX&Signature=oRLj2PALoDs1JEkx%2FHm4TV1ZM%2BQ%3D&Expires=1734026017";
            $external_id = Qlib::createSlug('11/12/2024 07');;
            $caminhoSalvar = 'pdf/termos_assinados/'.$external_id.'/arquivo.pdf';
            $ret = Qlib::download_file($url,$caminhoSalvar);
        }elseif($opc==5){
            return view('teste');
        }elseif($opc==6){
            $ret = (new ZapsingController)->status_doc_remoto($token);
        }elseif($opc==7){
            // $ret = (new RdstationController)->add_rd_negociacao($token);
            $ret = RdstationJob::dispatch($token);
        }else{
            $subject = 'SOLICITAÇÃO DE AGENDAMENTO DE MANUTENÇÃO';
            $dc = User::find(1);
            $mensagem =  'Antenção foi solicitado um orçamento por <b>'.$dc['name'].'</b> em '.Qlib::dataLocal();
            $mensagem .= (new OrcamentoController)->orcamento_html($token,'markdown');
            $nome = 'Fernando Queta';
            $cc = 'quetafernando1@gmail.com';
            // $cc = 'contato@aeroclubejf.com.br';
            $from = 'nao_responda@aeroclubejf.com.br';
            $email = 'contato@aeroclubejf.com.br';
            $email_admin = explode(',',Qlib::qoption('email_gerente'));
            // $d
            $details = [
                                'email' => $email_admin[0],
                                'from' => $from,
                                'name' => $nome,
                                'subject' => $subject,
                                'message' => $mensagem,
                                // 'cc' => $email_admin[1],
                                // 'bcc' => @$email_admin[2],
                            ];
            if(isset($email_admin[1])){
                $details['cc'] = $email_admin[1];
            }
            if(isset($email_admin[2])){
                $details['bcc'] = $email_admin[2];
            }
            // dd($details);
            if($request->get('envia')==1){
                if(isset($details['cc'])){
                    $ret = Mail::to($details['email'])->cc($details['cc'])->send(new EnviaMail($details));
                }else{
                    $ret = Mail::to($details['email'])->send(new EnviaMail($details));
                }
            }elseif($request->get('envia')==2){
                $ret = SendEmailJob::dispatch($details);
                dd(config('app.name'));
            }else{
                $ret = (new OrcamentoController)->orcamento_html($token);
                // $ret = new EnviaMail($details);
            }
        }
        return $ret;
    }
    public function ajax(){
        $limit = isset($_GET['limit']) ?$_GET['limit'] : 50;
        $page = isset($_GET['page']) ?$_GET['page'] : 1;
        $site=false;

        $urlApi = $site?$site: 'https://po.presidenteolegario.mg.gov.br';
        $link = $urlApi.'/api/diaries?page='.$page.'&limit='.$limit;
        $link_html = dirname(__FILE__).'/html/front.html';
        $dir_img = $urlApi.'/uploads/posts/image_previews/{id}/thumbnail/{image_preview_file_name}';
        $dir_file = $urlApi.'/uploads/diaries/files/{id}/original/{file_file_name}';

        $api = file_get_contents($link);
        $arr_api = Qlib::lib_json_array($api);

        return response()->json($arr_api);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
