<?php
require_once("Iugu.php");

Iugu::setApiKey($_POST['api_token']);

$criar = Iugu_Invoice::create(Array(
    "email" => $_POST['email'],
    "due_date" => $_POST['due_date'],
	"return_url" => $_POST['return_url'],
	"expired_url" => $_POST['expired_url'],
	"notification_url" => $_POST['notification_url'],
	"custom_variables" => $_POST['custom_variables'],
    "items" => $_POST['items'],
	"ignore_due_email" => true
));

if($criar->secure_url){
	$retorno = array('status'=>'success','url'=>$criar->secure_url);
}else{
	$retorno = array('status'=>'error');
}

echo json_encode($retorno);
?>