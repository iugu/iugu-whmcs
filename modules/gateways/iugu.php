<?php

function iugu_activate(){
	defineGatewayField("iugu","text","api_token","","Token", "40", "Você pode gerenciar seus tokens nas configurações de sua <a href='https://iugu.com/settings/account' target='blank'>conta</a>.");
}

function iugu_link($params){
	$db_invoice = mysql_query("SELECT * FROM tblinvoices WHERE id='{$params['invoiceid']}' AND userid='{$params['clientdetails']['userid']}'") or $db_invoice = 0;
	$dados_invoice = mysql_fetch_array($db_invoice);
	
	$db_invoice_items = mysql_query("SELECT * FROM tblinvoiceitems WHERE invoiceid='{$params['invoiceid']}' AND userid='{$params['clientdetails']['userid']}'") or $db_invoice_items = 0;
	
	$data = date('d/m/Y', strtotime($dados_invoice['duedate']));
	$hoje = date('d/m/Y');
	
	if($data > $hoje){
		$vencimento = $data;
	}else{
		$vencimento = $hoje;
	}
	
	$itens = "";
	if($db_invoice_items){
		while($dados_items = mysql_fetch_array($db_invoice_items)){
			$valor = number_format($dados_items['amount'], 2, '', '');
			
			$itens .= '<input type="hidden" name="items[][description]" value="'.$dados_items['description'].'">
		<input type="hidden" name="items[][quantity]" value="1">
		<input type="hidden" name="items[][price_cents]" value="'.$valor.'">
		';
		}
	}
    
    	// Se não tiver nenhum item, força um item com o valor total e colocando o número da fatura
        if($itens=="") {
		$itens .= '<input type="hidden" name="items[][description]" value="Fatura #'.$params['invoiceid'].'">
		<input type="hidden" name="items[][quantity]" value="1">
		<input type="hidden" name="items[][price_cents]" value="'.$dados_invoice['total'].'">
		';
       }
	
	$code = '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<br>
	<form id="formulario">
		<input type="hidden" name="api_token" value="'.$params['api_token'].'">
		<input type="hidden" name="return_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentsuccess=true">
		<input type="hidden" name="expired_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentfailed=true">
		<input type="hidden" name="notification_url" value="'.$params['systemurl'].'/modules/gateways/callback/iugu.php">
		<input type="hidden" name="name" value="'.$params['clientdetails']['firstname'].' '.$params['clientdetails']['lastname'].'">
		<input type="hidden" name="email" value="'.$params['clientdetails']['email'].'">
		<input type="hidden" name="cpf_cnpj" value="'.$params['clientdetails']['cpfcnpj'].'">
		<input type="hidden" name="due_date" value="'.$vencimento.'">
		'.$itens.'<input type="hidden" name="custom_variables[][name]" value="invoice_id">
		<input type="hidden" name="custom_variables[][value]" value="'.$params['invoiceid'].'">
		<input type="button" id="btnPagar" value="Pagar">
	</form>
	<script>
		$("#btnPagar").click(function(){
			$("#btnPagar").val("Gerando, aguarde...");
			$("#btnPagar").attr("disabled", true);
			var dados = $("#formulario").serialize();
			$.ajax({
				url: "modules/gateways/iugu/gerar.php",
				type: "POST",
				data: dados,
				success: function(result){
					var json = jQuery.parseJSON(result);
					if(json.status == "success"){
						$("#btnPagar").val("Redirecionando...");
						location.href=json.url;
					}else{
						$("#btnPagar").val("Erro ao gerar cobrança. Contate o suporte.");
					}
				},
				error: function(){
					$("#btnPagar").val("Erro ao gerar cobrança. Contate o suporte.");
				}
			});
		});
	</script>';
	
	return $code;
}

$GATEWAYMODULE["iuguname"]="iugu";
$GATEWAYMODULE["iuguvisiblename"]="Iugu v1.0";
$GATEWAYMODULE["iugutype"]="Invoices";
