<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZapsingController extends Controller
{

    public $api_id;
    public $url_id;
    public function __construct()
    {
        $this->api_id = config('app.zapsing_id');
        $this->url_id = config('app.zapsing_url_api');
    }
    /**
     * Metodo para realizar as requisições post na api
     * @return $config = ['endpoint' => '', 'body' => [''], 'headers' =>'']
     */
    public function post($config){
        $endpoint = isset($config['endpoint']) ? $config['endpoint'] : 'docs'; //'docs'
        $body = isset($config['body']) ? $config['body'] : [];
        $ret['exec'] = false;
        $ret['mens'] = 'Endpoint não encontrado';
        $ret['color'] = 'danger';
        if($endpoint){
            $body = isset($config['body']) ? $config['body'] : [];
            $url_pdf = false;
            if(isset($config['gerar_pdf']['conteudo']) && ($cont=$config['gerar_pdf']['conteudo'])){
                //$config['gerar_pdf'] = ['titulo' => '','conteudo' =>''];
                $new_pdf = (new PdfController)->salvarPdf($config['gerar_pdf'],['arquivo'=>'termo_pdf']);
                $url_pdf = isset($new_pdf['caminho']) ? $new_pdf['caminho'] : false;
                if($url_pdf){
                    $body["url_pdf"] = $url_pdf;
                }
            }

            $body["lang"] = isset($body["lang"]) ? $body["lang"] : "pt-br";
            $body["brand_logo"] = isset($body["brand_logo"]) ? $body["brand_logo"] : asset(config('adminlte.logo_img'));
            $body["folder_path"] = isset($body["folder_path"]) ? $body["folder_path"] : "/".config('app.id_app');
            $body["brand_name"] = isset($body["brand_name"]) ? $body["brand_name"] : config('app.name');
            $body["brand_primary_color"] = isset($body["brand_primary_color"]) ? $body["brand_primary_color"] : "#073b5b";
            // $body["disable_signer_emails"] = isset($body["disable_signer_emails"]) ? $body["disable_signer_emails"] : false;
            // $body["created_by"] = isset($body["created_by"]) ? $body["created_by"] : "";
            // $body["date_limit_to_sign"] = isset($body["date_limit_to_sign"]) ? $body["date_limit_to_sign"] : '';
            // $body["signature_order_active"] = isset($body["signature_order_active"]) ? $body["signature_order_active"] : false;
            // $body["observers"] = isset($body["observers"]) ? $body["observers"] : [
            //     "fernando@maisaqui.com.br"
            // ];
            // $body["reminder_every_n_days"] = isset($body["reminder_every_n_days"]) ? $body["reminder_every_n_days"] : 0;
            // $body["allow_refuse_signature"] = isset($body["allow_refuse_signature"]) ? $body["allow_refuse_signature"] : false;
            // $body["disable_signers_get_original_file"] = isset($body["disable_signers_get_original_file"]) ? $body["disable_signers_get_original_file"] : false;
            // dd($body);
            try {
                //code...
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->api_id,
                ])->post($this->url_id.'/'.$endpoint, $body);
                if($response){
                    $ret['exec'] = true;
                    $ret['mens'] = 'Documento enviado com sucesso';
                    $ret['color'] = 'success';
                }else{
                    $ret['exec'] = false;
                }
                $ret['body'] =  $body;
                $ret['response_json'] = $response;
                $ret['response_code'] = base64_encode($response);
                $ret['response'] =  Qlib::lib_json_array($response);
            } catch (\Throwable $e) {
                $ret['error'] = $e->getMessage();
            }
            return $ret;
        }else{
            return $ret;
        }
    }
}