<?php
 
require_once 'CommandManipulator.php';

$manipuladorComando = new CommandManipulator();

$hostIP = "200.16.100.100";
$hostUser = "admin";
$hostPass = "987654321";

/**
 * $hostIP      = IP do Host destino desejado
 * $hostUser    = Usuário de login do host
 * $hostPass    = Senha de login do host
 */
$response = $manipuladorComando->set($hostIP, $hostUser, $hostPass);
// Faça algo com a resposta dos comandos...
print_r($response);

// ...
// ...
// ...
