<?php

require_once 'Telnet.php';

/**
 * <b>CLASS</b>
 * Classe responsável pelos métodos gerenciadores 
 * dos comandos que serão executados nos hosts.
 * 
 * @author    Igor Maximo <igormaximo_1989@hotmail.com>
 * @date      01/08/2019
 */
class CommandManipulator extends Telnet {

    var $telnet;
    var $result;

    /**
     * <b>FUNCTION</b>
     * <br>Método que executa comandos no host desejado.
     * 
     * OBS: Implemente seus comandos nesse método ou crie novos
     * 
     * @author    Igor Maximo <igormaximo_1989@hotmail.com>
     * @date      01/08/2019
     */
    public function set($hostIP, $user, $pass) {
        try {
            // Abre conexão com host/equipamento
            $this->result = $this->conectarViaTelnet($hostIP, $user, $pass);
            ////////////////////////////////////////////////////////
            //                       PADRÃO                       //
            ////////////////////////////////////////////////////////
            sleep(2);  
            $this->executaComandoShellDelay('cd gpononu', $this->result, 2);
            $this->executaComandoShellDelay('', $this->result, 2); // [ENTER]
            $this->executaComandoShellDelay('show discovery slot all link all', $this->result, 3); // Lista todos as onus que estão solicitando autorização de todos os slots e todas as pons
            $this->desconectar();
            // Retorne a resposta da operação ou trate os dados aqui...
            return $this->result;
        } catch (Exception $ex) {
            // Faça algo...
        }
    }

}
