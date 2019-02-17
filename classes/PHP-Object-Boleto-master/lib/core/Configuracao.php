<?php //18/05/2010

class Configuracao{
    public $Instrucoes = array();
    public $LocalPagamento;
    
    /**
      *
      * @version 0.1 18/05/2011 Initial
      *
      */
    public function __construct(&$obj){
        $this->parent = $obj;
    }
    
    /**
      *
      * @version 0.1 18/05/2011 Initial
      *
      */
    public function addInstrucao($frase){
        if(!empty($frase)){
            $this->Instrucoes[] = $frase;
        }        
        return $this;
    }
    
    /**
      *
      * @version 0.1 19/05/2011 Initial
      *
      */
    public function setLocalPagamento($frase){
        if(!empty($frase)){
            $this->LocalPagamento = $frase;
        }        
        return $this;
    }
    
}