<?php

/**
 * Classe que realiza acesso via TELNET em um ativo de rede
 * para executar comandos shell através de um LINUX.
 *
 * @modified    Igor Maximo
 * @date        01/08/2019
 */
abstract class Telnet {

    var $show_connect_error = 1;
    var $use_usleep = 0; // Troque para 1 para acelerar a execução, porém JAMAIS use 1 se for em Windows Server
    var $sleeptime = 125000; // 125000 padrão
    var $loginsleeptime = 1000000;
    var $fp = NULL;
    var $loginprompt;
    var $conn1;
    var $conn2;

    /**
     * 0 = SUCESSO = OK
     * 1 = Não é possível abrir uma conexão
     * 2 = Host desconhecido
     * 3 = Falha na autenticação/login
     * 4 = Versão do PhP muito antiga
     *
     * @author      Igor Maximo
     * @date        01/08/2019
     */
    public function conectarViaTelnet($server, $user, $pass) {
        $ipAtivoRede = $server;
        $rv = 0;
        $vers = explode('.', PHP_VERSION);
        $needvers = array(4, 3, 0);
        $j = count($vers);
        $k = count($needvers);
        if ($k < $j)
            $j = $k;
        for ($i = 0; $i < $j; $i++) {
            if (($vers[$i] + 0) > $needvers[$i])
                break;
            if (($vers[$i] + 0) < $needvers[$i]) {
                $this->errosConexao(4);
                return 4;
            }
        }
        $this->desconectar();
        if (strlen($server)) {
            if (preg_match('/[^0-9.]/', $server)) {
                $ip = gethostbyname($server);
                if ($ip == $server) {
                    $ip = '';
                    $rv = 2;
                }
            }
            else
                $ip = $server;
        }
        else
            $ip = '127.0.0.1';
        if (strlen($ip)) {
            if ($this->fp = fsockopen($ip, 23)) {
                fputs($this->fp, $this->conn1);
                $this->Sleep();
                fputs($this->fp, $this->conn2);
                $this->Sleep();
                $this->getRetorno($r);
                $r = explode("\n", $r);
                $this->loginprompt = $r[count($r) - 1];
                fputs($this->fp, "$user\r");
                $this->Sleep();
                fputs($this->fp, "$pass\r");
                if ($this->use_usleep)
                    usleep($this->loginsleeptime);
                else
                    sleep(1);
                $this->getRetorno($r);
                $r = explode("\n", $r);
                if (($r[count($r) - 1] == '') || ($this->loginprompt == $r[count($r) - 1])) {
                    $rv = 3;
                    $this->desconectar();
                }
            }
            else
                $rv = 1;
        }
        if ($rv)
            $this->errosConexao($rv);
        return $rv;
    }

    /**
     * <b>FUNCTION</b>
     * <br>Desconecta do host
     * 
     * @return    array Retorna da solicitação
     * @author    Igor Maximo <igormaximo_1989@hotmail.com>
     * @date      30/06/2020
     */
    function desconectar($exit = 1) {
        if ($this->fp) {
            if ($exit)
                $this->executaComandoShell('quit', $junk);
            fclose($this->fp);
            $this->fp = NULL;
        }
    }

    /**
     * <b>FUNCTION</b>
     * <br>Set executar comando e aguardar a resposta
     * 
     * @return    array Retorna da solicitação
     * @author    Igor Maximo <igormaximo_1989@hotmail.com>
     * @date      30/06/2020
     */
    function executaComandoShell($c, &$r) {
        if ($this->fp) {
            fputs($this->fp, "$c\r"); // Comando
            $this->Sleep();
            //sleep(3);
            $this->getRetorno($r);
            $r = preg_replace("/^.*?\n(.*)\n[^\n]*$/", "$1", $r);
        }
        $this->Sleep();
        return $this->fp ? 1 : 0;
    }

    /**
     * <b>FUNCTION</b>
     * <br>Set executar comando e aguardar a resposta
     * com sleep personalizado
     * 
     * @return    array Retorna da solicitação
     * @author    Igor Maximo <igormaximo_1989@hotmail.com>
     * @date      30/06/2020
     */
    function executaComandoShellDelay($c, &$r, $sleep) { // Com tempo de delay para melhor coleta do retorno
        if ($this->fp) {
            fputs($this->fp, "$c\r"); // Comando
            $this->Sleep();
            sleep($sleep);
            $this->getRetorno($r);
            $r = preg_replace("/^.*?\n(.*)\n[^\n]*$/", "$1", $r);
        }
        $this->Sleep();
        return $this->fp ? 1 : 0;
    }

    /**
     * <b>FUNCTION</b>
     * <br>Captura o retorno do comando executado
     * 
     * @return    array Retorna da solicitação
     * @author    Igor Maximo <igormaximo_1989@hotmail.com>
     * @date      30/06/2020
     */
    function getRetorno(&$r) {
        $r = '';
        do {
            $r .= fread($this->fp, 100000); // 100000
            $s = socket_get_status($this->fp);
        }
        while ($s['unread_bytes']);

        // Retorno dos comandos (DESCOMENTE APENAS EM AMBIENTE DEV)
        //  echo "<pre style='color: #32CD32; background-color: #111; padding: 10px;'>" . str_replace("<", "", $r) . "</pre>"; // Imprime o retorno
    }

    // Para reduzir a velocidade de execução dos comandos
    function Sleep() {
        if ($this->use_usleep) {
            usleep($this->sleeptime);
        }
        else {
            sleep(1);
        }
    }

    function Telnet() {
        $this->conn1 = chr(0xFF) . chr(0xFB) . chr(0x1F) . chr(0xFF) . chr(0xFB) .
                chr(0x20) . chr(0xFF) . chr(0xFB) . chr(0x18) . chr(0xFF) . chr(0xFB) .
                chr(0x27) . chr(0xFF) . chr(0xFD) . chr(0x01) . chr(0xFF) . chr(0xFB) .
                chr(0x03) . chr(0xFF) . chr(0xFD) . chr(0x03) . chr(0xFF) . chr(0xFC) .
                chr(0x23) . chr(0xFF) . chr(0xFC) . chr(0x24) . chr(0xFF) . chr(0xFA) .
                chr(0x1F) . chr(0x00) . chr(0x50) . chr(0x00) . chr(0x18) . chr(0xFF) .
                chr(0xF0) . chr(0xFF) . chr(0xFA) . chr(0x20) . chr(0x00) . chr(0x33) .
                chr(0x38) . chr(0x34) . chr(0x30) . chr(0x30) . chr(0x2C) . chr(0x33) .
                chr(0x38) . chr(0x34) . chr(0x30) . chr(0x30) . chr(0xFF) . chr(0xF0) .
                chr(0xFF) . chr(0xFA) . chr(0x27) . chr(0x00) . chr(0xFF) . chr(0xF0) .
                chr(0xFF) . chr(0xFA) . chr(0x18) . chr(0x00) . chr(0x58) . chr(0x54) .
                chr(0x45) . chr(0x52) . chr(0x4D) . chr(0xFF) . chr(0xF0);
        $this->conn2 = chr(0xFF) . chr(0xFC) . chr(0x01) . chr(0xFF) . chr(0xFC) .
                chr(0x22) . chr(0xFF) . chr(0xFE) . chr(0x05) . chr(0xFF) . chr(0xFC) . chr(0x21);
    }

    function errosConexao($num) {
        if ($this->show_connect_error) {
            switch ($num) {
                case 1:
                    print "Falha na conexão: não foi possível abrir a conexão de rede.";
                    break;
                case 2:
                    print "Falha na conexão: host desconhecido";
                    break;
                case 3:
                    print "Falha na conexão: Falha no login";
                    break;
                case 4:
                    print "Falha na conexão: Falha no login.";
                    break;
            }
        }
        return;
    }

}
