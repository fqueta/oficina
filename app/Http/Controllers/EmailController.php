<?php

namespace App\Http\Controllers;

use App\Mail\usersEmail;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use LaravelLang\Lang\Plugins\Spark\Stripe;
use Illuminate\Support\Str;
use stdClass;

class EmailController extends Controller
{
    const API_KEY = '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';
	const URL = 'https://api.brevo.com/v3';
	const SENDER = 'nao_responda@maisaqui.com.br';
	/**Metodo para o envio de email Transacional
	 * @parm array
	 * @return array
	 */
	public function sendEmail($fields=false){
        //Exemplo de uso
        // $config_em = array(
		// 	'emails'=>array(
		// 		array('email'=>$dadosCompra[0]['email'],'nome'=>$dadosCompra[0]['nome'].' '.$dadosCompra[0]['sobrenome']),
		// 		),
		// 		//'Bcc'=>'fernando@maisaqui.com.br',
		// 		'assunto'=>'Compra aprovada! Seu acesso ao Curso de '.@$dadosCompra[0]['nome_curso'],
		// 		'mensagem'=>$mensagem,
		// 		'empresa'=>$dadosCompra[0]['empresa'],
		// 		'post'=>false,
		// 		'resp'=>array(
		// 			'envia'=>true,
		// 			'email'=>queta_option('email_gerente'),
		// 			'nome'=>$dadosCompra[0]['nome'],
		// 			'mensResp'=>'Foi enviado e-mail de Compra aprovada no sistema EAD '.$dadosCompra[0]['empresa'].' '.date('d/m/Y H:m:i'),
		// 			'assunto'=>'Compra aprovada',
		// 		),
		// 	);
		// 	$ret['enviaEmail2'] = (new sendinBlue)->sendEmail($config_em);
		// }
		$ret['exec'] = false;
		if(!$fields){
			return $ret;
		}
		$json = $this->schemeEmail($fields);
		if(!$json){
			return $ret;
		}
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => self::URL.'/smtp/email',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>$json,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'api-key: '.self::API_KEY
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$res =Qlib::lib_json_array($response);
		if(isset($res['messageId'])){
			$ret['result'] = $res;
			$ret['exec'] = true;
			$ret['json'] = Qlib::lib_json_array($json);
		}
		return $ret;
	}
	/**Mentodo para gerar um json válido para a integração com a api e emails */
	protected function schemeEmail($config){
		// $config = array(
			//     'emails'=>array(
			//         array('email'=>'ferqueta@yahoo.com.br','nome'=>'Fernando teste'),
			//         ),
			//         //'Bcc'=>'fernando@maisaqui.com.br',
			//         'assunto'=>'Compra aprovada! Seu acesso ao Curso de Teste cadas',
			//         'mensagem'=>$mensagem,
			//         'empresa'=>'Maisa aqui teste',
			//         'post'=>false,
			//         'resp'=>array(
			//             'envia'=>true,
			//             'email'=>'ferqueta@yahoo.com.br',
			//             'nome'=>'Fernando programador',
			//             'mensResp'=>'Foi enviado e-mail de Compra aprovada no sistema EAD  '.date('d/m/Y H:m:i'),
			//             'assunto'=>'Compra aprovada',
			//         ),
			    // );
		$arr = [];
		$name_sender = isset($config['name_sender']) ? $config['name_sender'] :Qlib::qoption('empresa');
		$email_sender = self::SENDER;
		$arr['sender'] = ['name'=>$name_sender,'email'=>$email_sender];
		if(isset($config['emails'])){
			if(is_array($config['emails'])){
				foreach ($config['emails'] as $k => $v) {
					$arr['to'][$k]['email'] = @$v['email'];
					$arr['to'][$k]['name'] = @$v['nome'];
				}
			}else{
				$arr['to']['email'] = @$config['emails']['email'];
				$arr['to']['name'] = @$config['emails']['nome'];
			}
		}else{
			return false;
		}
		// dd($config['resp']);
		// if(isset($config['resp']['email']) && isset($config['resp']['nome'])){
		// 	$config['bcc'][0]['email'] = $config['resp']['email'];
		// 	$config['bcc'][0]['name'] = $config['resp']['nome'];
		// }
		if(isset($config['bcc'])){
			if(is_array($config['bcc'])){
				foreach ($config['bcc'] as $k => $v) {
					$arr['bcc'][$k]['email'] = @$v['email'];
					$arr['bcc'][$k]['name'] = @$v['nome'];
				}
			}else{
				$arr['bcc']['email'] = @$config['bcc']['email'];
				$arr['bcc']['name'] = @$config['bcc']['nome'];
			}
		}
		if(isset($config['cc'])){
			if(is_array($config['cc'])){
				foreach ($config['cc'] as $k => $v) {
					$arr['cc'][$k]['email'] = @$v['email'];
					$arr['cc'][$k]['name'] = @$v['nome'];
				}
			}else{
				$arr['cc']['email'] = @$config['cc']['email'];
				$arr['cc']['name'] = @$config['cc']['nome'];
			}
		}
		if(isset($config['tags'])){
			if(is_array($config['tags'])){
				$arr['tags'] = @$config['tags'];
			}
		}
		if(isset($config['mensagem'])){
			$arr['htmlContent'] = $config['mensagem'];
		}
		if(isset($config['assunto'])){
			$arr['subject'] = $config['assunto'];
		}

		return Qlib::lib_array_json($arr);
	}
    public function sendEmailTest(){
        $user = new stdClass();
        $user->name = 'Fernando Queta';
        $user->email = 'ger.maisaqui1@gmail.com';
        // return new \App\Mail\usersEmail($user);
        $enviar = Mail::send(new usersEmail($user));
        return $enviar;
        // $mensagem = new \App\Mail\usersEmail($user);
        $config = array(
                'emails'=>array(
                    array('email'=>$user->email,'nome'=>$user->name),
                    ),
                    //'Bcc'=>'fernando@maisaqui.com.br',
                    'assunto'=>'Cadastro na plataforma '.config('app.name'),
                    'mensagem'=>$mensagem,
                    'empresa'=>Qlib::qoption('empresa'),
                    'post'=>false,
                    // 'resp'=>array(
                    // 	'envia'=>true,
                    // 	'email'=>Qlib::qoption('email_gerente'),
                    // 	'nome'=>$dadosCompra[0]['nome'],
                    // 	'mensResp'=>'Foi enviado e-mail de Compra aprovada no sistema EAD '.$dadosCompra[0]['empresa'].' '.date('d/m/Y H:m:i'),
                    // 	'assunto'=>'Compra aprovada',
                    // ),
                );
        $enviar = (new EmailController)->sendEmail($config);
        return $enviar;
    }
}
