<?php
    header('Content-type: text/html; charset=utf-8');
    include '../OB_init.php';

    $ob = new OB('237');

    //*
    $ob->Vendedor

            ->setAgencia('5986')
            ->setConta('0000174')
            ->setCarteira('06')
            ->setRazaoSocial('Condominio Villa Verde')
            ->setCNPJ("00.995.009/0001-80")
            //->setCpf('012.345.678-39')
            ->setEndereco('Estrada Municipal SQE 120, Araçariguama/SP CEP 18.147-000')
            ->setEmail('maraetome@hotmail.com')
			->setCodigoCedente('0001740')
			->setInsertDVAtPosFour(false)
        ;

    $ob->Configuracao
            ->setLocalPagamento('Pagável em qualquer banco até o vencimento');
    $ob->Configuracao->addInstrucao("Sr. Caixa, cobrar multa de 2% e após 1% de mora após o vencimento");
    $ob->Configuracao->addInstrucao("Receber até 30 dias após o vencimento");
	$ob->Configuracao->addInstrucao("Em caso de dúvidas entre em contato conosco: atendimento@villaverde.com.br");
	$ob->Configuracao->addInstrucao("Emitido pelo sistema Condgest - www.condgest.com.br");
    
    $ob->Template
            ->setTitle('PHP->OB ObjectBoleto')
            ->setTemplate('html5')
        ;

    $ob->Cliente
            ->setNome('Jesse Formigoni Machado')
            ->setCpf('331.919.348-12')
            ->setEmail('jeform@gmail.com')
            ->setEndereco('')
            ->setCidade('')
            ->setUf('')
            ->setCep('')
        ;

    $ob->Boleto
            ->setValor(5.00)
            //->setDiasVencimento(5)
            ->setVencimento(31,7,2013)
            ->setNossoNumero('9907201301')
            ->setNumDocumento('9907201301')
            ->setQuantidade(1)
        ;

    $ob->render(); /**/
